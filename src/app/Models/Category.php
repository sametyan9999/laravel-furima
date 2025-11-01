<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // ← 追加！
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory; // ← 追加！

    protected $fillable = ['name', 'parent_id', 'sort'];

    /** 🔥 複数カテゴリ対応（N:N） */
    public function items()
    {
        return $this->belongsToMany(Item::class, 'category_item')->withTimestamps();
    }

    /** （旧）単一カテゴリ用の関係：後方互換として残す */
    public function singleItems()
    {
        return $this->hasMany(Item::class);
    }

    /** 階層化対応：親カテゴリ */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /** 階層化対応：子カテゴリ */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }
}
