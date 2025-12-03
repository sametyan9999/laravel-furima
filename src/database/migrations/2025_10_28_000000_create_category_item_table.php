<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_item', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('item_id');

            // 複合主キー（重複禁止）
            $table->primary(['category_id', 'item_id']);

            // 仕様書どおりタイムスタンプも持たせる
            $table->timestamps();

            // 外部キー（削除連鎖）
            $table->foreign('category_id')
                  ->references('id')->on('categories')
                  ->cascadeOnDelete();

            $table->foreign('item_id')
                  ->references('id')->on('items')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_item');
    }
};