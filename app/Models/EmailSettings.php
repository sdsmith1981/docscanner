<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'process_email_attachments',
        'allowed_senders',
        'blocked_senders',
        'default_document_type',
        'auto_process_attachments',
        'delete_processed_emails',
    ];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'process_email_attachments' => 'boolean',
        'allowed_senders' => 'array',
        'blocked_senders' => 'array',
        'auto_process_attachments' => 'boolean',
        'delete_processed_emails' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isSenderAllowed(string $email): bool
    {
        // If blocked senders list exists and contains the email, block it
        if (!empty($this->blocked_senders) && in_array($email, $this->blocked_senders)) {
            return false;
        }

        // If allowed senders list is empty, allow all (except blocked ones)
        if (empty($this->allowed_senders)) {
            return true;
        }

        // If allowed senders list exists, check if email is in it
        return in_array($email, $this->allowed_senders);
    }

    public function shouldProcessAttachments(): bool
    {
        return $this->process_email_attachments && $this->auto_process_attachments;
    }
}