<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RankCheckBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'tenant_id',
        'status',
        'keywords_count',
        'completed_count',
        'failed_count',
        'error_message',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'started_at'  => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
