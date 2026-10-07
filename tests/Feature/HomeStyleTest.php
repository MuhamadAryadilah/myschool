<?php

it('menampilkan langkah pendaftaran dan teks tombol yang benar di beranda', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Langkah pendaftaran')
        ->assertSee('Daftar Siswa Baru')
        ->assertSee('Sudah punya akun?');
});