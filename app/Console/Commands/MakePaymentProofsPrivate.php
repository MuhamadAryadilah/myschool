<?php

namespace App\Console\Commands;

use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MakePaymentProofsPrivate extends Command
{
    protected $signature = 'payments:make-private';

    protected $description = 'Pindahkan bukti bayar lama dari disk public ke disk privat';

    public function handle(): int
    {
        $public = Storage::disk('public');
        $private = Storage::disk('local');
        $moved = 0;
        $already = 0;
        $missing = 0;

        foreach (Payment::whereNotNull('proof_path')->cursor() as $payment) {
            $path = $payment->proof_path;

            if ($private->exists($path)) {
                if ($public->exists($path)) {
                    $public->delete($path);
                }
                $already++;
                continue;
            }

            if (! $public->exists($path)) {
                $this->warn("Tidak ketemu: {$path}");
                $missing++;
                continue;
            }

            $private->put($path, $public->get($path));

            if ($private->exists($path)) {
                $public->delete($path);
                $moved++;
            }
        }

        $this->info("Dipindah: {$moved}, sudah privat: {$already}, tidak ketemu: {$missing}");

        return self::SUCCESS;
    }
}