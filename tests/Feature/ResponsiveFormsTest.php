<?php

it('menampilkan tabel pendaftaran admin sebagai kartu di layar kecil', function () {
    $this->actingAs(makeAdmin())
        ->get(route('admin.registrations.index'))
        ->assertOk()
        ->assertSee('table-stack', false)
        ->assertSee('Tidak ada pendaftaran yang cocok.');
});

it('memakai padding yang pas di HP pada halaman tagihan massal', function () {
    $this->actingAs(makeAdmin())
        ->get(route('admin.invoices.bulk.create'))
        ->assertOk()
        ->assertSee('p-4 sm:p-6', false);
});