<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Integration extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'type',
        'provider',
        'access_token',
        'refresh_token',
        'expires_at',
        'scope',
        'provider_data',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'id' => 'integer',
        'tenant_id' => 'string',
        'user_id' => 'integer',
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
        'provider_data' => 'array',
        'settings' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function needsRefresh(): bool
    {
        return $this->isExpired() || $this->expires_at?->diffInHours(now()) < 24;
    }

    public function isXero(): bool
    {
        return $this->provider === 'xero';
    }

    public function isSage(): bool
    {
        return $this->provider === 'sage';
    }
}