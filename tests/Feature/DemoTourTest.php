<?php

it('memasang panduan spotlight saat DEMO_MODE aktif', function () {
    config(['demo.enabled' => true]);

    $this->get('/')->assertOk()->assertSee('data-demo-tour="home"', false);
    $this->get('/login')->assertOk()->assertSee('data-demo-tour="login"', false);
    $this->actingAs(makeParent())->get(route('dashboard'))->assertOk()->assertSee('data-demo-tour="dashboard"', false);
    $this->actingAs(makeAdmin())->get(route('admin.dashboard'))->assertOk()->assertSee('data-demo-tour="admin.dashboard"', false);
});

it('tidak memasang panduan saat DEMO_MODE mati', function () {
    config(['demo.enabled' => false]);

    $this->get('/')->assertOk()->assertDontSee('data-demo-tour', false);
    $this->actingAs(makeParent())->get(route('dashboard'))->assertOk()->assertDontSee('data-demo-tour', false);
});

it('tidak memasang panduan otomatis di halaman selain beranda, login, dan dashboard', function () {
    config(['demo.enabled' => true]);

    $this->actingAs(makeParent())->get(route('payments.history'))->assertOk()->assertDontSee('data-demo-tour', false);
});