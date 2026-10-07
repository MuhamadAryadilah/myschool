@php
    $daftarLangkah = \App\Models\RegistrationStep::active()->ordered()->get();
    $ringkas = $compact ?? false;
@endphp

@if ($daftarLangkah->isNotEmpty())
    <section class="{{ $ringkas ? 'mt-6' : 'mt-10' }} text-left rounded-lg border border-brand-200 bg-brand-50 p-4 sm:p-6 dark:border-brand-700/50 dark:bg-brand-900/20">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $ringkas ? 'Alur setelah mendaftar' : 'Langkah berikutnya' }}</h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
            {{ $ringkas ? 'Ini tahapan yang akan Anda lalui setelah mengirim pendaftaran.' : 'Ikuti urutan berikut supaya proses pendaftaran lancar.' }}
        </p>

        <ol class="mt-4 space-y-4">
            @foreach ($daftarLangkah as $langkah)
                <li class="flex gap-3">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-brand-600 text-sm font-bold text-white">{{ $loop->iteration }}</span>
                    <div class="min-w-0">
                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ $langkah->title }}</p>
                        @if ($langkah->description)
                            <p class="mt-0.5 text-sm text-gray-600 dark:text-gray-300 whitespace-pre-line break-words">{{ $langkah->description }}</p>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
    </section>
@endif