<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Profile;

class ProfilesSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'admin@example.com')->first();

        if ($user) {
            Profile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'postal_code'   => '123-4567',
                    'address_line1' => '東京都渋谷区道玄坂1-2-3',
                    'address_line2' => 'テストビル101',
                    'avatar_path'   => null,
                ]
            );
        }
    }
}