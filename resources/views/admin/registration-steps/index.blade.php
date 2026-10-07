<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Alur Setelah Mendaftar</h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            @include('admin.registration-settings._alerts')

            <div class="p-4 sm:p-6 bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    Langkah di bawah ditampilkan ke calon orang tua di formulir pendaftaran dan di halaman setelah pendaftaran terkirim.
                    Urutan mengikuti kolom Urutan (angka kecil tampil lebih dulu). Langkah nonaktif tidak ditampilkan.
                </p>

                <a href="{{ route('admin.registration-steps.create') }}"
                   class="mt-4 inline-block px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white text-sm rounded-md">Tambah Langkah</a>

                <ul class="mt-4 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($steps as $step)
                        <li class="py-3 flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <p class="font-medium text-gray-900 dark:text-gray-100">
                                    <span class="text-gray-500 dark:text-gray-400">{{ $step->sort_order }}.</span>
                                    {{ $step->title }}
                                    @unless ($step->is_active)
                                        <span class="ms-2 inline-block px-2 py-0.5 text-xs font-medium rounded-full bg-gray-200 text-gray-700 dark:bg-gray-600/40 dark:text-gray-300">Nonaktif</span>
                                    @endunless
                                </p>
                                @if ($step->description)
                                    <p class="mt-0.5 text-sm text-gray-600 dark:text-gray-300 whitespace-pre-line break-words">{{ $step->description }}</p>
                                @endif
                            </div>

                            <div class="flex shrink-0 gap-4 text-sm">
                                <a href="{{ route('admin.registration-steps.edit', $step) }}" class="text-brand-600 dark:text-brand-300 hover:underline">Edit</a>
                                <form action="{{ route('admin.registration-steps.destroy', $step) }}" method="POST"
                                      onsubmit="return confirm('Hapus langkah ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-accent-500 hover:underline">Hapus</button>
                                </form>
                            </div>
                        </li>
                    @empty
                        <li class="py-8 text-center text-gray-500 dark:text-gray-400">Belum ada langkah. Calon orang tua tidak melihat bagian alur.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>