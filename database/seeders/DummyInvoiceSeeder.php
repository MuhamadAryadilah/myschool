<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DummyInvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $students = Student::orderBy('id')->get();

        if ($students->isEmpty()) {
            $this->command->warn('Belum ada siswa. Tambahkan siswa dulu.');

            return;
        }

        $bulan = [
            ['title' => 'SPP Agustus 2026', 'due' => '2026-08-10'],
            ['title' => 'SPP September 2026', 'due' => '2026-09-10'],
            ['title' => 'SPP Oktober 2026', 'due' => '2026-10-10'],
            ['title' => 'SPP November 2026', 'due' => '2026-11-10'],
        ];

        $dibuat = 0;
        $dilewati = 0;

        foreach ($students as $student) {
            $amount = match (substr((string) $student->kelas, 0, 1)) {
                '7' => 450000,
                '8' => 475000,
                '9' => 500000,
                default => 450000,
            };

            foreach ($bulan as $i => $b) {
                $lunas = match ($i) {
                    0 => $student->id % 5 !== 0,
                    1 => $student->id % 3 !== 0,
                    2 => $student->id % 4 === 0,
                    default => false,
                };

                $due = Carbon::parse($b['due']);

                $invoice = Invoice::firstOrCreate(
                    ['student_id' => $student->id, 'title' => $b['title']],
                    [
                        'amount' => $amount,
                        'due_date' => $due->toDateString(),
                        'status' => $lunas ? 'lunas' : 'belum_lunas',
                        'paid_at' => $lunas
                            ? ($due->isFuture() ? Carbon::parse('2026-10-01 10:00') : $due->copy()->subDays($student->id % 5)->setTime(10, 0))
                            : null,
                    ]
                );

                $invoice->wasRecentlyCreated ? $dibuat++ : $dilewati++;
            }
        }

        $this->command->info("Selesai: {$dibuat} tagihan baru dibuat, {$dilewati} dilewati (sudah ada).");
    }
}