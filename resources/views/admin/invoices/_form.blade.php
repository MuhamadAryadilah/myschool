@csrf

<div x-data="{ busy: false }" class="grid gap-4 md:grid-cols-2">
    <div>
        <label class="block text-sm text-gray-700 dark:text-gray-300">Siswa</label>
        <select name="student_id"
                class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
            <option value="">- Pilih siswa -</option>
            @foreach ($students as $student)
                <option value="{{ $student->id }}"
                    @selected(old('student_id', $invoice->student_id ?? '') == $student->id)>
                    {{ $student->name }} ({{ $student->kelas }})
                </option>
            @endforeach
        </select>
        @error('student_id') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm text-gray-700 dark:text-gray-300">Nama Tagihan</label>
        <input type="text" name="title" value="{{ old('title', $invoice->title ?? '') }}"
               placeholder="Contoh: SPP Oktober 2026"
               class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
        @error('title') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm text-gray-700 dark:text-gray-300">Jumlah (Rp)</label>
        <input type="number" name="amount" min="1" value="{{ old('amount', $invoice->amount ?? '') }}"
               class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
        @error('amount') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm text-gray-700 dark:text-gray-300">Jatuh Tempo</label>
        <input type="date" name="due_date"
               value="{{ old('due_date', isset($invoice) ? $invoice->due_date->format('Y-m-d') : '') }}"
               class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
        @error('due_date') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    <div class="grid gap-4 sm:grid-cols-2 md:col-span-2">
        <div>
            <label class="block text-sm text-gray-700 dark:text-gray-300">Semester (opsional)</label>
            <select name="semester"
                    class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                <option value="">- Tanpa semester -</option>
                @foreach (\App\Models\Invoice::SEMESTERS as $nilai => $label)
                    <option value="{{ $nilai }}" @selected(old('semester', $invoice->semester ?? '') === $nilai)>{{ $label }}</option>
                @endforeach
            </select>
            @error('semester') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm text-gray-700 dark:text-gray-300">Tahun ajaran (opsional)</label>
            <input type="text" name="academic_year" value="{{ old('academic_year', $invoice->academic_year ?? '') }}" placeholder="Contoh: 2026/2027"
                   class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
            @error('academic_year') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="flex gap-3 md:col-span-2">
        <button type="submit" :disabled="busy" @click="if ($el.form.checkValidity()) setTimeout(() => busy = true, 10)" class="disabled:opacity-60 px-4 py-2 bg-brand-600 text-white rounded-md">Simpan</button>
        <a href="{{ route('admin.invoices.index') }}" class="px-4 py-2 text-gray-600 dark:text-gray-300">Batal</a>
    </div>
</div>