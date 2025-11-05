<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite では ALTER ... MODIFY / DROP PK が使えない。
        // かつ本プロジェクトでは purchases は最初から UUID で作成されるため何もしない。
        // （もし本番MySQLで既存int→uuid変換が必要なら、ここにMySQL専用のALTERを書く）
        if (DB::getDriverName() !== 'mysql') {
            return;
        }
    }

    public function down(): void
    {
        // 何もしない（元に戻さない）
    }
};