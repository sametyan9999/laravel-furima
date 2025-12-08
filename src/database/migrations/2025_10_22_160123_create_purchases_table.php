<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            // UUID 主キー
            $table->uuid('id')->primary();

            // 購入者
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete()
                  ->comment('購入者');

            // 購入された商品
            $table->foreignId('item_id')
                  ->constrained('items')
                  ->cascadeOnDelete()
                  ->comment('購入された商品');

            // 金額・決済
            // ★ テストコードで amount を指定しないケースがあるため default(0) を付与
            $table->unsignedInteger('amount')
                  ->default(0)
                  ->comment('購入金額');

            $table->enum('payment_method', ['card', 'convenience'])
                  ->nullable()
                  ->comment('支払い方法');

            $table->enum('payment_status', ['pending', 'paid', 'failed', 'canceled'])
                  ->default('paid')
                  ->comment('支払いステータス');

            // Stripe決済ID
            // ★ テスト・アプリともに未設定で保存するケースがあるため nullable()
            $table->string('stripe_payment_intent_id', 100)
                  ->nullable()
                  ->comment('StripeのPaymentIntent ID');

            // 購入日時
            $table->dateTime('purchased_at')
                  ->nullable()
                  ->comment('購入日時');

            // ★ 取引完了フラグ
            $table->boolean('is_done')
                  ->default(false)
                  ->comment('取引完了フラグ');

            // 配送先情報（購入時のスナップショット）
            $table->string('shipping_name', 100)
                  ->nullable()
                  ->comment('配送先氏名');

            $table->string('shipping_postal_code', 8)
                  ->nullable()
                  ->comment('配送先郵便番号（例: 123-4567）');

            $table->string('shipping_address1', 255)
                  ->nullable()
                  ->comment('配送先住所1');

            $table->string('shipping_address2', 255)
                  ->nullable()
                  ->comment('配送先住所2');

            $table->timestamps();

            // ★ 1商品につき0or1購入を DB 制約で保証
            $table->unique('item_id');

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