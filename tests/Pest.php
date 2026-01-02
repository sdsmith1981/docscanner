<?php

use Tests\TenantTestCase;
use Tests\TestCase;
use Tests\Traits\RefreshDatabaseTrait;
use Tests\Traits\RefreshDatabaseWithTenantTrait;

pest()
    ->extend(TenantTestCase::class)
    ->use(RefreshDatabaseWithTenantTrait::class)
    ->in('Tenant');

pest()
    ->extend(TestCase::class)
    ->use(RefreshDatabaseTrait::class)
    ->in('Feature');
