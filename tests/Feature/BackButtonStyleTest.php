<?php

use App\Models\Extracurricular;

it('memakai tombol kembali bergaya pil di halaman daftar admin', function (string $route) {
    $this->actingAs(makeAdmin())
        ->get(route($route))
        ->assertOk()
        ->assertSee('px-3 py-1.5 text-sm font-medium rounded-full', false)
        ->assertSee('Kembali ke dashboard');
})->with([
    'admin.students.index',
    'admin.parents.index',
    'admin.invoices.index',
    'admin.payments.index',
    'admin.news.index',
    'admin.registrations.index',
    'admin.extracurriculars.index',
]);

it('memakai tombol kembali bergaya pil di halaman tambah admin', function (string $route) {
    $this->actingAs(makeAdmin())
        ->get(route($route))
        ->assertOk()
        ->assertSee('px-3 py-1.5 text-sm font-medium rounded-full', false)
        ->assertSee('Kembali ke daftar');
})->with([
    'admin.students.create',
    'admin.parents.create',
    'admin.invoices.create',
    'admin.news.create',
    'admin.extracurriculars.create',
]);

it('memakai tombol kembali bergaya pil di riwayat pembayaran orang tua', function () {
    $this->actingAs(makeParent())
        ->get(route('payments.history'))
        ->assertOk()
        ->assertSee('px-3 py-1.5 text-sm font-medium rounded-full', false)
        ->assertSee('Kembali ke dashboard');
});

it('tidak memakai gaya tombol kembali yang lama lagi', function () {
    $this->actingAs(makeAdmin())
        ->get(route('admin.students.index'))
        ->assertOk()
        ->assertDontSee('rounded-md border border-gray-300 dark:border-gray-600 text-gray-700', false);
});

it('hanya menampilkan satu tombol kembali di detail eskul', function () {
    $eskul = Extracurricular::factory()->create();

    $html = $this->actingAs(makeAdmin())
        ->get(route('admin.extracurriculars.show', $eskul))
        ->assertOk()
        ->getContent();

    expect(substr_count($html, 'Kembali ke daftar'))->toBe(1);
});