<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class ToolUsageLog extends Model
{
    protected $fillable = [
        'tenant_id',
        'user_id',
        'tool_slug',
        'date',
        'count',
    ];

    protected $casts = [
        'date'  => 'date',
        'count' => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ── Static Helpers ─────────────────────────────────────────────────────────

    /**
     * Record one tool usage for today (upsert).
     */
    public static function logUsage(?int $tenantId, ?int $userId, string $toolSlug): void
    {
        if (! $tenantId) {
            return;
        }

        static::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'tool_slug' => $toolSlug,
                'date'      => Carbon::today()->toDateString(),
            ],
            []
        )->increment('count');

        // Also stamp the user_id on the row (last user wins, acceptable for multi-user tenants)
        static::where('tenant_id', $tenantId)
            ->where('tool_slug', $toolSlug)
            ->where('date', Carbon::today()->toDateString())
            ->update(['user_id' => $userId]);
    }

    /**
     * Get today's usage count for a given tenant + tool.
     */
    public static function getUsageToday(?int $tenantId, string $toolSlug): int
    {
        if (! $tenantId) {
            return 0;
        }

        return (int) static::where('tenant_id', $tenantId)
            ->where('tool_slug', $toolSlug)
            ->where('date', Carbon::today()->toDateString())
            ->value('count');
    }

    /**
     * Get all tool usages for a tenant today, keyed by tool_slug.
     */
    public static function getAllUsageToday(?int $tenantId): array
    {
        if (! $tenantId) {
            return [];
        }

        return static::where('tenant_id', $tenantId)
            ->where('date', Carbon::today()->toDateString())
            ->pluck('count', 'tool_slug')
            ->toArray();
    }

    /**
     * Purge rows older than the given number of days (default: 7).
     */
    public static function purgeOlderThan(int $days = 7): int
    {
        return static::where('date', '<', Carbon::today()->subDays($days)->toDateString())->delete();
    }
}
