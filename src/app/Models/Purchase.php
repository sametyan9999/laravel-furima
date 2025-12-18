<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Purchase extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'item_id',
        'amount',
        'payment_method',
        'payment_status',
        'stripe_payment_intent_id',
        'purchased_at',
        'shipping_name',
        'shipping_postal_code',
        'shipping_address1',
        'shipping_address2',
        'buyer_read_at',
        'seller_read_at',
    ];

    protected $casts = [
        'purchased_at'   => 'datetime',
        'buyer_read_at'  => 'datetime',
        'seller_read_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    /** 購入者 */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** 購入された商品 */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /** 商品を出品したユーザー（出品者） */
    public function seller()
    {
        return $this->hasOneThrough(
            User::class,
            Item::class,
            'id',        // items.id
            'id',        // users.id
            'item_id',   // purchases.item_id
            'user_id'    // items.user_id
        );
    }

    /** この取引に紐づくメッセージ一覧 */
    public function tradeMessages()
    {
        return $this->hasMany(TradeMessage::class);
    }

    /** この取引のレビュー一覧 */
    public function tradeReviews()
    {
        return $this->hasMany(TradeReview::class);
    }
}