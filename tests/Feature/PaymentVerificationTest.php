<?php

use App\Models\Invoice;
use App\Models\Payment;

beforeEach(function () {
    $this->admin = makeAdmin();
    $this->payment = Payment::factory()->create();
});

it('menerima pembayaran dan menandai tagihan lunas', function () {
    $this->actingAs($this->admin)
        ->patch(route('admin.payments.approve', $this->payment))
        ->assertSessionHasNoErrors();

    $this->payment->refresh();
    $invoice = $this->payment->invoice->refresh();

    expect($this->payment->status)->toBe('diterima')
        ->and($this->payment->verified_at)->not->toBeNull()
        ->and($invoice->isPaid())->toBeTrue()
        ->and($invoice->paid_at)->not->toBeNull();
});

it('tidak memproses pembayaran yang sudah diproses', function () {
    $done = Payment::factory()->rejected()->create();

    $this->actingAs($this->admin)
        ->patch(route('admin.payments.approve', $done))
        ->assertSessionHasErrors('payment');

    expect($done->refresh()->status)->toBe('ditolak')
        ->and($done->invoice->refresh()->isPaid())->toBeFalse();
});

it('mewajibkan alasan saat menolak pembayaran', function () {
    $this->actingAs($this->admin)
        ->patch(route('admin.payments.reject', $this->payment), [])
        ->assertSessionHasErrors('admin_note');

    expect($this->payment->refresh()->status)->toBe('menunggu');
});

it('menolak pembayaran dengan alasan dan tagihan tetap belum lunas', function () {
    $this->actingAs($this->admin)
        ->patch(route('admin.payments.reject', $this->payment), ['admin_note' => 'Nominal tidak sesuai'])
        ->assertSessionHasNoErrors();

    $this->payment->refresh();

    expect($this->payment->status)->toBe('ditolak')
        ->and($this->payment->admin_note)->toBe('Nominal tidak sesuai')
        ->and($this->payment->invoice->refresh()->isPaid())->toBeFalse();
});

it('melarang orang tua memverifikasi pembayaran', function () {
    $this->actingAs(makeParent())
        ->patch(route('admin.payments.approve', $this->payment))
        ->assertForbidden();

    expect($this->payment->refresh()->status)->toBe('menunggu');
});

it('menandai tagihan lunas secara manual oleh admin', function () {
    $invoice = Invoice::factory()->create();

    $this->actingAs($this->admin)
        ->patch(route('admin.invoices.pay', $invoice))
        ->assertRedirect(route('admin.invoices.index'));

    expect($invoice->refresh()->isPaid())->toBeTrue();
});