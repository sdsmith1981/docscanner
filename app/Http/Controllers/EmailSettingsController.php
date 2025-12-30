<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmailSettingsController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        $emailSettings = $user->emailSettings ?? $user->emailSettings()->create([
            'process_email_attachments' => false,
            'allowed_senders' => [],
            'blocked_senders' => [],
            'default_document_type' => 'invoice',
            'auto_process_attachments' => true,
            'delete_processed_emails' => false,
        ]);

        return Inertia::render('EmailSettings/Edit', [
            'emailSettings' => $emailSettings,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $emailSettings = $user->emailSettings;
        $emailSettings->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Email settings updated successfully.',
        ]);
    }

    public function generateEmail(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenant = $user->tenant;

        if (! $tenant) {
            return response()->json([
                'error' => 'Tenant not found',
            ], 400);
        }

        $email = "tenant-{$tenant->id}@docscanner.test";

        return response()->json([
            'email' => $email,
            'instructions' => [
                'Forward emails with document attachments to this address',
                'Only attachments will be processed, email content will be ignored',
                'Configure your sender and filter settings below',
            ],
        ]);
    }
}
