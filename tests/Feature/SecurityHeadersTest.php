<?php

it('mengirim header keamanan di halaman publik', function (string $url) {
    $this->get($url)
        ->assertOk()
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
})->with(['/', '/berita', '/login']);

it('mengirim header keamanan di dashboard', function () {
    $this->actingAs(makeParent())->get(route('dashboard'))
        ->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff');
});