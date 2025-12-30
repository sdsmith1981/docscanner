<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'description',
        'quantity',
        'unit_price',
        'total_amount',
        'tax_rate',
        'tax_amount',
    ];

    protected $casts = [
        'id' => 'integer',
        'document_id' => 'integer',
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function getAmountIncludingTax(): float
    {
        return (float) $this->total_amount + (float) $this->tax_amount;
    }
}
