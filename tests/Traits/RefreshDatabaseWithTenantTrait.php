<?php

declare(strict_types=1);

namespace Tests\Traits;

use Exception;
use App\Models\Tenant;
use Database\Seeders\Tenants\PermissionSeeder;
use Database\Seeders\Tenants\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\ParallelTesting;
use Illuminate\Support\Facades\URL;
use Log;

trait RefreshDatabaseWithTenantTrait
{
    use RefreshDatabase;

    /**
     * The database connections that should have transactions.
     *
     * `null` is the default landlord connection, used for system-wide operations.
     * `tenant` is the tenant connection, specific to each tenant in the multi-tenant system.
     */
    protected array $connectionsToTransact = [null, 'tenant'];

    /**
     * Setup trait for tenancy before each test.
     * This avoids method collision with RefreshDatabase.
     */
    public static function setUpTrait(): void
    {
        parent::setUpTrait();
        // If the test uses this trait, initialize tenancy before each test.
        if (method_exists(static::class, 'initializeTenantForTest')) {
            (new static)->initializeTenantForTest();
        }
    }

    /**
     * Initialize tenant for testing environment.
     * This function sets up a specific tenant for testing purposes.
     */
    protected function initializeTenantForTest(): void
    {
        // Hardcoded tenant ID for testing purposes.
        $tenantId = 'test_tenant';

        // Retrieve or create the tenant with the given ID.
        $tenant = Tenant::query()->firstOr(function () use ($tenantId) {
            if (ParallelTesting::token()) {
                config(['tenancy.database.prefix' => config('tenancy.database.prefix') . ParallelTesting::token() . '_']);
            }
            $dbName = config('tenancy.database.prefix') . $tenantId;
            DB::unprepared("DROP DATABASE IF EXISTS `{$dbName}`");
            try {
                $t = Tenant::query()->create([
                    'id' => $tenantId,
                ]);
            } catch (Exception $e) {
                Log::error('Error creating tenant: ' . $e->getMessage());
            }
            if (! $t->domains()->count()) {
                $t->domains()->create(['domain' => 'tenant.test']);
            }
            return $t;
        });
        tenancy()->initialize($tenant);
        if ($tenant->wasRecentlyCreated) {
            // Optionally run seeders here
            //Artisan::call('db:seed', ['--class' => RoleSeeder::class]);
            //Artisan::call('db:seed', ['--class' => PermissionSeeder::class]);
        }
        URL::forceRootUrl('http://tenant.test');
    }
}
