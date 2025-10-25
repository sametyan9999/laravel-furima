<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 0) まず NULL 行が無いことは確認済み（もし不安なら下の2行のコメントアウトを外す）
        // $fallbackUserId = DB::table('users')->min('id') ?? throw new \RuntimeException('users が空です');
        // DB::table('comments')->whereNull('user_id')->update(['user_id' => $fallbackUserId]);

        // 1) 外部キーが存在すれば落とす（存在しないなら何もしない）
        $fk = DB::selectOne("
            SELECT CONSTRAINT_NAME
            FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS
            WHERE CONSTRAINT_SCHEMA = DATABASE()
              AND TABLE_NAME = 'comments'
              AND CONSTRAINT_NAME = 'comments_user_id_foreign'
        ");
        if ($fk) {
            DB::statement("ALTER TABLE comments DROP FOREIGN KEY `comments_user_id_foreign`");
        }

        // 2) 必要なら user_id のインデックスを用意（無ければ追加）
        $idx = DB::selectOne("
            SELECT INDEX_NAME
            FROM INFORMATION_SCHEMA.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'comments'
              AND INDEX_NAME = 'comments_user_id_foreign'
        ");
        if (!$idx) {
            DB::statement("ALTER TABLE comments ADD INDEX `comments_user_id_foreign` (`user_id`)");
        }

        // 3) user_id を NOT NULL に変更（DBAL不要）
        DB::statement("ALTER TABLE comments MODIFY user_id BIGINT UNSIGNED NOT NULL");

        // 4) 外部キーを再作成（無い場合のみ追加）
        if (!$fk) {
            DB::statement("
                ALTER TABLE comments
                ADD CONSTRAINT `comments_user_id_foreign`
                FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
                ON DELETE CASCADE
            ");
        }
    }

    public function down(): void
    {
        // 1) 外部キーがあれば落とす
        $fk = DB::selectOne("
            SELECT CONSTRAINT_NAME
            FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS
            WHERE CONSTRAINT_SCHEMA = DATABASE()
              AND TABLE_NAME = 'comments'
              AND CONSTRAINT_NAME = 'comments_user_id_foreign'
        ");
        if ($fk) {
            DB::statement("ALTER TABLE comments DROP FOREIGN KEY `comments_user_id_foreign`");
        }

        // 2) user_id を NULL 許可に戻す
        DB::statement("ALTER TABLE comments MODIFY user_id BIGINT UNSIGNED NULL");

        // 3) 外部キーを再作成
        DB::statement("
            ALTER TABLE comments
            ADD CONSTRAINT `comments_user_id_foreign`
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
            ON DELETE CASCADE
        ");
    }
};