<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'plan',
        'scan_limit_per_day',
        'stripe_customer_id',
        'stripe_subscription_id',
        'stripe_subscription_status',
        'paypal_customer_id',
        'paypal_subscription_id',
        'paypal_subscription_status',
        'paypal_plan_id',
        'billing_cycle',
        'trial_ends_at',
        'logo_path',
        'primary_color',
        'agency_name',
        'agency_website',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'deleted_at'    => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Tenant $tenant) {
            if (empty($tenant->slug)) {
                $tenant->slug = Str::slug($tenant->name) . '-' . Str::random(6);
            }
        });
    }

    // ── Accessors ──────────────────────────────────────────────────────────────

    /**
     * Full public URL of the logo, or null if not uploaded.
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (! $this->logo_path) {
            return null;
        }
        return Storage::disk('public')->url($this->logo_path);
    }

    /**
     * Logo as base64 for embedding in PDFs.
     */
    public function getLogoBase64Attribute(): ?string
    {
        if (! $this->logo_path) {
            return null;
        }
        if (! Storage::disk('public')->exists($this->logo_path)) {
            return null;
        }
        $contents = Storage::disk('public')->get($this->logo_path);
        $mime     = Storage::disk('public')->mimeType($this->logo_path);
        return 'data:' . $mime . ';base64,' . base64_encode($contents);
    }

    // ── Relationships ──────────────────────────────────────────────────────────

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function planChangeLogs()
    {
        return $this->hasMany(PlanChangeLog::class)->latest();
    }

    // ── Plan Helpers ──────────────────────────────────────────────────────────

    public function isPro(): bool
    {
        return $this->plan === 'pro';
    }

    public function isGuru(): bool
    {
        return $this->plan === 'guru';
    }

    public function isBusiness(): bool
    {
        return $this->plan === 'business';
    }

    public function isAgency(): bool
    {
        return $this->plan === 'agency';
    }

    public function isFree(): bool
    {
        return empty($this->plan) || $this->plan === 'free';
    }

    public function onTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function hasActiveSubscription(): bool
    {
        return $this->paypal_subscription_status === 'ACTIVE' || $this->onTrial();
    }

    /**
     * Human-readable plan label.
     */
    public function getPlanLabelAttribute(): string
    {
        return \App\Support\PlanLimits::labelFor($this->plan ?? 'free');
    }
}

