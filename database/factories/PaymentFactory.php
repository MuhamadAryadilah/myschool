<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'user_id' => User::factory()->state(['role' => 'orang_tua']),
            'proof_path' => 'payments/bukti.jpg',
            'note' => null,
        ];
    }

    public function rejected(string $reason = 'Bukti tidak jelas'): static
    {
        return $this->state(fn () => [
            'status' => 'ditolak',
            'admin_note' => $reason,
            'verified_at' => now(),
        ]);
    }
}