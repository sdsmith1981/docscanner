<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Hash;
use App\Models\Tenant;
use App\Models\User;

trait CreatesApplication
{
    use RefreshDatabase, WithFaker;

    /**
     * Create the application.
     */
    public function createApplication(): Application
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Include test helpers
        require_once __DIR__.'/helpers.php';

        // Create a tenant for this test
        $this->tenant = Tenant::factory()->create([
            'id' => 'test-tenant-' . uniqid(),
        ]);

        // Initialize tenancy
        tenancy()->initialize($this->tenant);

        // Run tenant migrations
        $this->artisan('tenants:migrate', ['--force' => true]);
    }

    /**
     * Clean up the test environment.
     */
    protected function tearDown(): void
    {
        // Clean up tenant data
        if (isset($this->tenant)) {
            $this->artisan('tenants:migrate:rollback', ['--force' => true]);
            $this->tenant->delete();
        }

        parent::tearDown();
    }

    /**
     * Create a user for testing.
     */
    protected function createUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'password' => Hash::make('password'),
            'tenant_id' => $this->tenant->id,
        ], $attributes));
    }

    /**
     * Create a document for testing.
     */
    protected function createDocument(array $attributes = []): \App\Models\Document
    {
        return \App\Models\Document::factory()->create(array_merge([
            'user_id' => $this->createUser()->id,
        ], $attributes));
    }
}