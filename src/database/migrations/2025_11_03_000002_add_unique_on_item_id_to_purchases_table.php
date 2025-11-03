<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $t) {
            if (! $this->hasUnique('purchases', 'purchases_item_id_unique')) {
                $t->unique('item_id');
            }
        });
    }
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $t) {
            $t->dropUnique(['item_id']);
        });
    }

    // 既存ユニーク判定（環境差吸収用）
    private function hasUnique(string $table, string $index): bool
    {
        try {
            return collect(Schema::getIndexes($table)['unique'] ?? [])->contains($index);
        } catch (\Throwable $e) {
            return false;
        }
    }
};