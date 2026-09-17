<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverPayment extends Model
{
    protected $guarded = [];

    protected $casts = ['paid_at' => 'datetime'];

    public function settlement()
    {
        return $this->belongsTo(DriverSettlement::class, 'settlement_id');
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
