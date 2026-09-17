<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $guarded = [];

    protected $casts = ['paid_at' => 'datetime'];

    public function advertiser()
    {
        return $this->belongsTo(Advertiser::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
