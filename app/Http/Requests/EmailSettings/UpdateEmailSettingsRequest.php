<?php

declare(strict_types=1);

namespace App\Http\Requests\EmailSettings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmailSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'process_email_attachments' => 'required|boolean',
            'allowed_senders' => 'nullable|array',
            'allowed_senders.*' => 'email',
            'blocked_senders' => 'nullable|array',
            'blocked_senders.*' => 'email',
            'default_document_type' => 'required|in:invoice,receipt,purchase_order,other',
            'auto_process_attachments' => 'required|boolean',
            'delete_processed_emails' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'allowed_senders.*.email' => 'Each allowed sender must be a valid email address.',
            'blocked_senders.*.email' => 'Each blocked sender must be a valid email address.',
            'default_document_type.in' => 'Default document type must be one of: invoice, receipt, purchase order, other.',
        ];
    }
}