<?php

use App\Models\Invoice;
use App\Models\Student;

beforeEach(function () {
    $this->parent = makeParent();
    $this->student = Student::factory()->for($this->parent, 'parent')->create();
    $this->paid = Invoice::factory()->for($this->student)->paid()->create();
    $this->unpaid = Invoice::factory()->for($this->student)->create();
});

it('mengizinkan orang tua pemilik mengunduh kuitansi', function () {
    $this->actingAs($this->parent)
        ->get(route('receipts.show', $this->paid))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

it('mengizinkan admin membuka kuitansi', function () {
    $this->actingAs(makeAdmin())
        ->get(route('receipts.show', $this->paid))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

it('melarang orang tua lain membuka kuitansi', function () {
    $this->actingAs(makeParent())
        ->get(route('receipts.show', $this->paid))
        ->assertForbidden();
});

it('mengembalikan 404 untuk tagihan yang belum lunas', function () {
    $this->actingAs($this->parent)
        ->get(route('receipts.show', $this->unpaid))
        ->assertNotFound();

    $this->actingAs(makeAdmin())
        ->get(route('receipts.show', $this->unpaid))
        ->assertNotFound();
});

it('mengarahkan tamu ke halaman login', function () {
    $this->get(route('receipts.show', $this->paid))->assertRedirect('/login');
});