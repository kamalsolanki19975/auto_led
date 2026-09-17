<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceAssignment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'assigned_at' => 'datetime',
        'unassigned_at' => 'datetime',
        'active' => 'boolean',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function auto()
    {
        return $this->belongsTo(Auto::class);
    }

    public function screen()
    {
        return $this->belongsTo(Screen::class);
    }

    public function sim()
    {
        return $this->belongsTo(Sim::class);
    }
}
