<?php

use Tests\TestCase;
use Tests\Traits\RefreshDatabaseWithTenantTrait;

pest()->extend(TestCase::class)
    ->use(RefreshDatabaseWithTenantTrait::class)
    ->in('Feature');
