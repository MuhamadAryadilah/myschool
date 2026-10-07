<?php

use App\Models\Extracurricular;
use App\Models\Student;

it('mencari anggota di dalam eskul', function () {
    $eskul = Extracurricular::create(['name' => 'Pramuka Cari', 'is_active' => true]);
    Student::factory()->create(['name' => 'Anggota Satu Unik', 'nis' => '80001', 'kelas' => '7A'])->extracurriculars()->attach($eskul);
    Student::factory()->create(['name' => 'Anggota Dua Lain', 'nis' => '80002', 'kelas' => '7A'])->extracurriculars()->attach($eskul);

    $admin = makeAdmin();

    $this->actingAs($admin)
        ->get(route('admin.extracurriculars.show', [$eskul, 'm' => 'Satu']))
        ->assertOk()
        ->assertSee('Anggota Satu Unik')
        ->assertDontSee('Anggota Dua Lain')
        ->assertSee('Anggota (2)');

    $this->actingAs($admin)
        ->get(route('admin.extracurriculars.show', [$eskul, 'm' => 'tidakada']))
        ->assertOk()
        ->assertSee('Tidak ada anggota yang cocok.');
});

it('menampilkan halaman 404 yang ramah', function () {
    $this->get('/halaman-yang-tidak-ada-123')
        ->assertNotFound()
        ->assertSee('Halaman tidak ditemukan')
        ->assertSee('Ke beranda');
});

it('memakai judul tab sesuai halaman', function () {
    $this->actingAs(makeAdmin())
        ->get(route('admin.students.index'))
        ->assertOk()
        ->assertSee('Data Siswa - ', false);
});