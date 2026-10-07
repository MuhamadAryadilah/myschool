<?php

it('menampilkan pemberitahuan uji coba di dashboard orang tua', function () {
    config(['demo.enabled' => true]);

    $this->actingAs(makeParent())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Ini web uji coba');
});

it('menampilkan pemberitahuan uji coba di dashboard admin', function () {
    config(['demo.enabled' => true]);

    $this->actingAs(makeAdmin())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Ini web uji coba');
});

it('tidak menampilkan pop-up uji coba di halaman selain dashboard', function () {
    config(['demo.enabled' => true]);

    $this->actingAs(makeParent())
        ->get(route('payments.history'))
        ->assertOk()
        ->assertDontSee('Ini web uji coba');
});

it('tidak menampilkan pemberitahuan uji coba saat DEMO_MODE mati', function () {
    config(['demo.enabled' => false]);

    $this->actingAs(makeParent())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('Ini web uji coba');
});