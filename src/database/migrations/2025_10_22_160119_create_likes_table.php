<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->timestamps();

            // 二重いいね防止
            $table->unique(['user_id', 'item_id']);
            $table->index(['item_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('likes');
    }
};