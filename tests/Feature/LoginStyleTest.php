<?php

it('menampilkan login dua kolom dengan form yang sama', function () {
    $this->get('/login')
        ->assertOk()
        ->assertSee('lg:grid-cols-2', false)
        ->assertSee('Portal tagihan dan pembayaran sekolah')
        ->assertSee('Masuk ke akun')
        ->assertSee('name="email"', false)
        ->assertSee('name="password"', false);
});

it('menampilkan halaman cara bayar untuk orang tua', function () {
    $this->actingAs(makeParent())
        ->get(route('payments.methods'))
        ->assertOk()
        ->assertSee('Langkah pembayaran')
        ->assertSee('Transfer bank');
});