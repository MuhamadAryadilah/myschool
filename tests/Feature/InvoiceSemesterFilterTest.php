<?php

it('menampilkan filter tahun ajaran dan semester di daftar tagihan', function () {
    $this->actingAs(makeAdmin())->get(route('admin.invoices.index'))
        ->assertOk()
        ->assertSee('name="academic_year"', false)
        ->assertSee('name="semester"', false);
});

it('menerima filter semester di daftar dan ekspor tagihan', function () {
    $q = ['semester' => 'ganjil', 'academic_year' => '2026/2027'];

    $this->actingAs(makeAdmin())->get(route('admin.invoices.index', $q))->assertOk();
    $this->actingAs(makeAdmin())->get(route('admin.invoices.export', $q))->assertOk();
});