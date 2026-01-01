<?php

use Tests\Traits\TenantTestTrait;

uses(TenantTestTrait::class);

beforeEach(function () {
    $this->setUpTenant();
});

afterEach(function () {
    $this->tearDownTenant();
});

it('creates tenant with sequential numbering', function () {
    // The tenant should be created with the naming pattern tenant_1, tenant_2, etc.
    expect($this->tenant)->not->toBeNull();
    expect($this->tenant->id)->toStartWith('tenant_');
    expect($this->tenant->id)->toMatch('/tenant_\d+/');
});

it('runs tenant migrations successfully', function () {
    // Basic verification that tenant was set up
    expect($this->tenant)->not->toBeNull();
    expect($this->tenant->domains)->toHaveCount(1);
});