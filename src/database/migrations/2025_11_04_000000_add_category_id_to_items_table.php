<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            // 仕様書では必須指定がないため nullable で追加し、外部キーは null 許容にします
            $table->unsignedBigInteger('category_id')->nullable()->after('condition_id');
            $table->foreign('category_id')
                ->references('id')->on('categories')
                ->nullOnDelete(); // カテゴリ削除時は null
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropIndex(['category_id']);
            $table->dropColumn('category_id');
        });
    }
};