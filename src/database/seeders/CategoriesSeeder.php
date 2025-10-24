<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'ファッション', '家電', '食品', 'PC', 'オーディオ',
            'バッグ', '生活雑貨', 'キッチン', '小物', '美容'
        ];
        foreach ($names as $i => $name) {
            Category::updateOrCreate(['name' => $name], ['sort' => $i]);
        }
    }
}