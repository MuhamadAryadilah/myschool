<?php

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');

    $this->parent = makeParent();
    $this->student = Student::factory()->for($this->parent, 'parent')->create();
    $this->invoice = Invoice::factory()->for($this->student)->create(['title' => 'SPP Milik Saya']);
});

function bukti(string $name = 'bukti.pdf'): UploadedFile
{
    return UploadedFile::fake()->create($name, 100, 'application/pdf');
}

it('mengizinkan orang tua upload bukti bayar untuk tagihan anaknya', function () {
    $this->actingAs($this->parent)
        ->post(route('payments.store', $this->invoice), ['amount_paid' => 1, 'proof' => bukti(), 'note' => 'Transfer BCA'])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $payment = Payment::firstOrFail();

    expect($payment->invoice_id)->toBe($this->invoice->id)
        ->and($payment->user_id)->toBe($this->parent->id)
        ->and($payment->status)->toBe('menunggu')
        ->and($payment->note)->toBe('Transfer BCA');

    Storage::disk('local')->assertExists($payment->proof_path);
});

it('menolak upload untuk tagihan anak orang lain', function () {
    $other = makeParent();

    $this->actingAs($other)
        ->post(route('payments.store', $this->invoice), ['amount_paid' => 1, 'proof' => bukti()])
        ->assertForbidden();

    expect(Payment::count())->toBe(0);
});

it('menolak upload untuk tagihan yang sudah lunas', function () {
    $paid = Invoice::factory()->for($this->student)->paid()->create();

    $this->actingAs($this->parent)
        ->post(route('payments.store', $paid), ['amount_paid' => 1, 'proof' => bukti()])
        ->assertSessionHasErrors('proof');

    expect(Payment::count())->toBe(0);
});

it('menolak upload kalau bukti sebelumnya masih menunggu verifikasi', function () {
    Payment::factory()->for($this->invoice)->create(['user_id' => $this->parent->id]);

    $this->actingAs($this->parent)
        ->post(route('payments.store', $this->invoice), ['amount_paid' => 1, 'proof' => bukti()])
        ->assertSessionHasErrors('proof');

    expect(Payment::count())->toBe(1);
});

it('mengizinkan upload ulang setelah bukti ditolak', function () {
    Payment::factory()->rejected()->for($this->invoice)->create(['user_id' => $this->parent->id]);

    $this->actingAs($this->parent)
        ->post(route('payments.store', $this->invoice), ['amount_paid' => 1, 'proof' => bukti()])
        ->assertSessionHasNoErrors();

    expect(Payment::count())->toBe(2);
});

it('menolak file yang bukan gambar atau pdf', function () {
    $this->actingAs($this->parent)
        ->post(route('payments.store', $this->invoice), [
            'amount_paid' => 1, 'proof' => UploadedFile::fake()->create('catatan.txt', 10, 'text/plain'),
        ])
        ->assertSessionHasErrors('proof');

    expect(Payment::count())->toBe(0);
});

it('mewajibkan file bukti', function () {
    $this->actingAs($this->parent)
        ->post(route('payments.store', $this->invoice), [])
        ->assertSessionHasErrors('proof');
});

it('hanya menampilkan riwayat pembayaran milik sendiri', function () {
    Payment::factory()->for($this->invoice)->create(['user_id' => $this->parent->id]);

    $otherInvoice = Invoice::factory()->create(['title' => 'SPP Milik Orang Lain']);
    Payment::factory()->for($otherInvoice)->create();

    $this->actingAs($this->parent)
        ->get(route('payments.history'))
        ->assertOk()
        ->assertSee('SPP Milik Saya')
        ->assertDontSee('SPP Milik Orang Lain');
});

it('menampilkan alasan penolakan di riwayat pembayaran', function () {
    Payment::factory()->rejected('Foto buram, upload ulang')
        ->for($this->invoice)->create(['user_id' => $this->parent->id]);

    $this->actingAs($this->parent)
        ->get(route('payments.history'))
        ->assertSee('Foto buram, upload ulang');
});