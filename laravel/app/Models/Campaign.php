<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'target_areas' => 'array',
        'target_groups' => 'array',
        'schedule' => 'array',
        'approved_at' => 'datetime',
        'activated_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function advertiser()
    {
        return $this->belongsTo(Advertiser::class);
    }

    public function advertisements()
    {
        return $this->belongsToMany(Advertisement::class, 'campaign_advertisement')->withPivot('order');
    }

    public function assignments()
    {
        return $this->hasMany(CampaignAssignment::class);
    }

    public function autos()
    {
        return $this->belongsToMany(Auto::class, 'campaign_assignments');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function playbackEvents()
    {
        return $this->hasMany(PlaybackEvent::class);
    }

    public function proofOfPlays()
    {
        return $this->hasMany(ProofOfPlay::class);
    }

    public function playlists()
    {
        return $this->hasMany(Playlist::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
