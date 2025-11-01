<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // テスト（SQLite）では実行しない
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // 1) 既存FKを（あれば）外す
        Schema::table('comments', function (Blueprint $table) {
            try {
                $table->dropForeign(['user_id']); // 無ければ例外→握りつぶす
            } catch (\Throwable $e) {
                // ignore
            }
        });

        // 2) user_id を NOT NULL に変更（MySQLは生SQLが確実）
        DB::statement("ALTER TABLE `comments` MODIFY `user_id` BIGINT UNSIGNED NOT NULL");

        // 3) 外部キーを再作成（ON DELETE CASCADE）
        Schema::table('comments', function (Blueprint $table) {
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        // テスト（SQLite）では実行しない
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // 1) FKを外す
        Schema::table('comments', function (Blueprint $table) {
            try {
                $table->dropForeign(['user_id']);
            } catch (\Throwable $e) {
                // ignore
            }
        });

        // 2) user_id を NULL 許可へ戻す
        DB::statement("ALTER TABLE `comments` MODIFY `user_id` BIGINT UNSIGNED NULL");

        // 3) FKを戻す（ON DELETE CASCADE）
        Schema::table('comments', function (Blueprint $table) {
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->cascadeOnDelete();
        });
    }
};
