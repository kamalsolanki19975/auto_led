<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookDelivery extends Model
{
    protected $guarded = [];

    protected $casts = ['payload' => 'array', 'next_retry_at' => 'datetime', 'delivered_at' => 'datetime'];

    public function webhook()
    {
        return $this->belongsTo(Webhook::class);
    }
}
