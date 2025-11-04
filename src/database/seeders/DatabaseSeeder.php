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
            ItemsSeeder::class,
            // CommentsSeeder::class,   // 提出用は無効化
            // LikesSeeder::class,      // 提出用は無効化
        ]);
    }
}