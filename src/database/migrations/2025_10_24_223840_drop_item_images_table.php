<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // MySQLのFK制約で詰まらないように一応保険
        Schema::disableForeignKeyConstraints();

        if (Schema::hasTable('item_images')) {
            Schema::drop('item_images');
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // 将来復活させる時の最小定義（使わないなら空でもOK）
        Schema::create('item_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->string('path', 255);
            $table->unsignedTinyInteger('sort')->default(1);
            $table->timestamps();
            $table->index(['item_id', 'sort']);
        });
    }
};