<?php

use App\Models\Invoice;
use App\Models\Student;
use App\Rules\AcademicYear;
use Database\Seeders\DemoSeeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

function dasarTagihan(Student $siswa, array $override = []): array
{
    return array_merge([
        'student_id' => $siswa->id,
        'title' => 'Uang Semester',
        'amount' => 1500000,
        'due_date' => '2026-12-31',
    ], $override);
}

it('menerima tahun ajaran dua tahun berurutan dan menolak yang lain', function (string $nilai, bool $valid) {
    $v = Validator::make(['tahun' => $nilai], ['tahun' => [new AcademicYear]]);

    expect($v->passes())->toBe($valid);
})->with([
    'valid' => ['2026/2027', true],
    'tidak berurutan' => ['2026/2028', false],
    'terbalik' => ['2027/2026', false],
    'pakai strip' => ['2026-2027', false],
    'huruf' => ['abc', false],
    'terlalu pendek' => ['26/27', false],
]);

it('membuat label semester', function () {
    expect((new Invoice(['semester' => 'ganjil', 'academic_year' => '2026/2027']))->semesterLabel())
        ->toBe('Semester Ganjil 2026/2027')
        ->and((new Invoice(['semester' => null, 'academic_year' => null]))->semesterLabel())
        ->toBeNull();
});

it('menampilkan kolom semester di form tagihan', function () {
    $this->actingAs(makeAdmin())
        ->get(route('admin.invoices.create'))
        ->assertOk()
        ->assertSee('Tahun ajaran (opsional)');
});

it('membuat tagihan dengan semester dan tahun ajaran', function () {
    $siswa = Student::factory()->create();

    $this->actingAs(makeAdmin())
        ->post(route('admin.invoices.store'), dasarTagihan($siswa, ['semester' => 'ganjil', 'academic_year' => '2026/2027']))
        ->assertRedirect(route('admin.invoices.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('invoices', [
        'student_id' => $siswa->id,
        'semester' => 'ganjil',
        'academic_year' => '2026/2027',
    ]);
});

it('tetap menerima tagihan tanpa semester', function () {
    $siswa = Student::factory()->create();

    $this->actingAs(makeAdmin())
        ->post(route('admin.invoices.store'), dasarTagihan($siswa))
        ->assertSessionHasNoErrors();

    $invoice = Invoice::firstOrFail();
    expect($invoice->semester)->toBeNull()->and($invoice->academic_year)->toBeNull();
});

it('mewajibkan semester dan tahun ajaran diisi berpasangan', function () {
    $siswa = Student::factory()->create();
    $admin = makeAdmin();

    $this->actingAs($admin)
        ->post(route('admin.invoices.store'), dasarTagihan($siswa, ['semester' => 'ganjil']))
        ->assertSessionHasErrors('academic_year');

    $this->actingAs($admin)
        ->post(route('admin.invoices.store'), dasarTagihan($siswa, ['academic_year' => '2026/2027']))
        ->assertSessionHasErrors('semester');

    expect(Invoice::count())->toBe(0);
});

it('menolak semester dan tahun ajaran yang tidak valid', function () {
    $siswa = Student::factory()->create();
    $admin = makeAdmin();

    $this->actingAs($admin)
        ->post(route('admin.invoices.store'), dasarTagihan($siswa, ['semester' => 'tengah', 'academic_year' => '2026/2027']))
        ->assertSessionHasErrors('semester');

    $this->actingAs($admin)
        ->post(route('admin.invoices.store'), dasarTagihan($siswa, ['semester' => 'ganjil', 'academic_year' => '2026/2030']))
        ->assertSessionHasErrors('academic_year');

    expect(Invoice::count())->toBe(0);
});

it('mengubah semester tagihan', function () {
    $invoice = Invoice::factory()->create();

    $this->actingAs(makeAdmin())
        ->put(route('admin.invoices.update', $invoice), [
            'student_id' => $invoice->student_id,
            'title' => $invoice->title,
            'amount' => $invoice->amount,
            'due_date' => $invoice->due_date->toDateString(),
            'semester' => 'genap',
            'academic_year' => '2026/2027',
        ])
        ->assertSessionHasNoErrors();

    $invoice->refresh();
    expect($invoice->semester)->toBe('genap')->and($invoice->academic_year)->toBe('2026/2027');
});

it('membuat tagihan massal dengan semester', function () {
    Student::factory()->count(3)->create();

    $this->actingAs(makeAdmin())
        ->post(route('admin.invoices.bulk.store'), [
            'target' => 'semua',
            'title' => 'Uang Semester',
            'amount' => 1500000,
            'due_date' => '2026-12-31',
            'semester' => 'ganjil',
            'academic_year' => '2026/2027',
        ])
        ->assertSessionHasNoErrors();

    expect(Invoice::where('semester', 'ganjil')->where('academic_year', '2026/2027')->count())->toBe(3);
});

it('melewati tagihan massal kembar di semester yang sama tapi membuatnya di semester lain', function () {
    Student::factory()->count(2)->create();
    $admin = makeAdmin();
    $payload = [
        'target' => 'semua',
        'title' => 'Uang Semester',
        'amount' => 1500000,
        'due_date' => '2026-12-31',
        'semester' => 'ganjil',
        'academic_year' => '2026/2027',
    ];

    $this->actingAs($admin)->post(route('admin.invoices.bulk.store'), $payload);
    $this->actingAs($admin)->post(route('admin.invoices.bulk.store'), $payload);
    expect(Invoice::count())->toBe(2);

    $this->actingAs($admin)->post(route('admin.invoices.bulk.store'), array_merge($payload, ['semester' => 'genap']));
    expect(Invoice::count())->toBe(4);
});

it('mewajibkan tahun ajaran di tagihan massal kalau semester dipilih', function () {
    Student::factory()->create();

    $this->actingAs(makeAdmin())
        ->post(route('admin.invoices.bulk.store'), [
            'target' => 'semua',
            'title' => 'Uang Semester',
            'amount' => 1500000,
            'due_date' => '2026-12-31',
            'semester' => 'ganjil',
        ])
        ->assertSessionHasErrors('academic_year');

    expect(Invoice::count())->toBe(0);
});

it('mengisi semester di tagihan data demo', function () {
    Storage::fake('local');

    $this->seed(DemoSeeder::class);

    expect(Invoice::count())->toBe(30)
        ->and(Invoice::whereNotNull('semester')->count())->toBe(30);
});