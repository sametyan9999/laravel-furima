<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trade_messages', function (Blueprint $table) {
            $table->id();

            // メッセージ投稿者
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // どの取引のメッセージか（★ purchases.id は UUID なので foreignUuid を使う）
            $table->foreignUuid('purchase_id')
                ->constrained('purchases')
                ->cascadeOnDelete();

            // 本文
            $table->text('body')->nullable();

            // 画像パス
            $table->string('image_path')->nullable();

            // 削除フラグ（編集・削除機能用）
            $table->boolean('is_deleted')->default(false);

            $table->timestamps();

            // 最適化
            $table->index(['purchase_id', 'created_at']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trade_messages');
    }
};