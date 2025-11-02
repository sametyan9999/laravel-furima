<?php

namespace Tests\Feature\Items;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Like;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ItemSearchTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * 「商品名」で部分一致検索できる
     */
    public function 商品名で部分一致検索できる()
    {
        Item::factory()->create(['name' => 'りんご時計']);
        Item::factory()->create(['name' => 'バナナ時計']);

        // 検索キーワード q=りんご
        $res = $this->get('/?q=りんご');

        $res->assertOk();
        $res->assertSee('りんご時計');
        $res->assertDontSee('バナナ時計');
    }

    /**
     * @test
     * 検索キーワードがマイリストでも保持される
     */
    public function 検索キーワードがマイリストでも保持される()
    {
        $user = User::factory()->create();

        // キーワード「りんご」にマッチする/しない商品を用意
        $matchLiked    = Item::factory()->create(['name' => 'りんごバッグ']);
        $notMatchLiked = Item::factory()->create(['name' => 'バナナバッグ']);
        $others        = Item::factory()->create(['name' => 'みかんバッグ']); // 参照外

        // ユーザーが2件に「いいね」
        Like::factory()->create(['user_id' => $user->id, 'item_id' => $matchLiked->id]);
        Like::factory()->create(['user_id' => $user->id, 'item_id' => $notMatchLiked->id]);

        // 1) 一覧で検索 → セッションに q を保存
        $this->actingAs($user)->get('/?q=りんご')->assertOk();

        // 2) マイリストタブへ遷移（セッション q を流用）
        $res = $this->get('/?tab=mylist');

        $res->assertOk();
        // マイリスト見出し
        $res->assertSee('いいね済み商品');
        // キーワードに一致した「いいね」だけが表示
        $res->assertSee('りんごバッグ');
        $res->assertDontSee('バナナバッグ');
        $res->assertDontSee('みかんバッグ');
    }
}