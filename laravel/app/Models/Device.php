<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Device extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $hidden = ['auth_token'];

    protected $casts = [
        'last_heartbeat_at' => 'datetime',
        'last_sync_at' => 'datetime',
        'config' => 'array',
        'temperature' => 'float',
    ];

    public function auto()
    {
        return $this->belongsTo(Auto::class, 'current_auto_id');
    }

    public function screen()
    {
        return $this->belongsTo(Screen::class, 'current_screen_id');
    }

    public function sim()
    {
        return $this->belongsTo(Sim::class, 'current_sim_id');
    }

    public function heartbeats()
    {
        return $this->hasMany(Heartbeat::class);
    }

    public function assignments()
    {
        return $this->hasMany(DeviceAssignment::class);
    }

    public function isOnline(): bool
    {
        if (! $this->last_heartbeat_at) {
            return false;
        }
        $threshold = (int) SystemSetting::get('device', 'offline_threshold', 180);
        return $this->last_heartbeat_at->gt(now()->subSeconds($threshold));
    }
}
