<?php

it('menampilkan tombol kembali ke dashboard di halaman daftar admin', function (string $route) {
    $this->actingAs(makeAdmin())
        ->get(route($route))
        ->assertOk()
        ->assertSee('Kembali ke dashboard');
})->with([
    'admin.students.index',
    'admin.parents.index',
    'admin.invoices.index',
    'admin.payments.index',
    'admin.news.index',
]);

it('menampilkan tombol kembali ke daftar di halaman tambah admin', function (string $route) {
    $this->actingAs(makeAdmin())
        ->get(route($route))
        ->assertOk()
        ->assertSee('Kembali ke daftar');
})->with([
    'admin.students.create',
    'admin.parents.create',
    'admin.invoices.create',
    'admin.news.create',
]);

it('tidak menampilkan tombol kembali di dashboard admin', function () {
    $this->actingAs(makeAdmin())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertDontSee('Kembali ke dashboard');
});

it('menampilkan tombol kembali di riwayat pembayaran orang tua', function () {
    $this->actingAs(makeParent())
        ->get(route('payments.history'))
        ->assertOk()
        ->assertSee('Kembali ke dashboard');
});

it('tidak menampilkan tombol kembali di dashboard orang tua', function () {
    $this->actingAs(makeParent())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('Kembali ke dashboard');
});