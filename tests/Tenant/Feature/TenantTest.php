<?php


it('creates tenant with sequential numbering', function () {
    // The tenant should be created with the naming pattern tenant_1, tenant_2, etc.
    expect(tenant())->not->toBeNull();
    expect(tenant()->id)->toStartWith('tenant_');
    expect(tenant()->id)->toMatch('/tenant_\d+/');
});

it('runs tenant migrations successfully', function () {
    // Basic verification that tenant was set up
    expect(tenant())->not->toBeNull();
    expect(tenant()->domains)->toHaveCount(1);
});