<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Data contoh: orang tua + siswa di beberapa kelas.
 * Aman dijalankan berulang: data yang sudah ada (email / NIS sama) dilewati, tidak ditimpa.
 *
 * Jalankan: php artisan db:seed --class=DummyDataSeeder
 */
class DummyDataSeeder extends Seeder
{
    private const PASSWORD = 'ortu12345';

    public function run(): void
    {
        $families = [
        [
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@myschool.test',
            'kids' => [
                ['260001', 'Aditya Santoso', '7A'],
                ['260002', 'Salsabila Santoso', '9A'],
            ],
        ],
        [
            'name' => 'Siti Rahmawati',
            'email' => 'siti.rahmawati@myschool.test',
            'kids' => [
                ['260003', 'Rizky Pratama', '7A'],
                ['260004', 'Nabila Pratama', '8B'],
                ['260005', 'Dimas Pratama', '9A'],
            ],
        ],
        [
            'name' => 'Agus Wijaya',
            'email' => 'agus.wijaya@myschool.test',
            'kids' => [
                ['260006', 'Kevin Wijaya', '7B'],
            ],
        ],
        [
            'name' => 'Dewi Lestari',
            'email' => 'dewi.lestari@myschool.test',
            'kids' => [
                ['260007', 'Putri Lestari', '7B'],
                ['260008', 'Bayu Lestari', '8A'],
            ],
        ],
        [
            'name' => 'Hendra Gunawan',
            'email' => 'hendra.gunawan@myschool.test',
            'kids' => [
                ['260009', 'Fajar Gunawan', '8A'],
                ['260010', 'Intan Gunawan', '8B'],
            ],
        ],
        [
            'name' => 'Rina Marlina',
            'email' => 'rina.marlina@myschool.test',
            'kids' => [
                ['260011', 'Alya Marlina', '7A'],
            ],
        ],
        [
            'name' => 'Joko Susilo',
            'email' => 'joko.susilo@myschool.test',
            'kids' => [
                ['260012', 'Raka Susilo', '9A'],
                ['260013', 'Naufal Susilo', '7B'],
                ['260014', 'Zahra Susilo', '8A'],
            ],
        ],
        [
            'name' => 'Ratna Sari',
            'email' => 'ratna.sari@myschool.test',
            'kids' => [
                ['260015', 'Nadia Sari', '8B'],
            ],
        ],
        [
            'name' => 'Eko Prasetyo',
            'email' => 'eko.prasetyo@myschool.test',
            'kids' => [
                ['260016', 'Galih Prasetyo', '7A'],
                ['260017', 'Salma Prasetyo', '9B'],
            ],
        ],
        [
            'name' => 'Yuni Handayani',
            'email' => 'yuni.handayani@myschool.test',
            'kids' => [
                ['260018', 'Dinda Handayani', '7B'],
            ],
        ],
        [
            'name' => 'Ahmad Fauzi',
            'email' => 'ahmad.fauzi@myschool.test',
            'kids' => [
                ['260019', 'Farhan Fauzi', '8A'],
                ['260020', 'Aisyah Fauzi', '7A'],
            ],
        ],
        [
            'name' => 'Maya Anggraini',
            'email' => 'maya.anggraini@myschool.test',
            'kids' => [
                ['260021', 'Citra Anggraini', '8B'],
                ['260022', 'Reza Anggraini', '7B'],
            ],
        ],
        [
            'name' => 'Slamet Riyadi',
            'email' => 'slamet.riyadi@myschool.test',
            'kids' => [
                ['260023', 'Ilham Riyadi', '9A'],
            ],
        ],
        [
            'name' => 'Wulan Purnama',
            'email' => 'wulan.purnama@myschool.test',
            'kids' => [
                ['260024', 'Kirana Purnama', '8A'],
                ['260025', 'Arga Purnama', '7A'],
                ['260026', 'Sekar Purnama', '9B'],
            ],
        ],
        [
            'name' => 'Bambang Hartono',
            'email' => 'bambang.hartono@myschool.test',
            'kids' => [
                ['260027', 'Yoga Hartono', '8B'],
                ['260028', 'Tiara Hartono', '7B'],
            ],
        ],
        [
            'name' => 'Fitri Nuraini',
            'email' => 'fitri.nuraini@myschool.test',
            'kids' => [
                ['260029', 'Alif Nuraini', '8A'],
            ],
        ],
        [
            'name' => 'Rudi Hermawan',
            'email' => 'rudi.hermawan@myschool.test',
            'kids' => [
                ['260030', 'Daffa Hermawan', '9B'],
                ['260031', 'Syifa Hermawan', '7B'],
            ],
        ],
        [
            'name' => 'Nurul Hidayah',
            'email' => 'nurul.hidayah@myschool.test',
            'kids' => [
                ['260032', 'Rafi Hidayah', '8B'],
            ],
        ],
        [
            'name' => 'Taufik Ramadhan',
            'email' => 'taufik.ramadhan@myschool.test',
            'kids' => [
                ['260033', 'Gilang Ramadhan', '7A'],
                ['260034', 'Mutiara Ramadhan', '8A'],
            ],
        ],
        [
            'name' => 'Sri Wahyuni',
            'email' => 'sri.wahyuni@myschool.test',
            'kids' => [
                ['260035', 'Bima Wahyuni', '9A'],
                ['260036', 'Laras Wahyuni', '9B'],
            ],
        ],
        ];

        $ortuBaru = 0;
        $siswaBaru = 0;

        foreach ($families as $family) {
            $parent = User::where('email', $family['email'])->first();

            if (! $parent) {
                $parent = new User();
                $parent->name = $family['name'];
                $parent->email = $family['email'];
                $parent->password = self::PASSWORD;
                $parent->role = 'orang_tua';
                $parent->email_verified_at = now();
                $parent->save();
                $ortuBaru++;
            }

            foreach ($family['kids'] as [$nis, $name, $kelas]) {
                $student = Student::firstOrCreate(
                    ['nis' => $nis],
                    ['user_id' => $parent->id, 'name' => $name, 'kelas' => $kelas]
                );

                if ($student->wasRecentlyCreated) {
                    $siswaBaru++;
                }
            }
        }

        $this->command->info("Selesai: {$ortuBaru} orang tua baru, {$siswaBaru} siswa baru.");
    }
}
