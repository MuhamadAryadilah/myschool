<?php

it('menampilkan footer sekolah di beranda dan halaman berita', function (string $url) {
    $this->get($url)
        ->assertOk()
        ->assertSee('Hak cipta dilindungi')
        ->assertSee('info@myschool.test');
})->with(['/', '/berita']);

it('tidak memasang footer di halaman login dan dashboard', function () {
    $this->get('/login')->assertOk()->assertDontSee('Hak cipta dilindungi');
    $this->actingAs(makeParent())->get(route('dashboard'))->assertOk()->assertDontSee('Hak cipta dilindungi');
});