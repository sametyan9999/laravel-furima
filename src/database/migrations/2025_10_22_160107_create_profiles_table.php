<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('avatar_path', 255)->nullable();
            // 郵便番号はハイフンあり8文字、DBはvarchar(8)で保存
            $table->string('postal_code', 8);
            // 画面定義：住所・建物名のみ
            $table->string('address_line1', 255);
            $table->string('address_line2', 255)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('bio', 255)->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('profiles');
    }
};