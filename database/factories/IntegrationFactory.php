<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Integration;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Integration>
 */
class IntegrationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\Integration>
     */
    protected $model = Integration::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => 'tenant_test',
            'user_id' => User::factory(),
            'type' => 'accounting',
            'provider' => 'xero',
            'access_token' => 'test_token_'.uniqid(),
            'refresh_token' => 'test_refresh_'.uniqid(),
            'expires_at' => now()->addDays(30),
            'scope' => 'accounting.read',
            'provider_data' => [],
            'is_active' => true,
            'settings' => [],
        ];
    }

    /**
     * Indicate the integration is for Xero.
     */
    public function xero(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'xero',
            'provider' => 'xero',
        ]);
    }

    /**
     * Indicate the integration is for Sage.
     */
    public function sage(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'sage',
            'provider' => 'sage',
        ]);
    }

    /**
     * Indicate the integration is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subDay(),
        ]);
    }
}
