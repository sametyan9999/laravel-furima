<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) ピボットテーブルを「無ければ」作る
        if (!Schema::hasTable('category_item')) {
            Schema::create('category_item', function (Blueprint $table) {
                $table->unsignedBigInteger('category_id');
                $table->unsignedBigInteger('item_id');
                $table->timestamps();

                $table->primary(['category_id', 'item_id']);
                $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete();
                $table->foreign('item_id')->references('id')->on('items')->cascadeOnDelete();
            });
        }

        // 2) 互換のため items.category_id を nullable に（カラムがある場合のみ）
        if (Schema::hasColumn('items', 'category_id')) {
            try {
                Schema::table('items', function (Blueprint $table) {
                    $table->unsignedBigInteger('category_id')->nullable()->change();
                });
            } catch (\Throwable $e) {
                // Doctrine DBAL 未導入 or 既にnullable 等なら無視
            }
        }

        // 3) 既存データの「単一カテゴリ」をピボットへバックフィル（重複は無視）
        //    すでにcategory_itemが埋まっていても安全にスキップされます
        if (Schema::hasTable('category_item') && Schema::hasColumn('items', 'category_id')) {
            try {
                // MySQLなら INSERT IGNORE が楽
                DB::statement("
                    INSERT IGNORE INTO category_item (category_id, item_id, created_at, updated_at)
                    SELECT category_id, id, NOW(), NOW()
                    FROM items
                    WHERE category_id IS NOT NULL
                ");
            } catch (\Throwable $e) {
                // 失敗しても致命ではないのでスキップ
            }
        }
    }

    public function down(): void
    {
        // 元に戻すときはピボットのみ削除（既存items.category_idは残す）
        if (Schema::hasTable('category_item')) {
            Schema::drop('category_item');
        }
    }
};