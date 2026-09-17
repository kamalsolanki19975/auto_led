<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Driver extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = ['license_expiry' => 'date'];

    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function autos()
    {
        return $this->hasMany(Auto::class, 'primary_driver_id');
    }

    public function earnings()
    {
        return $this->hasMany(DriverEarning::class);
    }

    public function settlements()
    {
        return $this->hasMany(DriverSettlement::class);
    }

    public function payments()
    {
        return $this->hasMany(DriverPayment::class);
    }

    public function disputes()
    {
        return $this->hasMany(DriverDispute::class);
    }
}
