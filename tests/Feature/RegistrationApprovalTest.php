<?php

use App\Mail\RegistrationApproved;
use App\Models\Registration;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

function buatPendaftaran(string $email, array $extra = []): Registration
{
    return Registration::create(array_merge([
        'status' => 'menunggu',
        'student_name' => 'Budi Santoso',
        'gender' => 'L',
        'birth_place' => 'Jakarta',
        'birth_date' => '2015-01-01',
        'nisn' => '1234567890',
        'religion' => 'Islam',
        'address' => 'Jl. Contoh No. 1',
        'previous_school' => 'TK Contoh',
        'father_name' => 'Ayah Budi',
        'mother_name' => 'Ibu Budi',
        'guardian_name' => 'Ayah Budi',
        'guardian_relation' => 'Ayah',
        'guardian_job' => 'Wiraswasta',
        'phone' => '081234567890',
        'email' => $email,
    ], $extra));
}

it('membuat akun baru dan mengirim email link password saat diterima', function () {
    Mail::fake();
    $reg = buatPendaftaran('ortu.baru@contoh.com');

    $this->actingAs(makeAdmin())
        ->patch(route('admin.registrations.approve', $reg), ['nis' => '1001', 'kelas' => '1A'])
        ->assertRedirect()->assertSessionHasNoErrors();

    $user = User::where('email', 'ortu.baru@contoh.com')->first();
    expect($user)->not->toBeNull();
    expect($user->role)->toBe('orang_tua');
    expect($reg->fresh()->status)->toBe('diterima');
    expect(Student::where('nis', '1001')->where('user_id', $user->id)->exists())->toBeTrue();

    Mail::assertSent(RegistrationApproved::class, function ($mail) {
        return $mail->hasTo('ortu.baru@contoh.com')
            && $mail->newAccount === true
            && str_contains((string) $mail->resetUrl, 'reset-password');
    });
});

it('menambah siswa ke akun lama tanpa link password', function () {
    Mail::fake();
    $ortu = User::factory()->create(['role' => 'orang_tua', 'email' => 'ortu.lama@contoh.com']);
    $reg = buatPendaftaran('ortu.lama@contoh.com');

    $this->actingAs(makeAdmin())
        ->patch(route('admin.registrations.approve', $reg), ['nis' => '1002', 'kelas' => '2B'])
        ->assertRedirect()->assertSessionHasNoErrors();

    expect(User::where('email', 'ortu.lama@contoh.com')->count())->toBe(1);
    expect(Student::where('nis', '1002')->first()->user_id)->toBe($ortu->id);

    Mail::assertSent(RegistrationApproved::class, function ($mail) {
        return $mail->newAccount === false && $mail->resetUrl === null;
    });
});

it('menolak email milik admin dan tidak mengirim email', function () {
    Mail::fake();
    $admin = makeAdmin();
    $reg = buatPendaftaran($admin->email);

    $this->actingAs($admin)
        ->patch(route('admin.registrations.approve', $reg), ['nis' => '1003', 'kelas' => '1A']);

    expect($reg->fresh()->status)->toBe('menunggu');
    Mail::assertNothingSent();
});

it('tidak memproses pendaftaran yang sama dua kali', function () {
    Mail::fake();
    $reg = buatPendaftaran('dobel@contoh.com');
    $admin = makeAdmin();

    $this->actingAs($admin)
        ->patch(route('admin.registrations.approve', $reg), ['nis' => '1004', 'kelas' => '1A']);
    $this->actingAs($admin)
        ->patch(route('admin.registrations.approve', $reg), ['nis' => '1005', 'kelas' => '1A']);

    expect(Student::count())->toBe(1);
    Mail::assertSent(RegistrationApproved::class, 1);
});