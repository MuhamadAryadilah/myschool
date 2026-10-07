@php
    $demoPassword = config('demo.password');
    $demoAkun = [
        'Admin' => collect(range(1, 3))->mapWithKeys(fn ($i) => ["admin{$i}" => "admin{$i}@demo.test"])->all(),
        'Orang tua' => collect(range(1, 10))->mapWithKeys(fn ($i) => ["ortu{$i}" => "ortu{$i}@demo.test"])->all(),
    ];
@endphp

<div class="mb-6 rounded-lg border-2 border-amber-400 bg-amber-50 p-4 text-sm text-amber-950 shadow-sm dark:border-amber-400/60 dark:bg-amber-400/10 dark:text-amber-50">
    <p class="text-base font-bold">Akun coba</p>
    <p class="mt-1">
        Klik salah satu akun untuk mengisi form, lalu tekan <strong>Masuk</strong>.
        Password semua akun: <code class="rounded bg-amber-200 px-1.5 py-0.5 font-mono font-bold text-amber-950 dark:bg-amber-400/30 dark:text-amber-50">{{ $demoPassword }}</code>
    </p>

    @foreach ($demoAkun as $label => $akun)
        <p class="mt-4 text-xs font-bold uppercase tracking-wide">{{ $label }}</p>
        <div class="mt-1.5 flex flex-wrap gap-2">
            @foreach ($akun as $nama => $email)
                <button type="button" data-email="{{ $email }}" data-password="{{ $demoPassword }}" onclick="isiAkunDemo(this)"
                        class="rounded-md border border-amber-500 bg-white px-2.5 py-1 text-xs font-semibold text-amber-900 hover:bg-amber-400 hover:text-amber-950 focus:outline-none focus:ring-2 focus:ring-amber-500 dark:border-amber-400/60 dark:bg-gray-900 dark:text-amber-100 dark:hover:bg-amber-400 dark:hover:text-amber-950">
                    {{ $nama }}
                </button>
            @endforeach
        </div>
    @endforeach

    <p class="mt-4 text-xs">Ini versi coba: jangan masukkan data asli atau unggah dokumen pribadi. Data direset berkala.</p>

    @if (config('demo.feedback_url'))
        <a href="{{ config('demo.feedback_url') }}" target="_blank" rel="noopener"
           class="mt-3 inline-flex items-center gap-1.5 rounded-md bg-accent-500 px-3 py-1.5 text-xs font-bold text-white hover:bg-accent-600 focus:outline-none focus:ring-2 focus:ring-accent-400 focus:ring-offset-2">
            Sudah mencoba? Beri masukan
        </a>
    @endif
</div>

<script>
    function isiAkunDemo(el) {
        document.getElementById('email').value = el.dataset.email;
        document.getElementById('password').value = el.dataset.password;
    }
</script>