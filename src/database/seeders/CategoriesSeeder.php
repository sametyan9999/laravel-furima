<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriesSeeder extends Seeder
{
    public function run(): void
    {
        // 並び順（sort）で表示順が決まる想定
        // 参考画像と同じ順に並べています
        $rows = [
            ['id' => 1,  'name' => 'ファッション',  'sort' => 1],
            ['id' => 2,  'name' => '家電',          'sort' => 2],
            ['id' => 3,  'name' => 'インテリア',    'sort' => 3],
            ['id' => 4,  'name' => 'レディース',    'sort' => 4],
            ['id' => 5,  'name' => 'メンズ',        'sort' => 5],
            ['id' => 6,  'name' => 'コスメ',        'sort' => 6],
            ['id' => 7,  'name' => '本',            'sort' => 7],
            ['id' => 8,  'name' => 'ゲーム',        'sort' => 8],
            ['id' => 9,  'name' => 'スポーツ',      'sort' => 9],
            ['id' => 10, 'name' => 'キッチン',      'sort' => 10],
            ['id' => 11, 'name' => 'ハンドメイド',  'sort' => 11],
            ['id' => 12, 'name' => 'アクセサリー',  'sort' => 12],
            ['id' => 13, 'name' => 'おもちゃ',      'sort' => 13],
            ['id' => 14, 'name' => 'ベビー・キッズ','sort' => 14],
        ];

        // 既存を壊したくない場合は upsert にする
        DB::table('categories')->upsert(
            $rows,
            ['id'],          // 一意キー
            ['name','sort']  // 更新カラム
        );
    }
}