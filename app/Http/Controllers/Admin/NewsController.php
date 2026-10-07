<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\NewsImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewsController extends Controller
{
    private const MAX_IMAGES = 10;

    public function index(Request $request): View
    {
        $news = News::query()
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', '%'.$request->q.'%'))
            ->when(
                in_array($request->status, ['draft', 'published'], true),
                fn ($q) => $q->where('status', $request->status)
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.news.index', compact('news'));
    }

    public function create(): View
    {
        return view('admin.news.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $news = News::create(
            Arr::only($data, ['title', 'content', 'author', 'status'])
            + ['slug' => News::makeSlug($data['title'])]
        );

        $this->storeImages($news, $request);

        return redirect()->route('admin.news.index')->with('success', 'Berita berhasil ditambahkan.');
    }

    public function edit(News $news): View
    {
        $news->load('images');

        return view('admin.news.edit', compact('news'));
    }

    public function update(Request $request, News $news): RedirectResponse
    {
        $data = $this->validated($request);

        $this->guardImageLimit($request, $news);

        // Slug sengaja tidak diubah supaya link lama tetap berfungsi
        $news->update(Arr::only($data, ['title', 'content', 'author', 'status']));

        $this->removeImages($request, $news);
        $this->storeImages($news, $request);

        return redirect()->route('admin.news.index')->with('success', 'Berita berhasil diperbarui.');
    }

    public function destroy(News $news): RedirectResponse
    {
        $news->delete();

        return redirect()->route('admin.news.index')->with('success', 'Berita dihapus.');
    }

    private function validated(Request $request): array
    {
        $max = self::MAX_IMAGES;

        return $request->validate(
            [
                'title' => ['required', 'string', 'max:255'],
                'content' => ['required', 'string'],
                'author' => ['nullable', 'string', 'max:100'],
                'status' => ['required', 'in:draft,published'],
                'images' => ['nullable', 'array', "max:$max"],
                'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
                'remove_images' => ['nullable', 'array'],
                'remove_images.*' => ['integer'],
            ],
            [
                'status.in' => 'Status harus draft atau published.',
                'images.max' => "Maksimal $max gambar per berita.",
                'images.*.image' => 'Semua file harus berupa gambar.',
                'images.*.mimes' => 'Gambar harus berformat JPG, PNG, atau WEBP.',
                'images.*.max' => 'Ukuran tiap gambar maksimal 4 MB.',
                'images.*.uploaded' => 'Gambar gagal diunggah. Kemungkinan ukurannya melebihi batas server.',
            ],
            [
                'title' => 'judul',
                'content' => 'isi berita',
                'author' => 'penulis',
                'status' => 'status',
            ]
        );
    }

    /**
     * Total gambar setelah penghapusan dan penambahan tidak boleh melebihi batas.
     */
    private function guardImageLimit(Request $request, News $news): void
    {
        $existing = NewsImage::where('news_id', $news->id)->count();
        $removing = NewsImage::where('news_id', $news->id)
            ->whereIn('id', $request->input('remove_images', []))
            ->count();
        $adding = count($request->file('images', []));

        if ($existing - $removing + $adding > self::MAX_IMAGES) {
            throw ValidationException::withMessages([
                'images' => 'Maksimal '.self::MAX_IMAGES.' gambar per berita.',
            ]);
        }
    }

    private function storeImages(News $news, Request $request): void
    {
        $position = (int) NewsImage::where('news_id', $news->id)->max('position');

        foreach ($request->file('images', []) as $file) {
            $news->images()->create([
                'path' => $file->store('news', 'public'),
                'position' => ++$position,
            ]);
        }
    }

    private function removeImages(Request $request, News $news): void
    {
        $ids = $request->input('remove_images', []);

        if (empty($ids)) {
            return;
        }

        // Dibatasi ke gambar milik berita ini saja
        NewsImage::where('news_id', $news->id)
            ->whereIn('id', $ids)
            ->get()
            ->each(function (NewsImage $image) {
                Storage::disk('public')->delete($image->path);
                $image->delete();
            });
    }
}