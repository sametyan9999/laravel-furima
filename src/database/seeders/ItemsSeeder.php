<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Item, User, Category};

class ItemsSeeder extends Seeder
{
    public function run(): void
    {
        $seller = User::first(); // なければFactoryで作ったユーザー

        $images = [
            '腕時計'         => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Armani+Mens+Clock.jpg',
            'HDD'           => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/HDD+Hard+Disk.jpg',
            '玉ねぎ3束'      => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/iLoveIMG+d.jpg',
            '革靴'           => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Leather+Shoes+Product+Photo.jpg',
            'ノートPC'       => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Living+Room+Laptop.jpg',
            'マイク'         => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Music+Mic+4632231.jpg',
            'ショルダーバッグ' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Purse+fashion+pocket.jpg',
            'タンブラー'      => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Tumbler+souvenir.jpg',
            'コーヒーミル'    => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Waitress+with+Coffee+Grinder.jpg',
            'メイクセット'    => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/%E5%A4%96%E5%87%BA%E3%83%A1%E3%82%A4%E3%82%AF%E3%82%A2%E3%83%83%E3%83%95%E3%82%9A%E3%82%BB%E3%83%83%E3%83%88.jpg',
        ];

        $map = [
            ['name' => '腕時計', 'price' => 15000, 'brand' => 'Rolax',     'cond' => 1, 'cat' => '小物',   'desc' => 'スタイリッシュなデザインのメンズ腕時計'],
            ['name' => 'HDD',   'price' => 5000,  'brand' => '西芝',       'cond' => 2, 'cat' => '家電',   'desc' => '高速で信頼性の高いハードディスク'],
            ['name' => '玉ねぎ3束','price'=> 300,  'brand' => null,        'cond' => 3, 'cat' => '食品',   'desc' => '新鮮な玉ねぎ3束のセット'],
            ['name' => '革靴',   'price' => 4000,  'brand' => null,        'cond' => 4, 'cat' => 'ファッション','desc'=>'クラシックなデザインの革靴'],
            ['name' => 'ノートPC','price'=> 45000, 'brand' => null,        'cond' => 1, 'cat' => 'PC',    'desc' => '高性能なノートパソコン'],
            ['name' => 'マイク', 'price' => 8000,  'brand' => null,        'cond' => 2, 'cat' => 'オーディオ','desc'=>'高音質のレコーディング用マイク'],
            ['name' => 'ショルダーバッグ','price'=>3500,'brand'=>null,    'cond' => 3, 'cat' => 'バッグ', 'desc' => 'おしゃれなショルダーバッグ'],
            ['name' => 'タンブラー','price'=>500, 'brand'=>null,          'cond' => 4, 'cat' => '生活雑貨','desc'=>'使いやすいタンブラー'],
            ['name' => 'コーヒーミル','price'=>4000,'brand'=>'Starbacks','cond'=>1,'cat'=>'キッチン','desc'=>'手動のコーヒーミル'],
            ['name' => 'メイクセット','price'=>2500,'brand'=>null,        'cond' => 2, 'cat' => '美容',   'desc' => '便利なメイクアップセット'],
        ];

        foreach ($map as $row) {
            $category = Category::firstOrCreate(['name' => $row['cat']], ['sort' => 0]);

            Item::updateOrCreate(
                ['name' => $row['name']],
                [
                    'user_id'        => $seller?->id,
                    'category_id'    => $category->id,
                    'condition_id'   => $row['cond'],
                    'description'    => $row['desc'],
                    'brand'          => $row['brand'],
                    'image'          => $images[$row['name']],
                    'price'          => $row['price'],
                    'status'         => 'on_sale',
                    'likes_count'    => 0,
                    'comments_count' => 0,
                ]
            );
        }
    }
}