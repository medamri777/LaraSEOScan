<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'company',
        'role',
        'tenant_id',
        'tenant_role',
        'is_admin',
        'google_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_admin'          => 'boolean',
        ];
    }

    // ── Filament admin access ──────────────────────────────────────────────────

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_admin === true;
    }

    // ── Relationships ──────────────────────────────────────────────────────────

    public function seoScans()
    {
        return $this->hasMany(SeoScan::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function hasTenant(): bool
    {
        return ! is_null($this->tenant_id);
    }

    public function isOwner(): bool
    {
        return $this->tenant_role === 'owner';
    }
}
