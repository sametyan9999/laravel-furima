<?php

namespace Tests\Feature\Items;

use Tests\TestCase;
use App\Models\User;
use App\Models\Condition;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

class ItemCreateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 15. 出品商品情報登録
     * 各項目が正しく保存されている
     * （カテゴリ / 商品の状態 / 商品名 / ブランド名 / 商品の説明 / 販売価格 / 画像）
     *
     * 実装はカテゴリ多対多（category_item）なので、
     * items.category_id は検証せず、ピボットを検証する。
     */
    public function test_各項目が正しく保存されている(): void
    {
        $user      = User::factory()->create();
        $category  = Category::factory()->create(['name' => 'バッグ']);
        $condition = Condition::factory()->create(['name' => '新品']);

        // 出品画面表示
        $this->actingAs($user)
            ->get(route('items.create'))
            ->assertOk();

        // 1x1 PNG を生成（GD不要）
        $png  = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVQImWNgYAAAAAMAASsJTYQAAAAASUVORK5CYII=');
        $path = sys_get_temp_dir().'/tiny.png';
        file_put_contents($path, $png);
        $image = new UploadedFile($path, 'tiny.png', 'image/png', null, true);

        // 実装のバリデーションに合わせる：image_file / category_ids[]
        $payload = [
            // 'category_id' => $category->id, // ← 単一カテゴリ列は廃止
            'category_ids' => [$category->id],
            'condition_id' => $condition->id,
            'name'         => '出品テスト商品',
            'brand'        => 'テストブランド',
            'description'  => '商品の説明テキスト',
            'price'        => 19800,
            'image_file'   => $image,
        ];

        $this->actingAs($user)
            ->from(route('items.create'))
            ->post(route('items.store'), $payload)
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        // items テーブルの主要カラムを検証（category_id は検証しない）
        $this->assertDatabaseHas('items', [
            'user_id'      => $user->id,
            'name'         => '出品テスト商品',
            'brand'        => 'テストブランド',
            'description'  => '商品の説明テキスト',
            'price'        => 19800,
            'condition_id' => $condition->id,
            // 'category_id' => $category->id, // ← 削除
        ]);

        // ピボット（多対多）を検証
        $itemId = \App\Models\Item::where('name', '出品テスト商品')->value('id');

        $this->assertNotNull($itemId, '作成された商品のIDが取得できませんでした。');

        $this->assertDatabaseHas('category_item', [
            'item_id'     => $itemId,
            'category_id' => $category->id,
        ]);
    }
}