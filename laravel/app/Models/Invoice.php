<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = ['invoice_date' => 'date', 'due_date' => 'date'];

    public function advertiser()
    {
        return $this->belongsTo(Advertiser::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function balance(): float
    {
        return (float) ($this->total - $this->amount_paid);
    }
}
