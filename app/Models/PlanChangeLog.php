<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanChangeLog extends Model
{
    protected $fillable = [
        'tenant_id',
        'admin_id',
        'old_plan',
        'new_plan',
        'old_scan_limit',
        'new_scan_limit',
        'source',
        'note',
    ];

    protected $casts = [
        'old_scan_limit' => 'integer',
        'new_scan_limit' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function getPlanBadgeColor(string $plan): string
    {
        return match ($plan) {
            'pro'    => 'warning',
            'agency' => 'success',
            default  => 'gray',
        };
    }
}
