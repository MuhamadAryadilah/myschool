<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['role' => 'orang_tua']),
            'name' => fake()->name(),
            'nis' => fake()->unique()->numerify('########'),
            'kelas' => fake()->randomElement(['7A', '7B', '8A', '9A']),
        ];
    }
}