<?php

use App\Mail\RegistrationSubmitted;
use App\Models\Registration;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(fn () => Mail::fake());

function payloadPendaftaran(array $override = []): array
{
    return array_merge([
        'student_name' => 'Budi Santoso',
        'gender' => 'L',
        'birth_place' => 'Jakarta',
        'birth_date' => '2014-05-10',
        'nisn' => '1234567890',
        'religion' => 'Islam',
        'address' => 'Jl. Melati No. 5, Jakarta',
        'previous_school' => 'SD Negeri 1 Jakarta',
        'father_name' => 'Slamet Santoso',
        'mother_name' => 'Siti Rahmawati',
        'guardian_name' => 'Slamet Santoso',
        'guardian_relation' => 'ayah',
        'guardian_job' => 'Wiraswasta',
        'phone' => '081234567890',
        'email' => 'slamet@example.com',
    ], $override);
}

it('menampilkan formulir pendaftaran untuk tamu', function () {
    $this->get(route('registration.create'))
        ->assertOk()
        ->assertSee('Pendaftaran Siswa Baru')
        ->assertSee('Nama lengkap')
        ->assertSee('Email');
});

it('membolehkan orang tua yang sudah login membuka formulir', function () {
    $this->actingAs(makeParent())->get(route('registration.create'))->assertOk();
});

it('menampilkan tombol daftar di halaman depan', function () {
    $this->get(route('home'))->assertOk()->assertSee('Daftar Siswa Baru');
});

it('menyimpan pendaftaran dengan status menunggu tanpa membuat akun', function () {
    $this->post(route('registration.store'), payloadPendaftaran())
        ->assertRedirect(route('registration.thanks'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('registrations', [
        'student_name' => 'Budi Santoso',
        'email' => 'slamet@example.com',
        'status' => 'menunggu',
    ]);

    $this->assertDatabaseMissing('users', ['email' => 'slamet@example.com']);
    expect(Student::count())->toBe(0);
});

it('menampilkan nomor pendaftaran di halaman terima kasih', function () {
    $this->followingRedirects()
        ->post(route('registration.store'), payloadPendaftaran())
        ->assertOk()
        ->assertSee('PPDB-');
});

it('mengarahkan ke formulir kalau halaman terima kasih dibuka langsung', function () {
    $this->get(route('registration.thanks'))->assertRedirect(route('registration.create'));
});

it('mewajibkan isian utama', function () {
    $this->post(route('registration.store'), [])
        ->assertSessionHasErrors([
            'student_name', 'gender', 'birth_place', 'birth_date', 'religion', 'previous_school',
            'address', 'father_name', 'mother_name', 'guardian_name', 'guardian_relation', 'phone', 'email',
        ]);

    expect(Registration::count())->toBe(0);
});

it('menolak isian yang tidak valid', function (array $override, string $field) {
    $this->post(route('registration.store'), payloadPendaftaran($override))
        ->assertSessionHasErrors($field);

    expect(Registration::count())->toBe(0);
})->with([
    'email salah' => [['email' => 'bukan-email'], 'email'],
    'jenis kelamin salah' => [['gender' => 'X'], 'gender'],
    'agama salah' => [['religion' => 'Lainnya'], 'religion'],
    'tanggal lahir di masa depan' => [['birth_date' => '2999-01-01'], 'birth_date'],
    'NISN bukan 10 digit' => [['nisn' => '123'], 'nisn'],
    'nomor HP salah' => [['phone' => 'abc'], 'phone'],
    'hubungan salah' => [['guardian_relation' => 'paman'], 'guardian_relation'],
]);

it('mengizinkan NISN dan pekerjaan dikosongkan', function () {
    $this->post(route('registration.store'), payloadPendaftaran(['nisn' => '', 'guardian_job' => '']))
        ->assertSessionHasNoErrors();

    expect(Registration::count())->toBe(1);
});

it('menolak pendaftaran ganda yang masih menunggu', function () {
    $this->post(route('registration.store'), payloadPendaftaran())->assertSessionHasNoErrors();
    $this->post(route('registration.store'), payloadPendaftaran())->assertSessionHasErrors('email');

    expect(Registration::count())->toBe(1);
});

it('mengizinkan dua anak dengan email yang sama', function () {
    $this->post(route('registration.store'), payloadPendaftaran())->assertSessionHasNoErrors();
    $this->post(route('registration.store'), payloadPendaftaran([
        'student_name' => 'Ani Santoso',
        'birth_date' => '2015-03-01',
    ]))->assertSessionHasNoErrors();

    expect(Registration::count())->toBe(2);
});

it('menyimpan email dalam huruf kecil tanpa spasi', function () {
    $this->post(route('registration.store'), payloadPendaftaran(['email' => '  SLAMET@Example.COM ']))
        ->assertSessionHasNoErrors();

    expect(Registration::first()->email)->toBe('slamet@example.com');
});

it('tidak menerima status dari input pendaftar', function () {
    $this->post(route('registration.store'), payloadPendaftaran(['status' => 'diterima', 'admin_note' => 'x']))
        ->assertSessionHasNoErrors();

    $registration = Registration::first();
    expect($registration->status)->toBe('menunggu')
        ->and($registration->admin_note)->toBeNull();
});

it('mengabaikan pengiriman yang mengisi kolom jebakan', function () {
    $this->post(route('registration.store'), payloadPendaftaran(['website' => 'http://spam.example']))
        ->assertRedirect(route('registration.thanks'));

    expect(Registration::count())->toBe(0);
    Mail::assertNothingSent();
});

it('mengirim email ke semua admin saat ada pendaftaran baru', function () {
    $admin1 = makeAdmin();
    $admin2 = makeAdmin();

    $this->post(route('registration.store'), payloadPendaftaran())->assertSessionHasNoErrors();

    Mail::assertSent(RegistrationSubmitted::class, 2);
    Mail::assertSent(RegistrationSubmitted::class, fn ($mail) => $mail->hasTo($admin1->email));
    Mail::assertSent(RegistrationSubmitted::class, fn ($mail) => $mail->hasTo($admin2->email));
});

it('tidak mengirim email ke alamat yang diketik pendaftar', function () {
    $this->post(route('registration.store'), payloadPendaftaran())->assertSessionHasNoErrors();

    Mail::assertNotSent(RegistrationSubmitted::class, fn ($mail) => $mail->hasTo('slamet@example.com'));
});

it('membatasi jumlah pengiriman dari satu alamat', function () {
    for ($i = 0; $i < 20; $i++) {
        $this->post(route('registration.store'), [])->assertStatus(302);
    }

    $this->post(route('registration.store'), [])->assertStatus(429);
});

it('menampilkan data pendaftaran di email untuk admin', function () {
    $registration = Registration::factory()->create([
        'student_name' => 'Budi Santoso',
        'guardian_name' => 'Slamet Santoso',
        'phone' => '081234567890',
    ]);

    $mail = new RegistrationSubmitted($registration);

    $mail->assertSeeInHtml('Budi Santoso');
    $mail->assertSeeInHtml('Slamet Santoso');
    $mail->assertSeeInHtml('081234567890');
    $mail->assertSeeInHtml($registration->reference());
});