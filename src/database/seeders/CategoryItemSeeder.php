<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Item;
use Illuminate\Database\Seeder;

class CategoryItemSeeder extends Seeder
{
    public function run(): void
    {
        // カテゴリ一覧を取得（空なら何もしない）
        $categoryIds = Category::query()->pluck('id')->all();
        if (!$categoryIds) return;

        // 各商品に1〜3件ランダムでカテゴリを付与（既存は維持）
        Item::query()->chunk(200, function ($items) use ($categoryIds) {
            foreach ($items as $item) {
                $attach = collect($categoryIds)
                    ->shuffle()
                    ->take(min(3, max(1, count($categoryIds))))
                    ->all();

                // 既存pivotを壊さないよう第2引数false（重複は内部で弾かれる）
                $item->categories()->sync($attach, false);
            }
        });
    }
}