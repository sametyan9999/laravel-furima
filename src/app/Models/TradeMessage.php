<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TradeMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'purchase_id',
        'body',
        'image_path',
        'is_deleted',
    ];

    protected $casts = [
        'is_deleted' => 'boolean',
    ];

    /** N+1改善 */
    protected $with = ['user'];

    /**
     * メッセージ投稿者（ユーザー）
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 紐づく取引（購入）
     */
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }
}