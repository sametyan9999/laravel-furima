<?php

namespace Tests\Feature\Items;

use App\Models\Item;
use App\Models\Like;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MylistIndexTest extends TestCase
{
    use RefreshDatabase;

    private const INDEX = '/mylist';

    /** @test */
    public function いいね済み商品だけが表示される(): void
    {
        $user = User::factory()->create();

        $likedItem   = Item::factory()->create();
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
    public function 購入済み商品には_sold_と表示される(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        // マイリストに入っている想定
        Like::factory()->create([
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        // 購入済みにする
        Purchase::factory()->create([
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $this->actingAs($user);
        $response = $this->get(self::INDEX);

        $response->assertOk();
        $response->assertSee('Sold');
    }

    /** @test */
    public function 未認証の場合は何も表示されない(): void
    {
        $response = $this->get(self::INDEX);

        $response->assertRedirect('/login');
    }
}