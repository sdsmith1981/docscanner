<?php

use Tests\TestCase;
use Tests\Traits\TenantTestTrait;

pest()->extend(TestCase::class)
    ->use(TenantTestTrait::class)
    ->in('Feature');

beforeEach(function () {
    $this->setUpTenant();
});

afterEach(function () {
    $this->tearDownTenant();
});
