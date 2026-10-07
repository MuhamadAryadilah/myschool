<?php

it('menampilkan angka rekap tagihan dengan ukuran besar', function () {
    $this->actingAs(makeAdmin())
        ->get(route('admin.invoices.index'))
        ->assertOk()
        ->assertSee('Total tagihan')
        ->assertSee('text-3xl', false);
});

it('memakai label kembali singkat di tagihan massal tapi tidak di halaman tambah', function () {
    $admin = makeAdmin();

    $this->actingAs($admin)->get(route('admin.invoices.bulk.create'))
        ->assertOk()
        ->assertSee('&larr; Kembali', false)
        ->assertDontSee('Kembali ke daftar');

    $this->actingAs($admin)->get(route('admin.invoices.create'))
        ->assertOk()
        ->assertSee('Kembali ke daftar');
});