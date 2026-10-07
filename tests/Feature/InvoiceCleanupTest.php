<?php

use Illuminate\Support\Facades\Route;

it('tidak lagi memiliki halaman rekap semester', function () {
    expect(Route::has('admin.semester-invoices.index'))->toBeFalse();

    $this->actingAs(makeAdmin())->get('/admin/tagihan-semester')->assertNotFound();
});

it('menampilkan tombol kembali, tambah, dan tagihan massal di daftar tagihan', function () {
    $this->actingAs(makeAdmin())
        ->get(route('admin.invoices.index'))
        ->assertOk()
        ->assertSee('Kembali ke dashboard')
        ->assertSee('Tambah Tagihan')
        ->assertSee(route('admin.invoices.bulk.create'), false)
        ->assertDontSee('Rekap Semester');
});

it('tetap menyediakan filter semester dan tahun ajaran di daftar tagihan', function () {
    $this->actingAs(makeAdmin())
        ->get(route('admin.invoices.index'))
        ->assertOk()
        ->assertSee('name="semester"', false)
        ->assertSee('name="academic_year"', false);
});

it('menampilkan tombol kembali ke daftar di tagihan massal', function () {
    $this->actingAs(makeAdmin())
        ->get(route('admin.invoices.bulk.create'))
        ->assertOk()
        ->assertSee('&larr; Kembali', false)->assertDontSee('Kembali ke daftar')
        ->assertDontSee('Rekap Semester');
});

it('menata Tambah Tagihan selebar daftar dengan dua kolom', function () {
    $this->actingAs(makeAdmin())
        ->get(route('admin.invoices.create'))
        ->assertOk()
        ->assertSee('Tambah Tagihan')
        ->assertSee('max-w-7xl', false)
        ->assertSee('md:grid-cols-2', false);
});