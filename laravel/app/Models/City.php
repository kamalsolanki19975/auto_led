<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $guarded = [];

    public function areas()
    {
        return $this->hasMany(Area::class);
    }

    public function autos()
    {
        return $this->hasMany(Auto::class);
    }
}
