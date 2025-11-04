<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 存在しない可能性があるのでcreate or table修正の両対応にします
        if (!Schema::hasTable('category_item')) {
            Schema::create('category_item', function (Blueprint $table) {
                $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
                $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
                $table->timestamps(); // ← これが重要
                $table->primary(['item_id', 'category_id']); // 重複防止
            });
        } else {
            Schema::table('category_item', function (Blueprint $table) {
                // 外部キー・主キーが無ければ追加（存在チェックを挟めないのでtry-catch的に実行）
                // まずtimestamps（無ければ追加）
                if (!Schema::hasColumn('category_item', 'created_at')) {
                    $table->timestamps();
                }
                // 主キーが無い場合に備え、複合ユニークで近似（主キーが既にあればスキップ）
                // 環境差異が出やすいため、まずユニークだけ追加を試みる
                if (!Schema::hasColumn('category_item', 'item_id') || !Schema::hasColumn('category_item', 'category_id')) {
                    // 念のため（通常は既にあります）
                    $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
                    $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
                }
                // 複合ユニーク（別名制約で衝突回避）
                $table->unique(['item_id','category_id'], 'category_item_item_id_category_id_unique');
            });
        }
    }

    public function down(): void
    {
        // downではtimestampsとユニーク制約だけ戻す（テーブルは残す）
        if (Schema::hasTable('category_item')) {
            Schema::table('category_item', function (Blueprint $table) {
                // 制約名が一致しない環境もあるので存在していれば削除を試みる想定
                if (Schema::hasColumn('category_item', 'created_at')) {
                    $table->dropColumn(['created_at','updated_at']);
                }
                $table->dropUnique('category_item_item_id_category_id_unique');
            });
        }
    }
};