<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        // 管理ユーザー
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'admin', 'password' => Hash::make('password')]
        );

        // テストユーザー
        User::factory()->count(3)->create();
    }
}