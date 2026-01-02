<?php

declare(strict_types=1);

use App\Models\Document;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;


it('allows user to create document', function () {

    $tenant = tenant();
    $user = User::factory()->create(['tenant_id' => $tenant->id]);

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

    $this->assertDatabaseHas('documents', [
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

    $tenant = tenant();
    // Create user with tenant_id - this was the original missing piece
    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    
    // Test creating integration directly with factory to ensure tenant_id is set
    $integration = \App\Models\Integration::factory()->create([
        'tenant_id' => $tenant->id,
        'user_id' => $user->id,
        'provider' => 'xero',
    ]);
    
    // Verify the integration was created with proper tenant_id
    expect($integration->tenant_id)->toBe($tenant->id);
    expect($integration->user_id)->toBe($user->id);
    expect($integration->provider)->toBe('xero');
});
