<?php

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;

it('mengekspor siswa sesuai filter kelas', function () {
    Student::factory()->create(['name' => 'Siswa Tujuh Xq', 'nis' => '70001', 'kelas' => '7A']);
    Student::factory()->create(['name' => 'Siswa Delapan Zr', 'nis' => '80001', 'kelas' => '8B']);

    $response = $this->actingAs(makeAdmin())->get(route('admin.students.export', ['kelas' => '7A']));

    $response->assertOk();
    $csv = $response->streamedContent();

    expect($csv)->toContain('NIS;Nama;Kelas;Eskul')
        ->toContain('Siswa Tujuh Xq')
        ->not->toContain('Siswa Delapan Zr');
});

it('mengamankan sel berawalan rumus di ekspor siswa', function () {
    Student::factory()->create(['name' => '=HYPERLINK("x")', 'nis' => '70002', 'kelas' => '7A']);

    $csv = $this->actingAs(makeAdmin())->get(route('admin.students.export'))->streamedContent();

    expect($csv)->toContain("'=HYPERLINK");
});

it('mengekspor pembayaran', function () {
    $parent = makeParent();
    $student = Student::factory()->for($parent, 'parent')->create(['name' => 'Siswa Bayar Xq']);
    $invoice = Invoice::factory()->for($student)->create();
    Payment::factory()->for($invoice)->create(['user_id' => $parent->id]);

    $csv = $this->actingAs(makeAdmin())->get(route('admin.payments.export'))->streamedContent();

    expect($csv)->toContain('"Tanggal Upload";NIS;Siswa')
        ->toContain('Siswa Bayar Xq');
});

it('menampilkan tombol ekspor di halaman siswa dan pembayaran', function () {
    $admin = makeAdmin();

    $this->actingAs($admin)->get(route('admin.students.index'))->assertOk()->assertSee('Ekspor CSV');
    $this->actingAs($admin)->get(route('admin.payments.index'))->assertOk()->assertSee('Ekspor CSV');
});

it('melarang orang tua mengekspor data', function () {
    $this->actingAs(makeParent())->get(route('admin.students.export'))->assertForbidden();
    $this->actingAs(makeParent())->get(route('admin.payments.export'))->assertForbidden();
});