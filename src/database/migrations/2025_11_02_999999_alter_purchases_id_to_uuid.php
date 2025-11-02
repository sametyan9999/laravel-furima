<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL を想定。id を CHAR(36) の主キーに変更する
        // 既に PRIMARY KEY でも安全のため DROP/ADD を明示
        DB::statement("
            ALTER TABLE purchases
            MODIFY id CHAR(36) NOT NULL,
            DROP PRIMARY KEY,
            ADD PRIMARY KEY (id)
        ");
    }

    public function down(): void
    {
        // もとに戻す（必要なら）: BIGINT に巻き戻し
        DB::statement("
            ALTER TABLE purchases
            MODIFY id BIGINT UNSIGNED NOT NULL,
            DROP PRIMARY KEY,
            ADD PRIMARY KEY (id)
        ");
    }
};