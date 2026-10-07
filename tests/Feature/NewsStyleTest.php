<?php

use App\Models\News;

it('menampilkan daftar berita kosong dengan tautan ke beranda', function () {
    $this->get('/berita')
        ->assertOk()
        ->assertSee('Belum ada berita.')
        ->assertSee('Kembali ke beranda');
});

it('menampilkan halaman baca berita dengan teks yang benar', function () {
    $news = News::create(['title' => 'Tes Judul', 'slug' => 'tes-judul', 'content' => 'Isi berita', 'status' => 'published']);

    $this->get(route('news.show', $news))
        ->assertOk()
        ->assertSee('Tes Judul')
        ->assertSee('Isi berita')
        ->assertSee('Semua berita');
});