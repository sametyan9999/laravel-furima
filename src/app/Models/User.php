<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name','email','password'];

    protected $hidden = ['password','remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // 平文なら自動でハッシュ、既にbcryptならそのまま
    public function setPasswordAttribute($value): void
    {
        $this->attributes['password'] =
            (is_string($value) && preg_match('/^\$2y\$/', $value))
                ? $value
                : Hash::make($value);
    }

    // リレーション
    public function profile()   { return $this->hasOne(Profile::class); }
    public function items()     { return $this->hasMany(Item::class); }
    public function comments()  { return $this->hasMany(Comment::class); }
    public function likes()     { return $this->hasMany(Like::class); }

    // いいねした商品（便宜）
    public function likedItems()
    {
        return $this->belongsToMany(Item::class, 'likes')->withTimestamps();
    }

    // 購入情報（buyer_user_id を使用）
    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'buyer_user_id');
    }
}