<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Condition extends Model
{
    use HasFactory;

    // ✅ テーブルに timestamps が無いので自動更新を無効化
    public $timestamps = false;

    protected $fillable = ['name'];
}
