<?php

declare(strict_types=1);

namespace App\Http\Requests\Integration;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIntegrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'settings.auto_sync' => 'boolean',
            'settings.revenue_account_code' => 'string|max:10',
            'settings.auto_send_invoices' => 'boolean',
        ];
    }
}
