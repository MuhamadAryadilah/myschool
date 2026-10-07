@csrf

<div x-data="{ busy: false }" class="space-y-4">
    <div>
        <label class="block text-sm text-gray-700 dark:text-gray-300">Nama</label>
        <input type="text" name="name" value="{{ old('name', $parent->name ?? '') }}"
               class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
        @error('name') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm text-gray-700 dark:text-gray-300">Email</label>
        <input type="email" name="email" value="{{ old('email', $parent->email ?? '') }}"
               class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
        @error('email') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm text-gray-700 dark:text-gray-300">Nomor HP / WhatsApp (opsional)</label>
        <input type="tel" name="phone" value="{{ old('phone', $parent->phone ?? '') }}" placeholder="Contoh: 081234567890"
               class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
        @error('phone') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm text-gray-700 dark:text-gray-300">Password</label>
        <input type="password" name="password"
               class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
        @isset($parent)
            <p class="text-sm text-gray-500 dark:text-gray-400">Kosongkan jika tidak ingin mengubah password.</p>
        @endisset
        @error('password') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    <div class="flex gap-3">
        <button type="submit" :disabled="busy" @click="if ($el.form.checkValidity()) setTimeout(() => busy = true, 10)" class="disabled:opacity-60 px-4 py-2 bg-brand-600 text-white rounded-md">Simpan</button>
        <a href="{{ route('admin.parents.index') }}" class="px-4 py-2 text-gray-600 dark:text-gray-300">Batal</a>
    </div>
</div>