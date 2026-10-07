<?php

it('menampilkan tombol bukti bayar dan terima tolak yang jelas', function () {
    expect(file_get_contents(resource_path('views/admin/payments/index.blade.php')))
        ->toContain('btn-proof')
        ->toContain('grid-cols-2')
        ->toContain("route('admin.payments.approve'")
        ->toContain("route('admin.payments.reject'")
        ->not->toContain('text-gray-800dark');
});

it('menampilkan tombol besar di detail pendaftaran', function () {
    expect(file_get_contents(resource_path('views/admin/registrations/show.blade.php')))
        ->toContain('btn-big-accept')
        ->not->toContain('diterima.Tolak');
});