<?php

use App\Models\User;
use Tests\Traits\TenantTestTrait;

uses(TenantTestTrait::class);

beforeEach(function () {
    $this->setUpTenant();
});

afterEach(function () {
    $this->tearDownTenant();
});

it('can create user and access email settings', function () {
    $user = $this->createUser();

    // User should not have email settings by default (hasOne relationship)
    expect($user->emailSettings)->toBeNull();
    expect($user->emailSettings()->count())->toBe(0);
});