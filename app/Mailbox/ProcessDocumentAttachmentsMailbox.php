<?php

declare(strict_types=1);

namespace App\Mailbox;

use App\Models\Document;
use App\Models\EmailSettings;
use App\Models\User;
use App\Services\DocumentProcessingService;
use BeyondCode\Mailbox\InboundEmail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessDocumentAttachmentsMailbox
{
    private DocumentProcessingService $processingService;

    public function __construct(DocumentProcessingService $processingService)
    {
        $this->processingService = $processingService;
    }

    public function __invoke(InboundEmail $email): void
    {
        try {
            $sender = $email->from();
            $tenantId = $this->extractTenantIdFromEmail($sender);

            if (! $tenantId) {
                Log::warning('Could not determine tenant for email', ['from' => $sender]);

                return;
            }

            tenancy()->initialize(tenant($tenantId), function () use ($email, $sender) {
                $user = $this->findUserByEmail($sender);

                if (! $user) {
                    Log::warning('User not found for email', ['from' => $sender]);

                    return;
                }

                $emailSettings = $user->emailSettings;

                if (! $emailSettings || ! $emailSettings->process_email_attachments) {
                    Log::info('Email attachments processing disabled for user', ['user_id' => $user->id]);

                    return;
                }

                if (! $emailSettings->isSenderAllowed($sender)) {
                    Log::info('Email sender not in allowed list', ['from' => $sender, 'user_id' => $user->id]);

                    return;
                }

                $this->processAttachments($email, $user, $emailSettings);

                if ($emailSettings->delete_processed_emails) {
                    $email->delete();
                }
            });
        } catch (\Exception $e) {
            Log::error('Failed to process email attachments', [
                'error' => $e->getMessage(),
                'from' => $email->from(),
                'subject' => $email->subject(),
            ]);
        }
    }

    private function extractTenantIdFromEmail(string $email): ?string
    {
        // Extract tenant from email format: tenant-{tenant-id}@domain.com
        if (preg_match('/tenant-(\w+)@/', $email, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function findUserByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    private function processAttachments(InboundEmail $email, User $user, EmailSettings $emailSettings): void
    {
        $message = $email->getMessage();

        foreach ($message->getAllAttachmentParts() as $attachment) {
            try {
                $filename = $attachment->getFilename();
                $content = $attachment->getContent();
                $contentType = $attachment->getContentType() ?? 'application/octet-stream';

                if (! $this->isDocumentAttachment($filename, $contentType)) {
                    continue;
                }

                $documentType = $this->determineDocumentType($filename, $email, $emailSettings);

                $path = "email-attachments/{$user->id}/".uniqid().'-'.$filename;
                Storage::disk('s3')->put($path, $content);

                $document = $user->documents()->create([
                    'title' => $filename,
                    'type' => $documentType,
                    'file_path' => $path,
                    'file_size' => strlen($content),
                    'mime_type' => $contentType,
                    'status' => $emailSettings->auto_process_attachments ? 'processing' : 'pending',
                ]);

                if ($emailSettings->auto_process_attachments) {
                    $this->processingService->processDocument($document);
                }

                Log::info('Processed email attachment', [
                    'document_id' => $document->id,
                    'user_id' => $user->id,
                    'filename' => $filename,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to process email attachment', [
                    'error' => $e->getMessage(),
                    'filename' => $attachment->getFilename() ?? 'unknown',
                    'user_id' => $user->id,
                ]);
            }
        }
    }

    private function isDocumentAttachment(string $filename, string $contentType): bool
    {
        $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx'];
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        $allowedMimeTypes = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];

        return in_array($extension, $allowedExtensions) ||
               in_array($contentType, $allowedMimeTypes);
    }

    private function determineDocumentType(string $filename, InboundEmail $email, EmailSettings $emailSettings): string
    {
        $subject = strtolower($email->subject());

        // Check email subject for document type hints
        if (str_contains($subject, 'invoice')) {
            return 'invoice';
        }

        if (str_contains($subject, 'receipt')) {
            return 'receipt';
        }

        if (str_contains($subject, 'purchase order') || str_contains($subject, 'po')) {
            return 'purchase_order';
        }

        // Use user's default setting
        return $emailSettings->default_document_type;
    }
}
