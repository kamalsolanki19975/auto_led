<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $guarded = [];

    protected $casts = ['purchase_date' => 'date', 'warranty_end' => 'date'];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function auto()
    {
        return $this->belongsTo(Auto::class, 'current_auto_id');
    }

    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }
}
