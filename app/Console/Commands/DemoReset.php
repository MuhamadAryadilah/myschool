<?php

namespace App\Console\Commands;

use Database\Seeders\DemoSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DemoReset extends Command
{
    protected $signature = 'demo:reset {--force : Lewati konfirmasi}';

    protected $description = 'Kosongkan database demo dan isi ulang dengan data awal (hanya untuk DEMO_MODE)';

    public function handle(): int
    {
        if (! config('demo.enabled')) {
            $this->error('DEMO_MODE tidak aktif. Dibatalkan.');

            return self::FAILURE;
        }

        $connection = config('database.default');
        $database = (string) config("database.connections.{$connection}.database");

        // Pengaman: jangan pernah menghapus database yang namanya tidak mengandung "demo"
        if (! str_contains(strtolower($database), 'demo')) {
            $this->error("Nama database \"{$database}\" tidak mengandung kata demo. Dibatalkan supaya data asli aman.");

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm("Hapus semua data di database \"{$database}\" dan isi ulang?")) {
            $this->info('Dibatalkan.');

            return self::SUCCESS;
        }

        Storage::disk('local')->deleteDirectory('payments');

        $this->call('migrate:fresh', [
            '--seed'   => true,
            '--seeder' => DemoSeeder::class,
            '--force'  => true,
        ]);

        $this->info('Data demo diisi ulang.');

        return self::SUCCESS;
    }
}