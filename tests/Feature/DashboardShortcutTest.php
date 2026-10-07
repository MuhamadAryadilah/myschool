<?php

it('menampilkan kartu Eskul di dashboard admin', function () {
    $this->actingAs(makeAdmin())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Kelola ekstrakurikuler dan anggotanya');
});

it('menampilkan tombol kembali ke dashboard di halaman berita untuk orang tua', function () {
    $this->actingAs(makeParent())
        ->get(route('news.index'))
        ->assertOk()
        ->assertSee('Kembali ke dashboard');
});

it('menampilkan tombol kembali ke dashboard di halaman berita untuk admin', function () {
    $this->actingAs(makeAdmin())
        ->get(route('news.index'))
        ->assertOk()
        ->assertSee('Kembali ke dashboard');
});

it('tidak menampilkan tombol kembali ke dashboard untuk tamu', function () {
    $this->get(route('news.index'))
        ->assertOk()
        ->assertDontSee('Kembali ke dashboard');
});