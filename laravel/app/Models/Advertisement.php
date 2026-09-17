<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Advertisement extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'tags' => 'array',
        'approved_at' => 'datetime',
    ];

    public function advertiser()
    {
        return $this->belongsTo(Advertiser::class);
    }

    public function versions()
    {
        return $this->hasMany(AdvertisementVersion::class);
    }

    public function campaigns()
    {
        return $this->belongsToMany(Campaign::class, 'campaign_advertisement');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isApproved(): bool
    {
        return in_array($this->approval_status, ['approved', 'scheduled', 'active', 'completed'], true);
    }
}
