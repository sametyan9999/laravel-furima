<?php

namespace Tests\Feature\Items;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Like;
use App\Models\Purchase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MylistIndexTest extends TestCase
{
    use RefreshDatabase;

    private const INDEX = '/items/mylist';

    /** @test */
    public function いいね済み商品だけが表示される()
    {
        $user = User::factory()->create();
        $likedItem = Item::factory()->create();
        $unlikedItem = Item::factory()->create();

        Like::factory()->create([
            'user_id' => $user->id,
            'item_id' => $likedItem->id,
        ]);

        $this->actingAs($user);
        $response = $this->get(self::INDEX);

        $response->assertOk();
        $response->assertSee($likedItem->name);
        $response->assertDontSee($unlikedItem->name);
    }

    /** @test */
    public function 購入済み商品には_Sold_と表示される()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();
        Like::factory()->create(['user_id' => $user->id, 'item_id' => $item->id]);
        Purchase::factory()->create(['item_id' => $item->id, 'user_id' => $user->id]);

        $this->actingAs($user);
        $response = $this->get(self::INDEX);

        $response->assertOk();
        $response->assertSee('Sold');
    }

    /** @test */
    public function 未認証の場合は何も表示されない()
    {
        $response = $this->get(self::INDEX);

        $response->assertRedirect('/login'); // 未ログイン時はログインページにリダイレクトされる
    }
}