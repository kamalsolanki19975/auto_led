<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Screen extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = ['installation_date' => 'date', 'warranty_end' => 'date'];

    public function auto()
    {
        return $this->belongsTo(Auto::class, 'current_auto_id');
    }

    public function device()
    {
        return $this->belongsTo(Device::class, 'current_device_id');
    }
}
