<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationPreference extends Model
{
    protected $guarded = [];

    protected $casts = ['email' => 'boolean', 'in_app' => 'boolean', 'sms' => 'boolean', 'whatsapp' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
