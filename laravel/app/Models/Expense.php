<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $guarded = [];

    protected $casts = ['date' => 'date'];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function auto()
    {
        return $this->belongsTo(Auto::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function sim()
    {
        return $this->belongsTo(Sim::class);
    }
}
