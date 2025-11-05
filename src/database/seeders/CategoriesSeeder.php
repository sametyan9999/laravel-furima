<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CategoriesSeeder extends Seeder
{
    public function run(): void
    {
        // 1) 外部キー制約を一時的に停止（各ドライバに合わせて内部で処理される）
        Schema::disableForeignKeyConstraints();

        // 2) テーブルを安全に初期化（ドライバ別に truncate / delete を切り替え）
        $driver = DB::getDriverName(); // 'mysql' | 'sqlite' | 'pgsql' など

        // 中間テーブルがあれば先に空にする（FKの都合で先に消す）
        if (Schema::hasTable('category_item')) {
            if ($driver === 'mysql') {
                DB::table('category_item')->truncate();
            } else {
                DB::table('category_item')->delete();
                if ($driver === 'sqlite') {
                    DB::statement("DELETE FROM sqlite_sequence WHERE name = 'category_item'");
                }
            }
        }

        // categories を初期化
        if ($driver === 'mysql') {
            DB::table('categories')->truncate();
        } else {
            DB::table('categories')->delete();
            if ($driver === 'sqlite') {
                DB::statement("DELETE FROM sqlite_sequence WHERE name = 'categories'");
            }
        }

        // 3) データ投入
        $now = now();
        $rows = [
            ['id' => 1,  'name' => 'ファッション',   'sort' => 1,  'created_at' => $now, 'updated_at' => $now],
            ['id' => 2,  'name' => '家電',           'sort' => 2,  'created_at' => $now, 'updated_at' => $now],
            ['id' => 3,  'name' => 'インテリア',     'sort' => 3,  'created_at' => $now, 'updated_at' => $now],
            ['id' => 4,  'name' => 'レディース',     'sort' => 4,  'created_at' => $now, 'updated_at' => $now],
            ['id' => 5,  'name' => 'メンズ',         'sort' => 5,  'created_at' => $now, 'updated_at' => $now],
            ['id' => 6,  'name' => 'コスメ',         'sort' => 6,  'created_at' => $now, 'updated_at' => $now],
            ['id' => 7,  'name' => '本',             'sort' => 7,  'created_at' => $now, 'updated_at' => $now],
            ['id' => 8,  'name' => 'ゲーム',         'sort' => 8,  'created_at' => $now, 'updated_at' => $now],
            ['id' => 9,  'name' => 'スポーツ',       'sort' => 9,  'created_at' => $now, 'updated_at' => $now],
            ['id' => 10, 'name' => 'キッチン',       'sort' => 10, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 11, 'name' => 'ハンドメイド',   'sort' => 11, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 12, 'name' => 'アクセサリー',   'sort' => 12, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 13, 'name' => 'おもちゃ',       'sort' => 13, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 14, 'name' => 'ベビー・キッズ', 'sort' => 14, 'created_at' => $now, 'updated_at' => $now],
        ];
        DB::table('categories')->insert($rows);

        // 4) 外部キー制約を再開
        Schema::enableForeignKeyConstraints();
    }
}