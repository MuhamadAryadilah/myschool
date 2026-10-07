<?php

use App\Models\Invoice;
use App\Models\Student;
use Illuminate\Support\Facades\File;

it('menampilkan tombol salin nominal dan tautan panduan lengkap di dashboard', function () {
    $parent = makeParent();
    $student = Student::factory()->for($parent, 'parent')->create();
    Invoice::factory()->for($student)->create(['amount' => 450000]);

    $this->actingAs($parent)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Nominal transfer')
        ->assertSee('Salin')
        ->assertSee('Transfer ke rekening sekolah')
        ->assertSee(route('payments.methods'), false);
});

it('menampilkan tombol perbesar dan unduh saat gambar QRIS ada', function () {
    $path = public_path('images/_tes-qris-ui.png');
    File::ensureDirectoryExists(dirname($path));
    File::put($path, 'x');
    config(['payment.qris_image' => 'images/_tes-qris-ui.png']);

    $this->actingAs(makeParent())
        ->get(route('payments.methods'))
        ->assertSee('Unduh gambar')
        ->assertSee('Perbesar');

    File::delete($path);
});

it('menampilkan tombol kembali di halaman cara bayar', function () {
    $this->actingAs(makeParent())
        ->get(route('payments.methods'))
        ->assertSee('Kembali ke dashboard');
});