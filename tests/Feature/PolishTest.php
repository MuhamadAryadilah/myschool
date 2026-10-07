<?php

it('menampilkan pesan sukses yang hilang sendiri', function () {
    $this->actingAs(makeAdmin())
        ->withSession(['success' => 'Berhasil disimpan'])
        ->get(route('admin.students.index'))
        ->assertOk()
        ->assertSee('Berhasil disimpan')
        ->assertSee('setTimeout', false);
});

it('menampilkan keadaan kosong yang ramah di tabel tagihan', function () {
    $this->actingAs(makeAdmin())
        ->get(route('admin.invoices.index'))
        ->assertOk()
        ->assertSee('Tidak ada tagihan yang cocok.');
});

it('memakai tombol anti klik ganda di form pendaftaran', function () {
    $this->get(route('registration.create'))
        ->assertOk()
        ->assertSee('busy', false)
        ->assertSee('Kirim Pendaftaran');
});

it('menampilkan menu Pendaftaran di bar atas admin', function () {
    $this->actingAs(makeAdmin())
        ->get('/admin')
        ->assertOk()
        ->assertSee(route('admin.registrations.index'), false);
});