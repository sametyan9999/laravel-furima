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
            $table->timestamps();

            // 複合主キーで重複を排除
            $table->primary(['category_id', 'item_id']);

            // 外部キー（ON DELETE CASCADE）
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