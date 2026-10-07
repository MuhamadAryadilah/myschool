<?php

use App\Models\Extracurricular;
use App\Models\Student;

it('menampilkan petunjuk dan tidak memuat daftar siswa sebelum mencari', function () {
    $eskul = Extracurricular::create(['name' => 'Pramuka Tes', 'is_active' => true]);
    Student::factory()->create(['name' => 'Zaenal Unik Satu', 'nis' => '90001', 'kelas' => '7A']);

    $this->actingAs(makeAdmin())
        ->get(route('admin.extracurriculars.show', $eskul))
        ->assertOk()
        ->assertSee('Tambah anggota')
        ->assertSee('lalu tekan Cari')
        ->assertDontSee('Zaenal Unik Satu');
});

it('mencari siswa berdasarkan nama atau NIS', function () {
    $eskul = Extracurricular::create(['name' => 'Pramuka Tes', 'is_active' => true]);
    Student::factory()->create(['name' => 'Zaenal Unik Satu', 'nis' => '90001', 'kelas' => '7A']);
    Student::factory()->create(['name' => 'Budi Lain Dua', 'nis' => '90002', 'kelas' => '7B']);

    $admin = makeAdmin();

    $this->actingAs($admin)
        ->get(route('admin.extracurriculars.show', [$eskul, 'q' => 'Zaenal']))
        ->assertOk()
        ->assertSee('Zaenal Unik Satu')
        ->assertDontSee('Budi Lain Dua');

    $this->actingAs($admin)
        ->get(route('admin.extracurriculars.show', [$eskul, 'q' => '90002']))
        ->assertOk()
        ->assertSee('Budi Lain Dua')
        ->assertDontSee('Zaenal Unik Satu');
});

it('menyaring siswa berdasarkan kelas dan menampilkan hasil kosong yang ramah', function () {
    $eskul = Extracurricular::create(['name' => 'Pramuka Tes', 'is_active' => true]);
    Student::factory()->create(['name' => 'Zaenal Unik Satu', 'nis' => '90001', 'kelas' => '7A']);
    Student::factory()->create(['name' => 'Budi Lain Dua', 'nis' => '90002', 'kelas' => '7B']);

    $admin = makeAdmin();

    $this->actingAs($admin)
        ->get(route('admin.extracurriculars.show', [$eskul, 'kelas' => '7B']))
        ->assertOk()
        ->assertSee('Budi Lain Dua')
        ->assertDontSee('Zaenal Unik Satu');

    $this->actingAs($admin)
        ->get(route('admin.extracurriculars.show', [$eskul, 'q' => 'tidakada']))
        ->assertOk()
        ->assertSee('Tidak ada siswa yang cocok.');
});

it('memakai judul kolom Kelola di daftar eskul', function () {
    Extracurricular::create(['name' => 'Pramuka Tes', 'is_active' => true]);

    $this->actingAs(makeAdmin())
        ->get(route('admin.extracurriculars.index'))
        ->assertOk()
        ->assertSee('data-label="Kelola"', false);
});