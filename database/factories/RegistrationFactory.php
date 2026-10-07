<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RegistrationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'status' => 'menunggu',
            'student_name' => fake()->name(),
            'gender' => fake()->randomElement(['L', 'P']),
            'birth_place' => fake()->city(),
            'birth_date' => fake()->dateTimeBetween('2013-01-01', '2015-12-31')->format('Y-m-d'),
            'nisn' => null,
            'religion' => 'Islam',
            'address' => fake()->address(),
            'previous_school' => 'SD Negeri 1',
            'father_name' => fake()->name('male'),
            'mother_name' => fake()->name('female'),
            'guardian_name' => fake()->name(),
            'guardian_relation' => 'ayah',
            'guardian_job' => null,
            'phone' => '081234567890',
            'email' => fake()->unique()->safeEmail(),
        ];
    }

    public function diterima(): static
    {
        return $this->state(fn () => ['status' => 'diterima', 'processed_at' => now()]);
    }

    public function ditolak(string $reason = 'Kuota penuh'): static
    {
        return $this->state(fn () => [
            'status' => 'ditolak',
            'admin_note' => $reason,
            'processed_at' => now(),
        ]);
    }
}