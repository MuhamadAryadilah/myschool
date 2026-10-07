<?php

use App\Models\Invoice;
use App\Models\Student;

function bacaCsv(string $csv): array
{
    $csv = preg_replace('/^\xEF\xBB\xBF/', '', $csv);
    $lines = array_values(array_filter(preg_split('/\r\n|\r|\n/', trim($csv)), fn ($l) => $l !== ''));

    return array_map(fn ($line) => str_getcsv($line, ';', '"', ''), $lines);
}

function judulCsv($response): array
{
    return collect(bacaCsv($response->streamedContent()))->skip(1)->pluck(3)->values()->all();
}

it('mengunduh CSV dengan penanda UTF-8, pemisah titik koma, dan header', function () {
    Invoice::factory()->create();

    $response = $this->actingAs(makeAdmin())->get(route('admin.invoices.export'));

    $response->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');

    $csv = $response->streamedContent();

    expect(str_starts_with($csv, "\xEF\xBB\xBF"))->toBeTrue();
    expect(bacaCsv($csv)[0])->toBe(['NIS', 'Siswa', 'Kelas', 'Tagihan', 'Jumlah', 'Jatuh Tempo', 'Status', 'Tanggal Lunas']);
});

it('mengisi baris CSV sesuai data tagihan', function () {
    $student = Student::factory()->create(['name' => 'Budi Santoso', 'nis' => '260001', 'kelas' => '7A']);
    $due = now()->addDays(10);
    Invoice::factory()->for($student)->create([
        'title' => 'SPP November 2026',
        'amount' => 450000,
        'due_date' => $due->toDateString(),
    ]);

    $rows = bacaCsv($this->actingAs(makeAdmin())->get(route('admin.invoices.export'))->streamedContent());

    expect($rows)->toHaveCount(2)
        ->and($rows[1])->toBe(['260001', 'Budi Santoso', '7A', 'SPP November 2026', '450000', $due->toDateString(), 'Belum lunas', '']);
});

it('mengisi status Terlambat dan tanggal lunas dengan benar', function () {
    $student = Student::factory()->create();
    Invoice::factory()->for($student)->overdue()->create(['title' => 'Tagihan Telat']);
    Invoice::factory()->for($student)->paid()->create(['title' => 'Tagihan Lunas']);

    $rows = collect(bacaCsv($this->actingAs(makeAdmin())->get(route('admin.invoices.export'))->streamedContent()))
        ->skip(1)
        ->keyBy(fn ($row) => $row[3]);

    expect($rows['Tagihan Telat'][6])->toBe('Terlambat')
        ->and($rows['Tagihan Telat'][7])->toBe('')
        ->and($rows['Tagihan Lunas'][6])->toBe('Lunas')
        ->and($rows['Tagihan Lunas'][7])->toBe(now()->toDateString());
});

it('hanya mengekspor tagihan sesuai filter status', function () {
    $student = Student::factory()->create();
    Invoice::factory()->for($student)->overdue()->create(['title' => 'Telat']);
    Invoice::factory()->for($student)->paid()->create(['title' => 'Lunas']);
    Invoice::factory()->for($student)->create(['title' => 'Belum']);

    $response = $this->actingAs(makeAdmin())->get(route('admin.invoices.export', ['status' => 'terlambat']));

    expect(judulCsv($response))->toBe(['Telat']);
});

it('hanya mengekspor tagihan sesuai filter kelas dan pencarian', function () {
    $ani = Student::factory()->create(['name' => 'Ani', 'kelas' => '7A']);
    $bimo = Student::factory()->create(['name' => 'Bimo', 'kelas' => '8B']);
    Invoice::factory()->for($ani)->create(['title' => 'SPP Ani']);
    Invoice::factory()->for($bimo)->create(['title' => 'SPP Bimo']);

    $admin = makeAdmin();

    expect(judulCsv($this->actingAs($admin)->get(route('admin.invoices.export', ['kelas' => '8B']))))
        ->toBe(['SPP Bimo']);

    expect(judulCsv($this->actingAs($admin)->get(route('admin.invoices.export', ['q' => 'Ani']))))
        ->toBe(['SPP Ani']);
});

it('tetap mengunduh file berisi header saja kalau tidak ada data', function () {
    $rows = bacaCsv($this->actingAs(makeAdmin())->get(route('admin.invoices.export'))->streamedContent());

    expect($rows)->toHaveCount(1);
});

it('melarang orang tua mengekspor tagihan', function () {
    $this->actingAs(makeParent())
        ->get(route('admin.invoices.export'))
        ->assertForbidden();
});

it('mengarahkan tamu ke halaman login saat ekspor', function () {
    $this->get(route('admin.invoices.export'))->assertRedirect('/login');
});

it('mencegah teks diawali karakter rumus dijalankan Excel', function () {
    $student = Student::factory()->create(['name' => '=1+1']);
    Invoice::factory()->for($student)->create();

    $rows = bacaCsv($this->actingAs(makeAdmin())->get(route('admin.invoices.export'))->streamedContent());

    expect($rows[1][1])->toBe("'=1+1");
});

it('menampilkan rekap total di daftar tagihan', function () {
    $student = Student::factory()->create();
    Invoice::factory()->for($student)->paid()->create(['amount' => 450000]);
    Invoice::factory()->for($student)->create(['amount' => 450000]);
    Invoice::factory()->for($student)->overdue()->create(['amount' => 450000]);

    $this->actingAs(makeAdmin())
        ->get(route('admin.invoices.index'))
        ->assertOk()
        ->assertSee('Rp 1.350.000')
        ->assertSee('Rp 900.000');
});

it('membuat rekap mengikuti filter yang aktif', function () {
    $student = Student::factory()->create();
    Invoice::factory()->for($student)->paid()->create(['amount' => 450000]);
    Invoice::factory()->for($student)->create(['amount' => 450000]);
    Invoice::factory()->for($student)->overdue()->create(['amount' => 450000]);

    $this->actingAs(makeAdmin())
        ->get(route('admin.invoices.index', ['status' => 'lunas']))
        ->assertOk()
        ->assertDontSee('Rp 1.350.000')
        ->assertSee('mengikuti filter');
});

it('membawa filter yang aktif ke tombol ekspor', function () {
    $this->actingAs(makeAdmin())
        ->get(route('admin.invoices.index', ['kelas' => '7A']))
        ->assertOk()
        ->assertSee('invoices/export?kelas=7A', false);
});