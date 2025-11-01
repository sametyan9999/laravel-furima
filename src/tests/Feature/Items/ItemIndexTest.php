<?php

namespace Tests\Feature\Items;

use App\Models\User;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Tests\TestCase;

class ItemIndexTest extends TestCase
{
    use RefreshDatabase;

    private const INDEX = '/'; // 画面定義 PG01

    /**
     * 1) 全商品を取得できる
     *
     * 期待: 一覧ページが200で表示され、登録した商品名が表示される
     * ※ ビュー側の表示カラムが title 等なら 'name' を合わせてください。
     */
    public function test_全商品を取得できる(): void
    {
        // 出品者2名
        $seller1 = User::factory()->create();
        $seller2 = User::factory()->create();

        // 商品作成（名前はUIに出る前提）
        $a = Item::factory()->create(['user_id' => $seller1->id, 'name' => 'テスト商品A']);
        $b = Item::factory()->create(['user_id' => $seller2->id, 'name' => 'テスト商品B']);

        $res = $this->get(self::INDEX);

        $res->assertOk();
        $res->assertSee('テスト商品A');
        $res->assertSee('テスト商品B');
    }

    /**
     * 2) 購入済み商品は「Sold」と表示される
     *
     * 期待: 購入レコードがある商品に "Sold" ラベルが出る
     */
    public function test_購入済み商品は_Sold_と表示される(): void
    {
        $seller = User::factory()->create();
        $buyer  = User::factory()->create();

        $item = Item::factory()->create([
            'user_id' => $seller->id,
            'name'    => '購入済みテスト商品',
        ]);

        // purchases テーブルへ直接挿入（Factoryが無い環境でも動くように）
        DB::table('purchases')->insert([
            'id'         => Str::uuid()->toString(), // 自動採番なら削除
            'user_id'    => $buyer->id,              // 購入者
            'item_id'    => $item->id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $res = $this->get(self::INDEX);

        $res->assertOk();
        $res->assertSee('購入済みテスト商品');
        $res->assertSee('Sold'); // ビューの表記が「SOLD」「売却済み」なら合わせて変更
    }

    /**
     * 3) 自分が出品した商品は表示されない
     *
     * 期待: ログイン中のユーザーが出品した商品は一覧に出ない
     */
    public function test_自分の出品商品は表示されない(): void
    {
        $me     = User::factory()->create();
        $other  = User::factory()->create();

        $myItem     = Item::factory()->create(['user_id' => $me->id,    'name' => '自分の出品']);
        $othersItem = Item::factory()->create(['user_id' => $other->id, 'name' => '他人の出品']);

        $this->actingAs($me);

        $res = $this->get(self::INDEX);

        $res->assertOk();
        $res->assertDontSee('自分の出品');
        $res->assertSee('他人の出品');
    }
}
