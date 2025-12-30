<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'title',
        'type',
        'file_path',
        'file_size',
        'mime_type',
        'status',
        'processed_data',
        'processing_error',
        'processed_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'tenant_id' => 'string',
        'user_id' => 'integer',
        'file_size' => 'integer',
        'processed_data' => 'array',
        'processed_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invoiceLines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class);
    }

    public function processingAttempts(): HasMany
    {
        return $this->hasMany(ProcessingAttempt::class);
    }

    public function isProcessed(): bool
    {
        return $this->status === 'processed';
    }

    public function hasFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}