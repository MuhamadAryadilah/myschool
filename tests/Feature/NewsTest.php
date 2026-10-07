<?php

use App\Models\News;

function buatBerita(array $attrs = []): News
{
    static $n = 0;
    $n++;

    return News::create(array_merge([
        'title' => "Berita $n",
        'slug' => "berita-$n",
        'content' => "Isi berita nomor $n",
        'author' => 'Admin',
        'status' => 'published',
    ], $attrs));
}

it('menampilkan berita terbit di halaman depan dan menyembunyikan draft', function () {
    buatBerita(['title' => 'Libur Semester', 'slug' => 'libur-semester']);
    buatBerita(['title' => 'Rahasia Draft', 'slug' => 'rahasia-draft', 'status' => 'draft']);

    $this->get('/')
        ->assertOk()
        ->assertSee('Libur Semester')
        ->assertDontSee('Rahasia Draft');
});

it('menampilkan pesan kosong kalau belum ada berita', function () {
    $this->get('/')->assertOk()->assertSee('Belum ada berita.');
});

it('menampilkan tombol masuk untuk tamu', function () {
    $this->get('/')->assertOk()->assertSee('Masuk')->assertDontSee('Dashboard');
});

it('menampilkan tombol dashboard untuk yang sudah login', function () {
    $this->actingAs(makeParent())->get('/')->assertOk()->assertSee('Dashboard')->assertDontSee('Masuk');
});

it('membagi daftar berita per 9 data dan hanya memuat yang terbit', function () {
    foreach (range(1, 10) as $i) {
        buatBerita();
    }
    buatBerita(['status' => 'draft']);

    $this->get('/berita')
        ->assertOk()
        ->assertViewHas('news', fn ($news) => $news->total() === 10 && $news->count() === 9);
});

it('mengizinkan tamu membaca berita terbit', function () {
    buatBerita(['title' => 'Pengumuman PPDB', 'slug' => 'pengumuman-ppdb', 'content' => 'Pendaftaran dibuka Senin']);

    $this->get('/berita/pengumuman-ppdb')
        ->assertOk()
        ->assertSee('Pengumuman PPDB')
        ->assertSee('Pendaftaran dibuka Senin');
});

it('mengembalikan 404 untuk draft bagi tamu dan orang tua', function () {
    buatBerita(['slug' => 'masih-draft', 'status' => 'draft']);

    $this->get('/berita/masih-draft')->assertNotFound();
    $this->actingAs(makeParent())->get('/berita/masih-draft')->assertNotFound();
});

it('mengizinkan admin melihat pratinjau draft', function () {
    buatBerita(['slug' => 'masih-draft', 'status' => 'draft']);

    $this->actingAs(makeAdmin())->get('/berita/masih-draft')->assertOk()->assertSee('Pratinjau');
});

it('mengarahkan link lama /news ke /berita', function () {
    $this->get('/news')->assertRedirect('/berita');
});

it('melarang tamu dan orang tua membuka pengelolaan berita', function () {
    $this->get(route('admin.news.index'))->assertRedirect('/login');
    $this->actingAs(makeParent())->get(route('admin.news.index'))->assertForbidden();
});

it('mengizinkan admin membuka pengelolaan berita', function () {
    buatBerita(['title' => 'Berita Pertama']);

    $this->actingAs(makeAdmin())
        ->get(route('admin.news.index'))
        ->assertOk()
        ->assertSee('Berita Pertama');
});

it('membuat berita oleh admin', function () {
    $this->actingAs(makeAdmin())
        ->post(route('admin.news.store'), [
            'title' => 'Pengumuman PPDB',
            'content' => 'Isi pengumuman',
            'author' => 'Humas',
            'status' => 'draft',
        ])
        ->assertRedirect(route('admin.news.index'));

    $this->assertDatabaseHas('news', [
        'title' => 'Pengumuman PPDB',
        'slug' => 'pengumuman-ppdb',
        'status' => 'draft',
    ]);
});

it('membuat slug unik untuk judul yang kembar', function () {
    $admin = makeAdmin();
    $data = ['title' => 'Libur Natal', 'content' => 'Isi', 'status' => 'published'];

    $this->actingAs($admin)->post(route('admin.news.store'), $data)->assertRedirect();
    $this->actingAs($admin)->post(route('admin.news.store'), $data)->assertRedirect();

    expect(News::orderBy('id')->pluck('slug')->all())->toBe(['libur-natal', 'libur-natal-2']);
});

it('menolak input berita yang tidak valid', function () {
    $this->actingAs(makeAdmin())
        ->post(route('admin.news.store'), ['title' => '', 'content' => '', 'status' => 'rahasia'])
        ->assertSessionHasErrors(['title', 'content', 'status']);

    expect(News::count())->toBe(0);
});

it('melarang orang tua membuat berita', function () {
    $this->actingAs(makeParent())
        ->post(route('admin.news.store'), ['title' => 'Palsu', 'content' => 'Isi', 'status' => 'published'])
        ->assertForbidden();

    expect(News::count())->toBe(0);
});

it('memperbarui berita tanpa mengubah slug', function () {
    $news = buatBerita(['title' => 'Judul Lama', 'slug' => 'judul-lama']);

    $this->actingAs(makeAdmin())
        ->put(route('admin.news.update', $news), [
            'title' => 'Judul Baru',
            'content' => 'Isi baru',
            'status' => 'published',
        ])
        ->assertRedirect(route('admin.news.index'));

    $news->refresh();

    expect($news->title)->toBe('Judul Baru')
        ->and($news->slug)->toBe('judul-lama');
});

it('menghapus berita oleh admin', function () {
    $news = buatBerita();

    $this->actingAs(makeAdmin())
        ->delete(route('admin.news.destroy', $news))
        ->assertRedirect(route('admin.news.index'));

    expect(News::count())->toBe(0);
});

it('melarang orang tua menghapus berita', function () {
    $news = buatBerita();

    $this->actingAs(makeParent())
        ->delete(route('admin.news.destroy', $news))
        ->assertForbidden();

    expect(News::count())->toBe(1);
});