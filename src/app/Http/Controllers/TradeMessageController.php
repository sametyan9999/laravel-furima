<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\TradeMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TradeMessageController extends Controller
{
    /**
     * 取引チャット画面
     */
    public function index(Request $request, Purchase $purchase)
    {
        $this->authorizeAccess($purchase);

        $me   = Auth::user();
        $item = $purchase->item;

        // 相手ユーザー
        $otherUser = $purchase->user_id === $me->id
            ? $item->user
            : $purchase->user;

        // 「編集対象メッセージ」があるか（?edit=◯）
        $editingMessage = null;
        if ($editId = $request->query('edit')) {
            $editingMessage = TradeMessage::where('purchase_id', $purchase->id)
                ->where('id', $editId)
                ->where('user_id', $me->id) // 自分のメッセージだけ
                ->first();
        }

        // サイドバーの取引一覧
        $sidebarTrades = Purchase::with('item')
            ->where(function ($q) use ($me) {
                $q->where('user_id', $me->id)
                    ->orWhereHas('item', function ($q2) use ($me) {
                        $q2->where('user_id', $me->id);
                    });
            })
            ->orderByDesc('purchased_at')
            ->get()
            ->map(function ($p) {
                return (object) [
                    'id'    => $p->id,
                    'name'  => $p->item?->name ?? '',
                    'image' => $p->item?->image ?? '',
                ];
            });

        // メッセージ一覧
        $messages = TradeMessage::with('user:id,name')
            ->where('purchase_id', $purchase->id)
            ->orderBy('created_at')
            ->get();

        return view('trade.index', [
            'purchase'       => $purchase,
            'item'           => $item,
            'messages'       => $messages,
            'me'             => $me,
            'otherUser'      => $otherUser,
            'sidebarTrades'  => $sidebarTrades,
            'editingMessage' => $editingMessage,
        ]);
    }

    /**
     * メッセージ投稿（新規 or 編集）
     */
    public function store(Request $request, Purchase $purchase)
    {
        $this->authorizeAccess($purchase);

        $data = $request->validate([
            'message_id' => ['nullable', 'integer'],
            'body'       => ['nullable', 'string', 'max:2000'],
            'image'      => ['nullable', 'image', 'max:2048'],
        ]);

        // 本文も画像も空の場合はエラー
        if (empty($data['body']) && !$request->hasFile('image')) {
            return back()->withErrors([
                'message' => 'メッセージまたは画像を入力してください',
            ]);
        }

        // 画像保存
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('trade_messages', 'public');
        }

        $messageId = $data['message_id'] ?? null;

        if ($messageId) {
            // ▼ 編集モード：既存メッセージを更新
            $message = TradeMessage::where('purchase_id', $purchase->id)
                ->where('id', $messageId)
                ->firstOrFail();

            // 自分のメッセージ以外は編集不可
            if ($message->user_id !== Auth::id()) {
                abort(403);
            }

            $update = [
                'body' => $data['body'] ?? $message->body,
            ];

            if ($imagePath !== null) {
                $update['image_path'] = $imagePath;
            }

            $message->update($update);
        } else {
            // ▼ 新規メッセージ作成
            TradeMessage::create([
                'user_id'     => Auth::id(),
                'purchase_id' => $purchase->id,
                'body'        => $data['body'] ?? null,
                'image_path'  => $imagePath,
            ]);
        }

        return redirect()->route('trade.show', $purchase);
    }

    /**
     * 削除
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
     * 取引完了
     */
    public function finish(Purchase $purchase)
    {
        $this->authorizeAccess($purchase);

        $purchase->update(['is_done' => true]);

        return back()->with('success', '取引を完了しました');
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