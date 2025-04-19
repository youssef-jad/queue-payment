<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;


class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'amount' => fake()->randomFloat(2, 10, 1000),
            'status' => fake()->randomElement(['pending', 'processing', 'completed', 'failed']),
            'created_at' => now(),
            'updated_at' => now()
        ];
    }
}
