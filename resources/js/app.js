import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Penjaga klik ganda untuk semua form (kecuali GET, x-data, atau data-no-lock).
const LOADING_TEXT = 'Memproses...';

function submitButtons(form) {
    return form.querySelectorAll('button[type="submit"], button:not([type]), input[type="submit"]');
}

function unlock(form) {
    delete form.dataset.locked;
    submitButtons(form).forEach((btn) => {
        if (btn.dataset.originalLabel !== undefined) {
            if (btn.tagName === 'INPUT') {
                btn.value = btn.dataset.originalLabel;
            } else {
                btn.innerHTML = btn.dataset.originalLabel;
            }
            delete btn.dataset.originalLabel;
        }
        btn.disabled = false;
        btn.classList.remove('opacity-60', 'cursor-wait');
    });
}

document.addEventListener('submit', (event) => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement)) return;
    if (form.method && form.method.toLowerCase() === 'get') return;
    if (form.hasAttribute('x-data') || form.hasAttribute('data-no-lock')) return;

    if (form.dataset.locked) {
        event.preventDefault();
        return;
    }

    // Jika ada handler lain yang membatalkan (konfirmasi ditolak, dsb.), jangan kunci.
    setTimeout(() => {
        if (event.defaultPrevented) return;

        form.dataset.locked = '1';
        submitButtons(form).forEach((btn) => {
            if (btn.tagName === 'INPUT') {
                btn.dataset.originalLabel = btn.value;
                btn.value = LOADING_TEXT;
            } else {
                btn.dataset.originalLabel = btn.innerHTML;
                btn.textContent = LOADING_TEXT;
            }
            btn.disabled = true;
            btn.classList.add('opacity-60', 'cursor-wait');
        });
    }, 0);
});

// Tombol Kembali di browser: aktifkan lagi semua form yang terkunci.
window.addEventListener('pageshow', () => {
    document.querySelectorAll('form[data-locked]').forEach(unlock);
});