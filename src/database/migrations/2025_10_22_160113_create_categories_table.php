<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100); // UNIQUEは任意。重複許容にしておく
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('categories');
    }
};