<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('conditions', function (Blueprint $table) {
            $table->tinyIncrements('id'); // tinyint unsigned AI
            $table->string('name', 50)->unique(); // 新品 / 未使用に近い など
        });
    }
    public function down(): void {
        Schema::dropIfExists('conditions');
    }
};