<?php

namespace Tests\Feature\Purchase;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Condition;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentMethodTest extends TestCase
{
    use RefreshDatabase;

    private function makeBuyerWithProfile(): User
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

    private function makeOnSaleItem(): Item
    {
        $seller    = User::factory()->create();
        $condition = Condition::factory()->create(['name' => '新品']);

        return Item::factory()->create([
            'user_id'      => $seller->id,
            'condition_id' => $condition->id,
            'status'       => 'on_sale',
            'name'         => '支払いテスト商品',
            'price'        => 6081,
        ]);
    }

    /** 小計画面で変更が反映される（選択した支払い方法が正しく反映される） */
    public function test_小計画面で変更が反映される(): void
    {
        $buyer = $this->makeBuyerWithProfile();
        $item  = $this->makeOnSaleItem();

        // 1) デフォルト：コンビニ払いが選択
        $res = $this->actingAs($buyer)->get(route('purchase.index', $item));
        $res->assertOk()->assertSee('支払い方法')->assertSee('コンビニ払い');

        $html = $res->getContent();
        // convenience が selected、card には selected が付かない
        $this->assertMatchesRegularExpression(
            '/<option[^>]*value=(?:"|\')convenience(?:"|\')[^>]*selected[^>]*>/i',
            $html
        );
        $this->assertDoesNotMatchRegularExpression(
            '/<option[^>]*value=(?:"|\')card(?:"|\')[^>]*selected[^>]*>/i',
            $html
        );

        // 2) ?payment_method=card で「カード支払い」に更新
        $res2 = $this->actingAs($buyer)
            ->get(route('purchase.index', ['item' => $item->id, 'payment_method' => 'card']));
        $res2->assertOk()->assertSee('カード支払い');

        $html2 = $res2->getContent();
        // card が selected、convenience には selected が付かない
        $this->assertMatchesRegularExpression(
            '/<option[^>]*value=(?:"|\')card(?:"|\')[^>]*selected[^>]*>/i',
            $html2
        );
        $this->assertDoesNotMatchRegularExpression(
            '/<option[^>]*value=(?:"|\')convenience(?:"|\')[^>]*selected[^>]*>/i',
            $html2
        );
    }
}