<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
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

    //public function canUploadMoreDocuments(): bool
    //{
    //    $currentDocumentCount = tenancy()->initialize($this, function () {
    //        return Document::count();
    //    });
    //
    //    return $currentDocumentCount < $this->document_limit;
    //}
    //
    //public function getRemainingDocumentLimit(): int
    //{
    //    $currentDocumentCount = tenancy()->initialize($this, function () {
    //        return Document::count();
    //    });
    //
    //    return max(0, $this->document_limit - $currentDocumentCount);
    //}


}
