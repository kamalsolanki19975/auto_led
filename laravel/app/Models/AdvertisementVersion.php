<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdvertisementVersion extends Model
{
    protected $guarded = [];

    public function advertisement()
    {
        return $this->belongsTo(Advertisement::class);
    }
}
