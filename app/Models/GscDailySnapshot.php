<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GscDailySnapshot extends Model
{
    protected $fillable = [
        'gsc_connection_id',
        'date',
        'clicks',
        'impressions',
        'ctr',
        'avg_position',
        'top_queries',
    ];

    protected function casts(): array
    {
        return [
            'date'        => 'date',
            'top_queries' => 'array',
        ];
    }

    public function connection()
    {
        return $this->belongsTo(GscConnection::class, 'gsc_connection_id');
    }
}
