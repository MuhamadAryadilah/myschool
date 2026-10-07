@csrf

<div x-data="{ busy: false }" class="space-y-4">
    <div>
        <label class="block text-sm text-gray-700 dark:text-gray-300">Nama</label>
        <input type="text" name="name" value="{{ old('name', $student->name ?? '') }}"
               class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
        @error('name') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm text-gray-700 dark:text-gray-300">NIS</label>
        <input type="text" name="nis" value="{{ old('nis', $student->nis ?? '') }}"
               class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
        @error('nis') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm text-gray-700 dark:text-gray-300">Kelas</label>
        <input type="text" name="kelas" value="{{ old('kelas', $student->kelas ?? '') }}"
               class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
        @error('kelas') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm text-gray-700 dark:text-gray-300">Ekstrakurikuler</label>
        <div class="mt-1 grid gap-2 sm:grid-cols-2">
        @php $dipilih = collect(old('extracurricular_ids', isset($student) ? $student->extracurriculars->pluck('id')->all() : []))->map(fn ($id) => (string) $id)->all(); @endphp
        @forelse (($extracurriculars ?? collect()) as $eskul)
            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                <input type="checkbox" name="extracurricular_ids[]" value="{{ $eskul->id }}" @checked(in_array((string) $eskul->id, $dipilih, true))
                       class="rounded border-gray-300 dark:bg-gray-900 dark:border-gray-700 text-brand-600 shadow-sm focus:ring-brand-500">
                <span>{{ $eskul->name }}{{ $eskul->is_active ? '' : ' (nonaktif)' }}</span>
            </label>
        @empty
            <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada eskul aktif.</p>
        @endforelse
    </div>
    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Boleh memilih lebih dari satu, atau dikosongkan.</p>
    @error('extracurricular_ids') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
    @error('extracurricular_ids.*') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm text-gray-700 dark:text-gray-300">Orang Tua</label>
        <select name="user_id"
                class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
            <option value="">- Belum dihubungkan -</option>
            @foreach ($parents as $parent)
                <option value="{{ $parent->id }}"
                    @selected(old('user_id', $student->user_id ?? '') == $parent->id)>
                    {{ $parent->name }} ({{ $parent->email }})
                </option>
            @endforeach
        </select>
        @error('user_id') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    <div class="flex gap-3">
        <button type="submit" :disabled="busy" @click="if ($el.form.checkValidity()) setTimeout(() => busy = true, 10)" class="disabled:opacity-60 px-4 py-2 bg-brand-600 text-white rounded-md">Simpan</button>
        <a href="{{ route('admin.students.index') }}" class="px-4 py-2 text-gray-600 dark:text-gray-300">Batal</a>
    </div>
</div>