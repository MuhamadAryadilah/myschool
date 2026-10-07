<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Eskul {{ $extracurricular->name }}</h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @include('admin.extracurriculars._alerts')

            <div class="p-4 sm:p-6 bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-gray-800 dark:text-gray-200 break-words">{{ $extracurricular->description ?: 'Belum ada deskripsi.' }}</p>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            Jadwal: {{ $extracurricular->schedule ?: '-' }} &middot; Anggota: {{ $totalAnggota }}
                            @unless ($extracurricular->is_active) &middot; <strong>Nonaktif</strong> @endunless
                        </p>
                    </div>
                    <div class="flex gap-4 text-sm">
                        <a href="{{ route('admin.extracurriculars.edit', $extracurricular) }}" class="text-brand-600 dark:text-brand-300 hover:underline">Edit eskul</a>
                    </div>
                </div>
            </div>

            @if ($extracurricular->is_active)
                <div id="tambah-anggota" class="p-4 sm:p-6 bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">Tambah anggota</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Cari siswa dengan nama atau NIS. Siswa boleh ikut lebih dari satu eskul.</p>

                    <form method="GET" action="{{ route('admin.extracurriculars.show', $extracurricular) }}#tambah-anggota"
                          class="mt-3 flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-2">
                        @if ($cariAnggota !== '')
                            <input type="hidden" name="m" value="{{ $cariAnggota }}">
                        @endif
                        <input type="text" name="q" value="{{ $cari }}" placeholder="Cari nama atau NIS siswa"
                               class="w-full sm:w-72 text-sm rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200">

                        <select name="kelas"
                                class="w-full sm:w-auto text-sm rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200">
                            <option value="">Semua kelas</option>
                            @foreach ($kelasList as $kelas)
                                <option value="{{ $kelas }}" @selected($kelasDipilih === (string) $kelas)>{{ $kelas }}</option>
                            @endforeach
                        </select>

                        <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white text-sm rounded-md">Cari</button>

                        @if ($mencari)
                            <a href="{{ route('admin.extracurriculars.show', array_filter(['extracurricular' => $extracurricular, 'm' => $cariAnggota])) }}#tambah-anggota"
                               class="text-sm text-center sm:text-left text-gray-600 dark:text-gray-300 hover:underline">Reset</a>
                        @endif
                    </form>

                    @if (! $mencari)
                        <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">Ketik nama atau NIS, atau pilih kelas, lalu tekan Cari.</p>
                    @elseif ($results->isEmpty())
                        <div class="mt-4 flex flex-col items-center gap-2 py-6 text-center text-gray-500 dark:text-gray-400">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                            <span>Tidak ada siswa yang cocok.</span>
                        </div>
                    @else
                        <ul class="mt-4 divide-y divide-gray-200 dark:divide-gray-700 border border-gray-200 dark:border-gray-700 rounded-md">
                            @foreach ($results as $siswa)
                                <li class="flex flex-col gap-2 p-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="min-w-0">
                                        <p class="font-medium text-gray-900 dark:text-gray-100 break-words">{{ $siswa->name }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            NIS: {{ $siswa->nis }} &middot; Kelas: {{ $siswa->kelas }}
                                            @if ($siswa->extracurriculars->isNotEmpty())
                                                &middot; <span class="inline-block px-2 py-0.5 text-xs font-medium rounded-full bg-yellow-100 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-400">juga ikut: {{ $siswa->extracurriculars->pluck('name')->join(', ') }}</span>
                                            @endif                                        </p>
                                    </div>

                                    <form method="POST" action="{{ route('admin.extracurriculars.members.store', $extracurricular) }}"
                                          x-data="{ busy: false }" @submit="busy = true">
                                        @csrf
                                        <input type="hidden" name="student_id" value="{{ $siswa->id }}">
                                        <button type="submit" :disabled="busy"
                                                class="w-full sm:w-auto px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white text-sm rounded-md disabled:opacity-60">
                                            Tambah
                                        </button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>

                        <div class="mt-4">
                            {{ $results->links() }}
                        </div>
                    @endif
                </div>
            @endif

            <div id="anggota" class="p-4 sm:p-6 bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">Anggota ({{ $totalAnggota }})</h3>

                    @if ($totalAnggota > 0)
                        <form method="GET" action="{{ route('admin.extracurriculars.show', $extracurricular) }}#anggota"
                              class="flex w-full sm:w-auto items-center gap-2">
                            @if ($cari !== '')
                                <input type="hidden" name="q" value="{{ $cari }}">
                            @endif
                            @if ($kelasDipilih !== '')
                                <input type="hidden" name="kelas" value="{{ $kelasDipilih }}">
                            @endif
                            <input type="text" name="m" value="{{ $cariAnggota }}" placeholder="Cari anggota (nama atau NIS)"
                                   class="min-w-0 flex-1 sm:w-64 text-sm rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200">
                            <button type="submit" class="px-3 py-1.5 bg-brand-600 hover:bg-brand-500 text-white text-sm rounded-md">Cari</button>
                            @if ($cariAnggota !== '')
                                <a href="{{ route('admin.extracurriculars.show', array_filter(['extracurricular' => $extracurricular, 'q' => $cari, 'kelas' => $kelasDipilih])) }}#anggota"
                                   class="text-sm text-gray-600 dark:text-gray-300 hover:underline">Reset</a>
                            @endif
                        </form>
                    @endif
                </div>

                <div class="mt-3 overflow-x-auto">
                    <table class="table-stack w-full text-left text-gray-800 dark:text-gray-200">
                        <thead>
                            <tr class="border-b border-gray-300 dark:border-gray-700">
                                <th class="py-2">NIS</th>
                                <th class="py-2">Nama</th>
                                <th class="py-2">Kelas</th>
                                <th class="py-2">Orang Tua</th>
                                <th class="py-2">Pindah / Keluarkan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($anggota as $siswa)
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <td class="py-2" data-label="NIS">{{ $siswa->nis }}</td>
                                    <td class="py-2" data-label="Nama">{{ $siswa->name }}</td>
                                    <td class="py-2" data-label="Kelas">{{ $siswa->kelas }}</td>
                                    <td class="py-2" data-label="Orang Tua">{{ $siswa->parent?->name ?? '-' }}</td>
                                    <td class="py-2" data-label="Pindah / Keluarkan">
                                        <div class="flex w-full flex-col gap-2 md:flex-row md:items-center">
                                            @if ($others->isNotEmpty())
                                                <form method="POST" action="{{ route('admin.students.extracurricular.update', $siswa) }}"
                                                      x-data="{ busy: false }" @submit="busy = true"
                                                      class="flex w-full md:w-auto gap-2">
                                                    @csrf
                                                    @method('PATCH')
<input type="hidden" name="from" value="{{ $extracurricular->id }}">
                                                    <select name="extracurricular_id" required
                                                            class="min-w-0 flex-1 text-sm rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200">
                                                        <option value="" disabled selected>Pindah ke...</option>
                                                        @foreach ($others as $other)
                                                            <option value="{{ $other->id }}">{{ $other->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <button type="submit" :disabled="busy" class="px-3 py-1.5 bg-brand-600 hover:bg-brand-500 text-white text-sm rounded-md disabled:opacity-60">Pindahkan</button>
                                                </form>
                                            @endif

                                            <form method="POST" action="{{ route('admin.students.extracurricular.update', $siswa) }}"
                                                  onsubmit="return confirm('Keluarkan siswa ini dari eskul?')">
                                                @csrf
                                                @method('PATCH')
<input type="hidden" name="from" value="{{ $extracurricular->id }}">
                                                <input type="hidden" name="extracurricular_id" value="">
                                                <button type="submit" class="btn-act btn-act-danger">Keluarkan</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-8 text-center text-gray-500 dark:text-gray-400">{{ $cariAnggota !== '' ? 'Tidak ada anggota yang cocok.' : 'Belum ada anggota.' }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $anggota->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>