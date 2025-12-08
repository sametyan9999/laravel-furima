<?php

namespace App\Http\Controllers;

use App\Mail\TradeCompletedMail;
use App\Models\Purchase;
use App\Models\TradeReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class TradeReviewController extends Controller
{
    /**
     * レビュー投稿処理
     * POST /trade/{purchase}/review
     *
     * 要件:
     * - 購入者 / 出品者の両方が評価できる
     * - 同じ人が同じ取引を複数回評価できない
     * - 「商品購入者が取引を完了すると、出品者に完了メール」が送られる
     */
    public function store(Request $request, Purchase $purchase)
    {
        $user = Auth::user();

        // ▼ 自分が購入者か出品者か判定
        $isBuyer  = ($purchase->user_id === $user->id);
        $isSeller = ($purchase->item->user_id === $user->id);

        // どちらでもなければ権限なし
        if (!$isBuyer && !$isSeller) {
            abort(403);
        }

        // ▼ バリデーション（FN007 / FN008）
        $validated = $request->validate(
            [
                'rating' => ['required', 'integer', 'between:1,5'],
            ],
            [
                'rating.required' => '評価を選択してください',
                'rating.integer'  => '評価の形式が不正です',
                'rating.between'  => '評価は1〜5で入力してください',
            ]
        );

        // ▼ 既に自分がこの取引を評価済みなら弾く
        $exists = TradeReview::where('purchase_id', $purchase->id)
            ->where('reviewer_id', $user->id)
            ->exists();

        if ($exists) {
            return redirect()
                ->route('items.index')
                ->with('status', 'この取引はすでに評価済みです。');
        }

        // ▼ 評価対象ユーザー（購入者 / 出品者）
        $targetId = $isBuyer
            ? $purchase->item->user_id   // 自分が購入者 → 出品者を評価
            : $purchase->user_id;        // 自分が出品者 → 購入者を評価

        // ▼ レビュー登録
        TradeReview::create([
            'purchase_id' => $purchase->id,
            'reviewer_id' => $user->id,
            'target_id'   => $targetId,
            'score'       => $validated['rating'],
            'comment'     => null, // コメントは今回必須ではない
        ]);

        // ▼ 「商品購入者が取引を完了すると、出品者にメール」
        //   → 購入者が初めてレビューしたタイミングを「取引完了」とみなす
        if ($isBuyer) {

            // 取引完了フラグ
            if (!$purchase->is_done) {
                $purchase->is_done = true;
                $purchase->save();
            }

            // 出品者へ完了メール送信（Mailhog/Mailtrap で確認）
            $seller = $purchase->item->user;
            if ($seller && $seller->email) {
                Mail::to($seller->email)->send(
                    new TradeCompletedMail($purchase)
                );
            }
        }

        // 要件：評価後は商品一覧へ遷移（FN014）
        return redirect()
            ->route('items.index')
            ->with('status', '評価を送信しました');
    }
}