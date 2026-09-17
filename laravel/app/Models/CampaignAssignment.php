<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignAssignment extends Model
{
    protected $guarded = [];

    protected $casts = ['assigned_at' => 'datetime'];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function auto()
    {
        return $this->belongsTo(Auto::class);
    }
}
