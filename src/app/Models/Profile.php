<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    public $timestamps = false; // テーブルにtimestampsが無いため無効化

    protected $fillable = [
        'user_id',
        'avatar_path',
        'postal_code',
        'prefecture',
        'city',
        'address_line1',
        'address_line2',
        'phone',
        'bio',
    ];

    /** ユーザー情報とのリレーション */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}