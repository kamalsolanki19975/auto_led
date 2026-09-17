<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RateCard extends Model
{
    protected $guarded = [];

    protected $casts = ['valid_from' => 'date', 'valid_to' => 'date'];

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
