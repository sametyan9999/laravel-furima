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

        $driver = Schema::getConnection()->getDriverName(); // 'mysql' | 'sqlite' など

        if ($driver === 'mysql') {
            // ✅ MySQL: 先に外部キーとインデックスを落としてから列を削除
            Schema::table('items', function (Blueprint $table) {
                // 外部キー名が環境で異なっても配列指定でOK
                try { $table->dropForeign(['category_id']); } catch (\Throwable $e) {}
                // インデックス名が不明でも配列指定でOK
                try { $table->dropIndex(['category_id']); } catch (\Throwable $e) {}
                // 最後に列を削除
                try { $table->dropColumn('category_id'); } catch (\Throwable $e) {}
            });
        } else {
            // ✅ SQLite 等: dropForeign を呼ばず、dropColumn のみ（Laravel がテーブル再作成で面倒を見る）
            Schema::table('items', function (Blueprint $table) {
                try { $table->dropColumn('category_id'); } catch (\Throwable $e) {}
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('items')) return;
        if (Schema::hasColumn('items', 'category_id')) return;

        Schema::table('items', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable()->after('user_id');
            // インデックスは任意（性能向上用）
            try { $table->index('category_id', 'items_category_id_index'); } catch (\Throwable $e) {}

            // 外部キーは MySQL では有効。SQLite では無視されてもOK。
            try {
                $table->foreign('category_id')
                    ->references('id')->on('categories')
                    ->onDelete('cascade');
            } catch (\Throwable $e) {}
        });
    }
};