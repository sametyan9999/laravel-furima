<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Condition extends Model
{
    public $timestamps = false; // id, name のみ
    protected $fillable = ['name'];

    public function items()
    {
        return $this->hasMany(Item::class, 'condition_id');
    }
}