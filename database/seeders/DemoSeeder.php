<?php

namespace Database\Seeders;

use App\Models\Extracurricular;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $password = config('demo.password');

        foreach (range(1, 3) as $i) {
            User::factory()->create([
                'name'     => "Admin Demo {$i}",
                'email'    => "admin{$i}@demo.test",
                'password' => $password,
                'role'     => 'admin',
            ]);
        }

        $orangTua = [
            'Budi Santoso', 'Siti Aminah', 'Agus Prasetyo', 'Dewi Lestari', 'Rudi Hartono',
            'Rina Wulandari', 'Hendra Gunawan', 'Maya Sari', 'Eko Saputra', 'Fitri Handayani',
        ];
        $siswa = [
            'Andi Santoso', 'Nabila Aminah', 'Raka Prasetyo', 'Aulia Lestari', 'Dimas Hartono',
            'Putri Wulandari', 'Fajar Gunawan', 'Salsa Sari', 'Bagas Saputra', 'Citra Handayani',
        ];
        $kelas = ['7A', '7B', '8A', '9A'];

        $eskul = collect([
            ['name' => 'Pramuka', 'description' => 'Kegiatan kepanduan, kemah, dan kepemimpinan.', 'schedule' => 'Jumat, 14.00-16.00'],
            ['name' => 'Basket', 'description' => 'Latihan dasar dan pertandingan antarkelas.', 'schedule' => 'Selasa, 15.00-17.00'],
            ['name' => 'Futsal', 'description' => 'Latihan tim dan turnamen sekolah.', 'schedule' => 'Kamis, 15.00-17.00'],
            ['name' => 'Paduan Suara', 'description' => 'Latihan vokal untuk acara sekolah.', 'schedule' => 'Rabu, 14.00-15.30'],
            ['name' => 'Robotik', 'description' => 'Merakit dan memprogram robot sederhana.', 'schedule' => 'Sabtu, 08.00-10.00'],
        ])->map(fn ($data) => Extracurricular::create($data + ['is_active' => true]));

        // Contoh bukti bayar (gambar sederhana) untuk bukti yang sudah menunggu verifikasi
        Storage::disk('local')->put('payments/demo-bukti.svg', $this->svgBukti());

        foreach (range(1, 10) as $i) {
            $parent = User::factory()->create([
                'name'     => $orangTua[$i - 1],
                'email'    => "ortu{$i}@demo.test",
                'phone'    => sprintf('08120000%04d', $i),
                'password' => $password,
                'role'     => 'orang_tua',
            ]);

            $student = Student::create([
                'user_id' => $parent->id,
                'name'    => $siswa[$i - 1],
                'nis'     => sprintf('2026%04d', $i),
                'kelas'   => $kelas[($i - 1) % 4],
            ]);

            if ($i <= 8) {
                $student->extracurriculars()->attach($eskul[($i - 1) % $eskul->count()]->id);
            }

            // Tiga siswa pertama ikut dua eskul, supaya contoh banyak eskul terlihat
            if ($i <= 3) {
                $student->extracurriculars()->attach($eskul[($i + 1) % $eskul->count()]->id);
            }

            // Sudah lunas
            Invoice::create([
                'student_id' => $student->id,
                'title'      => 'SPP Juli',
                'amount'     => 350000,
                'semester'   => 'ganjil',
                'academic_year' => '2026/2027',
                'due_date'   => now()->subDays(60)->toDateString(),
                'status'     => 'lunas',
                'paid_at'    => now()->subDays(55),
            ]);

            // Terlambat
            $terlambat = Invoice::create([
                'student_id' => $student->id,
                'title'      => 'SPP Agustus',
                'amount'     => 350000,
                'semester'   => 'ganjil',
                'academic_year' => '2026/2027',
                'due_date'   => now()->subDays(10)->toDateString(),
                'status'     => 'belum_lunas',
            ]);

            // Belum jatuh tempo
            Invoice::create([
                'student_id' => $student->id,
                'title'      => 'SPP Oktober',
                'amount'     => 350000,
                'semester'   => 'ganjil',
                'academic_year' => '2026/2027',
                'due_date'   => now()->addDays(14)->toDateString(),
                'status'     => 'belum_lunas',
            ]);

            // ortu6 sampai ortu10 sudah punya bukti yang menunggu, supaya admin langsung punya yang diverifikasi
            if ($i > 5) {
                $terlambat->payments()->create([
                    'user_id'    => $parent->id,
                    'proof_path' => 'payments/demo-bukti.svg',
                    'note'       => 'Transfer lewat mobile banking',
                ]);
            }
        }

        $this->call(RegistrationRulesSeeder::class);

        $now = now();
        DB::table('news')->insert([
            [
                'title'      => 'Selamat datang di versi coba MySchool',
                'slug'       => 'selamat-datang-di-versi-coba',
                'content'    => "Ini versi coba MySchool.\n\nSilakan masuk dengan salah satu akun coba di halaman login, lalu jelajahi fitur untuk admin atau orang tua. Data di sini bukan data asli dan akan direset berkala.",
                'author'     => 'Admin Demo',
                'status'     => 'published',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title'      => 'Pembayaran SPP bisa dilakukan dari HP',
                'slug'       => 'pembayaran-spp-dari-hp',
                'content'    => "Orang tua dapat melihat tagihan, mengunggah bukti bayar, dan mengunduh kuitansi langsung dari HP.\n\nAdmin memverifikasi bukti bayar dari menu Pembayaran.",
                'author'     => 'Admin Demo',
                'status'     => 'published',
                'created_at' => $now->copy()->subDay(),
                'updated_at' => $now->copy()->subDay(),
            ],
            [
                'title'      => 'Pendaftaran siswa baru dibuka',
                'slug'       => 'pendaftaran-siswa-baru-dibuka',
                'content'    => "Calon orang tua dapat mengisi formulir pendaftaran dari halaman Pendaftaran.\n\nUntuk mencoba, isi dengan data karangan, bukan data asli.",
                'author'     => 'Admin Demo',
                'status'     => 'published',
                'created_at' => $now->copy()->subDays(2),
                'updated_at' => $now->copy()->subDays(2),
            ],
        ]);
    }

    private function svgBukti(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'
            . '<svg xmlns="http://www.w3.org/2000/svg" width="480" height="320" viewBox="0 0 480 320">'
            . '<rect width="480" height="320" fill="#f3f4f6"/>'
            . '<rect x="20" y="20" width="440" height="280" rx="12" fill="#ffffff" stroke="#d1d5db"/>'
            . '<text x="240" y="140" text-anchor="middle" font-family="Arial" font-size="22" font-weight="bold" fill="#111827">CONTOH BUKTI TRANSFER</text>'
            . '<text x="240" y="180" text-anchor="middle" font-family="Arial" font-size="16" fill="#4b5563">Data demo, bukan transaksi asli</text>'
            . '</svg>';
    }
}