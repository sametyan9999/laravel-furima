<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            // すでに存在しない場合のみ追加（リランに強い）
            if (!Schema::hasColumn('items', 'category_id')) {
                $table->foreignId('category_id')
                    ->after('user_id')
                    ->constrained('categories')   // categories.id を参照
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (Schema::hasColumn('items', 'category_id')) {
                // 先に外部キーを落とす
                $table->dropForeign(['category_id']);
                $table->dropColumn('category_id');
            }
        });
    }
};