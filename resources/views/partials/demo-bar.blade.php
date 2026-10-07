@if (config('demo.enabled'))
    <div class="bg-amber-400 px-3 py-2 text-center text-xs font-semibold text-amber-950 sm:text-sm">
        Versi coba MySchool. Data direset berkala, jangan masukkan data asli.
        <span class="block sm:inline">Klik <strong>Masuk</strong> untuk mencoba web ini.</span>
        @if (config('demo.feedback_url'))
            <a href="{{ config('demo.feedback_url') }}" target="_blank" rel="noopener"
               data-demo-feedback class="ms-1 underline decoration-2 underline-offset-2 hover:text-black">
                Beri masukan di sini
            </a>
        @endif
    </div>

    @if (request()->routeIs('dashboard', 'admin.dashboard'))
        <div x-data="{ open: false }"
             x-init="try { open = ! sessionStorage.getItem('demoNoticeSeen'); } catch (e) { open = true; }"
             x-show="open" x-cloak
             @keydown.escape.window="open = false; try { sessionStorage.setItem('demoNoticeSeen', '1'); } catch (e) {}"
             class="fixed inset-0 z-[60] flex items-center justify-center bg-black/60 p-4"
             role="dialog" aria-modal="true" aria-labelledby="demo-notice-title">
            <div class="w-full max-w-md rounded-xl border-2 border-amber-400 bg-white p-6 shadow-2xl dark:bg-gray-800">
                <p id="demo-notice-title" class="text-xl font-bold text-gray-900 dark:text-gray-100">Ini web uji coba</p>
                <ul class="mt-3 list-disc space-y-1.5 ps-5 text-sm text-gray-700 dark:text-gray-300">
                    <li>Jangan masukkan data asli, seperti nama, alamat, nomor HP, atau bukti transfer sungguhan.</li>
                    <li>Semua data di sini hanya contoh dan direset berkala.</li>
                    <li>Tidak ada pembayaran sungguhan yang diproses.</li>
                    @if (config('demo.feedback_url'))
                        <li>Mohon beri masukan lewat tautan "Beri masukan" di bagian atas halaman.</li>
                    @endif
                </ul>
                <button type="button"
                        @click="open = false; try { sessionStorage.setItem('demoNoticeSeen', '1'); } catch (e) {}"
                        class="mt-5 w-full rounded-md bg-amber-400 px-4 py-2 text-sm font-bold text-amber-950 hover:bg-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2">
                    Saya mengerti
                </button>
            </div>
        </div>
    @endif
@endif