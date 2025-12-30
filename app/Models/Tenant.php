<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends Model implements TenantWithDatabase
{
    use HasDatabase, HasDomains, HasFactory;

    protected $fillable = [
        'id',
        'plan',
        'document_limit',
        'stripe_customer_id',
        'xero_tenant_id',
        'sage_company_id',
    ];

    protected $casts = [
        'id' => 'string',
        'document_limit' => 'integer',
    ];

    public function domains(): HasMany
    {
        return $this->hasMany(\App\Models\Domain::class);
    }

    public function getDatabaseName(): string
    {
        return config('tenancy.database_prefix', 'tenant_').$this->getKey();
    }

    public function canUploadMoreDocuments(): bool
    {
        $currentDocumentCount = tenancy()->initialize($this, function () {
            return Document::count();
        });

        return $currentDocumentCount < $this->document_limit;
    }

    public function getRemainingDocumentLimit(): int
    {
        $currentDocumentCount = tenancy()->initialize($this, function () {
            return Document::count();
        });

        return max(0, $this->document_limit - $currentDocumentCount);
    }

    public function getInternal($key)
    {
        return $this->getAttribute($key);
    }

    public function getTenantKeyName(): string
    {
        return $this->getKeyName();
    }

    public function getTenantKey()
    {
        return $this->getKey();
    }

    public function setInternal($key, $value): void
    {
        $this->setAttribute($key, $value);
    }

    public function run(callable $callback)
    {
        return tenancy()->initialize($this, $callback);
    }
}
