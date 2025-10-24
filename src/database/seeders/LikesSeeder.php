<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Like, Item, User};

class LikesSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'admin@example.com')->first();
        $item = Item::first();
        if ($user && $item) {
            Like::firstOrCreate(['user_id' => $user->id, 'item_id' => $item->id]);
            $item->increment('likes_count');
        }
    }
}