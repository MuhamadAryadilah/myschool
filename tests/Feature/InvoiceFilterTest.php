<?php

use App\Models\Invoice;
use App\Models\Student;

beforeEach(function () {
    $this->admin = makeAdmin();

    $this->budi = Student::factory()->create(['name' => 'Budi Santoso', 'kelas' => '7A']);
    $this->sari = Student::factory()->create(['name' => 'Sari Wulandari', 'kelas' => '8B']);

    Invoice::factory()->for($this->budi)->create(['title' => 'SPP Januari']);
    Invoice::factory()->for($this->sari)->paid()->create(['title' => 'SPP Februari']);
});

it('mencari tagihan berdasarkan nama siswa', function () {
    $this->actingAs($this->admin)
        ->get('/admin/invoices?q=Bud')
        ->assertSee('Budi Santoso')
        ->assertDontSee('Sari Wulandari');
});

it('mencari tagihan berdasarkan judul tagihan', function () {
    $this->actingAs($this->admin)
        ->get('/admin/invoices?q=Februari')
        ->assertSee('Sari Wulandari')
        ->assertDontSee('Budi Santoso');
});

it('menyaring tagihan lunas', function () {
    $this->actingAs($this->admin)
        ->get('/admin/invoices?status=lunas')
        ->assertSee('Sari Wulandari')
        ->assertDontSee('Budi Santoso');
});

it('menyaring tagihan belum lunas', function () {
    $this->actingAs($this->admin)
        ->get('/admin/invoices?status=belum_lunas')
        ->assertSee('Budi Santoso')
        ->assertDontSee('Sari Wulandari');
});

it('menyaring tagihan terlambat', function () {
    $tono = Student::factory()->create(['name' => 'Tono Hartanto', 'kelas' => '9A']);
    Invoice::factory()->for($tono)->overdue()->create(['title' => 'SPP Desember']);

    $this->actingAs($this->admin)
        ->get('/admin/invoices?status=terlambat')
        ->assertSee('Tono Hartanto')
        ->assertDontSee('Budi Santoso')
        ->assertDontSee('Sari Wulandari');
});

it('menyaring tagihan berdasarkan kelas', function () {
    $this->actingAs($this->admin)
        ->get('/admin/invoices?kelas=7A')
        ->assertSee('Budi Santoso')
        ->assertDontSee('Sari Wulandari');
});

it('menggabungkan pencarian dan filter', function () {
    $this->actingAs($this->admin)
        ->get('/admin/invoices?q=Budi&status=lunas')
        ->assertDontSee('Budi Santoso')
        ->assertDontSee('Sari Wulandari')
        ->assertSee('Tidak ada tagihan yang cocok.');
});