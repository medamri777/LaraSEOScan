<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KeywordRanking extends Model
{
    use HasFactory;

    protected $fillable = [
        'keyword_id',
        'checked_at',
        'rank',
        'previous_rank',
        'url',
        'domain',
        'title',
        'search_volume',
        'cpc',
        'competition',
        'serp_features',
        'data_source',
    ];

    protected $casts = [
        'checked_at'    => 'date',
        'serp_features' => 'array',
        'rank'          => 'integer',
        'previous_rank' => 'integer',
        'search_volume' => 'integer',
        'cpc'           => 'float',
        'competition'   => 'integer',
    ];

    public function keyword()
    {
        return $this->belongsTo(Keyword::class);
    }

    /**
     * +N = improved (moved up), -N = dropped, null = no previous
     */
    public function getTrendAttribute(): ?int
    {
        if (is_null($this->rank) || is_null($this->previous_rank)) {
            return null;
        }
        return $this->previous_rank - $this->rank; // positive = improved
    }
}
