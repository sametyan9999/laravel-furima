<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;

use App\Models\Profile;
use App\Models\Item;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Purchase;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * 一括代入を許可する属性
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * シリアライズ時に隠す属性
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * キャスト
     * ※ Laravel 9未満互換のため 'password' => 'hashed' は使わない
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * パスワードのミューテータ
     * - すでにbcrypt形式($2y$...)ならそのまま
     * - 平文なら Hash::make して保存
     */
    public function setPasswordAttribute($value): void
    {
        $this->attributes['password'] =
            (is_string($value) && preg_match('/^\$2y\$/', $value))
                ? $value
                : Hash::make($value);
    }

    /**
     * リレーション
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

    /**
     * いいね（中間テーブルレコードそのもの）
     */
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    /**
     * いいねした商品（多対多の便宜リレーション）
     */
    public function likedItems()
    {
        return $this->belongsToMany(Item::class, 'likes')->withTimestamps();
    }

    /**
     * 購入情報（buyer_user_id を使用）
     */
    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'buyer_user_id');
    }
}