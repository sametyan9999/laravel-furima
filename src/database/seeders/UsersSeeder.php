<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        // ★ユーザーA：CO01〜CO05
        User::updateOrCreate(
            ['email' => 'sellerA@example.com'],
            [
                'name' => '販売者A',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // ★ユーザーB：CO06〜CO10
        User::updateOrCreate(
            ['email' => 'sellerB@example.com'],
            [
                'name' => '販売者B',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // ★紐付けなしユーザーC
        User::updateOrCreate(
            ['email' => 'viewer@example.com'],
            [
                'name' => '閲覧ユーザー',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
    }
}