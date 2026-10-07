<?php

use App\Models\News;
use App\Models\NewsImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

function beritaDenganGambar(int $jumlah = 1, array $attrs = []): News
{
    $news = News::create(array_merge([
        'title' => 'Berita Gambar',
        'slug' => 'berita-gambar-'.uniqid(),
        'content' => 'Isi berita',
        'author' => 'Admin',
        'status' => 'published',
    ], $attrs));

    foreach (range(1, $jumlah) as $i) {
        $path = UploadedFile::fake()->image("g$i.jpg")->store('news', 'public');
        $news->images()->create(['path' => $path, 'position' => $i]);
    }

    return $news->fresh();
}

function dataBerita(array $extra = []): array
{
    return array_merge([
        'title' => 'Study Tour',
        'content' => 'Isi berita',
        'status' => 'published',
    ], $extra);
}

it('membuat berita dengan beberapa gambar', function () {
    $this->actingAs(makeAdmin())
        ->post(route('admin.news.store'), dataBerita([
            'images' => [
                UploadedFile::fake()->image('a.jpg'),
                UploadedFile::fake()->image('b.png'),
            ],
        ]))
        ->assertRedirect(route('admin.news.index'));

    $news = News::first();

    expect($news->images)->toHaveCount(2);

    foreach ($news->images as $image) {
        Storage::disk('public')->assertExists($image->path);
    }
});

it('tetap bisa membuat berita tanpa gambar', function () {
    $this->actingAs(makeAdmin())
        ->post(route('admin.news.store'), dataBerita())
        ->assertSessionHasNoErrors();

    expect(News::first()->images)->toHaveCount(0);
});

it('menolak file yang bukan gambar', function () {
    $this->actingAs(makeAdmin())
        ->post(route('admin.news.store'), dataBerita([
            'images' => [UploadedFile::fake()->create('dok.pdf', 100, 'application/pdf')],
        ]))
        ->assertSessionHasErrors('images.0');

    expect(News::count())->toBe(0);
});

it('menolak gambar lebih dari 4 MB', function () {
    $this->actingAs(makeAdmin())
        ->post(route('admin.news.store'), dataBerita([
            'images' => [UploadedFile::fake()->image('besar.jpg')->size(5000)],
        ]))
        ->assertSessionHasErrors('images.0');
});

it('menolak lebih dari 10 gambar sekaligus', function () {
    $images = array_map(fn ($i) => UploadedFile::fake()->image("g$i.jpg"), range(1, 11));

    $this->actingAs(makeAdmin())
        ->post(route('admin.news.store'), dataBerita(['images' => $images]))
        ->assertSessionHasErrors('images');

    expect(News::count())->toBe(0);
});

it('menambah gambar saat edit tanpa menghapus yang lama', function () {
    $news = beritaDenganGambar(1);

    $this->actingAs(makeAdmin())
        ->put(route('admin.news.update', $news), dataBerita([
            'images' => [UploadedFile::fake()->image('baru.jpg')],
        ]))
        ->assertRedirect(route('admin.news.index'));

    expect($news->fresh()->images)->toHaveCount(2);
});

it('menghapus gambar yang dipilih saat edit beserta filenya', function () {
    $news = beritaDenganGambar(2);
    $hapus = $news->images->first();
    $simpan = $news->images->last();

    $this->actingAs(makeAdmin())
        ->put(route('admin.news.update', $news), dataBerita(['remove_images' => [$hapus->id]]))
        ->assertRedirect(route('admin.news.index'));

    Storage::disk('public')->assertMissing($hapus->path);
    Storage::disk('public')->assertExists($simpan->path);
    expect($news->fresh()->images)->toHaveCount(1);
});

it('menolak edit kalau total gambar melebihi 10', function () {
    $news = beritaDenganGambar(9);

    $this->actingAs(makeAdmin())
        ->put(route('admin.news.update', $news), dataBerita([
            'images' => [
                UploadedFile::fake()->image('x.jpg'),
                UploadedFile::fake()->image('y.jpg'),
            ],
        ]))
        ->assertSessionHasErrors('images');

    expect($news->fresh()->images)->toHaveCount(9);
});

it('tidak bisa menghapus gambar milik berita lain', function () {
    $milikku = beritaDenganGambar(1);
    $lain = beritaDenganGambar(1);
    $gambarLain = $lain->images->first();

    $this->actingAs(makeAdmin())
        ->put(route('admin.news.update', $milikku), dataBerita(['remove_images' => [$gambarLain->id]]))
        ->assertRedirect(route('admin.news.index'));

    $this->assertDatabaseHas('news_images', ['id' => $gambarLain->id]);
    Storage::disk('public')->assertExists($gambarLain->path);
});

it('menghapus file gambar saat berita dihapus', function () {
    $news = beritaDenganGambar(2);
    $paths = $news->images->pluck('path');

    $this->actingAs(makeAdmin())
        ->delete(route('admin.news.destroy', $news))
        ->assertRedirect(route('admin.news.index'));

    foreach ($paths as $path) {
        Storage::disk('public')->assertMissing($path);
    }

    expect(NewsImage::count())->toBe(0);
});

it('melarang orang tua mengunggah gambar berita', function () {
    $this->actingAs(makeParent())
        ->post(route('admin.news.store'), dataBerita([
            'images' => [UploadedFile::fake()->image('a.jpg')],
        ]))
        ->assertForbidden();

    expect(NewsImage::count())->toBe(0);
});

it('menampilkan slide kalau gambar lebih dari satu dan tanpa slide kalau satu', function () {
    $banyak = beritaDenganGambar(3, ['slug' => 'banyak-gambar']);
    $satu = beritaDenganGambar(1, ['slug' => 'satu-gambar']);

    $this->get('/berita/banyak-gambar')
        ->assertOk()
        ->assertSee('Gambar berikutnya')
        ->assertSee($banyak->images->first()->url(), false)
        ->assertSee($banyak->images->last()->url(), false);

    $this->get('/berita/satu-gambar')
        ->assertOk()
        ->assertSee($satu->images->first()->url(), false)
        ->assertDontSee('Gambar berikutnya');
});

it('menampilkan gambar pertama sebagai sampul di halaman depan', function () {
    $news = beritaDenganGambar(2);

    $this->get('/')
        ->assertOk()
        ->assertSee($news->images->first()->url(), false);
});