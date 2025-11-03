<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('items')) return;

        if (Schema::hasColumn('items', 'category_id')) {
            // 1) 外部キーを先に落とす（存在しない場合は握りつぶす）
            try {
                Schema::table('items', function (Blueprint $table) {
                    $table->dropForeign(['category_id']); // items_category_id_foreign
                });
            } catch (\Throwable $e) { /* no-op */ }

            // 2) インデックスを落とす（存在しない場合は握りつぶす）
            try {
                DB::statement('ALTER TABLE `items` DROP INDEX `items_category_id_index`');
            } catch (\Throwable $e) { /* no-op */ }

            // 3) DBALを使わずに列を落とす（enum問題を回避）
            DB::statement('ALTER TABLE `items` DROP COLUMN `category_id`');
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('items')) return;

        if (!Schema::hasColumn('items', 'category_id')) {
            // 列を戻す（生SQLで追加）
            DB::statement('ALTER TABLE `items` ADD COLUMN `category_id` BIGINT UNSIGNED NULL AFTER `user_id`');

            // インデックスを復元（存在確認は生SQLだと難しいのでtry-catch）
            try {
                DB::statement('ALTER TABLE `items` ADD INDEX `items_category_id_index` (`category_id`)');
            } catch (\Throwable $e) { /* no-op */ }

            // 外部キーを復元（SchemaでOK）
            Schema::table('items', function (Blueprint $table) {
                $table->foreign('category_id')
                    ->references('id')->on('categories')
                    ->onDelete('cascade');
            });
        }
    }
};