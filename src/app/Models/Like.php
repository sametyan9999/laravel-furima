<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; // ← これを追加！

class Like extends Model
{
    use HasFactory; // ← これも追加！

    // likes は (user_id, item_id) の複合UNIQUE、type列は無し
    protected $fillable = ['user_id', 'item_id'];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
