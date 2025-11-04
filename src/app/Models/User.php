<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail; // ✅ メール認証インターフェース
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * 一括代入可能属性
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * 非表示属性
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * キャスト属性
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * パスワードが平文なら自動でハッシュ化する。
     */
    public function setPasswordAttribute($value): void
    {
        $this->attributes['password'] =
            (is_string($value) && preg_match('/^\$2y\$/', $value))
                ? $value
                : Hash::make($value);
    }

    /**
     * リレーション定義
     */
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    /**
     * いいねした商品一覧
     */
    public function likedItems()
    {
        return $this->belongsToMany(Item::class, 'likes')->withTimestamps();
    }

    /**
     * 購入履歴（buyer_user_id を使用）
     */
    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'buyer_user_id');
    }
}