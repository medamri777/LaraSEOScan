<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RobotRule extends Model
{
    protected $fillable = [
        'user_agent',
        'rule_type',
        'path',
        'crawl_delay',
        'sitemap_url',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'crawl_delay' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForAgent($query, string $agent)
    {
        return $query->where('user_agent', $agent);
    }
}
