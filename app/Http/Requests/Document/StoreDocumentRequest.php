<?php

declare(strict_types=1);

namespace App\Http\Requests\Document;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx|max:10240',
            'title' => 'required|string|max:255',
            'type' => 'required|in:invoice,receipt,purchase_order,other',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'The file field is required.',
            'file.mimes' => 'The file must be a PDF, image, or Office document.',
            'file.max' => 'The file may not be greater than 10 megabytes.',
            'title.required' => 'The title field is required.',
            'title.max' => 'The title may not be greater than 255 characters.',
            'type.required' => 'The document type field is required.',
            'type.in' => 'The selected document type is invalid.',
        ];
    }
}
