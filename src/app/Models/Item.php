<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = [
        'user_id', 'category_id', 'condition_id',
        'name', 'description', 'brand', 'image', 'price',
        'status', 'likes_count', 'comments_count', 'sold_at',
    ];

    protected $casts = [
        'sold_at' => 'datetime',
    ];

    // 出品者
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // カテゴリ
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // 状態
    public function condition()
    {
        return $this->belongsTo(Condition::class, 'condition_id');
    }

    // いいね（1:N）
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    // コメント（1:N）
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    // 購入（1:1）
    public function purchase()
    {
        return $this->hasOne(Purchase::class);
    }

    // 便宜：いいねしたユーザー
    public function likedUsers()
    {
        return $this->belongsToMany(User::class, 'likes');
    }
}