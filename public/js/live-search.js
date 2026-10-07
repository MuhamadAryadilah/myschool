(function () {
    var timer = null, controller = null;

    function setup(form) {
        var q = form.querySelector('input[name="q"]');
        if (!q || form.dataset.live) return;
        form.dataset.live = '1';

        // sembunyikan tombol Cari (tetap ada kalau JS mati)
        var submit = form.querySelector('button[type="submit"]');
        if (submit) submit.style.display = 'none';

        // bungkus input + tombol X
        var wrap = document.createElement('div');
        wrap.className = 'relative w-full sm:w-72';
        q.parentNode.insertBefore(wrap, q);
        wrap.appendChild(q);
        q.className = q.className.replace(/sm:w-auto|sm:w-72/g, '').trim() + ' w-full pe-9';
        q.setAttribute('autocomplete', 'off');
        q.setAttribute('type', 'search');
        q.style.webkitAppearance = 'none';

        var clear = document.createElement('button');
        clear.type = 'button';
        clear.setAttribute('aria-label', 'Hapus pencarian');
        clear.innerHTML = '&times;';
        clear.className = 'absolute inset-y-0 end-0 px-3 text-xl leading-none text-gray-400 hover:text-gray-600 dark:hover:text-gray-200';
        clear.style.display = q.value ? 'block' : 'none';
        wrap.appendChild(clear);

        function url() {
            var p = new URLSearchParams(new FormData(form));
            Array.from(p.keys()).forEach(function (k) { if (!p.get(k)) p.delete(k); });
            var s = p.toString();
            return form.action.split('?')[0] + (s ? '?' + s : '');
        }

        function load() {
            var target = url();
            if (controller) controller.abort();
            controller = new AbortController();
            var table = document.querySelector('.overflow-x-auto');
            if (table) table.style.opacity = '0.5';

            fetch(target, { signal: controller.signal, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.text(); })
                .then(function (html) {
                    var doc = new DOMParser().parseFromString(html, 'text/html');
                    var newTable = doc.querySelector('.overflow-x-auto');
                    var oldTable = document.querySelector('.overflow-x-auto');
                    if (newTable && oldTable) oldTable.replaceWith(newTable);

                    // paginasi: blok setelah tabel
                    var oldPag = document.querySelector('.overflow-x-auto + .mt-4');
                    var newPag = doc.querySelector('.overflow-x-auto + .mt-4');
                    if (oldPag && newPag) oldPag.replaceWith(newPag);

                    history.replaceState(null, '', target);
                })
                .catch(function (e) {
                    if (e.name !== 'AbortError') { window.location.href = target; }
                });
        }

        function schedule() {
            clear.style.display = q.value ? 'block' : 'none';
            clearTimeout(timer);
            timer = setTimeout(load, 350);
        }

        q.addEventListener('input', schedule);
        form.querySelectorAll('select').forEach(function (s) {
            s.addEventListener('change', function () { clearTimeout(timer); load(); });
        });
        form.addEventListener('submit', function (e) { e.preventDefault(); clearTimeout(timer); load(); });
        clear.addEventListener('click', function () { q.value = ''; q.focus(); schedule(); });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('form[data-live-search]').forEach(setup);
    });
})();