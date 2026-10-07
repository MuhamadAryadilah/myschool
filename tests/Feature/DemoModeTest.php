<?php

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

it('menampilkan akun coba di halaman login saat DEMO_MODE aktif', function () {
    config(['demo.enabled' => true]);

    $this->get(route('login'))
        ->assertOk()
        ->assertSee('Akun coba')
        ->assertSee('admin1@demo.test')
        ->assertSee('ortu10@demo.test');
});

it('menyembunyikan akun coba saat DEMO_MODE mati', function () {
    config(['demo.enabled' => false]);

    $this->get(route('login'))
        ->assertOk()
        ->assertDontSee('Akun coba')
        ->assertDontSee('demo.test');
});

it('menolak ubah profil, ganti password, dan hapus akun saat DEMO_MODE aktif', function () {
    config(['demo.enabled' => true]);
    $user = makeParent();

    $this->actingAs($user)
        ->patch(route('profile.update'), ['name' => 'Nama Baru', 'email' => 'baru@example.com'])
        ->assertForbidden();

    $this->actingAs($user)
        ->put(route('password.update'), [
            'current_password'      => 'password',
            'password'              => 'PasswordBaru123!',
            'password_confirmation' => 'PasswordBaru123!',
        ])
        ->assertForbidden();

    $this->actingAs($user)
        ->delete(route('profile.destroy'), ['password' => 'password'])
        ->assertForbidden();

    expect(User::find($user->id))->not->toBeNull();
});

it('menampilkan pesan pembatasan di halaman profil saat DEMO_MODE aktif', function () {
    config(['demo.enabled' => true]);

    $this->actingAs(makeParent())
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('dimatikan di versi coba');
});

it('menampilkan form profil biasa saat DEMO_MODE mati', function () {
    config(['demo.enabled' => false]);

    $this->actingAs(makeParent())
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertDontSee('dimatikan di versi coba');
});

it('mengisi data demo yang lengkap dan akunnya bisa dipakai login', function () {
    Storage::fake('local');

    $this->seed(DemoSeeder::class);

    expect(User::where('role', 'admin')->count())->toBe(3)
        ->and(User::where('role', 'orang_tua')->count())->toBe(10)
        ->and(Student::count())->toBe(10)
        ->and(Invoice::count())->toBe(30)
        ->and(Payment::where('status', 'menunggu')->count())->toBe(5)
        ->and(DB::table('news')->count())->toBe(3);

    Storage::disk('local')->assertExists('payments/demo-bukti.svg');

    $this->post('/login', ['email' => 'ortu1@demo.test', 'password' => config('demo.password')]);
    $this->assertAuthenticatedAs(User::where('email', 'ortu1@demo.test')->first());
});

it('menolak demo:reset kalau DEMO_MODE mati', function () {
    config(['demo.enabled' => false]);

    $this->artisan('demo:reset', ['--force' => true])->assertFailed();
});

it('menolak demo:reset kalau nama database tidak mengandung demo', function () {
    config(['demo.enabled' => true]);

    $this->artisan('demo:reset', ['--force' => true])->assertFailed();
});