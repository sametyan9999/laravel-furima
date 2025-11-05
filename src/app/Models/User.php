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

    /** 一括代入可能属性 */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /** 非表示属性 */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /** キャスト属性 */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /** パスワードが平文なら自動でハッシュ化 */
    public function setPasswordAttribute($value): void
    {
        $this->attributes['password'] =
            (is_string($value) && preg_match('/^\$2y\$/', $value))
                ? $value
                : Hash::make($value);
    }

    // ===== リレーション定義 =====

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

    /** 購入履歴 */
    public function purchases(): HasMany
    {
        // テーブルの外部キー名が user_id の場合はこちらを使用
        return $this->hasMany(Purchase::class, 'user_id');
    }
}