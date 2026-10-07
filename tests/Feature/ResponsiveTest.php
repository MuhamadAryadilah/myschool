<?php

use App\Models\News;
use App\Models\Student;

it('memuat viewport untuk layar HP di halaman publik', function (string $url) {
    $this->get($url)->assertOk()->assertSee('width=device-width, initial-scale=1', false);
})->with(['/', '/berita', '/login']);

it('memuat viewport di halaman admin', function () {
    $this->actingAs(makeAdmin())
        ->get('/admin')
        ->assertOk()
        ->assertSee('width=device-width, initial-scale=1', false);
});

it('memakai menu hamburger sampai layar xl supaya menu admin tidak berdesakan', function () {
    $this->actingAs(makeAdmin())->get('/admin')->assertSee('xl:hidden', false);
});

it('menampilkan tabel siswa sebagai kartu di layar kecil', function () {
    Student::factory()->create();

    $this->actingAs(makeAdmin())
        ->get(route('admin.students.index'))
        ->assertOk()
        ->assertSee('table-stack', false)
        ->assertSee('data-label="NIS"', false)
        ->assertSee('data-label="Aksi"', false);
});

it('menampilkan tabel berita admin sebagai kartu di layar kecil', function () {
    News::create(['title' => 'Tes', 'slug' => 'tes', 'content' => 'Isi', 'status' => 'published']);

    $this->actingAs(makeAdmin())
        ->get(route('admin.news.index'))
        ->assertOk()
        ->assertSee('table-stack', false)
        ->assertSee('data-label="Judul"', false);
});