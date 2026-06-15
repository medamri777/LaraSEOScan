<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Keyword extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'tenant_id',
        'keyword',
        'location_code',
        'language_code',
        'device',
        'is_active',
        'last_checked_at',
    ];

    protected $casts = [
        'is_active'       => 'boolean',
        'last_checked_at' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function rankings()
    {
        return $this->hasMany(KeywordRanking::class)->orderByDesc('checked_at');
    }

    public function latestRanking()
    {
        return $this->hasOne(KeywordRanking::class)->latestOfMany('checked_at');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForProject($query, int $projectId)
    {
        return $query->where('project_id', $projectId);
    }
}
