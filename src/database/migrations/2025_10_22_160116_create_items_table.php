<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->comment('出品者');

            // カテゴリはNULL許容（未分類もOK）、親削除時はNULL
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();

            // conditions(id) = tinyint unsigned
            $table->unsignedTinyInteger('condition_id');
            $table->foreign('condition_id')
                  ->references('id')->on('conditions')
                  ->restrictOnDelete();

            $table->string('name', 120);
            $table->text('description'); // 仕様上必須
            $table->string('brand', 80)->nullable();

            // 画像は1枚：URL or storageパス
            $table->string('image', 255);

            $table->unsignedInteger('price');
            $table->enum('status', ['on_sale', 'sold', 'draft'])->default('on_sale');
            $table->unsignedInteger('likes_count')->default(0);
            $table->unsignedInteger('comments_count')->default(0);
            $table->dateTime('sold_at')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['category_id', 'status']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('items');
    }
};