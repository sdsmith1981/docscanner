<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\DocumentProcessingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProcessingController extends Controller
{
    public function __construct(
        private DocumentProcessingService $processingService
    ) {}

    public function index(Request $request): Response
    {
        $processingAttempts = $request->user()
            ->documents()
            ->with(['processingAttempts' => function ($query) {
                return $query->orderByDesc('created_at');
            }])
            ->latest()
            ->paginate(20);

        return Inertia::render('Processing/Index', [
            'processingAttempts' => $processingAttempts,
        ]);
    }

    public function failed(Request $request): Response
    {
        $failedDocuments = $request->user()
            ->documents()
            ->where('status', 'failed')
            ->with(['processingAttempts'])
            ->latest('failed_at')
            ->paginate(20);

        return Inertia::render('Processing/Failed', [
            'failedDocuments' => $failedDocuments,
        ]);
    }

    public function retry(Request $request, Document $document): RedirectResponse
    {
        $this->authorize('process', $document);

        if ($document->user_id !== $request->user()->id) {
            abort(403);
        }

        $this->processingService->processDocument($document);

        return redirect()
            ->back()
            ->with('success', 'Document processing restarted successfully.');
    }

    public function queue(): JsonResponse
    {
        $user = request()->user();

        $queueSize = \Illuminate\Support\Facades\Queue::size('default');
        $failedJobs = \Illuminate\Support\Facades\Queue::failed()->count();

        $stats = [
            'pending_documents' => $user->documents()->where('status', 'processing')->count(),
            'failed_documents' => $user->documents()->where('status', 'failed')->count(),
            'total_documents' => $user->documents()->count(),
            'queue_size' => $queueSize,
            'failed_jobs' => $failedJobs,
        ];

        return response()->json($stats);
    }
}
