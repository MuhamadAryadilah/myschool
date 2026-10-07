<?php

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;

it('menampilkan tabel orang tua sebagai kartu di layar kecil', function () {
    makeParent();

    $this->actingAs(makeAdmin())
        ->get(route('admin.parents.index'))
        ->assertOk()
        ->assertSee('table-stack', false)
        ->assertSee('data-label="Email"', false)
        ->assertSee('data-label="Aksi"', false);
});

it('menampilkan tabel tagihan sebagai kartu di layar kecil', function () {
    Invoice::factory()->create();

    $this->actingAs(makeAdmin())
        ->get(route('admin.invoices.index'))
        ->assertOk()
        ->assertSee('table-stack', false)
        ->assertSee('data-label="Jatuh Tempo"', false)
        ->assertSee('data-label="Status"', false);
});

it('menampilkan riwayat pembayaran sebagai kartu di layar kecil', function () {
    $parent = makeParent();
    $student = Student::factory()->for($parent, 'parent')->create();
    $invoice = Invoice::factory()->for($student)->create();
    Payment::factory()->for($invoice)->create(['user_id' => $parent->id]);

    $this->actingAs($parent)
        ->get(route('payments.history'))
        ->assertOk()
        ->assertSee('table-stack', false)
        ->assertSee('data-label="Tanggal upload"', false)
        ->assertSee('data-label="Bukti"', false);
});