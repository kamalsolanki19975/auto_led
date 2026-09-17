<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutoGroup extends Model
{
    protected $guarded = [];

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function autos()
    {
        return $this->belongsToMany(Auto::class, 'auto_group_auto');
    }
}
