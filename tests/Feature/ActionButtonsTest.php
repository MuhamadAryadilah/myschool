<?php

it('memakai gaya tombol aksi seragam di tabel admin', function (string $view) {
    expect(file_get_contents(resource_path("views/admin/{$view}.blade.php")))
        ->toContain('btn-act')
        ->not->toContain('text-accent-500hover');
})->with(['students/index', 'parents/index', 'invoices/index', 'news/index', 'extracurriculars/index']);

it('mendefinisikan gaya tombol aksi di app.css', function () {
    expect(file_get_contents(resource_path('css/app.css')))
        ->toContain('.btn-act-danger')
        ->toContain('.btn-act-success');
});