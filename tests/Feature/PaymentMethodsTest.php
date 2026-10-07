<?php

use Illuminate\Support\Facades\File;

it('mengarahkan tamu ke halaman login', function () {
    $this->get(route('payments.methods'))->assertRedirect('/login');
});

it('menampilkan rekening bank dan nama pemilik untuk orang tua', function () {
    $this->actingAs(makeParent())
        ->get(route('payments.methods'))
        ->assertOk()
        ->assertSee('Cara Bayar')
        ->assertSee('BCA')
        ->assertSee('50421861')
        ->assertSee('BNI')
        ->assertSee('2036861671')
        ->assertSee('Muhamad Aryadilah');
});

it('bisa dibuka juga oleh admin', function () {
    $this->actingAs(makeAdmin())->get(route('payments.methods'))->assertOk();
});

it('menampilkan gambar QRIS kalau berkasnya ada', function () {
    $path = public_path('images/_tes-qris.png');
    File::ensureDirectoryExists(dirname($path));
    File::put($path, 'x');
    config(['payment.qris_image' => 'images/_tes-qris.png']);

    $this->actingAs(makeParent())
        ->get(route('payments.methods'))
        ->assertSee('images/_tes-qris.png')
        ->assertDontSee('Kode QRIS belum tersedia');

    File::delete($path);
});

it('menampilkan pesan pengganti kalau gambar QRIS belum ada', function () {
    config(['payment.qris_image' => 'images/tidak-ada.png']);

    $this->actingAs(makeParent())
        ->get(route('payments.methods'))
        ->assertOk()
        ->assertSee('Kode QRIS belum tersedia');
});