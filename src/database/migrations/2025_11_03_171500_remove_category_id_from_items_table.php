<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('items')) return;
        if (!Schema::hasColumn('items', 'category_id')) return;

        Schema::table('items', function (Blueprint $table) {
            // 外部キーがあれば先に外す
            try { $table->dropForeign(['category_id']); } catch (\Throwable $e) {}
            // インデックスは列DROPで自動削除されるので dropIndex は呼ばない
            try { $table->dropColumn('category_id'); } catch (\Throwable $e) {}
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('items')) return;
        if (Schema::hasColumn('items', 'category_id')) return;

        Schema::table('items', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable()->after('user_id');
            // 必要ならインデックス再作成
            try { $table->index('category_id', 'items_category_id_index'); } catch (\Throwable $e) {}
            // 必要なら外部キー再作成
            try {
                $table->foreign('category_id')
                      ->references('id')->on('categories')
                      ->onDelete('cascade');
            } catch (\Throwable $e) {}
        });
    }
};