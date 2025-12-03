<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trade_reviews', function (Blueprint $table) {
            $table->id();

            // 評価をした人
            $table->foreignId('reviewer_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // 評価された人
            $table->foreignId('target_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // ★ UUID の purchases.id を参照
            $table->foreignUuid('purchase_id')
                  ->constrained('purchases')
                  ->cascadeOnDelete();

            $table->unsignedTinyInteger('score');      // 評価スコア（1〜5など）
            $table->string('comment', 255)->nullable();// コメント

            $table->timestamps();

            // 「同じ人が同じ取引を複数回評価できない」制約
            $table->unique(['purchase_id', 'reviewer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trade_reviews');
    }
};