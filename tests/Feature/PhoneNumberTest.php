<?php

use App\Models\Registration;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

beforeEach(fn () => Mail::fake());

function dataOrtuBaru(array $override = []): array
{
    return array_merge([
        'name' => 'Ibu Telepon',
        'email' => 'ibu.telepon@example.com',
        'password' => 'rahasia123',
    ], $override);
}

it('menambah kolom phone di tabel users', function () {
    expect(Schema::hasColumn('users', 'phone'))->toBeTrue();
});

it('menyalin nomor HP dari pendaftaran ke akun orang tua baru', function () {
    $registration = Registration::factory()->create(['phone' => '081234567890', 'email' => 'baru@example.com']);

    $this->actingAs(makeAdmin())
        ->patch(route('admin.registrations.approve', $registration), ['nis' => '280001', 'kelas' => '7A'])
        ->assertSessionHasNoErrors();

    expect(User::where('email', 'baru@example.com')->first()->phone)->toBe('081234567890');
});

it('mengisi nomor HP akun lama yang masih kosong saat pendaftaran diterima', function () {
    $ortu = makeParent();
    $registration = Registration::factory()->create(['email' => $ortu->email, 'phone' => '081111111111']);

    $this->actingAs(makeAdmin())
        ->patch(route('admin.registrations.approve', $registration), ['nis' => '280002', 'kelas' => '7A'])
        ->assertSessionHasNoErrors();

    expect($ortu->fresh()->phone)->toBe('081111111111');
});

it('tidak menimpa nomor HP akun lama yang sudah terisi', function () {
    $ortu = User::factory()->create(['role' => 'orang_tua', 'phone' => '082222222222']);
    $registration = Registration::factory()->create(['email' => $ortu->email, 'phone' => '081111111111']);

    $this->actingAs(makeAdmin())
        ->patch(route('admin.registrations.approve', $registration), ['nis' => '280003', 'kelas' => '7A'])
        ->assertSessionHasNoErrors();

    expect($ortu->fresh()->phone)->toBe('082222222222');
});

it('menyimpan nomor HP saat admin menambah orang tua', function () {
    $this->actingAs(makeAdmin())
        ->post(route('admin.parents.store'), dataOrtuBaru(['phone' => '081234567890']))
        ->assertRedirect(route('admin.parents.index'))
        ->assertSessionHasNoErrors();

    expect(User::where('email', 'ibu.telepon@example.com')->first()->phone)->toBe('081234567890');
});

it('mengizinkan nomor HP dikosongkan saat admin menambah orang tua', function () {
    $this->actingAs(makeAdmin())
        ->post(route('admin.parents.store'), dataOrtuBaru(['phone' => '']))
        ->assertSessionHasNoErrors();

    expect(User::where('email', 'ibu.telepon@example.com')->first()->phone)->toBeNull();
});

it('menolak nomor HP yang tidak valid di form admin', function (string $nomor) {
    $this->actingAs(makeAdmin())
        ->post(route('admin.parents.store'), dataOrtuBaru(['phone' => $nomor]))
        ->assertSessionHasErrors('phone');

    expect(User::where('email', 'ibu.telepon@example.com')->exists())->toBeFalse();
})->with([
    'huruf' => ['abc'],
    'terlalu pendek' => ['123'],
    'terlalu panjang' => ['0812345678901234567890'],
    'ada tag' => ['<b>0812345678</b>'],
]);

it('mengubah nomor HP orang tua oleh admin', function () {
    $ortu = makeParent();

    $this->actingAs(makeAdmin())
        ->patch(route('admin.parents.update', $ortu), [
            'name' => $ortu->name,
            'email' => $ortu->email,
            'phone' => '085599998888',
        ])
        ->assertSessionHasNoErrors();

    expect($ortu->fresh()->phone)->toBe('085599998888');
});

it('melarang orang tua mengubah nomor HP orang tua lain lewat menu admin', function () {
    $korban = User::factory()->create(['role' => 'orang_tua', 'phone' => '081200000000']);

    $this->actingAs(makeParent())
        ->patch(route('admin.parents.update', $korban), [
            'name' => $korban->name,
            'email' => $korban->email,
            'phone' => '089999999999',
        ])
        ->assertForbidden();

    expect($korban->fresh()->phone)->toBe('081200000000');
});

it('menampilkan nomor HP dan tombol WhatsApp di tabel orang tua', function () {
    User::factory()->create(['role' => 'orang_tua', 'name' => 'Ibu Telepon', 'phone' => '081234567890']);

    $this->actingAs(makeAdmin())
        ->get(route('admin.parents.index'))
        ->assertOk()
        ->assertSee('081234567890')
        ->assertSee('WhatsApp')
        ->assertSee('https://wa.me/6281234567890', false);
});

it('mencari orang tua berdasarkan nomor HP', function () {
    User::factory()->create(['role' => 'orang_tua', 'name' => 'Ibu Ketemu', 'phone' => '081299990000']);
    User::factory()->create(['role' => 'orang_tua', 'name' => 'Ibu Lain', 'phone' => '081277770000']);

    $this->actingAs(makeAdmin())
        ->get(route('admin.parents.index', ['q' => '99990000']))
        ->assertSee('Ibu Ketemu')
        ->assertDontSee('Ibu Lain');
});

it('membuat link WhatsApp dengan awalan 62', function (string $input) {
    expect((new User(['phone' => $input]))->whatsappUrl())->toBe('https://wa.me/6281234567890');
})->with([
    'awalan 0' => ['081234567890'],
    'awalan +62 dengan spasi dan strip' => ['+62 812-3456-7890'],
    'sudah 62' => ['6281234567890'],
    'tanpa 0' => ['81234567890'],
    'dengan kurung' => ['(0812) 3456-7890'],
]);

it('tidak membuat link WhatsApp kalau nomor kosong atau tidak masuk akal', function (?string $input) {
    expect((new User(['phone' => $input]))->whatsappUrl())->toBeNull();
})->with([
    'null' => [null],
    'kosong' => [''],
    'huruf' => ['abc'],
    'terlalu pendek' => ['123'],
]);

it('menampilkan kolom nomor HP di halaman profil', function () {
    $this->actingAs(makeParent())
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('Nomor HP / WhatsApp');
});

it('membolehkan orang tua memperbarui nomor HP sendiri di profil', function () {
    $ortu = makeParent();

    $this->actingAs($ortu)
        ->patch(route('profile.update'), [
            'name' => $ortu->name,
            'email' => $ortu->email,
            'phone' => '081234500000',
        ])
        ->assertSessionHasNoErrors();

    expect($ortu->fresh()->phone)->toBe('081234500000');
});

it('menolak nomor HP yang tidak valid di profil', function () {
    $ortu = makeParent();

    $this->actingAs($ortu)
        ->patch(route('profile.update'), [
            'name' => $ortu->name,
            'email' => $ortu->email,
            'phone' => 'abc',
        ])
        ->assertSessionHasErrors('phone');

    expect($ortu->fresh()->phone)->toBeNull();
});

it('mengisi nomor HP contoh di data demo', function () {
    Storage::fake('local');

    $this->seed(DemoSeeder::class);

    expect(User::where('role', 'orang_tua')->whereNull('phone')->count())->toBe(0);
});