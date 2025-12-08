<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Relations\{HasOne, HasMany, BelongsToMany};

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * パスワードを自動ハッシュ
     */
    public function setPasswordAttribute($value): void
    {
        $this->attributes['password'] =
            (is_string($value) && preg_match('/^\$2y\$/', $value))
                ? $value
                : Hash::make($value);
    }

    /** プロフィール */
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    /** 出品商品 */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    /** コメント */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /** いいね */
    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    /** いいねした商品一覧 */
    public function likedItems(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'likes')->withTimestamps();
    }

    /** 購入履歴（購入者側） */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class, 'user_id');
    }

    /** 自分が送った取引メッセージ */
    public function tradeMessages(): HasMany
    {
        return $this->hasMany(TradeMessage::class);
    }

    /** 自分が書いたレビュー（レビューした側） */
    public function writtenTradeReviews(): HasMany
    {
        return $this->hasMany(TradeReview::class, 'reviewer_id');
    }

    /**
     * 自分が評価されたレビュー（レビュー対象）
     * ★ migration のカラム target_id に合わせて修正済み
     */
    public function receivedTradeReviews(): HasMany
    {
        return $this->hasMany(TradeReview::class, 'target_id');
    }

    /**
     * ★ 取引評価の平均（四捨五入）
     * - 評価が無ければ null
     * - round() のエラーを防ぐため float キャストを追加
     */
    public function getReviewAverage(): ?int
    {
        $avg = $this->receivedTradeReviews()->avg('score');

        if ($avg === null) {
            return null;
        }

        return (int) round((float) $avg);
    }
}