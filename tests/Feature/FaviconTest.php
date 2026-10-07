<?php

it('memasang favicon sekolah di halaman publik dan login', function (string $url) {
    $this->get($url)->assertOk()->assertSee('favicon.svg', false);
})->with(['/', '/berita', '/login']);

it('memasang favicon sekolah di dashboard', function () {
    $this->actingAs(makeParent())->get(route('dashboard'))->assertOk()->assertSee('favicon.svg', false);
});