<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\Document;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum;

class DocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_document(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('documents.store'), [
                'title' => 'Test Invoice',
                'type' => 'invoice',
                'file' => uploaded_file(base64_encode('test content')),
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('documents', [
            'title' => 'Test Invoice',
            'type' => 'invoice',
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
    }

    public function test_document_processing_status_updates(): void
    {
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
    }

    public function test_document_can_be_searched(): void
    {
        $user = User::factory()->create();
        $document = Document::factory()->create([
            'user_id' => $user->id,
            'title' => 'Searchable Invoice',
            'processed_data' => [
                'invoice_number' => 'INV-001',
                'vendor_name' => 'Test Company',
            ],
        ]);

        $this->actingAs($user)
            ->get(route('search.index'), ['q' => 'Searchable'])
            ->assertOk()
            ->assertSee('Searchable Invoice');
    }

    public function test_invoice_validation_detects_total_mismatch(): void
    {
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
        $this->assertNotNull($document->processed_data['validation_results']);
        $this->assertTrue(!$document->processed_data['validation_results']['is_valid']);
        $this->assertContains('total_mismatch', $document->processed_data['validation_results']['errors']);
    }

    public function test_file_upload_stores_in_minio(): void
    {
        $user = User::factory()->create();
        $file = uploaded_file(base64_encode('test document content'));

        $response = $this->actingAs($user)
            ->post(route('documents.store'), [
                'title' => 'Uploaded Document',
                'file' => $file,
            ]);

        $response->assertStatus(201);
        $this->assertTrue(Storage::disk('s3')->exists($response->json('file_path')));
    }

    public function test_search_returns_correct_pagination(): void
    {
        $user = User::factory()->create();
        
        // Create 25 documents for pagination testing
        Document::factory()->count(25)->create([
            'user_id' => $user->id,
            'title' => "Document {$i}",
            'status' => 'processed',
        ]);

        $response = $this->actingAs($user)
            ->get(route('search.index'), ['per_page' => 10]);

        $response->assertOk();
        $response->assertJsonCount('documents.data', 10);
        $response->assertJsonPath('documents.current_page', 1);
    }

    public function test_email_settings_can_be_configured(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->put(route('email-settings.update'), [
                'process_email_attachments' => true,
                'allowed_senders' => ['test@example.com'],
                'default_document_type' => 'invoice',
                'auto_process_attachments' => true,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('email_settings', [
            'user_id' => $user->id,
            'process_email_attachments' => true,
            'allowed_senders' => json_encode(['test@example.com']),
            'default_document_type' => 'invoice',
            'auto_process_attachments' => true,
        ]);
    }

    public function test_integration_creation_flow(): void
    {
        $user = User::factory()->create();

        // Test Xero connection attempt
        $response = $this->actingAs($user)
            ->post(route('integrations.store'), [
                'provider' => 'xero',
                'auto_sync' => false,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('integrations', [
            'user_id' => $user->id,
            'provider' => 'xero',
            'is_active' => true,
        ]);
    }

    public function test_tenant_isolation(): void
    {
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
        $this->actingAs($user1)->get(route('documents.index'));
        $this->assertSee('Tenant 1 Document');
        $this->assertDontSee('Tenant 2 Document');

        $this->actingAs($user2)->get(route('documents.index'));
        $this->assertSee('Tenant 2 Document');
        $this->assertDontSee('Tenant 1 Document');
    }
}