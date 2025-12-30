<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Document;
use App\Models\EmailSettings;
use App\Models\Integration;
use App\Models\ProcessingAttempt;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoTenantSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating demo tenant with sample data...');

        // Create demo tenant
        $tenant = Tenant::create([
            'id' => 'demo-tenant',
            'plan' => 'pro',
            'document_limit' => 500,
            'stripe_customer_id' => 'cus_demo_'.Str::random(10),
        ]);

        $this->command->info('Demo tenant created with ID: '.$tenant->id);

        // Create demo domain
        $tenant->domains()->create([
            'domain' => 'demo.docscanner.test',
        ]);

        // Create demo user
        $user = User::create([
            'name' => 'Demo User',
            'email' => 'demo@docscanner.test',
            'password' => Hash::make('password123'),
            'tenant_id' => $tenant->id,
        ]);

        // Assign admin role
        $superAdminRole = \Spatie\Permission\Models\Role::where('name', 'super_admin')->first();
        $user->assignRole($superAdminRole);

        $this->command->info('Demo user created: '.$user->email.' (ID: '.$user->id.')');

        // Create email settings for demo user
        $emailSettings = EmailSettings::create([
            'user_id' => $user->id,
            'process_email_attachments' => true,
            'allowed_senders' => ['demo@example.com', 'invoices@company.test'],
            'blocked_senders' => [],
            'default_document_type' => 'invoice',
            'auto_process_attachments' => true,
            'delete_processed_emails' => false,
        ]);

        $this->command->info('Email settings configured for demo user');

        // Create demo Xero integration
        $xeroIntegration = Integration::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'type' => 'accounting',
            'provider' => 'xero',
            'access_token' => 'demo_xero_token',
            'refresh_token' => 'demo_xero_refresh',
            'expires_at' => now()->addYear(1),
            'is_active' => true,
            'settings' => [
                'auto_sync' => false,
                'revenue_account_code' => '200',
                'auto_send_invoices' => true,
            ],
        ]);

        $this->command->info('Xero integration created for demo tenant');

        // Create sample documents
        $documents = Document::factory()->count(25)->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'title' => fn ($index) => 'Demo Invoice '.($index + 1),
            'type' => ['invoice', 'receipt', 'purchase_order', 'other'][array_rand($index, 3)],
            'status' => ['pending', 'processing', 'processed', 'failed'][array_rand($index, 3)],
            'processed_data' => fn ($index) => [
                'invoice_number' => 'INV-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                'vendor_name' => ['ACME Corp', 'Demo Company', 'Test Suppliers Ltd', 'XYZ Industries'][array_rand(
                    $index,
                    3,
                )],
                'invoice_date' => now()->subDays(rand(5, 30))->format('Y-m-d'),
                'due_date' => now()->subDays(rand(10, 60))->format('Y-m-d'),
                'total_amount' => rand(50, 500) + rand(10, 200),
                'tax_amount' => round(rand(50, 500) * 0.2, 2),
            ],
        ]);

        foreach ($documents as $document) {
            // Create invoice lines for invoice documents
            if ($document->type === 'invoice') {
                $lineCount = rand(1, 5);
                $totalAmount = $document->processed_data['total_amount'] ?? 0;
                $remainingAmount = $totalAmount;
                $lineItems = [];

                for ($i = 0; $i < $lineCount; $i++) {
                    $lineAmount = rand(10, 200);
                    $description = 'Line item '.($i + 1);

                    if ($remainingAmount >= $lineAmount) {
                        $remainingAmount -= $lineAmount;
                        $taxAmount = round($lineAmount * 0.2, 2);
                        $lineTotal = $lineAmount + $taxAmount;
                        $unitPrice = $lineAmount / 1.5;
                        $quantity = 1;
                        $taxRate = 20; // 20% VAT
                        $taxAmount = round($lineAmount * 0.2, 2);
                    } else {
                        // Distribute remaining amount across lines
                        $unitPrice = $remainingAmount / $lineCount;
                        $taxRate = 20; // 20% VAT
                        $taxAmount = round($unitPrice * 0.2, 2);
                        $lineTotal = $unitPrice + ($unitPrice * 0.2);
                        $remainingAmount -= $unitPrice + $taxAmount;
                    }

                    $lineItems[] = [
                        'description' => $description,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_amount' => $lineTotal,
                        'tax_amount' => $taxAmount,
                    ];

                    $document->invoiceLines()->createMany($lineItems);
                }

                // Create processing attempts
                if (rand(1, 10) <= 3) {
                    $attemptCount = rand(1, 3);
                    for ($i = 0; $i < $attemptCount; $i++) {
                        ProcessingAttempt::create([
                            'document_id' => $document->id,
                            'status' => ['pending', 'failed', 'success'][array_rand($i, 2)],
                            'attempt_number' => $i + 1,
                            'error_message' => $i === 2 ? 'Simulated API error' : null,
                            'processing_time_ms' => rand(500, 2000),
                        ]);
                    }
                }
            }

            $this->command->info('Created '.count($documents).' demo documents with '.count($documents).' invoice lines',
            );
        }
    }
}
