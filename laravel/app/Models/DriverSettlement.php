<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DriverSettlement extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'calculation_snapshot' => 'array',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }

    public function earnings()
    {
        return $this->hasMany(DriverEarning::class, 'settlement_id');
    }

    public function payments()
    {
        return $this->hasMany(DriverPayment::class, 'settlement_id');
    }

    public function disputes()
    {
        return $this->hasMany(DriverDispute::class, 'settlement_id');
    }
}
