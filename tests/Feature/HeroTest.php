<?php

it('menampilkan satu ajakan daftar dan tautan masuk kecil di hero untuk tamu', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Daftar Siswa Baru')
        ->assertSee('Sudah punya akun?')
        ->assertDontSee('Masuk untuk Orang Tua');
});

it('menampilkan tautan dashboard di hero untuk yang sudah login', function () {
    $this->actingAs(makeParent())->get('/')
        ->assertOk()
        ->assertSee('Buka dashboard')
        ->assertDontSee('Sudah punya akun?');
});