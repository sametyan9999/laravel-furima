<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class ItemsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // ★ユーザーA・B を取得
        $sellerA = User::where('email', 'sellerA@example.com')->first()->id;
        $sellerB = User::where('email', 'sellerB@example.com')->first()->id;

        // 商品画像
        $images = [
            '腕時計' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Armani+Mens+Clock.jpg',
            'HDD' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/HDD+Hard+Disk.jpg',
            '玉ねぎ3束' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/iLoveIMG+d.jpg',
            '革靴' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Leather+Shoes+Product+Photo.jpg',
            'ノートPC' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Living+Room+Laptop.jpg',
            'マイク' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Music+Mic+4632231.jpg',
            'ショルダーバッグ' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Purse+fashion+pocket.jpg',
            'タンブラー' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Tumbler+souvenir.jpg',
            'コーヒーミル' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Waitress+with+Coffee+Grinder.jpg',
            'メイクセット' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/%E5%A4%96%E5%87%BA%E3%83%A1%E3%82%A4%E3%82%AF%E3%82%A2%E3%83%83%E3%83%95%E3%82%9A%E3%82%BB%E3%83%83%E3%83%88.jpg',
        ];

        // ★商品データ（5件ずつ分ける）
        $itemsA = [
            ['腕時計', 15000, 1, 'スタイリッシュなデザインのメンズ腕時計'],
            ['HDD', 5000, 2, '高速で信頼性の高いハードディスク'],
            ['玉ねぎ3束', 300, 3, '新鮮な玉ねぎ3束のセット'],
            ['革靴', 4000, 4, 'クラシックなデザインの革靴'],
            ['ノートPC', 45000, 1, '高性能なノートパソコン'],
        ];

        $itemsB = [
            ['マイク', 8000, 2, '高音質のレコーディング用マイク'],
            ['ショルダーバッグ', 3500, 3, 'おしゃれなショルダーバッグ'],
            ['タンブラー', 500, 4, '使いやすいタンブラー'],
            ['コーヒーミル', 4000, 1, '手動のコーヒーミル'],
            ['メイクセット', 2500, 2, '便利なメイクアップセット'],
        ];

        // ★ユーザーAの商品
        foreach ($itemsA as $i) {
            DB::table('items')->insert([
                'name' => $i[0],
                'price' => $i[1],
                'condition_id' => $i[2],
                'description' => $i[3],
                'brand' => null,
                'user_id' => $sellerA,
                'image' => $images[$i[0]],
                'status' => 'on_sale',
                'likes_count' => 0,
                'comments_count' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // ★ユーザーBの商品
        foreach ($itemsB as $i) {
            DB::table('items')->insert([
                'name' => $i[0],
                'price' => $i[1],
                'condition_id' => $i[2],
                'description' => $i[3],
                'brand' => null,
                'user_id' => $sellerB,
                'image' => $images[$i[0]],
                'status' => 'on_sale',
                'likes_count' => 0,
                'comments_count' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}