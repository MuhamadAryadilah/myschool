@csrf

<div class="space-y-4">
    <div>
        <label class="block text-sm text-gray-700 dark:text-gray-300">Judul langkah</label>
        <input type="text" name="title" value="{{ old('title', $step->title ?? '') }}" placeholder="Contoh: Jadwal tes seleksi"
               class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
        @error('title') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm text-gray-700 dark:text-gray-300">Keterangan (opsional)</label>
        <textarea name="description" rows="3"
                  class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">{{ old('description', $step->description ?? '') }}</textarea>
        @error('description') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm text-gray-700 dark:text-gray-300">Urutan (opsional)</label>
        <input type="number" name="sort_order" min="0" max="999" value="{{ old('sort_order', $step->sort_order ?? '') }}"
               class="mt-1 w-full sm:w-40 rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Angka kecil tampil lebih dulu. Kosongkan untuk menaruhnya di paling akhir.</p>
        @error('sort_order') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    <div class="flex items-center gap-2">
        <input type="hidden" name="is_active" value="0">
        <input id="is_active" type="checkbox" name="is_active" value="1" @checked(old('is_active', $step->is_active ?? true))
               class="rounded border-gray-300 dark:bg-gray-900 dark:border-gray-700 text-brand-600 shadow-sm focus:ring-brand-500">
        <label for="is_active" class="text-sm text-gray-700 dark:text-gray-300">Tampilkan ke calon orang tua</label>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-md">Simpan</button>
        <a href="{{ route('admin.registration-steps.index') }}" class="px-4 py-2 text-gray-600 dark:text-gray-300">Batal</a>
    </div>
</div>