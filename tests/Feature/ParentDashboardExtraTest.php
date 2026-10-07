<?php

it('menampilkan tombol hubungi sekolah hanya kalau nomor WhatsApp diisi', function () {
    config(['school.whatsapp' => '081234567890']);
    $this->actingAs(makeParent())->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Hubungi sekolah')
        ->assertSee('wa.me/6281234567890', false);

    config(['school.whatsapp' => null]);
    $this->actingAs(makeParent())->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('Hubungi sekolah');
});

it('tidak lagi memuat typo kelas pada dashboard orang tua', function () {
    $this->actingAs(makeParent())->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('justify-centerrounded-full', false);
});