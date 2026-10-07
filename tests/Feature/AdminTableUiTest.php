<?php

use App\Models\Registration;

it('menampilkan Periksa sebagai tombol aksi di daftar pendaftaran', function () {
    Registration::factory()->create();

    $this->actingAs(makeAdmin())
        ->get(route('admin.registrations.index'))
        ->assertOk()
        ->assertSee('btn-act btn-act-brand', false);
});