<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\Traits\TenantTestTrait;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
uses(TenantTestTrait::class);

beforeEach(function () {
    $this->setUpTenant();
});

afterEach(function () {
    $this->tearDownTenant();
});

it('can create user and access email settings', function () {
    $user = User::factory()->create();

    expect($user->emailSettings)->not->toBeNull();
    expect($user->emailSettings()->count())->toBe(0);
});