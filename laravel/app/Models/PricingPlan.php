<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingPlan extends Model
{
    protected $fillable = [
        'name', 'slug', 'tagline', 'price_label', 'price_note', 'features',
        'is_featured', 'cta_label', 'cta_url', 'sort_order', 'status',
    ];

    protected $casts = [
        'features' => 'array',
        'is_featured' => 'boolean',
        'status' => 'boolean',
    ];

    public function scopeActive($q)
    {
        return $q->where('status', true);
    }
}
