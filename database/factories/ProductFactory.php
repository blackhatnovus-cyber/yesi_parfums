<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'category' => fake()->randomElement(['Floral', 'Woody', 'Fresh', 'Amber']),
            'description' => fake()->sentence(18),
            'price' => fake()->randomFloat(2, 1200, 8000),
            'stock' => fake()->numberBetween(0, 30),
            'image' => '/images/signature.jpg',
            'status' => 'active',
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'inactive',
        ]);
    }
}
