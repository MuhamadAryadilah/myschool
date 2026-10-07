<?php

use App\Models\Student;
use App\Models\User;

beforeEach(function () {
    $this->admin = makeAdmin();

    $wali = User::factory()->create(['role' => 'orang_tua', 'name' => 'Hendra Gunawan Wijaya']);

    $this->zulkifli = Student::factory()->for($wali, 'parent')->create([
        'name' => 'Zulkifli Anwar', 'nis' => '11112222', 'kelas' => '7A',
    ]);
    $this->qonita = Student::factory()->create([
        'name' => 'Qonita Rahmawati', 'nis' => '33334444', 'kelas' => '8B',
    ]);
});

it('mencari siswa berdasarkan nama', function () {
    $this->actingAs($this->admin)
        ->get('/admin/students?q=Zulk')
        ->assertSee('Zulkifli Anwar')
        ->assertDontSee('Qonita Rahmawati');
});

it('mencari siswa berdasarkan NIS', function () {
    $this->actingAs($this->admin)
        ->get('/admin/students?q=33334444')
        ->assertSee('Qonita Rahmawati')
        ->assertDontSee('Zulkifli Anwar');
});

it('mencari siswa berdasarkan nama orang tua', function () {
    $this->actingAs($this->admin)
        ->get('/admin/students?q=Gunawan')
        ->assertSee('Zulkifli Anwar')
        ->assertDontSee('Qonita Rahmawati');
});

it('menyaring siswa berdasarkan kelas', function () {
    $this->actingAs($this->admin)
        ->get('/admin/students?kelas=8B')
        ->assertSee('Qonita Rahmawati')
        ->assertDontSee('Zulkifli Anwar');
});

it('menggabungkan pencarian dan filter kelas', function () {
    $this->actingAs($this->admin)
        ->get('/admin/students?q=Zulkifli&kelas=8B')
        ->assertDontSee('Zulkifli Anwar')
        ->assertDontSee('Qonita Rahmawati')
        ->assertSee('Tidak ada siswa yang cocok.');
});

it('menampilkan pesan kosong kalau tidak ada yang cocok', function () {
    $this->actingAs($this->admin)
        ->get('/admin/students?q=tidakadayangcocok')
        ->assertSee('Tidak ada siswa yang cocok.');
});

it('menampilkan link reset hanya saat pencarian atau filter aktif', function () {
    $this->actingAs($this->admin)
        ->get('/admin/students')
        ->assertDontSee('Reset');

    $this->actingAs($this->admin)
        ->get('/admin/students?q=Zulk')
        ->assertSee('Reset');
});

it('tetap menampilkan siswa yang belum punya orang tua', function () {
    Student::factory()->create(['name' => 'Yusuf Tanpawali', 'user_id' => null]);

    $this->actingAs($this->admin)
        ->get('/admin/students?q=Tanpawali')
        ->assertSee('Yusuf Tanpawali');
});

it('membagi daftar siswa per 15 data', function () {
    Student::factory()->count(16)->sequence(
        fn ($sequence) => ['name' => 'Murid ' . ($sequence->index + 1)]
    )->create();

    $this->actingAs($this->admin)
        ->get('/admin/students?q=Murid')
        ->assertViewHas('students', fn ($students) => $students->count() === 15 && $students->total() === 16);

    $this->actingAs($this->admin)
        ->get('/admin/students?q=Murid&page=2')
        ->assertViewHas('students', fn ($students) => $students->count() === 1);
});

it('mempertahankan kata kunci pencarian di link halaman berikutnya', function () {
    Student::factory()->count(16)->sequence(
        fn ($sequence) => ['name' => 'Murid ' . ($sequence->index + 1)]
    )->create();

    $this->actingAs($this->admin)
        ->get('/admin/students?q=Murid')
        ->assertSee('q=Murid', false);
});

it('melarang orang tua membuka daftar siswa', function () {
    $this->actingAs(makeParent())->get('/admin/students')->assertForbidden();
});