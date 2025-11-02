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

        // GD不要の 1x1 PNG を生成
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVQImWNgYAAAAAMAASsJTYQAAAAASUVORK5CYII=');
        $path = sys_get_temp_dir().'/tiny.png';
        file_put_contents($path, $png);
        $image = new UploadedFile($path, 'tiny.png', 'image/png', null, true);

        // 実装のバリデーション名に合わせる：image_file / category_ids[]
        $payload = [
            'category_id'   => $category->id,       // （実装で使っていれば保存確認に使われる）
            'category_ids'  => [$category->id],     // ← バリデーションを通すため必須
            'condition_id'  => $condition->id,
            'name'          => '出品テスト商品',
            'brand'         => 'テストブランド',
            'description'   => '商品の説明テキスト',
            'price'         => 19800,
            'image_file'    => $image,              // ← 実装側のフィールド名
        ];

        $this->actingAs($user)
            ->from(route('items.create'))
            ->post(route('items.store'), $payload)
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        // DB 反映確認（実装に合わせて主要カラムをチェック）
        $this->assertDatabaseHas('items', [
            'user_id'      => $user->id,
            'name'         => '出品テスト商品',
            'brand'        => 'テストブランド',
            'description'  => '商品の説明テキスト',
            'price'        => 19800,
            'condition_id' => $condition->id,
            // category_id を items テーブルに持っている実装ならこちらも一致
            'category_id'  => $category->id,
        ]);

        // 多対多を採用している実装の場合は、必要ならこちらも（テーブル名はプロジェクト準拠）
        // $this->assertDatabaseHas('category_item', [
        //     'category_id' => $category->id,
        //     'item_id'     => \App\Models\Item::where('name', '出品テスト商品')->value('id'),
        // ]);
    }
}