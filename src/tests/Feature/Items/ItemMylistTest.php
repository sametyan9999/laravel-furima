<?php

namespace Tests\Feature\Items;

use App\Models\User;
use App\Models\Item;
use App\Models\Like;
use App\Models\Purchase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MylistIndexTest extends TestCase
{
    use RefreshDatabase;

    private const INDEX = '/items?tab=mylist';

    /**
     * @test
     * いいねした商品だけが表示される
     */
    public function いいね済み商品だけが表示される()
    {
        $user = User::factory()->create();

        $liked = Item::factory()->create(['name' => 'いいね済み商品']);
        $notLiked = Item::factory()->create(['name' => '未いいね商品']);

        Like::factory()->create([
            'user_id' => $user->id,
            'item_id' => $liked->id,
        ]);

        $this->actingAs($user);
        $res = $this->get(self::INDEX);

        $res->assertOk();
        $res->assertSee('いいね済み商品');
        $res->assertDontSee('未いいね商品');
    }

    /**
     * @test
     * 購入済み商品には「Sold」と表示される
     */
    public function 購入済み商品には_Sold_と表示される()
    {
        $user = User::factory()->create();
        $buyer = User::factory()->create();

        $item = Item::factory()->create(['name' => '購入済み商品']);
        Like::factory()->create(['user_id' => $user->id, 'item_id' => $item->id]);

        Purchase::factory()->create([
            'user_id' => $buyer->id,
            'item_id' => $item->id,
        ]);

        $this->actingAs($user);
        $res = $this->get(self::INDEX);

        $res->assertOk();
        $res->assertSee('購入済み商品');
        $res->assertSee('Sold');
    }

    /**
     * @test
     * 未認証ユーザーはマイリストを見られない
     */
    public function 未認証ユーザーはマイリストを見られない()
    {
        $item = Item::factory()->create(['name' => '非ログイン商品']);
        $res = $this->get(self::INDEX);
        $res->assertRedirect('/login');
    }
}
