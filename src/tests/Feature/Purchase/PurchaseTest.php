<?php

namespace Tests\Feature\Purchase;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Condition;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    /** 購入者のプロフィール（住所あり）を用意 */
    private function seedBuyerWithProfile(): User
    {
        $buyer = User::factory()->create();
        $buyer->profile()->create([
            'postal_code'   => '123-4567',
            'address_line1' => '東京都テスト区1-2-3',
            'address_line2' => 'テストビル101',
            'phone'         => '0312345678',
        ]);
        return $buyer;
    }

    /** 販売中商品の作成 */
    private function seedOnSaleItem(User $seller): Item
    {
        $condition = Condition::factory()->create(['name' => '新品']);
        return Item::factory()->create([
            'user_id'      => $seller->id,
            'condition_id' => $condition->id,
            'status'       => 'on_sale',
            'name'         => '購入テスト商品',
            'price'        => 6081,
        ]);
    }

    /** 「購入する」ボタンを押下すると購入が完了する */
    public function test_購入するボタンを押下すると購入が完了する(): void
    {
        $seller = User::factory()->create();
        $buyer  = $this->seedBuyerWithProfile();
        $item   = $this->seedOnSaleItem($seller);

        $this->actingAs($buyer)
             ->from(route('purchase.index', $item))
             ->post(route('purchase.store', $item), ['payment_method' => 'convenience'])
             ->assertRedirect(route('items.index')); // 購入後は一覧へ

        // 購入レコード & ステータス
        $this->assertDatabaseHas('purchases', [
            'user_id' => $buyer->id,
            'item_id' => $item->id,
        ]);
        $this->assertSame('sold', $item->fresh()->status);
    }

    /** 購入した商品は商品一覧画面にて sold と表示される */
    public function test_購入した商品は商品一覧画面にて_sold_と表示される(): void
    {
        $seller = User::factory()->create();
        $buyer  = $this->seedBuyerWithProfile();
        $item   = $this->seedOnSaleItem($seller);

        // 購入
        $this->actingAs($buyer)
             ->post(route('purchase.store', $item), ['payment_method' => 'convenience']);

        // 一覧で Sold ラベル確認（テンプレート上は "Sold" 表記）
        $this->get(route('items.index'))
             ->assertOk()
             ->assertSee('購入テスト商品')
             ->assertSee('Sold');
    }

    /** プロフィールと購入した商品一覧に追加されている */
    public function test_プロフィールと購入した商品一覧に追加されている(): void
    {
        $seller = User::factory()->create();
        $buyer  = $this->seedBuyerWithProfile();
        $item   = $this->seedOnSaleItem($seller);

        // 購入
        $this->actingAs($buyer)
             ->post(route('purchase.store', $item), ['payment_method' => 'convenience']);

        // マイページの「購入した商品」タブ（?view=buy）で表示されることを確認
        $this->actingAs($buyer)
             ->get(route('mypage.index', ['view' => 'buy']))
             ->assertOk()
             ->assertSee('購入テスト商品')
             ->assertSee($buyer->name);
    }
}