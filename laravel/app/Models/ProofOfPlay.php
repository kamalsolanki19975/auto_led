<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProofOfPlay extends Model
{
    protected $table = 'proof_of_play';

    protected $guarded = [];

    protected $casts = [
        'validated_at' => 'datetime',
        'server_timestamp' => 'datetime',
        'completion_percent' => 'float',
    ];

    public function playbackEvent()
    {
        return $this->belongsTo(PlaybackEvent::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function auto()
    {
        return $this->belongsTo(Auto::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function advertisement()
    {
        return $this->belongsTo(Advertisement::class);
    }
}
