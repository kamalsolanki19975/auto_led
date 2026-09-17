<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverEarning extends Model
{
    protected $guarded = [];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'calculated_at' => 'datetime',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function auto()
    {
        return $this->belongsTo(Auto::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function rateCard()
    {
        return $this->belongsTo(RateCard::class);
    }

    public function settlement()
    {
        return $this->belongsTo(DriverSettlement::class, 'settlement_id');
    }
}
