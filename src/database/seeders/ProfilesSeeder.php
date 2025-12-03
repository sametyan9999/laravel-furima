<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Profile;

class ProfilesSeeder extends Seeder
{
    public function run(): void
    {
        $profiles = [
            'sellerA@example.com' => [
                'postal_code'   => '123-4567',
                'address_line1' => '東京都新宿区西新宿1-1-1',
                'address_line2' => 'sellerAマンション101',
                'phone'         => '080-1111-1111',
            ],
            'sellerB@example.com' => [
                'postal_code'   => '234-5678',
                'address_line1' => '大阪府大阪市北区梅田2-2-2',
                'address_line2' => 'sellerBハイツ202',
                'phone'         => '080-2222-2222',
            ],
            'viewer@example.com' => [
                'postal_code'   => '345-6789',
                'address_line1' => '福岡県福岡市中央区天神3-3-3',
                'address_line2' => 'viewerコーポ303',
                'phone'         => '080-3333-3333',
            ],
        ];

        foreach ($profiles as $email => $data) {
            $user = User::where('email', $email)->first();

            if (!$user) {
                continue;
            }

            Profile::updateOrCreate(
                ['user_id' => $user->id],
                $data
            );
        }
    }
}