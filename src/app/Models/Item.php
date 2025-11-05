<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, BelongsToMany, HasMany, HasOne};

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id', // 代表カテゴリ
        'condition_id',
        'name',
        'description',
        'brand',
        'image',
        'price',
        'status',
        'likes_count',
        'comments_count',
        'sold_at',
    ];

    protected $casts = [
        'sold_at' => 'datetime',
    ];

    protected $appends = ['image_url', 'is_sold'];

    /**
     * 画像URLを取得
     */
    public function getImageUrlAttribute(): ?string
    {
        $imagePath = $this->image;

        if (!$imagePath) {
            return null;
        }

        if (preg_match('#^https?://#', $imagePath) || str_starts_with($imagePath, '/storage/')) {
            return $imagePath;
        }

        if (str_starts_with($imagePath, 'public/')) {
            return '/storage/' . substr($imagePath, 7);
        }

        return Storage::url($imagePath);
    }

    /**
     * 出品者
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 商品状態
     */
    public function condition(): BelongsTo
    {
        return $this->belongsTo(Condition::class, 'condition_id');
    }

    /**
     * 単一カテゴリ
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * 複数カテゴリ（pivotにtimestamps列が無い前提）
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_item');
    }

    /**
     * いいね
     */
    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    /**
     * コメント
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * 購入履歴
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * 最新購入
     */
    public function purchase(): HasOne
    {
        return $this->hasOne(Purchase::class);
    }

    /**
     * いいねしたユーザー
     */
    public function likedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'likes');
    }

    /**
     * 売却済み判定
     */
    public function getIsSoldAttribute(): bool
    {
        return $this->purchases()->exists();
    }
}