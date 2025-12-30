<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Document;
use App\Models\InvoiceLine;
use App\Models\ProcessingAttempt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DocumentProcessingService
{
    private const AI_API_URL = 'https://api.openai.com/v1/chat/completions';

    public function processDocument(Document $document): void
    {
        Log::info('Starting document processing', ['document_id' => $document->id]);

        try {
            $startTime = microtime(true);

            // Create processing attempt
            $attempt = ProcessingAttempt::create([
                'document_id' => $document->id,
                'status' => 'pending',
                'attempt_number' => $document->processingAttempts()->max('attempt_number') + 1,
            ]);

            $extractedData = $this->extractDataFromDocument($document);

            if ($this->validateExtractedData($extractedData)) {
                $this->saveProcessedData($document, $extractedData);

                $document->update([
                    'status' => 'processed',
                    'processed_at' => now(),
                ]);

                $attempt->update([
                    'status' => 'success',
                    'processing_time_ms' => (int) ((microtime(true) - $startTime) * 1000),
                    'result_data' => $extractedData,
                ]);

                Log::info('Document processed successfully', [
                    'document_id' => $document->id,
                    'processing_time_ms' => (int) ((microtime(true) - $startTime) * 1000),
                ]);
            } else {
                $this->handleProcessingError($document, 'Validation failed');
            }
        } catch (\Exception $e) {
            $this->handleProcessingError($document, $e->getMessage());
        }
    }

    private function extractDataFromDocument(Document $document): array
    {
        $fileContent = Storage::disk('s3')->get($document->file_path);

        if ($document->type === 'invoice') {
            return $this->extractInvoiceData($fileContent, $document);
        }

        return $this->extractGenericData($fileContent, $document);
    }

    private function extractInvoiceData(string $fileContent, Document $document): array
    {
        $prompt = 'Extract the following information from this invoice document in JSON format:
        {
            "invoice_number": "string",
            "invoice_date": "YYYY-MM-DD",
            "due_date": "YYYY-MM-DD",
            "vendor_name": "string",
            "vendor_address": "string",
            "total_amount": "decimal",
            "tax_amount": "decimal",
            "subtotal": "decimal",
            "line_items": [
                {
                    "description": "string",
                    "quantity": "decimal",
                    "unit_price": "decimal",
                    "total_amount": "decimal",
                    "tax_rate": "decimal",
                    "tax_amount": "decimal"
                }
            ]
        }
        
        Document content: '.base64_encode($fileContent);

        $response = Http::withToken(config('services.openai.api_key'))
            ->post(self::AI_API_URL, [
                'model' => 'gpt-4-vision-preview',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                [
                                    'type' => 'text',
                                    'text' => $prompt,
                                ],
                                [
                                    'type' => 'image_url',
                                    'image_url' => [
                                        'url' => 'data:'.$document->mime_type.';base64,'.base64_encode($fileContent),
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]);

        if (! $response->successful()) {
            throw new \Exception('AI API request failed: '.$response->body());
        }

        $data = $response->json();
        $content = $data['choices'][0]['message']['content'] ?? '{}';

        return json_decode($content, true) ?: [];
    }

    private function extractGenericData(string $fileContent, Document $document): array
    {
        return [
            'document_type' => $document->type,
            'extracted_text' => substr($fileContent, 0, 1000),
            'file_size' => $document->file_size,
        ];
    }

    private function validateExtractedData(array $data): bool
    {
        if (empty($data)) {
            return false;
        }

        // Validate required fields
        $requiredFields = ['invoice_number', 'vendor_name'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return false;
            }
        }

        return true;
    }

    private function saveProcessedData(Document $document, array $data): void
    {
        $processedData = $document->processed_data ?? [];

        $document->update([
            'processed_data' => array_merge($processedData, $data),
            'validated_at' => now()->toISOString(),
        ]);

        if (! empty($data['line_items']) && isset($data['line_items'])) {
            $document->invoiceLines()->delete();

            foreach ($data['line_items'] as $lineItem) {
                InvoiceLine::create([
                    'document_id' => $document->id,
                    'description' => $lineItem['description'] ?? '',
                    'quantity' => (float) ($lineItem['quantity'] ?? 1),
                    'unit_price' => (float) ($lineItem['unit_price'] ?? 0),
                    'total_amount' => (float) ($lineItem['total_amount'] ?? 0),
                    'tax_rate' => (float) ($lineItem['tax_rate'] ?? 0),
                    'tax_amount' => (float) ($lineItem['tax_amount'] ?? 0),
                ]);
            }
        }
    }

    private function handleProcessingError(Document $document, string $error): void
    {
        $document->update([
            'status' => 'failed',
            'processing_error' => $error,
        ]);

        ProcessingAttempt::create([
            'document_id' => $document->id,
            'status' => 'failed',
            'error_message' => $error,
            'attempt_number' => $document->processingAttempts()->max('attempt_number') + 1,
            'processing_time_ms' => 0,
        ]);

        Log::error('Document processing failed', [
            'document_id' => $document->id,
            'error' => $error,
        ]);
    }
}
