<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Comment, Item, User};

class CommentsSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'admin@example.com')->first();
        $item = Item::first();
        if ($user && $item) {
            Comment::create([
                'user_id' => $user->id,
                'item_id' => $item->id,
                'body'    => 'こちらにコメントが入ります。',
            ]);
            $item->increment('comments_count');
        }
    }
}