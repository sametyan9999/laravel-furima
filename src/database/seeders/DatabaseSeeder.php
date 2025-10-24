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
            CommentsSeeder::class,   // 任意
            LikesSeeder::class,      // 任意
        ]);
    }
}