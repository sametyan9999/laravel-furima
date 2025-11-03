<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 既にあれば何もしないで安全に
        if (!Schema::hasColumn('items', 'category_id')) {
            Schema::table('items', function (Blueprint $table) {
                // users テーブルの次あたりに差し込み
                $table->foreignId('category_id')
                    ->after('user_id')
                    ->constrained('categories')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('items', 'category_id')) {
            Schema::table('items', function (Blueprint $table) {
                $table->dropForeign(['category_id']);
                $table->dropColumn('category_id');
            });
        }
    }
};