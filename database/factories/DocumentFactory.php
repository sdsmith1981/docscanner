<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Document;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\Document>
     */
    protected $model = Document::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'type' => $this->faker->randomElement(['invoice', 'receipt', 'purchase_order', 'other']),
            'description' => $this->faker->paragraph(2),
            'file_name' => $this->faker->word() . '.' . $this->faker->fileExtension(),
            'file_path' => 'documents/' . $this->faker->uuid() . '/' . $this->faker->word() . '.' . $this->faker->fileExtension(),
            'file_size' => $this->faker->numberBetween(1024, 10485760), // 1KB to 10MB
            'file_mime_type' => $this->faker->mimeType(),
            'processing_status' => $this->faker->randomElement(['pending', 'processing', 'completed', 'failed']),
            'customer_id' => $this->faker->optional()->uuid(),
            'invoice_number' => $this->faker->optional()->numerify('INV-####'),
            'invoice_date' => $this->faker->optional()->date(),
            'due_date' => $this->faker->optional()->date(),
            'amount' => $this->faker->optional()->randomFloat(2, 0, 10000),
            'currency' => $this->faker->optional()->currencyCode(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate the document is an invoice.
     */
    public function invoice(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'invoice',
            'invoice_number' => $this->faker->numerify('INV-####'),
            'invoice_date' => $this->faker->dateBetween('-1 year', 'now'),
            'due_date' => $this->faker->dateBetween('now', '+1 year'),
            'amount' => $this->faker->randomFloat(2, 100, 5000),
            'currency' => 'GBP',
        ]);
    }

    /**
     * Indicate the document is a receipt.
     */
    public function receipt(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'receipt',
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'currency' => 'GBP',
        ]);
    }

    /**
     * Indicate the document is a purchase order.
     */
    public function purchaseOrder(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'purchase_order',
            'customer_id' => $this->faker->uuid(),
        ]);
    }

    /**
     * Indicate the document processing is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'processing_status' => 'completed',
            'processing_completed_at' => now(),
        ]);
    }

    /**
     * Indicate the document processing has failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'processing_status' => 'failed',
            'processing_error' => $this->faker->sentence(10),
        ]);
    }

    /**
     * Indicate the document is currently processing.
     */
    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'processing_status' => 'processing',
            'processing_started_at' => now(),
        ]);
    }
}