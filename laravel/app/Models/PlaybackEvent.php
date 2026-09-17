<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlaybackEvent extends Model
{
    protected $guarded = [];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'device_timestamp' => 'datetime',
        'server_timestamp' => 'datetime',
        'sync_timestamp' => 'datetime',
        'processed' => 'boolean',
        'completion_percent' => 'float',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function auto()
    {
        return $this->belongsTo(Auto::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function advertisement()
    {
        return $this->belongsTo(Advertisement::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function proofOfPlay()
    {
        return $this->hasOne(ProofOfPlay::class);
    }
}
