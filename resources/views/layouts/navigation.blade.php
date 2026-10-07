@php
    $isAdmin = Auth::user()->isAdmin();
    $pending = $isAdmin ? \App\Models\Payment::where('status', 'menunggu')->count() : 0;
    $pendingReg = $isAdmin ? \App\Models\Registration::where('status', 'menunggu')->count() : 0;

    $ikon = [
        'home'    => 'M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25',
        'siswa' => 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z',
        'ortu' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
        'tagihan' => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
        'bayar'   => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'berita'  => 'M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 01-2.25 2.25M16.5 7.5V18a2.25 2.25 0 002.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 002.25 2.25h13.5M6 7.5h3v3H6v-3z',
        'riwayat' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
        'kartu' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z',
        'profil' => 'M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z',
        'keluar'  => 'M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9',
    ];

    if ($isAdmin) {
        $menu = [
            ['label' => 'Dashboard',  'route' => 'admin.dashboard',      'active' => 'admin.dashboard',   'icon' => $ikon['home']],
            ['label' => 'Siswa',      'route' => 'admin.students.index', 'active' => 'admin.students.*',  'icon' => $ikon['siswa']],
            ['label' => 'Orang Tua',  'route' => 'admin.parents.index',  'active' => 'admin.parents.*',   'icon' => $ikon['ortu']],
            ['label' => 'Tagihan',    'route' => 'admin.invoices.index', 'active' => 'admin.invoices.*',  'icon' => $ikon['tagihan']],
            ['label' => 'Pembayaran', 'route' => 'admin.payments.index', 'active' => 'admin.payments.*',  'icon' => $ikon['bayar'], 'badge' => $pending],
            ['label' => 'Berita',     'route' => 'admin.news.index',     'active' => 'admin.news.*',      'icon' => $ikon['berita']],
        ];
    } else {
        $menu = [
            ['label' => 'Dashboard',          'route' => 'dashboard',        'active' => 'dashboard',        'icon' => $ikon['home']],
            ['label' => 'Riwayat Pembayaran', 'route' => 'payments.history', 'active' => 'payments.history', 'icon' => $ikon['riwayat']],
            ['label' => 'Cara Bayar',         'route' => 'payments.methods', 'active' => 'payments.methods', 'icon' => $ikon['kartu']],
            ['label' => 'Berita',             'route' => 'news.index',       'active' => 'news.*',           'icon' => $ikon['berita']],
        ];
    }
    if ($isAdmin) {
        array_splice($menu, 5, 0, [[
            'label' => 'Pendaftaran', 'route' => 'admin.registrations.index', 'active' => 'admin.registrations.*',
            'icon' => 'M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM3 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 019.374 21c-2.331 0-4.512-.645-6.374-1.766z',
            'badge' => $pendingReg,
        ]]);
    }
    if ($isAdmin) {
        array_splice($menu, 2, 0, [[
            'label' => 'Eskul', 'route' => 'admin.extracurriculars.index', 'active' => 'admin.extracurriculars.*',
            'icon' => 'M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z',
        ]]);
    }
@endphp

@php
    if ($isAdmin) {
        // Urutan menu admin sama dengan urutan pintasan di dashboard
        $urutanAdmin = [
            'admin.dashboard',
            'admin.registrations.index',
            'admin.students.index',
            'admin.parents.index',
            'admin.extracurriculars.index',
            'admin.invoices.index',
            'admin.payments.index',
            'admin.news.index',
            'admin.registration-settings.edit',
        ];
        $menuAdmin = collect($menu)->keyBy('route');
        $menuAdmin->put('admin.registration-settings.edit', [
            'label'  => 'Pengaturan',
            'route'  => 'admin.registration-settings.edit',
            'active' => ['admin.registration-settings.*', 'admin.registration-steps.*'],
            'icon'   => 'M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75',
        ]);
        $menu = collect($urutanAdmin)->map(fn ($r) => $menuAdmin->get($r))->filter()->values()->all();
    }
    $menuDesktop = $menu;
@endphp

<nav x-data="{ open: false }" class="sticky top-0 z-40 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
    <!-- Menu utama -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ $isAdmin ? route('admin.dashboard') : route('dashboard') }}"
                       class="text-lg font-bold tracking-tight text-brand-700 dark:text-brand-300">
                        MySchool
                    </a>
                </div>

                <!-- Link navigasi -->
                <div class="hidden items-center space-x-3 xl:ms-6 xl:flex 2xl:space-x-6">
                    @foreach ($menuDesktop as $item)
                        @php $on = request()->routeIs(...(array) $item['active']); @endphp
                        <a href="{{ route($item['route']) }}" @if ($on) aria-current="page" @endif
                           class="inline-flex h-16 items-center gap-1.5 border-b-2 px-1 text-sm font-medium transition focus:outline-none {{ $on ? 'border-brand-400 text-gray-900 dark:text-gray-100' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/></svg>
                            {{ $item['label'] }}
                            @if (($item['badge'] ?? 0) > 0)
                                <span class="rounded-full bg-yellow-400 px-1.5 py-0.5 text-[10px] font-semibold leading-none text-yellow-950">{{ $item['badge'] }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Menu pengguna -->
            <div class="hidden xl:flex xl:items-center xl:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            <div class="flex items-center gap-2"><span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-brand-600 text-xs font-bold text-white">{{ mb_strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}</span><span>{{ Auth::user()->name }}</span></div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            Profil
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                Keluar
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center xl:hidden">
                <button @click="open = ! open" aria-label="Buka menu" class="relative inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    @if ($pending + $pendingReg > 0)
                        <span class="absolute right-1 top-1 h-2.5 w-2.5 rounded-full bg-yellow-400 ring-2 ring-white dark:ring-gray-800"></span>
                    @endif
                </button>
            </div>
        </div>
    </div>

    <!-- Garis aksen -->
    <div class="h-0.5 bg-gradient-to-r from-brand-500 via-brand-300 to-transparent dark:via-brand-700"></div>

    <!-- Menu untuk layar kecil -->
    <div x-show="open" style="display: none;" data-nav-panel
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="xl:hidden max-h-[calc(100vh-4rem)] overflow-y-auto">
        <div class="pt-2 pb-3 space-y-1">
            @foreach ($menu as $item)
                <x-responsive-nav-link :href="route($item['route'])"
                                       :active="request()->routeIs($item['active'])"
                                       :icon="$item['icon']"
                                       :badge="$item['badge'] ?? 0">
                    {{ $item['label'] }}
                </x-responsive-nav-link>
            @endforeach
        </div>

        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500 break-all">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 pb-2 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')" :icon="$ikon['profil']">
                    Profil
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')" :icon="$ikon['keluar']"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        Keluar
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>