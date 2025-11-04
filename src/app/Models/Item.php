<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id', // ★ これを必ず含める（NOT NULL対策）
        'condition_id',
        'name', 'description', 'brand', 'image', 'price',
        'status', 'likes_count', 'comments_count', 'sold_at',
    ];

    protected $casts = [
        'sold_at' => 'datetime',
    ];

    protected $appends = ['image_url', 'is_sold'];

    public function getImageUrlAttribute(): ?string
    {
        $v = $this->image;
        if (!$v) return null;
        if (preg_match('#^https?://#', $v) || str_starts_with($v, '/storage/')) return $v;
        if (str_starts_with($v, 'public/')) return '/storage/' . substr($v, 7);
        return Storage::url($v);
    }

    public function user()      { return $this->belongsTo(User::class); }
    public function condition() { return $this->belongsTo(Condition::class, 'condition_id'); }

    // 単一カテゴリ（代表カテゴリ）
    public function category()  { return $this->belongsTo(Category::class); }

    // 複数カテゴリ（中間テーブル category_item）
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_item')->withTimestamps();
    }

    public function likes()     { return $this->hasMany(Like::class); }
    public function comments()  { return $this->hasMany(Comment::class); }
    public function purchases() { return $this->hasMany(Purchase::class); }
    public function purchase()  { return $this->hasOne(Purchase::class); }

    public function likedUsers()
    {
        return $this->belongsToMany(User::class, 'likes');
    }

    public function getIsSoldAttribute(): bool
    {
        return $this->purchases()->exists();
    }
}