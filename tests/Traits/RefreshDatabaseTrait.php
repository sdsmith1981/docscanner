<?php

declare(strict_types=1);

namespace Tests\Traits;

use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\ParallelTesting;

trait RefreshDatabaseTrait
{
    use RefreshDatabase {
        RefreshDatabase::beginDatabaseTransaction as parentBeginDatabaseTransaction;
    }

    /**
     * We need to hook initialize tenancy _before_ we start the database
     * transaction, otherwise it cannot find the tenant connection.
     * This function initializes the tenant setup before starting a transaction.
     */
    public function beginDatabaseTransaction(): void
    {
        // Run our required seeders.
        //  Artisan::call('core:seeders');
        $this->createTestTenant();

        // Continue with the default database transaction setup.
        $this->parentBeginDatabaseTransaction();
    }

    public function createTestTenant(): void
    {
        $tenantId = 'tenant_test';

        // Retrieve or create the tenant with the given ID.
        Tenant::firstOr(function () use ($tenantId) {

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
            $t = Tenant::create(['id' => $tenantId, 'name' => 'Test Tenant']);
            if (! $t->domains()->count()) {
                $t->domains()->create(['domain' => $tenantId]);
            }

            return $t;
        });
    }
}
