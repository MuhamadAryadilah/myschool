<?php

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    Storage::disk('local')->put('payments/bukti-tes.pdf', 'isi bukti');

    $this->parent = makeParent();
    $this->student = Student::factory()->for($this->parent, 'parent')->create();
    $this->invoice = Invoice::factory()->for($this->student)->create();
    $this->payment = Payment::factory()->for($this->invoice)->create([
        'user_id'    => $this->parent->id,
        'proof_path' => 'payments/bukti-tes.pdf',
    ]);
});

it('mengizinkan orang tua pemilik membuka bukti bayarnya', function () {
    $this->actingAs($this->parent)
        ->get(route('payments.proof', $this->payment))
        ->assertOk();
});

it('menolak orang tua lain membuka bukti bayar milik orang lain', function () {
    $this->actingAs(makeParent())
        ->get(route('payments.proof', $this->payment))
        ->assertForbidden();
});

it('mengizinkan admin membuka bukti bayar', function () {
    $this->actingAs(makeAdmin())
        ->get(route('payments.proof', $this->payment))
        ->assertOk();
});

it('mengarahkan tamu yang belum login ke halaman login', function () {
    $this->get(route('payments.proof', $this->payment))
        ->assertRedirect(route('login'));
});

it('mengembalikan 404 kalau file bukti tidak ada', function () {
    Storage::disk('local')->delete('payments/bukti-tes.pdf');

    $this->actingAs($this->parent)
        ->get(route('payments.proof', $this->payment))
        ->assertNotFound();
});