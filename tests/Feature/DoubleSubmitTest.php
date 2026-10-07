<?php

it('memasang penjaga klik ganda global di app.js', function () {
    $js = file_get_contents(base_path('resources/js/app.js'));

    expect($js)
        ->toContain("addEventListener('submit'")
        ->toContain('Memproses...')
        ->toContain('data-no-lock')
        ->toContain('pageshow');
});

it('memuat app.js di halaman admin dan orang tua', function () {
    $this->actingAs(makeAdmin())->get('/admin/registrations')->assertOk();
    $this->actingAs(makeParent())->get(route('dashboard'))->assertOk();
});