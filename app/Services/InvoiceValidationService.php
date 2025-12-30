<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Document;
use App\Models\InvoiceLine;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class InvoiceValidationService
{
    public function validateInvoice(Document $document): array
    {
        $processedData = $document->processed_data ?? [];
        $invoiceLines = $document->invoiceLines;
        
        $validationResults = [
            'is_valid' => true,
            'errors' => [],
            'warnings' => [],
            'validations' => [],
        ];

        $totalValidation = $this->validateTotals($processedData, $invoiceLines);
        $validationResults['validations'][] = $totalValidation;
        (!$totalValidation['valid']) && ($validationResults['is_valid'] = false);
        (!$totalValidation['valid']) && ($validationResults['errors'] = array_merge($validationResults['errors'], $totalValidation['errors']));
        ($totalValidation['valid']) && ($validationResults['warnings'] = array_merge($validationResults['warnings'], $totalValidation['warnings'] ?? []));

        $taxValidation = $this->validateTaxCalculations($invoiceLines);
        $validationResults['validations'][] = $taxValidation;
        (!$taxValidation['valid']) && ($validationResults['is_valid'] = false);
        (!$taxValidation['valid']) && ($validationResults['errors'] = array_merge($validationResults['errors'], $taxValidation['errors']));
        ($taxValidation['valid']) && ($validationResults['warnings'] = array_merge($validationResults['warnings'], $taxValidation['warnings'] ?? []));

        $lineItemValidation = $this->validateLineItems($invoiceLines);
        $validationResults['validations'][] = $lineItemValidation;
        (!$lineItemValidation['valid']) && ($validationResults['is_valid'] = false);
        (!$lineItemValidation['valid']) && ($validationResults['errors'] = array_merge($validationResults['errors'], $lineItemValidation['errors']));
        ($lineItemValidation['valid']) && ($validationResults['warnings'] = array_merge($validationResults['warnings'], $lineItemValidation['warnings'] ?? []));

        $requiredFieldsValidation = $this->validateRequiredFields($processedData);
        $validationResults['validations'][] = $requiredFieldsValidation;
        (!$requiredFieldsValidation['valid']) && ($validationResults['is_valid'] = false);
        (!$requiredFieldsValidation['valid']) && ($validationResults['errors'] = array_merge($validationResults['errors'], $requiredFieldsValidation['errors']));

        $dateValidation = $this->validateDates($processedData);
        $validationResults['validations'][] = $dateValidation;
        (!$dateValidation['valid']) && ($validationResults['is_valid'] = false);
        (!$dateValidation['valid']) && ($validationResults['errors'] = array_merge($validationResults['errors'], $dateValidation['errors']));

        $document->update([
            'processed_data' => array_merge($processedData, [
                'validation_results' => $validationResults,
                'validated_at' => now()->toISOString(),
            ])
        ]);

        return $validationResults;
    }

    public function validateTotals(array $processedData, Collection $invoiceLines): array
    {
        $result = ['valid' => true, 'errors' => [], 'warnings' => []];

        $extractedTotal = (float) ($processedData['total_amount'] ?? 0);
        $calculatedTotal = $invoiceLines->sum(function ($line) {
            return (float) $line->total_amount + (float) $line->tax_amount;
        });
        
        $extractedSubtotal = (float) ($processedData['subtotal'] ?? 0);
        $calculatedSubtotal = $invoiceLines->sum('total_amount');

        $extractedTax = (float) ($processedData['tax_amount'] ?? 0);
        $calculatedTax = $invoiceLines->sum('tax_amount');

        if (abs($extractedTotal - $calculatedTotal) > 0.01) {
            $result['valid'] = false;
            $result['errors'][] = [
                'type' => 'total_mismatch',
                'message' => "Invoice total does not match line items. Extracted: £{$extractedTotal}, Calculated: £{$calculatedTotal}",
                'extracted' => $extractedTotal,
                'calculated' => $calculatedTotal,
            ];
        }

        if (abs($extractedSubtotal - $calculatedSubtotal) > 0.01) {
            $result['valid'] = false;
            $result['errors'][] = [
                'type' => 'subtotal_mismatch',
                'message' => "Subtotal does not match line items. Extracted: £{$extractedSubtotal}, Calculated: £{$calculatedSubtotal}",
                'extracted' => $extractedSubtotal,
                'calculated' => $calculatedSubtotal,
            ];
        }

        if (abs($extractedTax - $calculatedTax) > 0.01) {
            $result['valid'] = false;
            $result['errors'][] = [
                'type' => 'tax_mismatch',
                'message' => "Tax amount does not match line items. Extracted: £{$extractedTax}, Calculated: £{$calculatedTax}",
                'extracted' => $extractedTax,
                'calculated' => $calculatedTax,
            ];
        }

        if ($result['valid'] && abs($extractedTotal - $calculatedTotal) > 0.001) {
            $result['warnings'][] = [
                'type' => 'minor_total_difference',
                'message' => "Minor difference in total calculations (£" . number_format(abs($extractedTotal - $calculatedTotal), 3) . ")",
            ];
        }

        return $result;
    }

    public function validateTaxCalculations(Collection $invoiceLines): array
    {
        $result = ['valid' => true, 'errors' => [], 'warnings' => []];

        foreach ($invoiceLines as $index => $line) {
            $calculatedTaxAmount = (float) $line->total_amount * ((float) $line->tax_rate / 100);
            $actualTaxAmount = (float) $line->tax_amount;

            if (abs($calculatedTaxAmount - $actualTaxAmount) > 0.01) {
                $result['valid'] = false;
                $result['errors'][] = [
                    'type' => 'line_tax_calculation_error',
                    'message' => "Tax calculation error on line " . ($index + 1) . ". Expected: £{$calculatedTaxAmount}, Actual: £{$actualTaxAmount}",
                    'line_number' => $index + 1,
                    'description' => $line->description,
                    'expected_tax' => $calculatedTaxAmount,
                    'actual_tax' => $actualTaxAmount,
                ];
            }

            if ($line->tax_rate > 25) {
                $result['warnings'][] = [
                    'type' => 'high_tax_rate',
                    'message' => "Unusually high tax rate ({$line->tax_rate}%) on line " . ($index + 1) . "",
                    'line_number' => $index + 1,
                    'tax_rate' => $line->tax_rate,
                ];
            }

            if ($line->tax_rate < 0 && $line->tax_amount > 0) {
                $result['valid'] = false;
                $result['errors'][] = [
                    'type' => 'negative_tax_rate_with_positive_amount',
                    'message' => "Negative tax rate with positive tax amount on line " . ($index + 1) . "",
                    'line_number' => $index + 1,
                    'tax_rate' => $line->tax_rate,
                ];
            }
        }

        return $result;
    }

    public function validateLineItems(Collection $invoiceLines): array
    {
        $result = ['valid' => true, 'errors' => [], 'warnings' => []];

        if ($invoiceLines->isEmpty()) {
            $result['valid'] = false;
            $result['errors'][] = [
                'type' => 'no_line_items',
                'message' => 'Invoice has no line items',
            ];
            return $result;
        }

        foreach ($invoiceLines as $index => $line) {
            if (empty(trim($line->description))) {
                $result['valid'] = false;
                $result['errors'][] = [
                    'type' => 'empty_description',
                    'message' => "Line " . ($index + 1) . " has empty description",
                    'line_number' => $index + 1,
                ];
            }

            if ($line->quantity <= 0) {
                $result['valid'] = false;
                $result['errors'][] = [
                    'type' => 'invalid_quantity',
                    'message' => "Line " . ($index + 1) . " has invalid quantity ({$line->quantity})",
                    'line_number' => $index + 1,
                    'quantity' => $line->quantity,
                ];
            }

            if ($line->unit_price < 0 || $line->total_amount < 0) {
                $result['valid'] = false;
                $result['errors'][] = [
                    'type' => 'negative_price',
                    'message' => "Line " . ($index + 1) . " has negative price",
                    'line_number' => $index + 1,
                    'unit_price' => $line->unit_price,
                    'total_amount' => $line->total_amount,
                ];
            }

            if ($line->unit_price > 10000) {
                $result['warnings'][] = [
                    'type' => 'high_unit_price',
                    'message' => "Unusually high unit price (£{$line->unit_price}) on line " . ($index + 1) . "",
                    'line_number' => $index + 1,
                    'unit_price' => $line->unit_price,
                ];
            }

            if ($line->quantity > 10000) {
                $result['warnings'][] = [
                    'type' => 'high_quantity',
                    'message' => "Unusually high quantity ({$line->quantity}) on line " . ($index + 1) . "",
                    'line_number' => $index + 1,
                    'quantity' => $line->quantity,
                ];
            }
        }

        return $result;
    }

    public function validateRequiredFields(array $processedData): array
    {
        $result = ['valid' => true, 'errors' => []];

        $requiredFields = ['vendor_name', 'invoice_number'];
        $optionalFields = ['invoice_date', 'due_date', 'total_amount'];

        foreach ($requiredFields as $field) {
            if (empty($processedData[$field])) {
                $result['valid'] = false;
                $result['errors'][] = [
                    'type' => 'missing_required_field',
                    'message' => "Missing required field: {$field}",
                    'field' => $field,
                ];
            }
        }

        $hasFinancialData = !empty(array_intersect(array_keys($processedData), $optionalFields));
        (!$hasFinancialData) && ($result['valid'] = false);
        (!$hasFinancialData) && ($result['errors'][] = [
            'type' => 'missing_financial_data',
            'message' => 'Missing financial data (invoice date, due date, or total amount)',
            'fields_needed' => $optionalFields,
        ]);

        return $result;
    }

    public function validateDates(array $processedData): array
    {
        $result = ['valid' => true, 'errors' => [], 'warnings' => []];

        $invoiceDate = $processedData['invoice_date'] ?? null;
        $dueDate = $processedData['due_date'] ?? null;

        if ($invoiceDate) {
            $invoiceDateObj = \DateTime::createFromFormat('Y-m-d', $invoiceDate);
            (!$invoiceDateObj || $invoiceDateObj->format('Y-m-d') !== $invoiceDate) && ($result['valid'] = false);
            (!$invoiceDateObj || $invoiceDateObj->format('Y-m-d') !== $invoiceDate) && ($result['errors'][] = [
                'type' => 'invalid_invoice_date',
                'message' => "Invalid invoice date format: {$invoiceDate}",
                'invoice_date' => $invoiceDate,
            ]);

            if ($invoiceDateObj && $invoiceDateObj > now()) {
                $result['warnings'][] = [
                    'type' => 'future_invoice_date',
                    'message' => "Invoice date is in the future: {$invoiceDate}",
                    'invoice_date' => $invoiceDate,
                ];
            }
        }

        if ($dueDate) {
            $dueDateObj = \DateTime::createFromFormat('Y-m-d', $dueDate);
            (!$dueDateObj || $dueDateObj->format('Y-m-d') !== $dueDate) && ($result['valid'] = false);
            (!$dueDateObj || $dueDateObj->format('Y-m-d') !== $dueDate) && ($result['errors'][] = [
                'type' => 'invalid_due_date',
                'message' => "Invalid due date format: {$dueDate}",
                'due_date' => $dueDate,
            ]);

            if ($invoiceDateObj && $dueDateObj && $dueDateObj < $invoiceDateObj) {
                $result['valid'] = false;
                $result['errors'][] = [
                    'type' => 'due_date_before_invoice_date',
                    'message' => "Due date ({$dueDate}) is before invoice date ({$invoiceDate})",
                    'invoice_date' => $invoiceDate,
                    'due_date' => $dueDate,
                ];
            }
        }

        return $result;
    }
}