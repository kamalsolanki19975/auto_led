<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Advertiser extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function advertisements()
    {
        return $this->hasMany(Advertisement::class);
    }

    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function outstanding(): float
    {
        return (float) $this->invoices()->whereIn('status', ['issued', 'partially_paid', 'overdue'])
            ->sum(\DB::raw('total - amount_paid'));
    }
}
