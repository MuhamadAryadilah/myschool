<?php

use App\Models\Student;

it('mengarahkan tamu ke halaman login', function () {
    $this->get('/admin/invoices')->assertRedirect('/login');
    $this->get('/dashboard')->assertRedirect('/login');
});

it('melarang orang tua membuka halaman admin', function (string $url) {
    $this->actingAs(makeParent())->get($url)->assertForbidden();
})->with(['/admin', '/admin/students', '/admin/parents', '/admin/invoices', '/admin/payments']);

it('mengizinkan admin membuka halaman admin', function (string $url) {
    $this->actingAs(makeAdmin())->get($url)->assertOk();
})->with(['/admin', '/admin/students', '/admin/parents', '/admin/invoices', '/admin/payments']);

it('mengarahkan admin dari dashboard orang tua ke dashboard admin', function () {
    $this->actingAs(makeAdmin())
        ->get('/dashboard')
        ->assertRedirect(route('admin.dashboard'));
});

it('menampilkan dashboard orang tua', function () {
    $parent = makeParent();
    Student::factory()->for($parent, 'parent')->create();

    $this->actingAs($parent)->get('/dashboard')->assertOk();
});