<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'title' => 'SPP ' . fake()->monthName(),
            'amount' => fake()->numberBetween(1, 10) * 100000,
            'due_date' => now()->addDays(14)->toDateString(),
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'status' => 'lunas',
            'paid_at' => now(),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn () => [
            'due_date' => now()->subDays(5)->toDateString(),
        ]);
    }
}