<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_user_id')->constrained('users')->cascadeOnDelete()->comment('購入者');
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->unsignedInteger('amount');
            $table->enum('payment_method', ['card', 'convenience']);
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'canceled'])->default('paid');
            $table->string('stripe_payment_intent_id', 100)->nullable();
            $table->dateTime('purchased_at')->nullable();

            // 配送先（購入時点スナップショット）
            $table->string('shipping_name', 100);
            $table->string('shipping_postal_code', 8);      // 例: 123-4567
            $table->string('shipping_address1', 255);
            $table->string('shipping_address2', 255)->nullable();

            $table->timestamps();

            $table->index(['buyer_user_id', 'purchased_at']);
            $table->index(['item_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('purchases');
    }
};