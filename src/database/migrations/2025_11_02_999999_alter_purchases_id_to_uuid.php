<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ✅ SQLite では ALTER ... MODIFY / DROP PK が使えない。
        // かつ本プロジェクトでは purchases は最初から UUID で作成されるため何もしない。
        // （もし本番MySQLで既存int→uuid変換が必要なら、ここにMySQL専用のALTERを書く）
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // --- MySQLで旧環境をケアしたい場合だけ有効化 ---
        // DB::statement("ALTER TABLE purchases MODIFY id CHAR(36) NOT NULL");
        // DB::statement("ALTER TABLE purchases DROP PRIMARY KEY, ADD PRIMARY KEY (id)");
    }

    public function down(): void
    {
        // 何もしない（元に戻さない）
    }
};