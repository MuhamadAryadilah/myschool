<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ExtracurricularFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'description' => fake()->sentence(),
            'schedule' => 'Jumat, 14.00-16.00',
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}