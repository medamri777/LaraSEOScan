<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectCompetitor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['project_id', 'tenant_id', 'name', 'url'];

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
        return $this->hasMany(CompetitorRanking::class, 'competitor_id');
    }

    public function latestRankingForKeyword(int $keywordId): ?CompetitorRanking
    {
        return $this->rankings()
            ->where('keyword_id', $keywordId)
            ->latest('checked_at')
            ->first();
    }
}
