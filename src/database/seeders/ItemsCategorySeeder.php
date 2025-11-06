<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Item, Category};

class ItemsCategorySeeder extends Seeder
{
    public function run(): void
    {
        // 商品名 → 付けたいカテゴリ名（先頭が代表カテゴリ = items.category_id）
        $map = [
            '腕時計'          => ['メンズ','アクセサリー','ファッション'],
            'HDD'            => ['家電'],
            '玉ねぎ3束'       => ['キッチン'],
            '革靴'            => ['メンズ','ファッション'],
            'ノートPC'         => ['家電'],
            'マイク'           => ['家電'],
            'ショルダーバッグ'   => ['レディース','ファッション','アクセサリー'],
            'タンブラー'        => ['キッチン'],
            'コーヒーミル'       => ['キッチン'],
            'メイクセット'       => ['コスメ','レディース'],
        ];

        foreach ($map as $name => $cats) {
            $item = Item::firstWhere('name', $name);
            if (!$item) {
                // 商品が存在しない場合はスキップ
                echo "[SKIP] item not found: {$name}\n";
                continue;
            }

            // 指定順を保ったままカテゴリ名→IDに変換
            $ids = collect($cats)
                ->map(fn ($n) => Category::where('name', $n)->value('id'))
                ->filter()
                ->values()
                ->all();

            if (empty($ids)) {
                echo "[SKIP] categories not found for: {$name}\n";
                continue;
            }

            // 多対多を上書き
            $item->categories()->sync($ids);

            // 代表カテゴリは指定配列の先頭
            $primaryId = Category::where('name', $cats[0])->value('id');
            if ($primaryId) {
                $item->category_id = $primaryId;
            }

            $item->save();

            echo "[OK] {$name} -> ".implode(',', $cats)." (primary={$item->category_id})\n";
        }
    }
}