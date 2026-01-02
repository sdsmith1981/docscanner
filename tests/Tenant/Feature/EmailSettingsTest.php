<?php

use App\Models\User;



it('can create user and access email settings', function () {
    $user = $this->createUser();

    // User should not have email settings by default (hasOne relationship)
    expect($user->emailSettings)->toBeNull();
    expect($user->emailSettings()->count())->toBe(0);
});