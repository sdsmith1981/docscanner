<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\EmailSettings\UpdateEmailSettingsRequest;
use App\Models\EmailSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class EmailSettingsController extends Controller
{
    public function edit(Request $request): Response
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

    public function update(UpdateEmailSettingsRequest $request): RedirectResponse
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

        $emailSettings->update($request->validated());

        return redirect()
            ->route('email-settings.edit')
            ->with('success', 'Email settings updated successfully.');
    }

    public function generateEmail(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenant = $user->tenant;
        
        if (!$tenant) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }

        $email = "tenant-{$tenant->id}@docscanner.app";
        
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