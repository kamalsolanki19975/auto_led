<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
        'code', 'name', 'company', 'email', 'phone', 'message',
        'type', 'source', 'page', 'status', 'assigned_to', 'notes', 'followup_at', 'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'followup_at' => 'datetime',
    ];

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
