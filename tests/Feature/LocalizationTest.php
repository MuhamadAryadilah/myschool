<?php

use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

beforeEach(fn () => app()->setLocale('id'));

it('menampilkan pesan validasi minimal karakter dalam bahasa Indonesia', function () {
    $v = Validator::make(['password' => 'abc'], ['password' => 'min:8']);

    expect($v->errors()->first('password'))->toBe('Password minimal 8 karakter.');
});

it('menampilkan pesan wajib diisi dengan nama kolom yang diterjemahkan', function () {
    $v = Validator::make(['nis' => ''], ['nis' => 'required']);

    expect($v->errors()->first('nis'))->toBe('NIS wajib diisi.');
});

it('menampilkan pesan konfirmasi password tidak cocok dalam bahasa Indonesia', function () {
    $v = Validator::make(
        ['password' => 'rahasia123', 'password_confirmation' => 'beda'],
        ['password' => 'confirmed']
    );

    expect($v->errors()->first('password'))->toBe('Konfirmasi password tidak cocok.');
});

it('menampilkan pesan login gagal dalam bahasa Indonesia', function () {
    $user = User::factory()->create();

    $this->post('/login', ['email' => $user->email, 'password' => 'salah-total'])
        ->assertSessionHasErrors(['email' => 'Email atau password salah.']);
});

it('menampilkan teks navigasi halaman dalam bahasa Indonesia', function () {
    Student::factory()->count(16)->create();

    $this->actingAs(makeAdmin())
        ->get('/admin/students')
        ->assertOk()
        ->assertSee('Menampilkan')
        ->assertDontSee('Showing');
});