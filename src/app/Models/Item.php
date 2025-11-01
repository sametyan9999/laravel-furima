<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'category_id', 'condition_id',
        'name', 'description', 'brand', 'image', 'price',
        'status', 'likes_count', 'comments_count', 'sold_at',
    ];

    protected $casts = [
        'sold_at' => 'datetime',
    ];

    // Blade側で「$item->image_url」「$item->is_sold」で取得できるように
    protected $appends = ['image_url', 'is_sold'];

    /**
     * 画像URLの正規化
     * /storage/* または http(s)://* の形に変換して返す
     */
    public function getImageUrlAttribute(): ?string
    {
        $v = $this->image;

        if (!$v) {
            return null;
        }

        if (preg_match('#^https?://#', $v) || str_starts_with($v, '/storage/')) {
            return $v;
        }

        if (str_starts_with($v, 'public/')) {
            return '/storage/' . substr($v, 7);
        }

        return Storage::url($v);
    }

    /** 出品者 */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** 商品のメインカテゴリ（後方互換用） */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /** 状態（新品・中古など） */
    public function condition()
    {
        return $this->belongsTo(Condition::class, 'condition_id');
    }

    /** いいね（1:N） */
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    /** コメント（1:N） */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /** 購入（1:N）— テスト互換用 */
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    /** 1:1購入（旧定義） */
    public function purchase()
    {
        return $this->hasOne(Purchase::class);
    }

    /** いいねしたユーザー（N:N） */
    public function likedUsers()
    {
        return $this->belongsToMany(User::class, 'likes');
    }

    /** 🔥 複数カテゴリ（N:N） */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_item')->withTimestamps();
    }

    /** ✅ 購入済みかどうか（Sold表示用） */
    public function getIsSoldAttribute(): bool
    {
        return $this->purchases()->exists();
    }
}
