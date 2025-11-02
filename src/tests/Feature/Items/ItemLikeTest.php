<?php

namespace Tests\Feature\Items;

use App\Models\User;
use App\Models\Item;
use App\Models\Like;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemLikeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * いいねアイコンを押下でいいねした商品として登録することができる
     */
    public function いいねアイコンを押下でいいねした商品として登録することができる()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $this->actingAs($user)
            ->post(route('items.like', $item));

        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);
    }

    /**
     * @test
     * 追加済みのアイコンは色が変化する
     */
    public function 追加済みのアイコンは色が変化する()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        Like::factory()->create([
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $this->actingAs($user);

        // 詳細ページで「いいね済み」スタイルが付くことを確認
        $res = $this->get(route('items.show', $item));
        $res->assertOk();
        $res->assertSee('icon-like is-liked'); // 実装に合わせたクラス名
    }

    /**
     * @test
     * 再度いいねアイコンを押下でいいねを解除することができる
     */
    public function 再度いいねアイコンを押下でいいねを解除することができる()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        Like::factory()->create([
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $this->actingAs($user)
            ->delete(route('items.unlike', $item));

        $this->assertDatabaseMissing('likes', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);
    }
}