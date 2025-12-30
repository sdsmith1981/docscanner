<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Document;
use App\Models\InvoiceLine;
use App\Models\ProcessingAttempt;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DocumentProcessingService
{
    private const AI_API_URL = 'https://api.openai.com/v1/chat/completions';
    
    public function processDocument(Document $document): void
    {
        $attempt = $this->createProcessingAttempt($document);
        
        try {
            $startTime = microtime(true);
            
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
            } else {
                throw new \Exception('Extracted data validation failed');
            }
        } catch (\Exception $e) {
            Log::error('Document processing failed', [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
            ]);
            
            $document->update([
                'status' => 'failed',
                'processing_error' => $e->getMessage(),
            ]);
            
            $attempt->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'processing_time_ms' => (int) ((microtime(true) - $startTime) * 1000),
            ]);
        }
    }
    
    private function createProcessingAttempt(Document $document): ProcessingAttempt
    {
        $attemptNumber = $document->processingAttempts()->max('attempt_number') + 1;
        
        return $document->processingAttempts()->create([
            'attempt_number' => $attemptNumber,
            'status' => 'pending',
        ]);
    }
    
    private function extractDataFromDocument(Document $document): array
    {
        $fileContent = Storage::disk('s3')->get($document->file_path);
        
        if ($document->type === 'invoice') {
            return $this->extractInvoiceData($fileContent, $document);
        }
        
        return $this->extractGenericData($fileContent);
    }
    
    private function extractInvoiceData(string $fileContent, Document $document): array
    {
        $prompt = "Extract the following information from this invoice document in JSON format:
        {
            \"invoice_number\": \"string\",
            \"invoice_date\": \"YYYY-MM-DD\",
            \"due_date\": \"YYYY-MM-DD\",
            \"vendor_name\": \"string\",
            \"vendor_address\": \"string\",
            \"total_amount\": \"decimal\",
            \"tax_amount\": \"decimal\",
            \"subtotal\": \"decimal\",
            \"line_items\": [
                {
                    \"description\": \"string\",
                    \"quantity\": \"decimal\",
                    \"unit_price\": \"decimal\",
                    \"total_amount\": \"decimal\",
                    \"tax_rate\": \"decimal\"
                }
            ]
        }
        
        Document content: " . base64_encode($fileContent);
        
        $response = Http::withToken(config('services.openai.api_key'))
            ->post(self::AI_API_URL, [
                'model' => 'gpt-4-vision-preview',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => $prompt,
                            ],
                            [
                                'type' => 'image_url',
                                'image_url' => [
                                    'url' => 'data:' . $document->mime_type . ';base64,' . base64_encode($fileContent),
                                ],
                            ],
                        ],
                    ],
                ],
                'max_tokens' => 2000,
            ]);
        
        if ($response->failed()) {
            throw new \Exception('AI API request failed: ' . $response->body());
        }
        
        $data = $response->json();
        $content = $data['choices'][0]['message']['content'] ?? '{}';
        
        return json_decode($content, true) ?: [];
    }
    
    private function extractGenericData(string $fileContent): array
    {
        return [
            'extracted_text' => substr($fileContent, 0, 1000),
            'document_type' => 'generic',
        ];
    }
    
    private function validateExtractedData(array $data): bool
    {
        if (empty($data)) {
            return false;
        }
        
        if (isset($data['total_amount']) && !is_numeric($data['total_amount'])) {
            return false;
        }
        
        if (isset($data['line_items']) && !is_array($data['line_items'])) {
            return false;
        }
        
        return true;
    }
    
    private function saveProcessedData(Document $document, array $data): void
    {
        $document->update(['processed_data' => $data]);
        
        if (isset($data['line_items']) && is_array($data['line_items'])) {
            $document->invoiceLines()->delete();
            
            foreach ($data['line_items'] as $item) {
                InvoiceLine::create([
                    'document_id' => $document->id,
                    'description' => $item['description'] ?? '',
                    'quantity' => (float) ($item['quantity'] ?? 1),
                    'unit_price' => (float) ($item['unit_price'] ?? 0),
                    'total_amount' => (float) ($item['total_amount'] ?? 0),
                    'tax_rate' => (float) ($item['tax_rate'] ?? 0),
                    'tax_amount' => (float) ($item['tax_amount'] ?? 0),
                ]);
            }
        }
    }
}