<?php

it('memasang latar bulatan di halaman publik dan login', function (string $url) {
    $this->get($url)->assertOk()->assertSee('data-bg="orbs"', false);
})->with(['/', '/berita', '/login']);

it('memasang latar bulatan di dashboard admin dan orang tua', function () {
    $this->actingAs(makeAdmin())->get('/admin')->assertOk()->assertSee('data-bg="orbs"', false);
    $this->actingAs(makeParent())->get(route('dashboard'))->assertOk()->assertSee('data-bg="orbs"', false);
});