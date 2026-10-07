<?php

it('tidak lagi menampilkan kartu kembar di dashboard admin', function () {
    $this->actingAs(makeAdmin())->get(route('admin.dashboard'))
        ->assertOk()
        ->assertDontSee('Tagihan per Semester')
        ->assertDontSee('Pengaturan Pendaftaran')
        ->assertSee('Pendaftaran')
        ->assertSee('Tagihan');
});

it('menampilkan tab Pendaftaran di halaman daftar pendaftar', function () {
    $this->actingAs(makeAdmin())->get(route('admin.registrations.index'))
        ->assertOk()
        ->assertSee('aria-current="page"', false);
});