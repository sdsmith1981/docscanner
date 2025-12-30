<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Document\StoreDocumentRequest;
use App\Http\Requests\Document\UpdateDocumentRequest;
use App\Models\Document;
use App\Services\DocumentProcessingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class DocumentController extends Controller
{
    public function __construct(
        private DocumentProcessingService $processingService
    ) {}

    public function index(Request $request): Response
    {
        $documents = $request->user()
            ->documents()
            ->with(['invoiceLines'])
            ->latest()
            ->paginate(20);

        return Inertia::render('Documents/Index', [
            'documents' => $documents,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Documents/Create');
    }

    public function store(StoreDocumentRequest $request): RedirectResponse
    {
        $file = $request->file('file');
        $path = $file->store('documents', 's3');

        $document = $request->user()->documents()->create([
            'title' => $request->input('title', $file->getClientOriginalName()),
            'type' => $request->input('type', 'invoice'),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'status' => 'pending',
        ]);

        $this->processingService->processDocument($document);

        return redirect()
            ->route('documents.index')
            ->with('success', 'Document uploaded successfully.');
    }

    public function show(Document $document): Response
    {
        $this->authorize('view', $document);

        $document->load(['invoiceLines', 'processingAttempts']);

        return Inertia::render('Documents/Show', [
            'document' => $document,
        ]);
    }

    public function edit(Document $document): Response
    {
        $this->authorize('update', $document);

        return Inertia::render('Documents/Edit', [
            'document' => $document,
        ]);
    }

    public function update(UpdateDocumentRequest $request, Document $document): RedirectResponse
    {
        $this->authorize('update', $document);

        $document->update($request->validated());

        return redirect()
            ->route('documents.index')
            ->with('success', 'Document updated successfully.');
    }

    public function destroy(Document $document): RedirectResponse
    {
        $this->authorize('delete', $document);

        Storage::disk('s3')->delete($document->file_path);
        $document->delete();

        return redirect()
            ->route('documents.index')
            ->with('success', 'Document deleted successfully.');
    }

    public function download(Document $document): JsonResponse
    {
        $this->authorize('view', $document);

        $url = Storage::disk('s3')->temporaryUrl(
            $document->file_path,
            now()->addMinutes(30)
        );

        return response()->json(['url' => $url]);
    }

    public function retryProcessing(Document $document): RedirectResponse
    {
        $this->authorize('process', $document);

        $this->processingService->processDocument($document);

        return redirect()
            ->back()
            ->with('success', 'Document processing restarted.');
    }
}
