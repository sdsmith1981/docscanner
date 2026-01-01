<?php

declare(strict_types=1);

use App\Models\Document;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('allows user to create document', function () {
    $user = User::factory()->create();

    $response = test()->actingAs($user)
        ->post(route('documents.store'), [
            'title' => 'Test Invoice',
            'type' => 'invoice',
            'file' => uploaded_file(base64_encode('test content')),
        ]);

    $response->assertRedirect(); // Should redirect after successful creation
    test()->assertDatabaseHas('documents', [
        'title' => 'Test Invoice',
        'type' => 'invoice',
        'user_id' => $user->id,
        'status' => 'pending',
    ]);
});

it('updates document processing status', function () {
    $document = Document::factory()->create([
        'status' => 'pending',
        'processed_data' => null,
    ]);

    $document->update(['status' => 'processed']);

    test()->assertDatabaseHas('documents', [
        'id' => $document->id,
        'status' => 'processed',
        'processed_at' => now(),
    ]);
});

it('allows document to be searched', function () {
    $user = User::factory()->create();
    $document = Document::factory()->create([
        'user_id' => $user->id,
        'title' => 'Searchable Invoice',
        'processed_data' => [
            'invoice_number' => 'INV-001',
            'vendor_name' => 'Test Company',
        ],
    ]);

    test()->actingAs($user)
        ->get(route('search.index'), ['q' => 'Searchable'])
        ->assertOk()
        ->assertSee('Searchable Invoice');
});

it('detects invoice validation total mismatch', function () {
    $document = Document::factory()->create([
        'processed_data' => [
            'total_amount' => 100.00,
            'line_items' => [
                [
                    'quantity' => 2,
                    'unit_price' => 50.00,
                    'total_amount' => 90.00,
                    'tax_amount' => 18.00,
                ],
                [
                    'quantity' => 1,
                    'unit_price' => 20.00,
                    'total_amount' => 20.00,
                    'tax_amount' => 4.00,
                ],
            ],
        ],
    ]);

    // Should detect the mismatch (100+90+18 = 108 vs 90+20+4 = 114)
    test()->assertNotNull($document->processed_data['validation_results']);
    test()->assertTrue(! $document->processed_data['validation_results']['is_valid']);
    test()->assertContains('total_mismatch', $document->processed_data['validation_results']['errors']);
});

it('stores file upload in minio', function () {
    $user = User::factory()->create();
    $file = uploaded_file(base64_encode('test document content'));

    $response = test()->actingAs($user)
        ->post(route('documents.store'), [
            'title' => 'Uploaded Document',
            'file' => $file,
        ]);

    $response->assertStatus(201);
    test()->assertTrue(Storage::disk('s3')->exists($response->json('file_path')));
});

it('returns correct pagination for search', function () {
    $user = User::factory()->create();

    // Create 25 documents for pagination testing
    foreach (range(1, 25) as $i) {
        Document::factory()->create([
            'user_id' => $user->id,
            'title' => "Document {$i}",
            'status' => 'processed',
        ]);
    }

    $response = test()->actingAs($user)
        ->get(route('search.index'), ['per_page' => 10]);

    $response->assertOk();
    $response->assertJsonCount('documents.data', 10);
    $response->assertJsonPath('documents.current_page', 1);
});

it('allows email settings to be configured', function () {
    $user = User::factory()->create();

    $response = test()->actingAs($user)
        ->put(route('email-settings.update'), [
            'process_email_attachments' => true,
            'allowed_senders' => ['test@example.com'],
            'default_document_type' => 'invoice',
            'auto_process_attachments' => true,
        ]);

    $response->assertRedirect();
    test()->assertDatabaseHas('email_settings', [
        'user_id' => $user->id,
        'process_email_attachments' => true,
        'allowed_senders' => json_encode(['test@example.com']),
        'default_document_type' => 'invoice',
        'auto_process_attachments' => true,
    ]);
});

it('handles integration creation flow', function () {
    $user = User::factory()->create();

    // Test Xero connection attempt
    $response = test()->actingAs($user)
        ->post(route('integrations.store'), [
            'provider' => 'xero',
            'auto_sync' => false,
        ]);

    $response->assertRedirect();
    test()->assertDatabaseHas('integrations', [
        'user_id' => $user->id,
        'provider' => 'xero',
        'is_active' => true,
    ]);
});

it('ensures tenant isolation', function () {
    // Create documents for two different tenants
    $tenant1 = \App\Models\Tenant::factory()->create();
    $tenant2 = \App\Models\Tenant::factory()->create();

    $user1 = User::factory()->create(['tenant_id' => $tenant1->id]);
    $user2 = User::factory()->create(['tenant_id' => $tenant2->id]);

    $document1 = Document::factory()->create([
        'user_id' => $user1->id,
        'title' => 'Tenant 1 Document',
    ]);

    $document2 = Document::factory()->create([
        'user_id' => $user2->id,
        'title' => 'Tenant 2 Document',
    ]);

    // Test that documents are properly isolated
    test()->actingAs($user1)->get(route('documents.index'));
    test()->assertSee('Tenant 1 Document');
    test()->assertDontSee('Tenant 2 Document');

    test()->actingAs($user2)->get(route('documents.index'));
    test()->assertSee('Tenant 2 Document');
    test()->assertDontSee('Tenant 1 Document');
});
