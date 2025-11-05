<?php

namespace Tests\Feature\Items;

use App\Models\Item;
use App\Models\Like;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemSearchTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * 「商品名」で部分一致検索できる
     */
    public function 商品名で部分一致検索できる(): void
    {
        Item::factory()->create(['name' => 'りんご時計']);
        Item::factory()->create(['name' => 'バナナ時計']);

        $res = $this->get('/?q=りんご');

        $res->assertOk();
        $res->assertSee('りんご時計');
        $res->assertDontSee('バナナ時計');
    }

    /**
     * @test
     * 検索キーワードがマイリストでも保持される
     */
    public function 検索キーワードがマイリストでも保持される(): void
    {
        $user = \App\Models\User::factory()->create();

        $matchLiked    = Item::factory()->create(['name' => 'りんごバッグ']);
        $notMatchLiked = Item::factory()->create(['name' => 'バナナバッグ']);
        $others        = Item::factory()->create(['name' => 'みかんバッグ']);

        Like::factory()->create(['user_id' => $user->id, 'item_id' => $matchLiked->id]);
        Like::factory()->create(['user_id' => $user->id, 'item_id' => $notMatchLiked->id]);

        $this->actingAs($user)->get('/?q=りんご')->assertOk();

        $res = $this->get('/?tab=mylist');

        $res->assertOk();
        $res->assertSee('いいね済み商品');
        $res->assertSee('りんごバッグ');
        $res->assertDontSee('バナナバッグ');
        $res->assertDontSee('みかんバッグ');
    }
}