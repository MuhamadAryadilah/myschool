@php
    $s = config('school');
    $ic = [
        'phone' => 'M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z',
        'mail'  => 'M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75',
        'pin'   => 'M15 10.5a3 3 0 11-6 0 3 3 0 016 0z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z',
    ];
@endphp

<footer class="relative mt-16 overflow-hidden bg-gradient-to-br from-brand-800 via-brand-700 to-sky-800 text-slate-200">
    <!-- Cahaya halus di pojok kiri bawah -->
    <div class="pointer-events-none absolute -bottom-32 -left-32 h-96 w-96 rounded-full bg-teal-500/20 blur-3xl"></div>

    <div class="relative mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 md:grid-cols-2 lg:grid-cols-12 lg:px-8 lg:py-16">
        <!-- Kolom sekolah -->
        <div class="lg:col-span-4 lg:border-e lg:border-white/10 lg:pe-10">
            <a href="{{ url('/') }}" class="text-2xl font-bold tracking-tight text-white">{{ $s['name'] }}</a>
            <p class="mt-4 text-sm leading-7 text-slate-300">{{ $s['tagline'] }}</p>

            @if (array_filter($s['social']))
                <div class="mt-6 flex gap-3">
                    @if ($s['social']['facebook'])
                        <a href="{{ $s['social']['facebook'] }}" target="_blank" rel="noopener" aria-label="Facebook"
                           class="flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M22 12a10 10 0 10-11.5 9.9v-7H8v-2.9h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.3c-1.2 0-1.6.8-1.6 1.6V12h2.8l-.4 2.9h-2.3v7A10 10 0 0022 12z"/></svg>
                        </a>
                    @endif
                    @if ($s['social']['instagram'])
                        <a href="{{ $s['social']['instagram'] }}" target="_blank" rel="noopener" aria-label="Instagram"
                           class="flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>
                        </a>
                    @endif
                    @if ($s['social']['x'])
                        <a href="{{ $s['social']['x'] }}" target="_blank" rel="noopener" aria-label="X"
                           class="flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                        </a>
                    @endif
                </div>
            @endif
        </div>

        <!-- Menu -->
        <div class="lg:col-span-2 lg:col-start-6">
            <h3 class="text-lg font-semibold text-white">Menu</h3>
            <ul class="mt-5 space-y-3 text-sm">
                <li><a href="{{ url('/') }}" class="transition hover:text-white">Beranda</a></li>
                <li><a href="{{ url('/berita') }}" class="transition hover:text-white">Berita</a></li>
                <li><a href="{{ route('registration.create') }}" class="transition hover:text-white">Daftar Siswa Baru</a></li>
            </ul>
        </div>

        @auth
        <!-- Orang tua -->
        <div class="lg:col-span-2">
            <h3 class="text-lg font-semibold text-white">Orang Tua</h3>
            <ul class="mt-5 space-y-3 text-sm">
                <li><a href="{{ route('payments.methods') }}" class="transition hover:text-white">Cara Bayar</a></li>
                <li><a href="{{ route('payments.history') }}" class="transition hover:text-white">Riwayat Pembayaran</a></li>
                <li><a href="{{ route('dashboard') }}" class="transition hover:text-white">Dashboard</a></li>
            </ul>
        </div>

        @endauth

        <!-- Kontak -->
        <div class="md:col-span-2 lg:col-span-3">
            <h3 class="text-lg font-semibold text-white">Kontak</h3>
            <ul class="mt-5 space-y-4 text-sm">
                @foreach ([['phone', $s['phone']], ['mail', $s['email']], ['pin', $s['address']]] as [$icon, $text])
                    @if ($text)
                        <li class="flex items-start gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white/10 text-white">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ic[$icon] }}"/></svg>
                            </span>
                            <span class="break-words pt-2.5 leading-5">{{ $text }}</span>
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>
    </div>

    <!-- Bar bawah -->
    <div class="relative border-t border-white/10">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-2 px-4 py-5 text-sm text-slate-300 sm:flex-row sm:px-6 lg:px-8">
            <p>&copy; {{ date('Y') }} <span class="font-semibold text-white">{{ $s['name'] }}</span>. Hak cipta dilindungi.</p>
        </div>
    </div>
</footer>