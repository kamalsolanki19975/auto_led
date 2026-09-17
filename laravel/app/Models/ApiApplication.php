<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiApplication extends Model
{
    protected $guarded = [];

    public function keys()
    {
        return $this->hasMany(ApiKey::class);
    }

    public function webhooks()
    {
        return $this->hasMany(Webhook::class);
    }
}
