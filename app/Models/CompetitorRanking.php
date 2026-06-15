<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CompetitorRanking extends Model
{
    use HasFactory;

    protected $fillable = [
        'keyword_id',
        'competitor_id',
        'checked_at',
        'rank',
        'url',
        'title',
    ];

    protected $casts = [
        'checked_at' => 'date',
        'rank'       => 'integer',
    ];

    public function keyword()
    {
        return $this->belongsTo(Keyword::class);
    }

    public function competitor()
    {
        return $this->belongsTo(ProjectCompetitor::class, 'competitor_id');
    }
}
