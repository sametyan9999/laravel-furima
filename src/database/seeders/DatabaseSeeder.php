<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // 1) 参照されるマスタ類（あるものだけ並べる）
            UsersSeeder::class,        // 初期ユーザーを固定したい場合
            ProfilesSeeder::class,     // 初期プロフィールが必要なら
            ConditionsSeeder::class,   // 商品状態マスタ
            CategoriesSeeder::class,   // 14カテゴリ（必須）

            // 2) 本体データ（items）をスナップショットから再現
            ItemsSeeder::class,        // 現在のitemsスナップショット

            // 3) 多対多の紐付け（items × categories）
            ItemsCategorySeeder::class, // ← ここが正
        ]);
    }
}