<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiKey extends Model
{
    protected $guarded = [];

    protected $hidden = ['key_hash'];

    protected $casts = ['scopes' => 'array', 'last_used_at' => 'datetime', 'expires_at' => 'datetime'];

    public function application()
    {
        return $this->belongsTo(ApiApplication::class, 'api_application_id');
    }
}
