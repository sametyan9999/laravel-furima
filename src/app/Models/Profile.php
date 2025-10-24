<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = [
        'user_id','avatar_path','postal_code','prefecture','city',
        'address_line1','address_line2','phone','bio',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}