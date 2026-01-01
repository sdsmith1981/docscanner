<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenant>
 */
class TenantFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\Tenant>
     */
    protected $model = Tenant::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->unique()->slug(2),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Create a tenant with demo data.
     */
    public function demo(): static
    {
        return $this->state(fn (array $attributes) => [
            'id' => 'demo',
        ]);
    }

    /**
     * Create a tenant with test data.
     */
    public function test(): static
    {
        return $this->state(fn (array $attributes) => [
            'id' => 'test',
        ]);
    }
}