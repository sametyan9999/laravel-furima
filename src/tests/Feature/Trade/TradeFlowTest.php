<?php

namespace Tests\Feature;

use App\Mail\TradeCompletedMail;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\TradeMessage;
use App\Models\TradeReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TradeFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * テスト用の取引（購入者・出品者・商品・購入レコード）を作るヘルパ
     */
    private function createTrade(?User $buyer = null, ?User $seller = null): array
    {
        $buyer  = $buyer  ?? User::factory()->create();
        $seller = $seller ?? User::factory()->create();

        $item = Item::factory()->for($seller)->create();

        // Purchase 側のリレーション名が "user" 前提
        $purchase = Purchase::factory()
            ->for($buyer, 'user')
            ->for($item)
            ->create([
                'is_done' => 0,
            ]);

        return compact('buyer', 'seller', 'item', 'purchase');
    }

    /**
     * US001 / FN001 / FN002 / FN005
     * 取引中商品確認機能・取引チャット遷移機能・取引商品新規通知確認機能
     */
    /** @test */
    public function ユーザーは取引チャットを確認することができる_取引中商品確認機能()
    {
        $this->withoutExceptionHandling();

        $trade    = $this->createTrade();
        $buyer    = $trade['buyer'];
        $seller   = $trade['seller'];
        $purchase = $trade['purchase'];
        $item     = $trade['item'];

        // 相手（出品者）からの未読メッセージを 3 件作成
        TradeMessage::factory()->count(3)->create([
            'user_id'     => $seller->id,
            'purchase_id' => $purchase->id,
            'body'        => 'test',
            'is_deleted'  => false,
        ]);

        $response = $this->actingAs($buyer)->get('/mypage?view=trade');

        $response->assertStatus(200);

        // 商品名が表示されている
        $response->assertSee($item->name, false);

        // 未読件数バッジ（3）が表示されている
        $response->assertSee('<span class="badge-unread">3</span>', false);

        // 取引チャット画面へのリンクが張られている
        $response->assertSee(route('trade.show', $purchase->id), false);
    }

    /**
     * US001 / FN003 / FN004
     * 別取引遷移機能・取引自動ソート機能
     */
    /** @test */
    public function ユーザーは取引チャットを確認することができる_別取引遷移機能()
    {
        $this->withoutExceptionHandling();

        // 1件目の取引（ログインユーザーは buyer）
        $trade1  = $this->createTrade();
        $buyer   = $trade1['buyer'];
        $seller1 = $trade1['seller'];
        $p1      = $trade1['purchase'];

        // 同じ buyer が関わる 2件目の取引を作成
        $trade2  = $this->createTrade($buyer); // buyer は同じ、seller は別ユーザー
        $seller2 = $trade2['seller'];
        $p2      = $trade2['purchase'];

        // p1 より p2 の方が新しいメッセージ
        TradeMessage::factory()->create([
            'user_id'     => $seller1->id,
            'purchase_id' => $p1->id,
            'body'        => 'old',
            'created_at'  => now()->subMinute(),
        ]);

        TradeMessage::factory()->create([
            'user_id'     => $seller2->id,
            'purchase_id' => $p2->id,
            'body'        => 'new',
            'created_at'  => now(),
        ]);

        // p1 で /trade/{purchase} を開く
        $response = $this->actingAs($buyer)->get(route('trade.show', $p1));

        $response->assertStatus(200);
        $response->assertSee('その他の取引', false);

        // サイドバーにもう一方の取引(p2)へのリンクがある
        $response->assertSee(route('trade.show', $p2->id), false);
    }

    /**
     * US002(チャット) / FN006 / FN007 / FN008 / FN009
     * 取引チャット機能・バリデーション・エラーメッセージ表示・入力情報保持機能
     */
    /** @test */
    public function ユーザーは取引チャットの投稿をすることができる_バリデーション()
    {
        Storage::fake('public');

        $trade    = $this->createTrade();
        $buyer    = $trade['buyer'];
        $purchase = $trade['purchase'];

        // 本文は入力するが、画像を PDF で送信してバリデーションエラーを発生させる
        $response = $this->actingAs($buyer)
            ->from(route('trade.show', $purchase))
            ->post(route('trade.store', $purchase), [
                'body'  => 'これは下書き本文です',
                'image' => UploadedFile::fake()->create('dummy.pdf', 10, 'application/pdf'),
            ]);

        // 画像バリデーションエラー
        $response->assertSessionHasErrors(['image']);

        // リダイレクト先の画面 HTML を取得
        $followed = $this->followingRedirects()->get(route('trade.show', $purchase));

        // エラーメッセージ（仕様どおりの文言）が出ている
        $followed->assertSee('「.png」または「.jpeg」形式でアップロードしてください', false);

        // 入力していた本文が value="" として残っている
        $followed->assertSee('value="これは下書き本文です"', false);
    }

    /**
     * US003 / FN010 / FN011
     * メッセージ編集機能・メッセージ削除機能
     */
    /** @test */
    public function ユーザーは取引チャットの編集、削除をすることができる()
    {
        $this->withoutExceptionHandling();

        $trade    = $this->createTrade();
        $buyer    = $trade['buyer'];
        $purchase = $trade['purchase'];

        $message = TradeMessage::factory()->create([
            'user_id'     => $buyer->id,
            'purchase_id' => $purchase->id,
            'body'        => '元の本文',
            'is_deleted'  => false,
        ]);

        // 編集（message_id を付けて POST）
        $this->actingAs($buyer)->post(route('trade.store', $purchase), [
            'message_id' => $message->id,
            'body'       => '編集後の本文',
            'image'      => null,
        ])->assertRedirect(route('trade.show', $purchase));

        $this->assertDatabaseHas('trade_messages', [
            'id'   => $message->id,
            'body' => '編集後の本文',
        ]);

        // 削除（論理削除）
        $this->actingAs($buyer)
            ->delete(route('trade.delete', [$purchase, $message]))
            ->assertRedirect();

        $this->assertDatabaseHas('trade_messages', [
            'id'         => $message->id,
            'is_deleted' => true,
        ]);
    }

    /**
     * US004 / US005
     * 取引後評価機能（購入者）・取引後画面遷移・メール送信・メール送信機能
     */
    /** @test */
    public function 出品ユーザーは取引完了をメールで確認することができる()
    {
        Mail::fake();

        $trade    = $this->createTrade();
        $buyer    = $trade['buyer'];
        $seller   = $trade['seller'];
        $purchase = $trade['purchase'];

        // 1. 購入者が「取引を完了する」ボタンを押す
        $response = $this->actingAs($buyer)
            ->from(route('trade.show', $purchase))
            ->followingRedirects()
            ->post(route('trade.finish', $purchase));

        // モーダルの文言が表示されている（US004 / FN012）
        $response->assertSee('今回の取引相手はいかがでしたか？', false);

        // 2. 評価を送信
        $this->actingAs($buyer)->post(route('trade.review.store', $purchase), [
            'rating' => 5,
        ])->assertRedirect(route('items.index'));

        // DB にレビューが作成されている
        $this->assertDatabaseHas('trade_reviews', [
            'purchase_id' => $purchase->id,
            'reviewer_id' => $buyer->id,
            'target_id'   => $seller->id,
            'score'       => 5,
        ]);

        // 取引完了フラグが立っている（US004 / FN012）
        $this->assertTrue($purchase->fresh()->is_done == 1);

        // 出品者にメールが送られている（US005 / FN015 / FN016）
        Mail::assertSent(TradeCompletedMail::class, function (TradeCompletedMail $mail) use ($purchase, $seller) {
            return $mail->purchase->is($purchase)
                && in_array($seller->email, array_column($mail->to, 'address'));
        });
    }

    /**
     * US004 / FN013 / FN014 / US002(平均)
     * 取引後評価機能（出品者）・評価平均確認機能
     */
    /** @test */
    public function ユーザーは取引をしたユーザーを評価することができる_評価平均確認機能()
    {
        $this->withoutExceptionHandling();

        $trade    = $this->createTrade();
        $buyer    = $trade['buyer'];
        $seller   = $trade['seller'];
        $purchase = $trade['purchase'];

        // すでに購入者が評価済み + 取引完了という状態を作る
        TradeReview::factory()->create([
            'purchase_id' => $purchase->id,
            'reviewer_id' => $buyer->id,
            'target_id'   => $seller->id,
            'score'       => 5,
        ]);

        $purchase->update(['is_done' => 1]);

        // 出品者が /trade/{purchase} を開く
        $response = $this->actingAs($seller)->get(route('trade.show', $purchase));
        $response->assertStatus(200);

        // 出品者が購入者を評価（3点）
        $this->actingAs($seller)->post(route('trade.review.store', $purchase), [
            'rating' => 3,
        ])->assertRedirect(route('items.index'));

        // 購入者側に 3点のレビューが付いている
        $this->assertDatabaseHas('trade_reviews', [
            'purchase_id' => $purchase->id,
            'reviewer_id' => $seller->id,
            'target_id'   => $buyer->id,
            'score'       => 3,
        ]);

        // ★ 平均値の確認（購入者側に 3点、出品者側に 5点 → それぞれ getReviewAverage() で計算）
        $this->assertSame(5, $seller->fresh()->getReviewAverage()); // seller は 5 のみ
        $this->assertSame(3, $buyer->fresh()->getReviewAverage());  // buyer は 3 のみ
    }
}