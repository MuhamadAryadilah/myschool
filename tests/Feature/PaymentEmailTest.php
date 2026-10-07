<?php

use App\Mail\PaymentApproved;
use App\Mail\PaymentRejected;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();

    $this->admin = makeAdmin();
    $this->parent = makeParent();

    $student = Student::factory()->for($this->parent, 'parent')->create(['name' => 'Zulkifli Anwar']);
    $invoice = Invoice::factory()->for($student)->create(['title' => 'SPP Oktober', 'amount' => 300000]);

    $this->payment = Payment::factory()->for($invoice)->create(['user_id' => $this->parent->id]);
});

it('mengirim email ke orang tua saat pembayaran diterima', function () {
    $this->actingAs($this->admin)
        ->patch(route('admin.payments.approve', $this->payment))
        ->assertSessionHasNoErrors();

    Mail::assertSent(PaymentApproved::class, 1);
    Mail::assertSent(PaymentApproved::class, fn ($mail) => $mail->hasTo($this->parent->email));
    Mail::assertNotSent(PaymentRejected::class);
});

it('mengirim email ke orang tua saat pembayaran ditolak', function () {
    $this->actingAs($this->admin)
        ->patch(route('admin.payments.reject', $this->payment), ['admin_note' => 'Nominal tidak sesuai'])
        ->assertSessionHasNoErrors();

    Mail::assertSent(PaymentRejected::class, 1);
    Mail::assertSent(PaymentRejected::class, fn ($mail) => $mail->hasTo($this->parent->email));
    Mail::assertNotSent(PaymentApproved::class);
});

it('menampilkan data tagihan di email pembayaran diterima', function () {
    (new PaymentApproved($this->payment))
        ->assertSeeInHtml('Zulkifli Anwar')
        ->assertSeeInHtml('SPP Oktober')
        ->assertSeeInHtml('Rp 300.000');
});

it('menampilkan alasan penolakan di email pembayaran ditolak', function () {
    $rejected = Payment::factory()->rejected('Nominal tidak sesuai')
        ->for($this->payment->invoice)
        ->create(['user_id' => $this->parent->id]);

    (new PaymentRejected($rejected))
        ->assertSeeInHtml('Nominal tidak sesuai')
        ->assertSeeInHtml('SPP Oktober');
});

it('tidak mengirim email untuk pembayaran yang sudah diproses', function () {
    $done = Payment::factory()->rejected()->create();

    $this->actingAs($this->admin)
        ->patch(route('admin.payments.approve', $done))
        ->assertSessionHasErrors('payment');

    $this->actingAs($this->admin)
        ->patch(route('admin.payments.reject', $done), ['admin_note' => 'Coba lagi'])
        ->assertSessionHasErrors('payment');

    Mail::assertNothingSent();
});

it('tidak mengirim email kalau penolakan tanpa alasan', function () {
    $this->actingAs($this->admin)
        ->patch(route('admin.payments.reject', $this->payment), [])
        ->assertSessionHasErrors('admin_note');

    Mail::assertNothingSent();
});