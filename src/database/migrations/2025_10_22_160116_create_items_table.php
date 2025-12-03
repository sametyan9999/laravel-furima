<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('items', function (Blueprint $table) {
            $table->id();

            // 出品者
            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete()
                  ->comment('出品者');

            // メインカテゴリ（複数カテゴリのうち先頭）※あとで Seeder でセットするので nullable
            $table->foreignId('category_id')
                  ->nullable()
                  ->constrained('categories')
                  ->nullOnDelete()
                  ->comment('主要表示用カテゴリ（複数カテゴリのうち先頭）');

            // コンディション（conditions: tinyint unsigned）
            $table->unsignedTinyInteger('condition_id');
            $table->foreign('condition_id')
                  ->references('id')->on('conditions')
                  ->restrictOnDelete();

            // 基本情報
            $table->string('name', 120);
            $table->text('description');           // 仕様上必須
            $table->string('brand', 80)->nullable();

            // 画像は1枚：URL or storageパス
            $table->string('image', 255);

            // 価格
            $table->unsignedInteger('price');

            // on_sale / sold / draft（一覧ソートに使用）
            $table->enum('status', ['on_sale', 'sold', 'draft'])->default('on_sale');

            // 集計系
            $table->unsignedInteger('likes_count')->default(0);
            $table->unsignedInteger('comments_count')->default(0);

            // 売却日時
            $table->dateTime('sold_at')->nullable();

            // 論理削除
            $table->softDeletes();
            $table->timestamps();

            // よく使う絞り込みのインデックス
            $table->index(['category_id', 'status', 'created_at']);
            $table->index('user_id');
        });
    }

    public function down(): void {
        Schema::dropIfExists('items');
    }
};