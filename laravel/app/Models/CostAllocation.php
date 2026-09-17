<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CostAllocation extends Model
{
    protected $guarded = [];

    protected $casts = ['period_start' => 'date', 'period_end' => 'date'];

    public function auto()
    {
        return $this->belongsTo(Auto::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
}
