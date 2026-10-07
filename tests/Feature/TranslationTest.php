<?php

use App\Models\User;

it('menampilkan halaman lupa password dalam bahasa Indonesia', function () {
    $this->get('/forgot-password')
        ->assertOk()
        ->assertSee('Lupa password')
        ->assertSee('Kirim Tautan Baru')
        ->assertDontSee('Email Password Reset Link');
});

it('menampilkan halaman atur ulang password dalam bahasa Indonesia', function () {
    $this->get('/reset-password/token-contoh')
        ->assertOk()
        ->assertSee('Buat password')
        ->assertSee('Password Baru');
});

it('menampilkan halaman verifikasi email dalam bahasa Indonesia', function () {
    $user = User::factory()->unverified()->create(['role' => 'orang_tua']);

    $this->actingAs($user)
        ->get('/verify-email')
        ->assertOk()
        ->assertSee('Kirim Ulang Email Verifikasi')
        ->assertDontSee('Resend Verification Email');
});

it('menampilkan halaman konfirmasi password dalam bahasa Indonesia', function () {
    $this->actingAs(makeParent())
        ->get('/confirm-password')
        ->assertOk()
        ->assertSee('Konfirmasi password')
        ->assertDontSee('secure area');
});

it('menampilkan halaman profil dalam bahasa Indonesia untuk ortu dan admin', function (string $role) {
    $user = User::factory()->create(['role' => $role]);

    $this->actingAs($user)
        ->get('/profile')
        ->assertOk()
        ->assertSee('Informasi Profil')
        ->assertSee('Ubah Password')
        ->assertSee('Hapus Akun')
        ->assertDontSee('Profile Information')
        ->assertDontSee('Delete Account');
})->with(['orang_tua', 'admin']);
