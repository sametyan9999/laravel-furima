<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TradeReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'reviewer_id',
        'target_id',  // ← ER図どおり
        'score',      // ← ER図どおり
        'comment',
    ];

    protected $casts = [
        'score' => 'integer',
    ];

    /**
     * 評価対象の取引（購入）
     */
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * 評価をした側（レビュワー）
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /**
     * 評価された側（レビュー対象）
     */
    public function target()
    {
        return $this->belongsTo(User::class, 'target_id');
    }
}