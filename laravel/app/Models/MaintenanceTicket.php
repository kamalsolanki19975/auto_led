<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceTicket extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = ['sla_due' => 'datetime'];

    public function auto()
    {
        return $this->belongsTo(Auto::class);
    }

    public function screen()
    {
        return $this->belongsTo(Screen::class);
    }

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function sim()
    {
        return $this->belongsTo(Sim::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
