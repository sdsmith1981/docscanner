<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $query = $request->input('q');
        $filters = $request->only(['type', 'status', 'date_from', 'date_to']);

        if (empty($query)) {
            return Inertia::render('Search/Index', [
                'documents' => collect(),
                'filters' => $filters,
                'query' => $query,
            ]);
        }

        $documents = Document::search($query)
            ->where('user_id', $request->user()->id)
            ->when($filters['type'], fn ($query, $type) => $query->where('type', $type))
            ->when($filters['status'], fn ($query, $status) => $query->where('status', $status))
            ->when($filters['date_from'], fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'], fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->with(['invoiceLines'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return Inertia::render('Search/Index', [
            'documents' => $documents,
            'filters' => $filters,
            'query' => $query,
        ]);
    }

    public function suggestions(Request $request): JsonResponse
    {
        $query = $request->input('q');

        if (strlen($query) < 2) {
            return response()->json(['suggestions' => []]);
        }

        $documents = Document::search($query)
            ->where('user_id', $request->user()->id)
            ->where('status', 'processed')
            ->limit(5)
            ->get(['id', 'title', 'processed_data']);

        $suggestions = $documents->map(function ($document) {
            $processedData = $document->processed_data ?? [];

            return [
                'id' => $document->id,
                'title' => $document->title,
                'type' => $document->type,
                'invoice_number' => $processedData['invoice_number'] ?? null,
                'vendor_name' => $processedData['vendor_name'] ?? null,
                'total_amount' => $processedData['total_amount'] ?? null,
            ];
        })->toArray();

        return response()->json(['suggestions' => $suggestions]);
    }

    public function advanced(Request $request): JsonResponse
    {
        $query = $request->input('q');
        $filters = $request->only(['type', 'status', 'date_from', 'date_to', 'min_amount', 'max_amount']);

        $documents = Document::search($query)
            ->where('user_id', $request->user()->id)
            ->when($filters['type'], fn ($query, $type) => $query->where('type', $type))
            ->when($filters['status'], fn ($query, $status) => $query->where('status', $status))
            ->when($filters['date_from'], fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'], fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->when($filters['min_amount'], fn ($query, $amount) => $query->whereHas('invoiceLines', fn ($q) => $q->havingRaw('SUM(total_amount) >= ?', [$amount])
            )
            )
            ->when($filters['max_amount'], fn ($query, $amount) => $query->whereHas('invoiceLines', fn ($q) => $q->havingRaw('SUM(total_amount) <= ?', [$amount])
            )
            )
            ->with(['invoiceLines'])
            ->orderByDesc('created_at')
            ->paginate(50);

        $formattedDocuments = $documents->through(function ($document) {
            $processedData = $document->processed_data ?? [];

            return [
                'id' => $document->id,
                'title' => $document->title,
                'type' => $document->type,
                'status' => $document->status,
                'created_at' => $document->created_at->toISOString(),
                'invoice_number' => $processedData['invoice_number'] ?? null,
                'vendor_name' => $processedData['vendor_name'] ?? null,
                'invoice_date' => $processedData['invoice_date'] ?? null,
                'due_date' => $processedData['due_date'] ?? null,
                'total_amount' => $processedData['total_amount'] ?? null,
                'tax_amount' => $processedData['tax_amount'] ?? null,
                'line_items_count' => $document->invoiceLines->count(),
                'validation_results' => $processedData['validation_results'] ?? null,
            ];
        });

        return response()->json([
            'documents' => $formattedDocuments,
            'pagination' => [
                'current_page' => $documents->currentPage(),
                'last_page' => $documents->lastPage(),
                'per_page' => $documents->perPage(),
                'total' => $documents->total(),
                'has_more' => $documents->hasMorePages(),
            ],
        ]);
    }
}
