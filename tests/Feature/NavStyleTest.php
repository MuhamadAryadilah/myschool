<?php

it('menampilkan menu admin lengkap dengan panel hamburger', function () {
    $this->actingAs(makeAdmin())
        ->get('/admin')
        ->assertOk()
        ->assertSee('Pembayaran')
        ->assertSee('Orang Tua')
        ->assertSee('data-nav-panel', false);
});

it('menampilkan menu orang tua lengkap', function () {
    $this->actingAs(makeParent())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Riwayat Pembayaran')
        ->assertSee('Cara Bayar')
        ->assertSee('data-nav-panel', false);
});