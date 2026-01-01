<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;
use Tests\Traits\TenantTestTrait;

trait CreatesApplication
{
    use TenantTestTrait;

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

        // Setup tenant for this test
        $this->setUpTenant();
    }

    /**
     * Clean up the test environment.
     */
    protected function tearDown(): void
    {
        $this->tearDownTenant();
        parent::tearDown();
    }
}