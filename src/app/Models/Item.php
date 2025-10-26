<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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

    // 画面で使う派生属性
    protected $appends = ['image_url'];

    /** 画像URLの正規化（/storage/* or http(s)://* に変換） */
    public function getImageUrlAttribute(): ?string
    {
        $v = $this->image;

        if (!$v) {
            return null;
        }

        // 既に完全URLまたは/storage始まりならそのまま
        if (preg_match('#^https?://#', $v) || str_starts_with($v, '/storage/')) {
            return $v;
        }

        // 古い保存形式: "public/items/xxx.jpg" -> "/storage/items/xxx.jpg"
        if (str_starts_with($v, 'public/')) {
            return '/storage/' . substr($v, 7);
        }

        // 相対パス（items/xxx.jpg など）は Storage::url で公開URLへ
        return Storage::url($v);
    }

    // 出品者
    public function user() { return $this->belongsTo(User::class); }

    // カテゴリ
    public function category() { return $this->belongsTo(Category::class); }

    // 状態
    public function condition() { return $this->belongsTo(Condition::class, 'condition_id'); }

    // いいね（1:N）
    public function likes() { return $this->hasMany(Like::class); }

    // コメント（1:N）
    public function comments() { return $this->hasMany(Comment::class); }

    // 購入（1:1）
    public function purchase() { return $this->hasOne(Purchase::class); }

    // 便宜：いいねしたユーザー
    public function likedUsers() { return $this->belongsToMany(User::class, 'likes'); }
}