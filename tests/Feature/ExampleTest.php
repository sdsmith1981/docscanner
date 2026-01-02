<?php

use Tests\Traits\TenantTestTrait;

uses(TenantTestTrait::class);

beforeEach(function () {
    $this->setUpTenant();
});

afterEach(function () {
    $this->tearDownTenant();
});

it('returns a successful response', function () {
    $response = $this->get('/');

    // Welcome page should be accessible without authentication
    $response->assertRedirect();
});
