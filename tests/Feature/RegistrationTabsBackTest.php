<?php

it('menampilkan satu tombol kembali di tab Pendaftar', function () {
    $html = $this->actingAs(makeAdmin())->get(route('admin.registrations.index'))
        ->assertOk()->getContent();

    expect(substr_count($html, 'Kembali ke dashboard'))->toBe(1);
});

it('menampilkan tombol kembali yang sesuai di tab Pengaturan dan Alur Setelah Daftar', function (string $route, string $label) {
    $this->actingAs(makeAdmin())->get(route($route))
        ->assertOk()
        ->assertSee($label);
})->with([
    ['admin.registration-settings.edit', 'Kembali ke dashboard'],
    ['admin.registration-steps.index', 'Kembali ke Aturan Pendaftaran'],
]);