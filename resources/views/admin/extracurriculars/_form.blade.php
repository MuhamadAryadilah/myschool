@csrf

<div class="space-y-4">
    <div>
        <label class="block text-sm text-gray-700 dark:text-gray-300">Nama eskul</label>
        <input type="text" name="name" value="{{ old('name', $extracurricular->name ?? '') }}"
               class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
        @error('name') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm text-gray-700 dark:text-gray-300">Deskripsi (opsional)</label>
        <textarea name="description" rows="3"
                  class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">{{ old('description', $extracurricular->description ?? '') }}</textarea>
        @error('description') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm text-gray-700 dark:text-gray-300">Jadwal (opsional)</label>
        <input type="text" name="schedule" value="{{ old('schedule', $extracurricular->schedule ?? '') }}"
               placeholder="Contoh: Jumat, 14.00-16.00"
               class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
        @error('schedule') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    <div class="flex items-center gap-2">
        <input type="hidden" name="is_active" value="0">
        <input id="is_active" type="checkbox" name="is_active" value="1" @checked(old('is_active', $extracurricular->is_active ?? true))
               class="rounded border-gray-300 dark:bg-gray-900 dark:border-gray-700 text-brand-600 shadow-sm focus:ring-brand-500">
        <label for="is_active" class="text-sm text-gray-700 dark:text-gray-300">Aktif (muncul di formulir pendaftaran)</label>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-md">Simpan</button>
        <a href="{{ route('admin.extracurriculars.index') }}" class="px-4 py-2 text-gray-600 dark:text-gray-300">Batal</a>
    </div>
</div>