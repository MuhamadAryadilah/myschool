@if (config('demo.enabled'))
    @php $tourPage = request()->route()?->getName(); @endphp
    @if (in_array($tourPage, ['home', 'login', 'dashboard', 'admin.dashboard'], true))
        <script data-demo-tour="{{ $tourPage }}" data-login-url="{{ route('login') }}" data-feedback-url="{{ config('demo.feedback_url') }}">
        (function () {
            var meta = document.currentScript;
            var page = meta.getAttribute('data-demo-tour');
            var loginUrl = meta.getAttribute('data-login-url');
            var feedbackUrl = meta.getAttribute('data-feedback-url') || '';
            var KEY = 'demoTour:' + page;

            function seen() { try { return !!localStorage.getItem(KEY); } catch (e) { return false; } }
            function mark() { try { localStorage.setItem(KEY, '1'); } catch (e) {} }
            function q(sel) { return document.querySelector(sel); }
            function visible(n) {
                if (!n) return false;
                var r = n.getBoundingClientRect();
                return r.width > 0 && r.height > 0;
            }
            function byText(sel, text) {
                var list = Array.prototype.slice.call(document.querySelectorAll(sel));
                for (var i = 0; i < list.length; i++) {
                    if (list[i].textContent.indexOf(text) !== -1 && visible(list[i])) return list[i];
                }
                return null;
            }

            var all = {
                'home': [
                    { t: 'Selamat datang di MySchool', b: 'Ini versi coba portal sekolah: pendaftaran siswa baru, tagihan, pembayaran, dan berita. Ikuti panduan singkat ini, sekitar 1 menit. Semua data hanya contoh.' },
                    { t: 'Mulai dari sini', b: 'Klik Masuk, lalu pilih akun coba sebagai Admin sekolah atau Orang tua. Tidak perlu mendaftar.', el: function () { return q('header nav a[href$="/login"]'); } },
                    { t: 'Pendaftaran siswa baru', b: 'Calon orang tua bisa mendaftarkan anak secara online tanpa akun. Admin memeriksa dan menerimanya, lalu orang tua mendapat email untuk membuat password.', el: function () { return q('main a[href$="/pendaftaran"]'); } },
                    { t: 'Alur pendaftaran', b: 'Isi formulir, sekolah memeriksa data, lalu akun dibuat otomatis begitu pendaftaran diterima.', el: function () { var h = byText('h2', 'Langkah pendaftaran'); return h ? h.closest('section') : null; } },
                    { t: 'Apa saja yang bisa dicoba?', b: 'Orang tua: lihat tagihan anak, upload bukti bayar, unduh kuitansi.\nAdmin: terima pendaftaran, verifikasi pembayaran, buat tagihan massal, kelola berita dan eskul, ekspor CSV.', last: 'Coba sekarang', go: true }
                ],
                'login': [
                    { t: 'Pilih akun coba', b: 'Klik salah satu nama akun, misalnya admin1 atau ortu1. Email dan password terisi otomatis.', el: function () { var b = q('[data-email]'); return b ? b.parentElement : null; } },
                    { t: 'Tekan Masuk', b: 'Admin masuk ke dashboard admin, orang tua masuk ke dashboard orang tua. Panduan berikutnya muncul di sana.', el: function () { return q('form[action*="login"] button[type="submit"]'); }, last: 'Mengerti' }
                ],
                'dashboard': [
                    { t: 'Ringkasan tagihan', b: 'Lihat sisa tagihan, yang lewat jatuh tempo, dan yang sedang dicek admin.', el: function () { return q('main .grid-cols-2'); } },
                    { t: 'Upload bukti bayar', b: 'Pilih foto atau PDF bukti transfer lalu tekan Upload. Admin memverifikasi dan Anda menerima email hasilnya. Di versi coba tidak ada pembayaran sungguhan.', el: function () { return q('form[enctype="multipart/form-data"]'); } },
                    { t: 'Cara Bayar', b: 'Lihat rekening dan QRIS sekolah.', el: function () { return byText('nav a', 'Cara Bayar'); } },
                    { t: 'Riwayat dan kuitansi', b: 'Pantau status pembayaran dan unduh kuitansi setelah tagihan lunas.', el: function () { return byText('nav a', 'Riwayat Pembayaran'); }, last: 'Selesai' }
                ],
                'admin.dashboard': [
                    { t: 'Dashboard admin', b: 'Anda masuk sebagai admin sekolah. Menu ada di bar atas, atau di tombol garis tiga pada layar HP.' },
                    { t: 'Pendaftaran', b: 'Periksa pendaftar baru. Isi NIS dan kelas lalu tekan Terima: akun orang tua dibuat otomatis dan emailnya terkirim.', el: function () { return byText('nav a', 'Pendaftaran'); } },
                    { t: 'Pembayaran', b: 'Verifikasi bukti bayar. Terima untuk melunasi tagihan, atau Tolak dengan alasan.', el: function () { return byText('nav a', 'Pembayaran'); } },
                    { t: 'Keuangan', b: 'Tagihan, rekap per semester, dan tagihan massal ada di sini. Bisa diekspor ke CSV.', el: function () { return byText('nav a', 'Tagihan'); } },
                    { t: 'Berita', b: 'Tulis berita dengan beberapa gambar. Berita yang terbit tampil di halaman depan.', el: function () { return byText('nav a', 'Berita'); }, last: 'Selesai' }
                ]
            };
            var steps = all[page] || [];
            if (feedbackUrl && page !== 'login' && steps.length > 1) {
                steps.splice(steps.length - 1, 0, {
                    t: 'Beri review atau masukan',
                    b: 'Setelah mencoba, mohon isi formulir singkat: apa yang sudah bagus, apa yang membingungkan, dan fitur apa yang kurang. Masukan Anda sangat membantu.',
                    el: function () { return q('[data-demo-feedback]'); },
                    link: true
                });
            }
            if (!steps.length) return;

            var root = null, hole, card, elCount, elTitle, elBody, btnBack, btnNext, btnSkip, idx = 0, cur = null;

            function place() {
                if (!root) return;
                var vw = window.innerWidth, vh = window.innerHeight, pad = 6;
                var cw = card.offsetWidth, ch = card.offsetHeight;
                var top, left;
                if (cur) {
                    var r = cur.getBoundingClientRect();
                    hole.style.left = (r.left - pad) + 'px';
                    hole.style.top = (r.top - pad) + 'px';
                    hole.style.width = (r.width + pad * 2) + 'px';
                    hole.style.height = (r.height + pad * 2) + 'px';
                    if (vh - r.bottom >= ch + 24) { top = r.bottom + 14; }
                    else if (r.top >= ch + 24) { top = r.top - ch - 14; }
                    else { top = vh - ch - 16; }
                    left = Math.max(16, Math.min(r.left, vw - cw - 16));
                } else {
                    hole.style.left = (vw / 2) + 'px';
                    hole.style.top = (vh / 2) + 'px';
                    hole.style.width = '0px';
                    hole.style.height = '0px';
                    top = Math.max(16, (vh - ch) / 2);
                    left = Math.max(16, (vw - cw) / 2);
                }
                card.style.top = Math.max(16, top) + 'px';
                card.style.left = left + 'px';
            }

            function show(i) {
                idx = i;
                var s = steps[i];
                var n = s.el ? s.el() : null;
                cur = visible(n) ? n : null;
                if (cur && cur.scrollIntoView) cur.scrollIntoView({ block: 'center' });
                elCount.textContent = 'Langkah ' + (i + 1) + ' dari ' + steps.length;
                elTitle.textContent = s.t;
                elBody.textContent = s.b;
                var lk = root.querySelector('[data-link]');
                if (s.link && feedbackUrl) { lk.href = feedbackUrl; lk.style.display = 'inline-flex'; }
                else { lk.style.display = 'none'; }
                btnBack.style.visibility = i === 0 ? 'hidden' : 'visible';
                btnNext.textContent = i === steps.length - 1 ? (s.last || 'Selesai') : 'Lanjut';
                setTimeout(place, 60);
            }

            function close() {
                if (!root) return;
                root.parentNode.removeChild(root);
                root = null;
                window.removeEventListener('resize', place);
                window.removeEventListener('scroll', place, true);
                document.removeEventListener('keydown', onKey);
            }

            function finish() {
                var go = steps[idx].go;
                mark();
                close();
                if (go) window.location.href = loginUrl;
            }

            function onKey(e) {
                if (e.key === 'Escape') { mark(); close(); }
                else if (e.key === 'ArrowRight') { if (idx < steps.length - 1) show(idx + 1); else finish(); }
                else if (e.key === 'ArrowLeft' && idx > 0) { show(idx - 1); }
            }

            function start() {
                if (root || document.getElementById('demo-tour-root')) return;
                root = document.createElement('div');
                root.id = 'demo-tour-root';
                root.setAttribute('role', 'dialog');
                root.setAttribute('aria-modal', 'true');
                root.setAttribute('aria-label', 'Panduan mencoba web');
                root.style.cssText = 'position:fixed;inset:0;z-index:70;';
                var still = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                root.innerHTML =
                    '<div data-hole style="position:fixed;border-radius:12px;box-shadow:0 0 0 9999px rgba(2,6,23,.72);outline:2px solid rgba(45,212,191,.9);pointer-events:none;' + (still ? '' : 'transition:all .25s ease;') + '"></div>' +
                    '<div data-card class="rounded-xl border-2 border-brand-400 bg-white p-5 shadow-2xl dark:bg-gray-800" style="position:fixed;width:min(24rem,calc(100vw - 2rem));">' +
                        '<p data-count class="text-xs font-semibold uppercase tracking-wide text-brand-600 dark:text-brand-300"></p>' +
                        '<p data-title class="mt-1 text-lg font-bold text-gray-900 dark:text-gray-100"></p>' +
                        '<p data-body class="mt-2 whitespace-pre-line text-sm text-gray-600 dark:text-gray-300"></p>' +
                        '<a data-link target="_blank" rel="noopener" class="mt-3 items-center rounded-md border border-brand-500 px-3 py-1.5 text-sm font-semibold text-brand-700 hover:bg-brand-50 dark:text-brand-300 dark:hover:bg-gray-700" style="display:none">Buka formulir masukan</a>' +
                        '<div class="mt-4 flex items-center justify-between gap-2">' +
                            '<button type="button" data-skip class="text-sm text-gray-500 hover:underline dark:text-gray-400">Lewati</button>' +
                            '<div class="flex items-center gap-2">' +
                                '<button type="button" data-back class="rounded-md border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">Kembali</button>' +
                                '<button type="button" data-next class="rounded-md bg-gradient-to-r from-brand-600 to-sky-600 px-4 py-1.5 text-sm font-semibold text-white hover:from-brand-500 hover:to-sky-500"></button>' +
                            '</div>' +
                        '</div>' +
                    '</div>';
                document.body.appendChild(root);
                hole = root.querySelector('[data-hole]');
                card = root.querySelector('[data-card]');
                elCount = root.querySelector('[data-count]');
                elTitle = root.querySelector('[data-title]');
                elBody = root.querySelector('[data-body]');
                btnBack = root.querySelector('[data-back]');
                btnNext = root.querySelector('[data-next]');
                btnSkip = root.querySelector('[data-skip]');
                btnNext.addEventListener('click', function () { if (idx < steps.length - 1) show(idx + 1); else finish(); });
                btnBack.addEventListener('click', function () { if (idx > 0) show(idx - 1); });
                btnSkip.addEventListener('click', function () { mark(); close(); });
                window.addEventListener('resize', place);
                window.addEventListener('scroll', place, true);
                document.addEventListener('keydown', onKey);
                show(0);
                btnNext.focus();
            }

            // Tombol panduan kecil (tidak dipasang di halaman login)
            if (page !== 'login') {
                var again = document.createElement('button');
                again.type = 'button';
                again.setAttribute('aria-label', 'Lihat panduan');
                again.className = 'fixed bottom-4 left-4 z-40 flex items-center gap-1.5 rounded-full border border-brand-400/50 bg-white/90 px-3 py-1.5 text-xs font-semibold text-brand-700 shadow-lg backdrop-blur hover:bg-white dark:bg-gray-800/90 dark:text-brand-300 dark:hover:bg-gray-800 print:hidden';
                again.innerHTML = '<span aria-hidden="true">?</span><span>Panduan</span>';
                again.addEventListener('click', start);
                document.body.appendChild(again);
            }

            // Mulai otomatis sekali. Di dashboard, tunggu pop-up pemberitahuan ditutup.
            if (!seen()) {
                if (page === 'dashboard' || page === 'admin.dashboard') {
                    var tries = 0;
                    var timer = setInterval(function () {
                        tries++;
                        var d = q('[role="dialog"][aria-labelledby="demo-notice-title"]');
                        var open = d && getComputedStyle(d).display !== 'none';
                        if (!open && tries > 2) { clearInterval(timer); start(); }
                        if (tries > 240) clearInterval(timer);
                    }, 500);
                } else {
                    setTimeout(start, 700);
                }
            }
        })();
        </script>
    @endif
@endif