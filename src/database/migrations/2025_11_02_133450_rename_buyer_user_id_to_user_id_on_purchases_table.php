<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // すでに user_id があれば何もしない
        if (Schema::hasColumn('purchases', 'user_id')) {
            return;
        }
        // buyer_user_id が無ければ何もしない
        if (!Schema::hasColumn('purchases', 'buyer_user_id')) {
            return;
        }

        // 1) buyer_user_id に張られている外部キー名を調べて落とす（MySQL）
        $fkName = null;
        $rows = DB::select("
            SELECT CONSTRAINT_NAME AS name
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'purchases'
              AND COLUMN_NAME = 'buyer_user_id'
              AND REFERENCED_TABLE_NAME = 'users'
        ");
        if (!empty($rows)) {
            $fkName = $rows[0]->name ?? null;
        }
        if ($fkName) {
            DB::statement("ALTER TABLE purchases DROP FOREIGN KEY `{$fkName}`");
        }

        // 2) カラム名を変更（NOT NULL / UNSIGNED を保持）
        DB::statement("
            ALTER TABLE purchases
            CHANGE `buyer_user_id` `user_id` BIGINT UNSIGNED NOT NULL
        ");

        // 3) 新しい外部キーを作成（ON DELETE CASCADE）
        //    既に似た名前が存在しても問題ないように try-catch
        try {
            DB::statement("
                ALTER TABLE purchases
                ADD CONSTRAINT `purchases_user_id_foreign`
                FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
                ON DELETE CASCADE
            ");
        } catch (\Throwable $e) {}

        // 4) 便利なインデックス（存在しなくても try）
        try { Schema::table('purchases', fn (Blueprint $t) => $t->index(['user_id', 'purchased_at'])); } catch (\Throwable $e) {}
        try { Schema::table('purchases', fn (Blueprint $t) => $t->index(['item_id'])); } catch (\Throwable $e) {}
    }

    public function down(): void
    {
        // 逆向き（user_id → buyer_user_id）
        if (!Schema::hasColumn('purchases', 'user_id')) return;

        // 外部キーを落とす
        $rows = DB::select("
            SELECT CONSTRAINT_NAME AS name
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'purchases'
              AND COLUMN_NAME = 'user_id'
              AND REFERENCED_TABLE_NAME = 'users'
        ");
        if (!empty($rows)) {
            $fkName = $rows[0]->name ?? null;
            if ($fkName) DB::statement("ALTER TABLE purchases DROP FOREIGN KEY `{$fkName}`");
        }

        DB::statement("
            ALTER TABLE purchases
            CHANGE `user_id` `buyer_user_id` BIGINT UNSIGNED NOT NULL
        ");

        try {
            DB::statement("
                ALTER TABLE purchases
                ADD CONSTRAINT `purchases_buyer_user_id_foreign`
                FOREIGN KEY (`buyer_user_id`) REFERENCES `users`(`id`)
                ON DELETE CASCADE
            ");
        } catch (\Throwable $e) {}
    }
};