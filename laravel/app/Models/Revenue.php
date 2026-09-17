<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Revenue extends Model
{
    protected $guarded = [];

    protected $casts = ['date' => 'date'];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function advertiser()
    {
        return $this->belongsTo(Advertiser::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
