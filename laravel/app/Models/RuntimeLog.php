<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RuntimeLog extends Model
{
    protected $guarded = [];

    protected $casts = ['date' => 'date'];

    public function auto()
    {
        return $this->belongsTo(Auto::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
}
