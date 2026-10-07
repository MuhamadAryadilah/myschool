<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Pendaftaran {{ $registration->reference() }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 6000)" x-show="show" x-transition.opacity.duration.300ms role="status" class="flex items-start gap-2 p-3 bg-green-100 text-green-800 rounded-md"><svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span class="min-w-0 break-words">{{ session('success') }}</span></div>
            @endif

            @if (session('warning'))
                <div class="p-3 bg-yellow-100 text-yellow-800 rounded-md">{{ session('warning') }}</div>
            @endif

            @if ($errors->any())
                <div class="p-3 bg-red-100 text-red-800 rounded-md text-sm">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @php
                $sections = [
                    'Data calon siswa' => [
                        'Nama lengkap' => $registration->student_name,
                        'Jenis kelamin' => $registration->genderLabel(),
                        'Tempat, tanggal lahir' => $registration->birth_place.', '.$registration->birth_date->translatedFormat('j F Y'),
                        'NISN' => $registration->nisn ?: '-',
                        'Agama' => $registration->religion,
                        'Asal sekolah' => $registration->previous_school,
                        'Alamat' => $registration->address,
                        'Ekstrakurikuler pilihan' => $registration->extracurriculars->pluck('name')->join(', ') ?: 'Belum memilih',
                    ],
                    'Data orang tua / wali' => [
                        'Nama ayah' => $registration->father_name,
                        'Nama ibu' => $registration->mother_name,
                        'Pemilik akun' => $registration->guardian_name.' ('.$registration->guardian_relation.')',
                        'Pekerjaan' => $registration->guardian_job ?: '-',
                        'Nomor HP' => $registration->phone,
                        'Email (akun login)' => $registration->email,
                    ],
                ];
            @endphp

            <div class="p-4 sm:p-6 bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Dikirim {{ $registration->created_at->translatedFormat('j F Y H:i') }}
                    </p>
                    @if ($registration->status === 'diterima')
                        <span class="inline-block px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400">Diterima</span>
                    @elseif ($registration->status === 'ditolak')
                        <span class="inline-block px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400">Ditolak</span>
                    @else
                        <span class="inline-block px-2 py-0.5 text-xs font-medium rounded-full bg-yellow-100 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-400">Menunggu</span>
                    @endif
                </div>

                @foreach ($sections as $title => $rows)
                    <h3 class="mt-6 mb-2 font-semibold text-gray-900 dark:text-gray-100">{{ $title }}</h3>
                    <dl class="grid grid-cols-1 sm:grid-cols-3 gap-x-4 gap-y-2 text-sm text-gray-800 dark:text-gray-200">
                        @foreach ($rows as $label => $value)
                            <dt class="text-gray-500 dark:text-gray-400">{{ $label }}</dt>
                            <dd class="sm:col-span-2 whitespace-pre-line break-words">{{ $value }}</dd>
                        @endforeach
                    </dl>
                @endforeach
            </div>

            @if ($registration->isPending())
                <div class="p-4 sm:p-6 bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">Terima pendaftaran</h3>

                    @if ($emailIsAdmin)
                        <p class="mt-2 text-sm text-red-500">
                            Email ini dipakai akun admin, jadi pendaftaran tidak bisa diterima. Tolak pendaftaran ini dan minta pendaftar memakai email lain.
                        </p>
                    @else
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                            @if ($existingUser)
                                Email ini sudah punya akun ({{ $existingUser->name }}). Siswa akan dihubungkan ke akun tersebut.
                            @else
                                Akun orang tua baru akan dibuat dari email <strong>{{ $registration->email }}</strong>, lalu link membuat password dikirim ke email itu.
                            @endif
                        </p>

                        <form method="POST" action="{{ route('admin.registrations.approve', $registration) }}" x-data="{ busy: false }" @submit="busy = true"
                              class="mt-4 grid gap-4 sm:grid-cols-2">
                            @csrf
                            @method('PATCH')

                            <x-form-field name="nis" label="NIS siswa" :required="true" />
                            <x-form-field name="kelas" label="Kelas" :required="true">
                                <input id="kelas" name="kelas" list="kelas-list" value="{{ old('kelas') }}"
                                       class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                                <datalist id="kelas-list">
                                    @foreach ($kelasList as $kelas)
                                        <option value="{{ $kelas }}"></option>
                                    @endforeach
                                </datalist>
                            </x-form-field>

                            <div class="sm:col-span-2">
                                <button type="submit" :disabled="busy" class="btn-big-accept flex w-full sm:w-auto min-h-[2.75rem] items-center justify-center rounded-md bg-green-600 px-6 text-sm font-semibold text-white transition hover:bg-green-500 active:scale-[0.98] disabled:opacity-60"
                                        onclick="return confirm('Terima pendaftaran ini dan buat akunnya?')">
                                    Terima
                                </button>
                            </div>
                        </form>
                    @endif
                </div>

                <div class="p-4 sm:p-6 bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">Tolak pendaftaran</h3>

                    <form method="POST" action="{{ route('admin.registrations.reject', $registration) }}" x-data="{ busy: false }" @submit="busy = true" class="mt-4 space-y-3">
                        @csrf
                        @method('PATCH')

                        <x-form-field name="admin_note" label="Alasan penolakan (dikirim ke orang tua)" :required="true">
                            <textarea id="admin_note" name="admin_note" rows="2"
                                      class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">{{ old('admin_note') }}</textarea>
                        </x-form-field>

                        <button type="submit" :disabled="busy" class="flex w-full sm:w-auto min-h-[2.75rem] items-center justify-center rounded-md border border-red-500 bg-red-50 px-6 text-sm font-semibold text-red-700 transition hover:bg-red-100 active:scale-[0.98] disabled:opacity-60 dark:bg-red-500/10 dark:text-red-300 dark:hover:bg-red-500/20"
                                onclick="return confirm('Tolak pendaftaran ini?')">
                            Tolak
                        </button>
                    </form>
                </div>
            @elseif ($registration->status === 'diterima')
                <div class="p-4 sm:p-6 bg-white dark:bg-gray-800 shadow-sm rounded-lg text-sm text-gray-800 dark:text-gray-200">
                    <p>
                        Diterima {{ $registration->processed_at?->translatedFormat('j F Y H:i') }}.
                        @if ($registration->student)
                            Siswa tercatat dengan NIS <strong>{{ $registration->student->nis }}</strong>,
                            kelas <strong>{{ $registration->student->kelas }}</strong>.
                        @endif
                    </p>
                </div>
            @else
                <div class="p-4 sm:p-6 bg-white dark:bg-gray-800 shadow-sm rounded-lg text-sm text-gray-800 dark:text-gray-200">
                    <p>Ditolak {{ $registration->processed_at?->translatedFormat('j F Y H:i') }}.</p>
                    <p class="mt-1"><span class="text-gray-500 dark:text-gray-400">Alasan:</span> {{ $registration->admin_note }}</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>