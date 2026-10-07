<?php

use App\Mail\PaymentUploaded;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    Mail::fake();

    $this->parent = makeParent();
    $this->student = Student::factory()->for($this->parent, 'parent')->create();
    $this->invoice = Invoice::factory()->for($this->student)->create();
});

it('mengirim email ke admin saat orang tua upload bukti bayar', function () {
    $admin = makeAdmin();

    $this->actingAs($this->parent)
        ->post(route('payments.store', $this->invoice), ['amount_paid' => 1, 'proof' => UploadedFile::fake()->image('bukti.jpg')])
        ->assertSessionHasNoErrors();

    Mail::assertSent(PaymentUploaded::class, 1);
    Mail::assertSent(PaymentUploaded::class, fn ($mail) => $mail->hasTo($admin->email));
});

it('mengirim email ke semua admin', function () {
    makeAdmin();
    makeAdmin();

    $this->actingAs($this->parent)
        ->post(route('payments.store', $this->invoice), ['amount_paid' => 1, 'proof' => UploadedFile::fake()->image('bukti.jpg')])
        ->assertSessionHasNoErrors();

    Mail::assertSent(PaymentUploaded::class, 2);
});

it('tidak mengirim email kalau upload ditolak', function () {
    makeAdmin();

    $this->actingAs($this->parent)
        ->post(route('payments.store', $this->invoice), [])
        ->assertSessionHasErrors('proof');

    $paid = Invoice::factory()->for($this->student)->paid()->create();

    $this->actingAs($this->parent)
        ->post(route('payments.store', $paid), ['amount_paid' => 1, 'proof' => UploadedFile::fake()->image('bukti.jpg')])
        ->assertSessionHasErrors('proof');

    Mail::assertNotSent(PaymentUploaded::class);
});

it('tetap menyimpan bukti walau tidak ada admin', function () {
    $this->actingAs($this->parent)
        ->post(route('payments.store', $this->invoice), ['amount_paid' => 1, 'proof' => UploadedFile::fake()->image('bukti.jpg')])
        ->assertSessionHasNoErrors();

    expect(Payment::count())->toBe(1);
    Mail::assertNotSent(PaymentUploaded::class);
});

it('menampilkan data pembayaran di email untuk admin', function () {
    $student = Student::factory()->for($this->parent, 'parent')->create(['name' => 'Budi Santoso', 'kelas' => '7A']);
    $invoice = Invoice::factory()->for($student)->create(['title' => 'SPP November 2026', 'amount' => 500000]);
    $payment = Payment::factory()->for($invoice)->for($this->parent, 'user')->create();

    $mail = new PaymentUploaded($payment);

    $mail->assertSeeInHtml('Budi Santoso');
    $mail->assertSeeInHtml('SPP November 2026');
    $mail->assertSeeInHtml('Rp 500.000');
});