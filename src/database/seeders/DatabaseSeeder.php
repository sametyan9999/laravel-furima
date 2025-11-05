<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UsersSeeder::class,
            ProfilesSeeder::class,
            ConditionsSeeder::class,
            CategoriesSeeder::class,
            ItemsSeeder::class,        // 既存のままでOK（category_idは無視される）
            CategoryItemSeeder::class, // ← ピボット付与を最後に実行
        ]);
    }
}