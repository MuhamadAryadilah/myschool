<?php

use App\Models\Invoice;
use App\Models\Student;

function bulkPayload(array $override = []): array
{
    return array_merge([
        'target' => 'semua',
        'title' => 'SPP November 2026',
        'amount' => 500000,
        'due_date' => '2026-12-10',
    ], $override);
}

it('membuka halaman tagihan massal untuk admin', function () {
    $this->actingAs(makeAdmin())
        ->get(route('admin.invoices.bulk.create'))
        ->assertOk()
        ->assertSee('Tagihan Massal');
});

it('melarang orang tua memakai tagihan massal', function () {
    $parent = makeParent();

    $this->actingAs($parent)->get(route('admin.invoices.bulk.create'))->assertForbidden();
    $this->actingAs($parent)->post(route('admin.invoices.bulk.store'), bulkPayload())->assertForbidden();

    expect(Invoice::count())->toBe(0);
});

it('membuat tagihan untuk semua siswa', function () {
    Student::factory()->count(3)->create();

    $this->actingAs(makeAdmin())
        ->post(route('admin.invoices.bulk.store'), bulkPayload())
        ->assertRedirect(route('admin.invoices.index'))
        ->assertSessionHasNoErrors();

    expect(Invoice::count())->toBe(3);

    $invoice = Invoice::first();
    expect($invoice->title)->toBe('SPP November 2026')
        ->and($invoice->amount)->toBe(500000)
        ->and($invoice->due_date->toDateString())->toBe('2026-12-10');

    $this->assertDatabaseHas('invoices', ['title' => 'SPP November 2026', 'status' => 'belum_lunas']);
});

it('hanya membuat tagihan untuk kelas yang dipilih', function () {
    $a1 = Student::factory()->create(['kelas' => '7A']);
    $a2 = Student::factory()->create(['kelas' => '7A']);
    $b = Student::factory()->create(['kelas' => '7B']);

    $this->actingAs(makeAdmin())
        ->post(route('admin.invoices.bulk.store'), bulkPayload(['target' => 'kelas', 'kelas' => '7A']))
        ->assertSessionHasNoErrors();

    expect(Invoice::count())->toBe(2);
    $this->assertDatabaseHas('invoices', ['student_id' => $a1->id]);
    $this->assertDatabaseHas('invoices', ['student_id' => $a2->id]);
    $this->assertDatabaseMissing('invoices', ['student_id' => $b->id]);
});

it('melewati siswa yang sudah punya tagihan dengan nama sama', function () {
    $has = Student::factory()->create();
    $new = Student::factory()->create();
    Invoice::factory()->for($has)->create(['title' => 'SPP November 2026']);

    $this->actingAs(makeAdmin())
        ->post(route('admin.invoices.bulk.store'), bulkPayload())
        ->assertSessionHas('success');

    expect(session('success'))->toContain('dilewati');
    expect(Invoice::where('title', 'SPP November 2026')->count())->toBe(2);
    expect(Invoice::where('student_id', $has->id)->count())->toBe(1);
    expect(Invoice::where('student_id', $new->id)->count())->toBe(1);
});

it('tidak menggandakan tagihan saat dikirim dua kali', function () {
    Student::factory()->count(2)->create();
    $admin = makeAdmin();

    $this->actingAs($admin)->post(route('admin.invoices.bulk.store'), bulkPayload());
    $this->actingAs($admin)->post(route('admin.invoices.bulk.store'), bulkPayload());

    expect(Invoice::count())->toBe(2);
});

it('menolak input yang tidak valid', function (array $override, string $field) {
    Student::factory()->create(['kelas' => '7A']);

    $this->actingAs(makeAdmin())
        ->post(route('admin.invoices.bulk.store'), bulkPayload($override))
        ->assertSessionHasErrors($field);

    expect(Invoice::count())->toBe(0);
})->with([
    'tanpa nama' => [['title' => ''], 'title'],
    'jumlah nol' => [['amount' => 0], 'amount'],
    'tanggal salah' => [['due_date' => 'bukan-tanggal'], 'due_date'],
    'kelas kosong' => [['target' => 'kelas', 'kelas' => ''], 'kelas'],
    'kelas tidak ada' => [['target' => 'kelas', 'kelas' => '99Z'], 'kelas'],
]);

it('menolak kalau belum ada siswa', function () {
    $this->actingAs(makeAdmin())
        ->post(route('admin.invoices.bulk.store'), bulkPayload())
        ->assertSessionHasErrors('target');

    expect(Invoice::count())->toBe(0);
});

it('menampilkan tombol kembali di halaman tagihan massal', function () {
    $this->actingAs(makeAdmin())
        ->get(route('admin.invoices.bulk.create'))
        ->assertOk()
        ->assertSee('&larr; Kembali', false)->assertDontSee('Kembali ke daftar');
});