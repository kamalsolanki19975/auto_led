<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginHistory extends Model
{
    protected $guarded = [];

    protected $casts = ['successful' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
