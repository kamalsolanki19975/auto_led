<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
    protected $guarded = [];

    protected $casts = ['schedule' => 'array'];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function items()
    {
        return $this->hasMany(PlaylistItem::class)->orderBy('order');
    }
}
