<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            // UUID 主キー（テスト要件に一致）
            $table->uuid('id')->primary();

            // 購入者
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete()
                  ->comment('購入者');

            // 購入された商品
            $table->foreignId('item_id')
                  ->constrained('items')
                  ->cascadeOnDelete();

            // 金額・決済
            $table->unsignedInteger('amount')->default(0);
            $table->enum('payment_method', ['card', 'convenience'])->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'canceled'])->default('paid');

            // Stripe決済ID（任意）
            $table->string('stripe_payment_intent_id', 100)->nullable();

            // 購入日時
            $table->dateTime('purchased_at')->nullable();

            // ★ 配送先情報（購入時のスナップショット。未確定時はNULLを許容）
            $table->string('shipping_name', 100)->nullable()->comment('配送先氏名');
            $table->string('shipping_postal_code', 8)->nullable()->comment('配送先郵便番号（例: 123-4567）');
            $table->string('shipping_address1', 255)->nullable()->comment('配送先住所1');
            $table->string('shipping_address2', 255)->nullable()->comment('配送先住所2');

            $table->timestamps();

            // クエリ最適化
            $table->index(['user_id', 'purchased_at']);
            $table->index(['item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};