<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Owner extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function autos()
    {
        return $this->hasMany(Auto::class);
    }

    public function drivers()
    {
        return $this->hasMany(Driver::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function settlements()
    {
        return $this->hasMany(DriverSettlement::class);
    }
}
