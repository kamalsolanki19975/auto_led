<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
    protected $guarded = [];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function auto()
    {
        return $this->belongsTo(Auto::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
