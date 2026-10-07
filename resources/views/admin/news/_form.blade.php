@csrf

<div x-data="{ busy: false }" class="space-y-4">
    <div>
        <label class="block text-sm text-gray-700 dark:text-gray-300">Judul</label>
        <input type="text" name="title" value="{{ old('title', $news->title ?? '') }}"
               class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
        @error('title') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm text-gray-700 dark:text-gray-300">Isi Berita</label>
        <textarea name="content" rows="12"
                  class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">{{ old('content', $news->content ?? '') }}</textarea>
        @error('content') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    @isset($news)
        @if ($news->images->isNotEmpty())
            <div>
                <label class="block text-sm text-gray-700 dark:text-gray-300">Gambar saat ini</label>
                <div class="mt-2 grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach ($news->images as $image)
                        <label class="block p-2 border border-gray-200 dark:border-gray-700 rounded-md">
                            <img src="{{ $image->url() }}" alt="" class="h-28 w-full object-cover rounded">
                            <span class="mt-2 flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <input type="checkbox" name="remove_images[]" value="{{ $image->id }}"
                                       class="rounded border-gray-300 text-accent-500 focus:ring-accent-500">
                                Hapus gambar ini
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif
    @endisset

    <div>
        <label class="block text-sm text-gray-700 dark:text-gray-300">
            {{ isset($news) && $news->images->isNotEmpty() ? 'Tambah gambar' : 'Gambar' }}
        </label>
        <input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp"
               class="mt-1 block w-full text-sm text-gray-700 dark:text-gray-300 file:me-3 file:cursor-pointer file:rounded-md file:border-0 file:bg-brand-600 file:px-3 file:py-2 file:text-sm file:text-white hover:file:bg-brand-500">
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            Bisa pilih beberapa gambar sekaligus. Maksimal 10 gambar per berita, tiap gambar maksimal 4 MB (JPG, PNG, atau WEBP).
            Kalau gambarnya lebih dari satu, tampil sebagai slide otomatis di halaman berita.
        </p>
        @php
            $imageErrors = collect($errors->get('images'))
                ->merge(collect($errors->get('images.*'))->flatten())
                ->unique();
        @endphp
        @foreach ($imageErrors as $message)
            <p class="text-sm text-red-500">{{ $message }}</p>
        @endforeach
    </div>

    <div>
        <label class="block text-sm text-gray-700 dark:text-gray-300">Penulis</label>
        <input type="text" name="author" value="{{ old('author', $news->author ?? auth()->user()->name) }}"
               class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
        @error('author') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm text-gray-700 dark:text-gray-300">Status</label>
        <select name="status"
                class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
            <option value="draft" @selected(old('status', $news->status ?? 'draft') === 'draft')>Draft (belum tampil di publik)</option>
            <option value="published" @selected(old('status', $news->status ?? 'draft') === 'published')>Published (tampil di publik)</option>
        </select>
        @error('status') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    <div class="flex flex-col sm:flex-row gap-3">
        <button type="submit" :disabled="busy" @click="if ($el.form.checkValidity()) setTimeout(() => busy = true, 10)" class="disabled:opacity-60 w-full sm:w-auto px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-md">Simpan</button>
        <a href="{{ route('admin.news.index') }}" class="px-4 py-2 text-center text-gray-600 dark:text-gray-300">Batal</a>
    </div>
</div>