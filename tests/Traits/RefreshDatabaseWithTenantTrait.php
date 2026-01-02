<?php

declare(strict_types=1);

namespace Tests\Traits;

use App\Models\Tenant;
use Database\Seeders\Tenants\PermissionSeeder;
use Database\Seeders\Tenants\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\ParallelTesting;
use Illuminate\Support\Facades\URL;

trait RefreshDatabaseWithTenantTrait
{
    use RefreshDatabase {
        RefreshDatabase::beginDatabaseTransaction as parentBeginDatabaseTransaction;
    }

    /**
     * The database connections that should have transactions.
     *
     * `null` is the default landlord connection, used for system-wide operations.
     * `tenant` is the tenant connection, specific to each tenant in the multi-tenant system.
     */
    protected array $connectionsToTransact = [null, 'tenant'];

    /**
     * We need to hook initialize tenancy _before_ we start the database
     * transaction, otherwise it cannot find the tenant connection.
     * This function initializes the tenant setup before starting a transaction.
     */
    public function beginDatabaseTransaction(): void
    {
        // Initialize tenant before beginning the database transaction.
        $this->initializeTenant();

        // Continue with the default database transaction setup.
        $this->parentBeginDatabaseTransaction();
    }

    /**
     * Initialize tenant for testing environment.
     * This function sets up a specific tenant for testing purposes.
     */
    public function initializeTenant(): void
    {
        // Hardcoded tenant ID for testing purposes.
        $tenantId = 'test_tenant';

        // Retrieve or create the tenant with the given ID.
        $tenant = Tenant::firstOr(function () use ($tenantId) {

            /**
             * Set the tenant prefix to the parallel testing token.
             * This is necessary to avoid database collisions when running tests in parallel.
             */
            if (ParallelTesting::token()) {
                config(['tenancy.database.prefix' => config('tenancy.database.prefix').ParallelTesting::token().'_']);
            }

            // Define the database name for the tenant.
            $dbName = config('tenancy.database.prefix').$tenantId;

            // Drop the database if it already exists.
            DB::unprepared("DROP DATABASE IF EXISTS `{$dbName}`");

            // Create the tenant and associated domain if they don't exist.
            $t = Tenant::create([
                'id' => $tenantId,
                'name' => 'Test Tenant',
                'inbound_email' => 'inbound_email_test',
                'inbound_email_bucket' => 'inbound_email_bucket_test',
            ]);

            if (! $t->domains()->count()) {
                $t->domains()->create(['domain' => $tenantId]);
            }

            return $t;
        });

        // Initialize tenancy for the current test.
        tenancy()->initialize($tenant);

        // Just created the tenant?
        if ($tenant->wasRecentlyCreated) {
            // Run our required seeders.
            // TODO: Streamline this - maybe using a command that lists seeders required to run on deployment?
            Artisan::call('db:seed', ['--class' => RoleSeeder::class]);
            Artisan::call('db:seed', ['--class' => PermissionSeeder::class]);
        }

        // Set the root URL for the current tenant.
        URL::forceRootUrl('http://tenant.test');
    }
}
