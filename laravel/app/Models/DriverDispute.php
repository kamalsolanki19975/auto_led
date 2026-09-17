<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverDispute extends Model
{
    protected $guarded = [];

    protected $casts = ['resolved_at' => 'datetime'];

    public function settlement()
    {
        return $this->belongsTo(DriverSettlement::class, 'settlement_id');
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }
}
