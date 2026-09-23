<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'order_number' => 'IP-'.fake()->unique()->numerify('########'),
            'shipping_name' => fake()->name(),
            'shipping_email' => fake()->safeEmail(),
            'shipping_address' => fake()->address(),
            'total' => fake()->randomFloat(2, 1000, 15000),
            'status' => fake()->randomElement(Order::STATUSES),
        ];
    }
}
