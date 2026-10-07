<?php

it('tidak menumpuk link kuitansi di bawah badge status', function (string $file) {
    expect(file_get_contents(resource_path($file)))
        ->toContain('btn-act btn-act-brand')
        ->not->toContain('block text-xs text-brand-500 hover:underline">Kuitansi')
        ->not->toContain('block text-xs text-brand-500 hover:underline">Unduh kuitansi');
})->with(['views/admin/invoices/index.blade.php', 'views/dashboard.blade.php']);