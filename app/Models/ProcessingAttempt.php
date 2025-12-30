<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcessingAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'status',
        'attempt_number',
        'error_message',
        'processing_time_ms',
        'result_data',
    ];

    protected $casts = [
        'id' => 'integer',
        'document_id' => 'integer',
        'attempt_number' => 'integer',
        'processing_time_ms' => 'integer',
        'result_data' => 'array',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    public function hasFailed(): bool
    {
        return $this->status === 'failed';
    }
}
