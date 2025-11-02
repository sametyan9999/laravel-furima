<?php

namespace Tests\Feature\Items;

use App\Models\User;
use App\Models\Item;
use App\Models\Purchase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ItemIndexTest extends TestCase
{
    use RefreshDatabase;

    private const INDEX = '/';

    /** @test */
    public function 全商品を取得できる()
    {
        Item::factory()->count(3)->create(['status' => 'on_sale']);

        $res = $this->get(self::INDEX);
        $res->assertOk();
        $res->assertSee('商品一覧');
    }

    /** @test */
    public function 購入済み商品は_sold_と表示される()
    {
        $seller = User::factory()->create();
        $buyer  = User::factory()->create();

        $item = Item::factory()->create([
            'user_id' => $seller->id,
            'name'    => '購入済みテスト商品',
            'status'  => 'sold', // ← SOLD バッジ判定用（ビュー側条件に合わせる）
        ]);

        // 購入レコード（アクセサ is_sold 用の保険）
        Purchase::create([
            'id'                        => (string) Str::uuid(),
            'user_id'                   => $buyer->id,
            'item_id'                   => $item->id,
            'amount'                    => $item->price ?? 1000,
            'payment_method'            => 'card',
            'payment_status'            => 'paid',
            'purchased_at'              => now(),
            'shipping_name'             => $buyer->name,
            'shipping_postal_code'      => '123-4567',
            'shipping_address1'         => '東京都',
            'shipping_address2'         => '港区1-1-1',
        ]);

        $res = $this->get(self::INDEX);
        $res->assertOk();
        $res->assertSee('購入済みテスト商品');
        $res->assertSee('Sold'); // ビュー表記が「SOLD」「売却済み」の場合は合わせて変更
    }

    /** @test */
    public function 自分の出品した商品は表示されない()
    {
        $me    = User::factory()->create();
        $other = User::factory()->create();

        // 自分出品
        Item::factory()->create([
            'user_id' => $me->id,
            'name'    => '自分の商品',
            'status'  => 'on_sale',
        ]);

        // 他人出品
        Item::factory()->create([
            'user_id' => $other->id,
            'name'    => '他人の商品',
            'status'  => 'on_sale',
        ]);

        $this->be($me);

        $res = $this->get(self::INDEX);
        $res->assertOk();
        $res->assertDontSee('自分の商品');
        $res->assertSee('他人の商品');
    }
}