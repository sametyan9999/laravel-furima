<?php

namespace Tests\Feature\Items;

use App\Models\User;
use App\Models\Item;
use App\Models\Category;
use App\Models\Condition;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemShowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 7-1) 必要な情報がすべて表示される
     */
    public function test_商品詳細ページに必要な情報が表示される()
    {
        // 前提データ
        $condition = Condition::factory()->create(['name' => '新品']);
        $catA = Category::factory()->create(['name' => '家電']);
        $catB = Category::factory()->create(['name' => '時計']);

        $item = Item::factory()->create([
            'name'          => '詳細テスト商品',
            'brand'         => 'TESTブランド',
            'description'   => 'テスト用商品です。',
            'condition_id'  => $condition->id,
            'price'         => 12345,
            'image'         => '/storage/test.png',
            'likes_count'   => 5,
            'comments_count'=> 2,
        ]);
        $item->categories()->sync([$catA->id, $catB->id]);

        $u1 = User::factory()->create(['name' => '山田太郎']);
        $u2 = User::factory()->create(['name' => '田中花子']);
        Comment::factory()->create(['user_id' => $u1->id, 'item_id' => $item->id, 'body' => '良い商品ですね']);
        Comment::factory()->create(['user_id' => $u2->id, 'item_id' => $item->id, 'body' => '購入を検討中です']);

        // 実行
        $res = $this->get("/item/{$item->id}");
        $res->assertOk();

        $html = $res->getContent();

        // 画像
        $res->assertSee('/storage/test.png');

        // 基本情報
        $res->assertSee('詳細テスト商品');
        $res->assertSee('TESTブランド');

        // 価格（12,345 / 12345 許容）
        $this->assertMatchesRegularExpression('/12[,，]?345/', $html);

        // 説明
        $res->assertSee('テスト用商品です。');

        // 商品情報（状態/カテゴリ）
        $res->assertSee('新品');
        $res->assertSee('家電');
        $res->assertSee('時計');

        // いいね数・コメント数
        $res->assertSee('5');          // いいね数表示
        $res->assertSee('コメント（2）'); // コメント数見出し

        // コメントのユーザー名＋本文
        $res->assertSee('山田太郎');
        $res->assertSee('良い商品ですね');
        $res->assertSee('田中花子');
        $res->assertSee('購入を検討中です');
    }

    /**
     * 7-2) 複数選択されたカテゴリが表示されているか
     */
    public function test_複数カテゴリが表示される()
    {
        $condition = Condition::factory()->create(['name' => '新品']);
        $cat1 = Category::factory()->create(['name' => 'バッグ']);
        $cat2 = Category::factory()->create(['name' => 'アクセサリー']);
        $cat3 = Category::factory()->create(['name' => '限定']);

        $item = Item::factory()->create([
            'name'          => 'カテゴリ複数テスト',
            'condition_id'  => $condition->id,
            'image'         => '/storage/multi.png',
            'price'         => 9999,
        ]);
        $item->categories()->sync([$cat1->id, $cat2->id, $cat3->id]);

        $res = $this->get("/item/{$item->id}");
        $res->assertOk();

        // 3つとも表示されていること
        $res->assertSee('バッグ');
        $res->assertSee('アクセサリー');
        $res->assertSee('限定');
    }
}