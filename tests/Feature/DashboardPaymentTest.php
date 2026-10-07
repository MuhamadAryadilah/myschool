<?php

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;

beforeEach(function () {
    $this->parent = makeParent();
    $this->student = Student::factory()->for($this->parent, 'parent')->create();
});

it('menampilkan cara bayar dengan nominal tagihan untuk tagihan yang belum dibayar', function () {
    Invoice::factory()->for($this->student)->create(['title' => 'SPP Tes', 'amount' => 450000]);

    $this->actingAs($this->parent)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Transfer ke rekening sekolah')
        ->assertSee('Nominal transfer')
        ->assertSee('Rp 450.000')
        ->assertSee('50421861')
        ->assertSee('2036861671')
        ->assertSee('Muhamad Aryadilah')
        ->assertOk();
});

it('menampilkan nominal yang sesuai untuk tiap tagihan', function () {
    Invoice::factory()->for($this->student)->create(['title' => 'SPP A', 'amount' => 450000]);
    Invoice::factory()->for($this->student)->create(['title' => 'Buku', 'amount' => 125000]);

    $this->actingAs($this->parent)
        ->get('/dashboard')
        ->assertSee('Rp 450.000')
        ->assertSee('Rp 125.000');
});

it('tidak menampilkan cara bayar untuk tagihan yang sudah lunas', function () {
    Invoice::factory()->for($this->student)->paid()->create(['amount' => 450000]);

    $this->actingAs($this->parent)
        ->get('/dashboard')
        ->assertOk()
        ->assertDontSee('Nominal transfer');
});

it('tidak menampilkan cara bayar saat bukti bayar sedang menunggu verifikasi', function () {
    $invoice = Invoice::factory()->for($this->student)->create(['amount' => 450000]);
    Payment::factory()->for($invoice)->create(['user_id' => $this->parent->id]);

    $this->actingAs($this->parent)
        ->get('/dashboard')
        ->assertSee('admin sedang mengecek')
        ->assertDontSee('Nominal transfer');
});

it('menampilkan lagi cara bayar setelah bukti ditolak', function () {
    $invoice = Invoice::factory()->for($this->student)->create(['amount' => 450000]);
    Payment::factory()->rejected('Nominal tidak sesuai')->for($invoice)->create(['user_id' => $this->parent->id]);

    $this->actingAs($this->parent)
        ->get('/dashboard')
        ->assertSee('Nominal tidak sesuai')
        ->assertSee('Nominal transfer')
        ->assertSee('Rp 450.000');
});