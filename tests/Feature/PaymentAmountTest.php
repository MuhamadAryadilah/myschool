<?php

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

function berkasBuktiNominal(): UploadedFile
{
    return UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf');
}

beforeEach(function () {
    Storage::fake('local');
    Mail::fake();

    $this->parent = makeParent();
    $this->student = Student::factory()->for($this->parent, 'parent')->create();
    $this->invoice = Invoice::factory()->for($this->student)->create(['title' => 'SPP Nominal', 'amount' => 450000]);
});

it('menampilkan kolom nominal yang sudah terisi sesuai tagihan di dashboard', function () {
    $this->actingAs($this->parent)->get('/dashboard')
        ->assertOk()
        ->assertSee('name="amount_paid"', false)
        ->assertSee('value="450.000"', false);
});

it('menyimpan nominal yang dibayar', function () {
    $this->actingAs($this->parent)
        ->post(route('payments.store', $this->invoice), ['proof' => berkasBuktiNominal(), 'amount_paid' => '450.000'])
        ->assertSessionHasNoErrors();

    expect(Payment::firstOrFail()->amount_paid)->toBe(450000);
});

it('memakai nominal tagihan walau form mengirim nominal lain', function () {
    $this->actingAs($this->parent)
        ->post(route('payments.store', $this->invoice), ['proof' => berkasBuktiNominal(), 'amount_paid' => '1'])
        ->assertSessionHasNoErrors();

    expect(Payment::firstOrFail()->amount_paid)->toBe(450000);
});

it('tetap menyimpan nominal tagihan kalau kolom nominal tidak dikirim', function () {
    $this->actingAs($this->parent)
        ->post(route('payments.store', $this->invoice), ['proof' => berkasBuktiNominal()])
        ->assertSessionHasNoErrors();

    expect(Payment::firstOrFail()->amount_paid)->toBe(450000);
});

it('menandai pembayaran yang nominalnya sesuai tagihan', function () {
    Payment::factory()->for($this->invoice)->create(['user_id' => $this->parent->id, 'amount_paid' => 450000]);

    $this->actingAs(makeAdmin())->get(route('admin.payments.index'))
        ->assertOk()
        ->assertSee('sesuai tagihan')
        ->assertDontSee('selisih')
        ->assertDontSee('Nominal yang dibayar berbeda dari tagihan');
});

it('menandai selisih kurang dengan jumlahnya', function () {
    Payment::factory()->for($this->invoice)->create(['user_id' => $this->parent->id, 'amount_paid' => 400000]);

    $this->actingAs(makeAdmin())->get(route('admin.payments.index'))
        ->assertOk()
        ->assertSee('Nominal berbeda: kurang')
        ->assertSee('Rp 50.000');
});

it('menandai selisih lebih dengan jumlahnya', function () {
    Payment::factory()->for($this->invoice)->create(['user_id' => $this->parent->id, 'amount_paid' => 500000]);

    $this->actingAs(makeAdmin())->get(route('admin.payments.index'))
        ->assertSee('Nominal berbeda: lebih');
});

it('menampilkan keterangan untuk pembayaran lama tanpa nominal', function () {
    Payment::factory()->for($this->invoice)->create(['user_id' => $this->parent->id]);

    $this->actingAs(makeAdmin())->get(route('admin.payments.index'))
        ->assertOk()
        ->assertSee('Nominal tidak diisi')
        ->assertDontSee('selisih');
});

it('tetap membolehkan admin menerima pembayaran yang nominalnya berbeda', function () {
    $payment = Payment::factory()->for($this->invoice)->create(['user_id' => $this->parent->id, 'amount_paid' => 400000]);

    $this->actingAs(makeAdmin())
        ->patch(route('admin.payments.approve', $payment))
        ->assertSessionHasNoErrors();

    expect($payment->refresh()->status)->toBe('diterima')
        ->and($this->invoice->refresh()->isPaid())->toBeTrue();
});

it('menghitung selisih nominal di model', function () {
    $kurang = Payment::factory()->for($this->invoice)->create(['amount_paid' => 400000]);
    $pas = Payment::factory()->for($this->invoice)->create(['amount_paid' => 450000]);
    $lama = Payment::factory()->for($this->invoice)->create();

    expect($kurang->amountDifference())->toBe(-50000)
        ->and($pas->amountDifference())->toBe(0)
        ->and($lama->amountDifference())->toBeNull();
});