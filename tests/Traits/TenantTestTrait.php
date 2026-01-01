<?php

declare(strict_types=1);

namespace Tests\Traits;

use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\ParallelTesting;
use Illuminate\Support\Facades\URL;

trait TenantTestTrait
{
    use RefreshDatabase, WithFaker;

    /**
     * The tenant instance for the current test.
     */
    protected ?Tenant $tenant = null;

    /**
     * The database connections that should have transactions.
     */
    protected array $connectionsToTransact = [null, 'mysql'];

    

    /**
     * Setup the test environment with tenant.
     */
    public function setUpTenant(): void
    {
        $this->createTestTenant();
        $this->initializeTenant();
        $this->runTenantMigrations();
        $this->runTenantSeeders();
    }

    /**
     * Clean up the test environment.
     */
    public function tearDownTenant(): void
    {
        if ($this->tenant) {
            $this->artisan('tenants:migrate:rollback', ['--force' => true]);
            $this->tenant->delete();
            $this->tenant = null;
        }
    }

    /**
     * Create a test tenant.
     */
    protected function createTestTenant(): void
    {
        $tenantId = 'test-tenant-' . uniqid();

        // Configure database prefix for parallel testing
        if (ParallelTesting::token()) {
            config(['tenancy.database.prefix' => config('tenancy.database.prefix') . ParallelTesting::token() . '_']);
        }

        $dbName = config('tenancy.database.prefix') . $tenantId;

        // Drop database if it exists
        DB::unprepared("DROP DATABASE IF EXISTS `{$dbName}`");

        // Create tenant
        $this->tenant = Tenant::factory()->create([
            'id' => $tenantId,
            'name' => 'Test Tenant',
        ]);

        // Create domain for tenant
        $this->tenant->domains()->create([
            'domain' => $tenantId . '.test',
        ]);
    }

    /**
     * Initialize tenancy for the test.
     */
    protected function initializeTenant(): void
    {
        tenancy()->initialize($this->tenant);
        URL::forceRootUrl('http://' . $this->tenant->domains->first()->domain);
        
        // Ensure we're using the tenant connection
        $this->assertDatabaseUsesConnection('mysql');
    }

    /**
     * Run tenant migrations.
     */
    protected function runTenantMigrations(): void
    {
        $this->artisan('tenants:migrate', ['--force' => true]);
        
        // Verify tables exist in tenant database
        $this->assertDatabaseHas('migrations', [
            'migration' => '2025_12_30_102853_create_documents_table'
        ]);
    }

    /**
     * Run tenant seeders.
     */
    protected function runTenantSeeders(): void
    {
        if ($this->tenant->wasRecentlyCreated) {
            Artisan::call('db:seed', ['--class' => RoleAndPermissionSeeder::class]);
        }
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