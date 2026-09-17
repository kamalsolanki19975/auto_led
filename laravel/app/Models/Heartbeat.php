<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Heartbeat extends Model
{
    protected $guarded = [];

    public $timestamps = false;

    protected $casts = ['created_at' => 'datetime', 'temperature' => 'float'];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function auto()
    {
        return $this->belongsTo(Auto::class);
    }
}
