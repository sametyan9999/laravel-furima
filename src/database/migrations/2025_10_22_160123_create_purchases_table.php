<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            // ✅ UUID 主キー（テストでUUIDを使用しているため）
            $table->uuid('id')->primary();

            // ✅ 購入者
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete()
                  ->comment('購入者');

            // ✅ 購入された商品
            $table->foreignId('item_id')
                  ->constrained('items')
                  ->cascadeOnDelete();

            // ✅ 購入金額（デフォルト0）
            $table->unsignedInteger('amount')->default(0);

            // ✅ 支払い方法・ステータス
            $table->enum('payment_method', ['card', 'convenience'])->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'canceled'])->default('paid');

            // ✅ Stripe決済ID（任意）
            $table->string('stripe_payment_intent_id', 100)->nullable();

            // ✅ 購入日時
            $table->dateTime('purchased_at')->nullable();

            // ✅ 配送先情報（スナップショット）
            $table->string('shipping_name', 100)->nullable();
            $table->string('shipping_postal_code', 8)->nullable(); // 例: 123-4567
            $table->string('shipping_address1', 255)->nullable();
            $table->string('shipping_address2', 255)->nullable();

            // ✅ タイムスタンプ
            $table->timestamps();

            // ✅ インデックス（クエリ高速化）
            $table->index(['user_id', 'purchased_at']);
            $table->index(['item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
