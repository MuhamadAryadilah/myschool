<?php

it('menyamakan menu admin dengan pintasan di dashboard', function () {
    $this->actingAs(makeAdmin())->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSeeInOrder(['Dashboard', 'Pendaftaran', 'Siswa', 'Orang Tua', 'Eskul', 'Tagihan', 'Pembayaran', 'Berita', 'Pengaturan'])
        ->assertSee('data-nav-panel', false)
        ->assertDontSee('Rekap Semester');
});

it('memakai tombol kembali berbentuk pil di halaman daftar admin', function () {
    $this->actingAs(makeAdmin())->get(route('admin.students.index'))
        ->assertOk()
        ->assertSee('&larr; Kembali ke dashboard', false)
        ->assertSee('rounded-full', false);
});