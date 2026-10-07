<?php

it('menampilkan halaman buat password dengan email terisi dari link', function () {
    $this->get('/reset-password/token-contoh?email=ortu%40contoh.com')
        ->assertOk()
        ->assertSee('Buat password')
        ->assertSee('Simpan Password')
        ->assertSee('ortu@contoh.com', false)
        ->assertDontSee('Atur ulang password');
});

it('menjelaskan di halaman lupa password bahwa tautan baru akan dikirim', function () {
    $this->get('/forgot-password')
        ->assertOk()
        ->assertSee('tautan baru')
        ->assertSee('kedaluwarsa');
});