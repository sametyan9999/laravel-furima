<?php

namespace Tests\Feature\Purchase;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Condition;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ChangeAddressTest extends TestCase
{
    use RefreshDatabase;

    private function makeOnSaleItem(User $seller): Item
    {
        $condition = Condition::factory()->create(['name' => '新品']);
        return Item::factory()->create([
            'user_id'      => $seller->id,
            'condition_id' => $condition->id,
            'status'       => 'on_sale',
            'name'         => '住所変更テスト商品',
            'price'        => 6081,
        ]);
    }

    /**
     * 送付先住所変更画面にて登録した住所が商品購入画面に反映されている
     */
    public function test_送付先住所変更画面にて登録した住所が商品購入画面に反映されている(): void
    {
        $seller = User::factory()->create();
        $buyer  = User::factory()->create();

        // 初期プロフィール
        $buyer->profile()->create([
            'postal_code'   => '111-1111',
            'address_line1' => '東京都旧住所1-1-1',
            'address_line2' => null,
        ]);

        $item = $this->makeOnSaleItem($seller);

        // 住所を新規登録（PG07）
        $this->actingAs($buyer)
            ->put(route('purchase.address.update', $item), [
                'postal_code'   => '123-4567',
                'address_line1' => '東京都テスト区1-2-3',
                'address_line2' => 'テストビル101',
                'phone'         => '0312345678',
            ])
            ->assertRedirect(route('purchase.index', $item));

        // PG06 に反映されていること
        $this->actingAs($buyer)
            ->get(route('purchase.index', $item))
            ->assertOk()
            ->assertSee('123-4567')
            ->assertSee('東京都テスト区1-2-3')
            ->assertSee('テストビル101');
    }

    /**
     * 購入した商品に送付先住所が紐づいて登録される
     */
    public function test_購入した商品に送付先住所が紐づいて登録される(): void
    {
        $seller = User::factory()->create();
        $buyer  = User::factory()->create();

        // 住所を登録済みにしておく
        $buyer->profile()->create([
            'postal_code'   => '123-4567',
            'address_line1' => '東京都テスト区1-2-3',
            'address_line2' => 'テストビル101',
            'phone'         => '0312345678',
        ]);

        $item = $this->makeOnSaleItem($seller);

        // 購入確定（PG06）
        $this->actingAs($buyer)
            ->post(route('purchase.store', $item), [
                'payment_method' => 'convenience',
            ])
            ->assertRedirect(route('items.index'));

        // purchases に送付先住所が紐づいている
        $this->assertDatabaseHas('purchases', [
            'user_id'              => $buyer->id,
            'item_id'              => $item->id,
            'shipping_postal_code' => '123-4567',
            'shipping_address1'    => '東京都テスト区1-2-3',
            'shipping_address2'    => 'テストビル101',
        ]);
    }
}