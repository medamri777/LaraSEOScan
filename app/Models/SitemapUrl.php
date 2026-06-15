<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SitemapUrl extends Model
{
    protected $fillable = [
        'loc',
        'changefreq',
        'priority',
        'lastmod',
        'type',
        'is_active',
        'image_url',
    ];

    protected $casts = [
        'priority' => 'float',
        'is_active' => 'boolean',
        'lastmod' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
