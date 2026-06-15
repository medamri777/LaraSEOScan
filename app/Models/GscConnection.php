<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GscConnection extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'property_url',
        'access_token',
        'refresh_token',
        'expires_in',
        'token_expires_at',
        'last_sync_at',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    protected function casts(): array
    {
        return [
            'token_expires_at' => 'datetime',
            'last_sync_at'     => 'datetime',
        ];
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function dailySnapshots()
    {
        return $this->hasMany(GscDailySnapshot::class, 'gsc_connection_id');
    }

    public function isTokenExpired(): bool
    {
        return $this->token_expires_at && $this->token_expires_at->isPast();
    }
}
