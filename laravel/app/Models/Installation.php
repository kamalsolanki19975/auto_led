<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Installation extends Model
{
    protected $guarded = [];

    protected $casts = [
        'checklist' => 'array',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

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
}
