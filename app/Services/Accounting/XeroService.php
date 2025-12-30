<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Models\Document;
use App\Models\Integration;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XeroService
{
    private const BASE_URL = 'https://api.xero.com/api.xro/2.0';
    
    public function __construct(
        private Integration $integration
    ) {}

    public function createInvoice(Document $document): ?array
    {
        if (!$this->integration->isXero() || $this->integration->isExpired()) {
            throw new \Exception('Xero integration not available or expired');
        }

        try {
            $invoiceData = $this->formatDocumentForXero($document);
            
            $response = Http::withToken($this->integration->access_token)
                ->post(self::BASE_URL . '/Invoices', $invoiceData);

            if (!$response->successful()) {
                Log::error('Failed to create Xero invoice', [
                    'document_id' => $document->id,
                    'error' => $response->body(),
                ]);
                return null;
            }

            $xeroInvoice = $response->json()[0] ?? null;
            
            if ($xeroInvoice) {
                $document->update([
                    'processed_data' => array_merge(
                        $document->processed_data ?? [],
                        ['xero_invoice_id' => $xeroInvoice['InvoiceID']]
                    )
                ]);
            }

            return $xeroInvoice;
        } catch (\Exception $e) {
            Log::error('Xero API request failed', [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
            ]);
            
            return null;
        }
    }

    public function getContacts(): array
    {
        if ($this->integration->isExpired()) {
            return [];
        }

        try {
            $response = Http::withToken($this->integration->access_token)
                ->get(self::BASE_URL . '/Contacts');

            return $response->successful() ? $response->json() : [];
        } catch (\Exception $e) {
            Log::error('Failed to fetch Xero contacts', [
                'error' => $e->getMessage(),
            ]);
            
            return [];
        }
    }

    public function getAccounts(): array
    {
        if ($this->integration->isExpired()) {
            return [];
        }

        try {
            $response = Http::withToken($this->integration->access_token)
                ->get(self::BASE_URL . '/Accounts');

            return $response->successful() ? $response->json() : [];
        } catch (\Exception $e) {
            Log::error('Failed to fetch Xero accounts', [
                'error' => $e->getMessage(),
            ]);
            
            return [];
        }
    }

    public function testConnection(): bool
    {
        try {
            $response = Http::withToken($this->integration->access_token)
                ->get(self::BASE_URL . '/Organisation');

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    private function formatDocumentForXero(Document $document): array
    {
        $processedData = $document->processed_data ?? [];
        
        $lineItems = $document->invoiceLines->map(function ($line) {
            return [
                'Description' => $line->description,
                'Quantity' => $line->quantity,
                'UnitAmount' => $line->unit_price,
                'AccountCode' => $this->integration->settings['revenue_account_code'] ?? '200',
                'TaxType' => $line->tax_rate > 0 ? 'OUTPUT' : 'NONE',
            ];
        })->toArray();

        return [
            'Type' => 'ACCREC',
            'Contact' => [
                'Name' => $processedData['vendor_name'] ?? 'Unknown Vendor',
            ],
            'Date' => $processedData['invoice_date'] ?? now()->format('Y-m-d'),
            'DueDate' => $processedData['due_date'] ?? now()->addDays(30)->format('Y-m-d'),
            'LineAmountTypes' => 'Exclusive',
            'LineItems' => $lineItems,
            'Status' => 'DRAFT',
        ];
    }

    public function refreshToken(): void
    {
        try {
            $response = Http::asForm()->post('https://identity.xero.com/connect/token', [
                'grant_type' => 'refresh_token',
                'refresh_token' => $this->integration->refresh_token,
                'client_id' => config('services.xero.client_id'),
                'client_secret' => config('services.xero.client_secret'),
            ]);

            if (!$response->successful()) {
                Log::error('Failed to refresh Xero token', [
                    'error' => $response->body(),
                ]);
                
                $this->integration->update(['is_active' => false]);
                return;
            }

            $tokenData = $response->json();
            
            $this->integration->update([
                'access_token' => $tokenData['access_token'],
                'refresh_token' => $tokenData['refresh_token'],
                'expires_at' => now()->addSeconds($tokenData['expires_in']),
            ]);
        } catch (\Exception $e) {
            Log::error('Xero token refresh failed', [
                'error' => $e->getMessage(),
            ]);
            
            $this->integration->update(['is_active' => false]);
        }
    }
}