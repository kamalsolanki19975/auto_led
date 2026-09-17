<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    protected $guarded = [];

    protected $casts = ['sent_at' => 'datetime', 'failed_at' => 'datetime'];
}
