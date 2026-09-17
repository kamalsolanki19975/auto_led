<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sim extends Model
{
    use SoftDeletes;

    protected $table = 'sims';

    protected $guarded = [];

    protected $casts = ['activation_date' => 'date', 'renewal_date' => 'date'];

    public function device()
    {
        return $this->belongsTo(Device::class, 'current_device_id');
    }

    public function auto()
    {
        return $this->belongsTo(Auto::class, 'current_auto_id');
    }

    public function assignments()
    {
        return $this->hasMany(SimAssignment::class);
    }
}
