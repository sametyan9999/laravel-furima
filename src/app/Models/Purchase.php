<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Purchase extends Model
{
    use HasFactory;

    /** UUID 主キー */
    public $incrementing = false;
    protected $keyType = 'string';

    /** 一括代入許可 */
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
    ];

    protected $casts = [
        'purchased_at' => 'datetime',
    ];

    /** 作成時にUUIDを採番（id未指定なら） */
    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}