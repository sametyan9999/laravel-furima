<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'parent_id', 'sort'];

    /** 複数カテゴリ対応（N:N） ※ pivotにtimestampsなし */
    public function items()
    {
        return $this->belongsToMany(Item::class, 'category_item');
    }

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