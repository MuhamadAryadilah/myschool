<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use App\Models\RegistrationStep;
use Illuminate\Database\Seeder;

class RegistrationRulesSeeder extends Seeder
{
    public function run(): void
    {
        if (AppSetting::read('reg_min_age') === null && AppSetting::read('reg_max_age') === null) {
            AppSetting::put('reg_min_age', 11);
            AppSetting::put('reg_max_age', 14);
            AppSetting::put('reg_age_date', '2027-07-01');
        }

        if (RegistrationStep::exists()) {
            return;
        }

        $langkah = [
            ['title' => 'Pendaftaran diperiksa sekolah', 'description' => 'Panitia memeriksa data yang Anda kirim. Hasilnya diberitahukan lewat email.'],
            ['title' => 'Pembayaran biaya formulir', 'description' => 'Bayar biaya formulir sesuai petunjuk yang dikirim sekolah lewat email atau WhatsApp. Simpan bukti pembayaran.'],
            ['title' => 'Jadwal tes seleksi', 'description' => 'Sekolah mengirim jadwal dan lokasi tes. Datang tepat waktu dan bawa dokumen yang diminta.'],
            ['title' => 'Pengumuman hasil', 'description' => 'Hasil tes diumumkan lewat email. Jika diterima, Anda mendapat link untuk membuat password akun MySchool.'],
            ['title' => 'Daftar ulang dan pembayaran awal', 'description' => 'Setelah diterima, lakukan daftar ulang dan lunasi tagihan pertama lewat akun MySchool.'],
        ];

        foreach ($langkah as $i => $data) {
            RegistrationStep::create($data + ['sort_order' => $i + 1, 'is_active' => true]);
        }
    }
}