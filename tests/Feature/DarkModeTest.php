<?php

it('mengunci mode gelap di halaman login', function () {
    $this->get('/login')->assertOk()->assertSee('class="dark"', false);
});

it('mengunci mode gelap di beranda publik', function () {
    $this->get(route('home'))->assertOk()->assertSee('class="dark"', false);
});

it('mengunci mode gelap di dashboard admin', function () {
    $this->actingAs(makeAdmin())->get(route('admin.dashboard'))
        ->assertOk()->assertSee('class="dark"', false);
});

it('mengunci mode gelap di dashboard orang tua', function () {
    $this->actingAs(makeParent())->get(route('dashboard'))
        ->assertOk()->assertSee('class="dark"', false);
});