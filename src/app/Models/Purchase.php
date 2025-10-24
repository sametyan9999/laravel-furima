<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'buyer_user_id','item_id','amount',
        'payment_method','payment_status','stripe_payment_intent_id',
        'purchased_at',
        'shipping_name','shipping_postal_code','shipping_prefecture',
        'shipping_city','shipping_address1','shipping_address2','shipping_phone',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    // 購入者（カスタムFK）
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_user_id');
    }
}