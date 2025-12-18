<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->timestamp('buyer_read_at')->nullable()->after('is_done');
            $table->timestamp('seller_read_at')->nullable()->after('buyer_read_at');

            $table->index('buyer_read_at');
            $table->index('seller_read_at');
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropIndex(['buyer_read_at']);
            $table->dropIndex(['seller_read_at']);
            $table->dropColumn(['buyer_read_at', 'seller_read_at']);
        });
    }
};