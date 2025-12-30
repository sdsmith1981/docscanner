<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Document extends Model
{
    use HasFactory, SoftDeletes, Searchable;

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

    public function toSearchableArray(): array
    {
        $processedData = $this->processed_data ?? [];
        
        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'status' => $this->status,
            'created_at' => $this->created_at->toISOString(),
            'processed_at' => $this->processed_at?->toISOString(),
            'invoice_number' => $processedData['invoice_number'] ?? null,
            'vendor_name' => $processedData['vendor_name'] ?? null,
            'invoice_date' => $processedData['invoice_date'] ?? null,
            'due_date' => $processedData['due_date'] ?? null,
            'total_amount' => $processedData['total_amount'] ?? null,
            'tax_amount' => $processedData['tax_amount'] ?? null,
            'line_items' => $this->invoiceLines->map(function ($line) {
                return [
                    'description' => $line->description,
                    'quantity' => $line->quantity,
                    'unit_price' => $line->unit_price,
                    'total_amount' => $line->total_amount,
                    'tax_amount' => $line->tax_amount,
                ];
            })->toArray(),
            'user_name' => $this->user->name,
            'user_email' => $this->user->email,
        ];
    }

    public function scoutMetadata(): array
    {
        return [
            'tenant_id' => $this->tenant_id,
        ];
    }
}