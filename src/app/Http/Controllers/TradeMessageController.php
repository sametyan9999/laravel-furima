<?php

namespace App\Http\Controllers;

use App\Http\Requests\TradeMessageRequest;
use App\Models\Purchase;
use App\Models\TradeMessage;
use App\Models\TradeReview;
use Illuminate\Support\Facades\Auth;

class TradeMessageController extends Controller
{
    /**
     * 取引チャット画面
     */
    public function index(\Illuminate\Http\Request $request, Purchase $purchase)
    {
        $this->authorizeAccess($purchase);

        $me   = Auth::user();
        $item = $purchase->item;

        // 相手ユーザー
        $otherUser = $purchase->user_id === $me->id
            ? $item->user
            : $purchase->user;

        // 編集対象メッセージ（?edit=◯）
        $editingMessage = null;
        if ($editId = $request->query('edit')) {
            $editingMessage = TradeMessage::where('purchase_id', $purchase->id)
                ->where('id', $editId)
                ->where('user_id', $me->id)
                ->first();
        }

        /**
         * サイドバー：未完了の取引のみ & 最新メッセージ順
         */
        $sidebarTrades = Purchase::with('item')
            ->where('is_done', 0)
            ->where(function ($q) use ($me) {
                $q->where('user_id', $me->id)
                    ->orWhereHas('item', function ($q2) use ($me) {
                        $q2->where('user_id', $me->id);
                    });
            })
            // ★ 最新メッセージがある順（ない取引は後ろに回る）
            ->orderByDesc(
                TradeMessage::selectRaw('MAX(trade_messages.created_at)')
                    ->whereColumn('trade_messages.purchase_id', 'purchases.id')
                    ->where('trade_messages.is_deleted', 0)
            )
            ->get()
            ->map(function ($p) {
                $item = $p->item;

                return (object)[
                    'id'        => $p->id,
                    'name'      => $item?->name ?? '',
                    // image_url アクセサがあればそちらを優先
                    'image_url' => $item?->image_url ?? null,
                    // 生の image カラムも控えで持つ
                    'image'     => $item?->image ?? '',
                ];
            });

        // メッセージ一覧
        $messages = TradeMessage::with('user:id,name')
            ->where('purchase_id', $purchase->id)
            ->orderBy('created_at')
            ->get();

        // この取引をすでに評価済みか
        $alreadyReviewed = TradeReview::where('purchase_id', $purchase->id)
            ->where('reviewer_id', $me->id)
            ->exists();

        return view('trade.index', [
            'purchase'        => $purchase,
            'item'            => $item,
            'messages'        => $messages,
            'me'              => $me,
            'otherUser'       => $otherUser,
            'sidebarTrades'   => $sidebarTrades,
            'editingMessage'  => $editingMessage,
            'alreadyReviewed' => $alreadyReviewed,
        ]);
    }

    /**
     * メッセージ投稿（新規 or 編集）
     */
    public function store(TradeMessageRequest $request, Purchase $purchase)
    {
        $this->authorizeAccess($purchase);

        // FormRequest のバリデーション結果
        $data = $request->validated();

        // 画像保存
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('trade_messages', 'public');
        }

        // ★ message_id は validated() には含めないので、request から直接取得する
        $messageId = $request->input('message_id');

        if ($messageId) {
            // 編集モード
            $message = TradeMessage::where('purchase_id', $purchase->id)
                ->where('id', $messageId)
                ->firstOrFail();

            if ($message->user_id !== Auth::id()) {
                abort(403);
            }

            $update = [
                'body' => $data['body'],
            ];

            if ($imagePath !== null) {
                $update['image_path'] = $imagePath;
            }

            $message->update($update);
        } else {
            // 新規メッセージ
            TradeMessage::create([
                'user_id'     => Auth::id(),
                'purchase_id' => $purchase->id,
                'body'        => $data['body'],
                'image_path'  => $imagePath,
            ]);
        }

        return redirect()->route('trade.show', $purchase);
    }

    /**
     * 削除（論理削除）
     */
    public function destroy(Purchase $purchase, TradeMessage $message)
    {
        $this->authorizeAccess($purchase);

        if ($message->user_id !== Auth::id()) {
            abort(403);
        }

        $message->update(['is_deleted' => true]);

        return back();
    }

    /**
     * 取引完了ボタン
     */
    public function finish(Purchase $purchase)
    {
        $this->authorizeAccess($purchase);

        $userId = Auth::id();

        if ($purchase->user_id !== $userId) {
            abort(403);
        }

        $alreadyReviewed = TradeReview::where('purchase_id', $purchase->id)
            ->where('reviewer_id', $userId)
            ->exists();

        if (!$alreadyReviewed) {
            // 未評価なら次画面でモーダル表示
            session()->flash('review_modal', true);
        } else {
            // 既に評価済みなら取引完了にする
            if (!$purchase->is_done) {
                $purchase->update(['is_done' => true]);
            }
        }

        return redirect()->route('trade.show', $purchase);
    }

    /**
     * 権限チェック
     */
    private function authorizeAccess(Purchase $purchase): void
    {
        $uid = Auth::id();

        if (
            $purchase->user_id !== $uid &&
            $purchase->item->user_id !== $uid
        ) {
            abort(403);
        }
    }
}