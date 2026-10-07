<?php

use App\Mail\InvoiceReminder;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();

    $this->parent = makeParent();
    $this->student = Student::factory()->for($this->parent, 'parent')->create();
});

function tagihanJatuhTempo(Student $student, int $hari, array $override = []): Invoice
{
    return Invoice::factory()->for($student)->create(array_merge([
        'due_date' => today()->addDays($hari)->toDateString(),
    ], $override));
}

it('mengingatkan tagihan yang jatuh tempo dalam 3 hari', function () {
    $invoice = tagihanJatuhTempo($this->student, 3);

    $this->artisan('invoices:send-reminders')->assertSuccessful();

    Mail::assertSent(InvoiceReminder::class, 1);
    Mail::assertSent(InvoiceReminder::class, fn ($mail) => $mail->hasTo($this->parent->email));
    expect($invoice->refresh()->reminded_at)->not->toBeNull();
});

it('juga mengingatkan tagihan yang jatuh tempo hari ini', function () {
    tagihanJatuhTempo($this->student, 0);

    $this->artisan('invoices:send-reminders')->assertSuccessful();

    Mail::assertSent(InvoiceReminder::class, 1);
});

it('tidak mengingatkan tagihan yang jatuh temponya masih lama', function () {
    $invoice = tagihanJatuhTempo($this->student, 5);

    $this->artisan('invoices:send-reminders')->assertSuccessful();

    Mail::assertNotSent(InvoiceReminder::class);
    expect($invoice->refresh()->reminded_at)->toBeNull();
});

it('tidak mengingatkan tagihan yang sudah lunas', function () {
    Invoice::factory()->for($this->student)->paid()->create([
        'due_date' => today()->addDays(2)->toDateString(),
    ]);

    $this->artisan('invoices:send-reminders')->assertSuccessful();

    Mail::assertNotSent(InvoiceReminder::class);
});

it('tidak mengingatkan tagihan yang sudah lewat jatuh tempo', function () {
    tagihanJatuhTempo($this->student, -1);

    $this->artisan('invoices:send-reminders')->assertSuccessful();

    Mail::assertNotSent(InvoiceReminder::class);
});

it('tidak mengirim pengingat dua kali untuk tagihan yang sama', function () {
    tagihanJatuhTempo($this->student, 2);

    $this->artisan('invoices:send-reminders')->assertSuccessful();
    $this->artisan('invoices:send-reminders')->assertSuccessful();

    Mail::assertSent(InvoiceReminder::class, 1);
});

it('menggabungkan beberapa tagihan satu orang tua dalam satu email', function () {
    $anak2 = Student::factory()->for($this->parent, 'parent')->create();
    tagihanJatuhTempo($this->student, 1);
    tagihanJatuhTempo($anak2, 2);

    $this->artisan('invoices:send-reminders')->assertSuccessful();

    Mail::assertSent(InvoiceReminder::class, 1);
    Mail::assertSent(InvoiceReminder::class, fn ($mail) => $mail->invoices->count() === 2);
    expect(Invoice::whereNull('reminded_at')->count())->toBe(0);
});

it('mengirim email terpisah untuk tiap orang tua', function () {
    $ortuLain = makeParent();
    $anakLain = Student::factory()->for($ortuLain, 'parent')->create();
    tagihanJatuhTempo($this->student, 2);
    tagihanJatuhTempo($anakLain, 2);

    $this->artisan('invoices:send-reminders')->assertSuccessful();

    Mail::assertSent(InvoiceReminder::class, 2);
    Mail::assertSent(InvoiceReminder::class, fn ($mail) => $mail->hasTo($ortuLain->email));
});

it('melewati siswa yang belum punya orang tua', function () {
    $tanpaOrtu = Student::factory()->create(['user_id' => null]);
    $invoice = tagihanJatuhTempo($tanpaOrtu, 2);

    $this->artisan('invoices:send-reminders')->assertSuccessful();

    Mail::assertNotSent(InvoiceReminder::class);
    expect($invoice->refresh()->reminded_at)->toBeNull();
});

it('melewati tagihan yang bukti bayarnya sedang menunggu verifikasi', function () {
    $invoice = tagihanJatuhTempo($this->student, 2);
    Payment::factory()->for($invoice)->create();

    $this->artisan('invoices:send-reminders')->assertSuccessful();

    Mail::assertNotSent(InvoiceReminder::class);
});

it('tetap mengingatkan kalau bukti bayar sebelumnya ditolak', function () {
    $invoice = tagihanJatuhTempo($this->student, 2);
    Payment::factory()->for($invoice)->rejected()->create();

    $this->artisan('invoices:send-reminders')->assertSuccessful();

    Mail::assertSent(InvoiceReminder::class, 1);
});

it('memakai opsi --days untuk jangkauan yang berbeda', function () {
    tagihanJatuhTempo($this->student, 7);

    $this->artisan('invoices:send-reminders')->assertSuccessful();
    Mail::assertNotSent(InvoiceReminder::class);

    $this->artisan('invoices:send-reminders', ['--days' => 7])->assertSuccessful();
    Mail::assertSent(InvoiceReminder::class, 1);
});

it('menampilkan data tagihan di email pengingat', function () {
    $anak = Student::factory()->for($this->parent, 'parent')->create(['name' => 'Budi Santoso']);
    $invoice = tagihanJatuhTempo($anak, 2, ['title' => 'SPP November 2026', 'amount' => 450000]);

    $mail = new InvoiceReminder($this->parent, collect([$invoice]));

    $mail->assertHasSubject('Pengingat: tagihan segera jatuh tempo');
    $mail->assertSeeInHtml('Budi Santoso');
    $mail->assertSeeInHtml('SPP November 2026');
    $mail->assertSeeInHtml('Rp 450.000');
});