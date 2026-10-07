<?php

use App\Mail\RegistrationApproved;
use App\Mail\RegistrationRejected;
use App\Models\Registration;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(fn () => Mail::fake());

function setujuiPendaftaran($test, Registration $registration, array $override = [])
{
    return $test->patch(
        route('admin.registrations.approve', $registration),
        array_merge(['nis' => '260101', 'kelas' => '7A'], $override)
    );
}

it('melarang tamu dan orang tua membuka pendaftaran admin', function () {
    $registration = Registration::factory()->create();

    $this->get(route('admin.registrations.index'))->assertRedirect('/login');

    $parent = makeParent();
    $this->actingAs($parent)->get(route('admin.registrations.index'))->assertForbidden();
    $this->actingAs($parent)->get(route('admin.registrations.show', $registration))->assertForbidden();
    $this->actingAs($parent)
        ->patch(route('admin.registrations.approve', $registration), ['nis' => '1', 'kelas' => '7A'])
        ->assertForbidden();
    $this->actingAs($parent)
        ->patch(route('admin.registrations.reject', $registration), ['admin_note' => 'x'])
        ->assertForbidden();

    expect($registration->refresh()->status)->toBe('menunggu');
    expect(Student::count())->toBe(0);
});

it('menampilkan daftar dengan pendaftaran menunggu di atas', function () {
    Registration::factory()->diterima()->create(['student_name' => 'Anak Diterima']);
    Registration::factory()->create(['student_name' => 'Anak Menunggu']);

    $this->actingAs(makeAdmin())
        ->get(route('admin.registrations.index'))
        ->assertOk()
        ->assertSeeInOrder(['Anak Menunggu', 'Anak Diterima']);
});

it('menyaring daftar berdasarkan status', function () {
    Registration::factory()->diterima()->create(['student_name' => 'Anak Diterima']);
    Registration::factory()->create(['student_name' => 'Anak Menunggu']);

    $this->actingAs(makeAdmin())
        ->get(route('admin.registrations.index', ['status' => 'diterima']))
        ->assertOk()
        ->assertSee('Anak Diterima')
        ->assertDontSee('Anak Menunggu');
});

it('mencari pendaftaran berdasarkan nama siswa, orang tua, dan email', function () {
    Registration::factory()->create(['student_name' => 'Cari Aku', 'guardian_name' => 'Ortu A', 'email' => 'a@example.com']);
    Registration::factory()->create(['student_name' => 'Anak Lain', 'guardian_name' => 'Ortu Bimo', 'email' => 'b@example.com']);

    $admin = makeAdmin();

    $this->actingAs($admin)->get(route('admin.registrations.index', ['q' => 'Cari Aku']))
        ->assertSee('Cari Aku')->assertDontSee('Anak Lain');

    $this->actingAs($admin)->get(route('admin.registrations.index', ['q' => 'Bimo']))
        ->assertSee('Anak Lain')->assertDontSee('Cari Aku');

    $this->actingAs($admin)->get(route('admin.registrations.index', ['q' => 'b@example.com']))
        ->assertSee('Anak Lain')->assertDontSee('Cari Aku');
});

it('tidak error kalau parameter pencarian berbentuk array', function () {
    $this->actingAs(makeAdmin())
        ->get('/admin/registrations?q[]=a&status[]=b')
        ->assertOk();
});

it('menampilkan detail pendaftaran', function () {
    $registration = Registration::factory()->create([
        'student_name' => 'Budi Santoso',
        'phone' => '081234567890',
        'email' => 'slamet@example.com',
    ]);

    $this->actingAs(makeAdmin())
        ->get(route('admin.registrations.show', $registration))
        ->assertOk()
        ->assertSee('Budi Santoso')
        ->assertSee('081234567890')
        ->assertSee('slamet@example.com')
        ->assertSee($registration->reference())
        ->assertSee('Terima pendaftaran');
});

it('membuat siswa dan akun orang tua baru saat diterima', function () {
    $registration = Registration::factory()->create([
        'student_name' => 'Budi Santoso',
        'guardian_name' => 'Slamet Santoso',
        'email' => 'slamet@example.com',
    ]);

    setujuiPendaftaran($this->actingAs(makeAdmin()), $registration)
        ->assertRedirect(route('admin.registrations.show', $registration))
        ->assertSessionHasNoErrors();

    $user = User::where('email', 'slamet@example.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->role)->toBe('orang_tua')
        ->and($user->name)->toBe('Slamet Santoso')
        ->and($user->email_verified_at)->not->toBeNull();

    $student = Student::where('nis', '260101')->first();
    expect($student->name)->toBe('Budi Santoso')
        ->and($student->kelas)->toBe('7A')
        ->and($student->user_id)->toBe($user->id);

    $registration->refresh();
    expect($registration->status)->toBe('diterima')
        ->and($registration->student_id)->toBe($student->id)
        ->and($registration->user_id)->toBe($user->id)
        ->and($registration->processed_at)->not->toBeNull();

    Mail::assertSent(RegistrationApproved::class, 1);
    Mail::assertSent(RegistrationApproved::class, fn ($mail) => $mail->hasTo('slamet@example.com')
        && $mail->newAccount === true
        && str_contains($mail->resetUrl, 'reset-password'));
});

it('membuat link buat password yang bisa dibuka orang tua', function () {
    $registration = Registration::factory()->create();

    setujuiPendaftaran($this->actingAs(makeAdmin()), $registration);

    $url = null;
    Mail::assertSent(RegistrationApproved::class, function ($mail) use (&$url) {
        $url = $mail->resetUrl;

        return true;
    });

    $this->app['auth']->forgetGuards();

    $this->get($url)->assertOk();
});

it('menghubungkan siswa ke akun orang tua yang sudah ada', function () {
    $admin = makeAdmin();
    $ortu = makeParent();
    $registration = Registration::factory()->create(['email' => $ortu->email]);
    $usersBefore = User::count();

    setujuiPendaftaran($this->actingAs($admin), $registration, ['nis' => '260103'])
        ->assertSessionHasNoErrors();

    expect(User::count())->toBe($usersBefore);
    expect(Student::where('nis', '260103')->first()->user_id)->toBe($ortu->id);

    Mail::assertSent(RegistrationApproved::class, fn ($mail) => $mail->newAccount === false && $mail->resetUrl === null);
});

it('menolak pendaftaran yang emailnya milik akun admin', function () {
    $admin = makeAdmin();
    $registration = Registration::factory()->create(['email' => $admin->email]);

    setujuiPendaftaran($this->actingAs($admin), $registration)->assertSessionHasErrors('email');

    expect(Student::count())->toBe(0);
    expect($registration->refresh()->status)->toBe('menunggu');
    Mail::assertNotSent(RegistrationApproved::class);
});

it('mewajibkan NIS dan kelas yang valid saat menerima', function (array $override, string $field) {
    Student::factory()->create(['nis' => '260105']);
    $registration = Registration::factory()->create();

    setujuiPendaftaran($this->actingAs(makeAdmin()), $registration, $override)->assertSessionHasErrors($field);

    expect($registration->refresh()->status)->toBe('menunggu');
    expect(User::where('email', $registration->email)->exists())->toBeFalse();
})->with([
    'NIS kosong' => [['nis' => ''], 'nis'],
    'kelas kosong' => [['kelas' => ''], 'kelas'],
    'NIS sudah dipakai' => [['nis' => '260105'], 'nis'],
]);

it('tidak bisa menerima pendaftaran yang sudah diproses', function () {
    $registration = Registration::factory()->create();
    $admin = makeAdmin();

    setujuiPendaftaran($this->actingAs($admin), $registration)->assertSessionHasNoErrors();
    setujuiPendaftaran($this->actingAs($admin), $registration, ['nis' => '260999'])->assertSessionHasErrors('registration');

    expect(Student::count())->toBe(1);
    Mail::assertSent(RegistrationApproved::class, 1);
});

it('menolak pendaftaran dengan alasan dan mengirim email', function () {
    $registration = Registration::factory()->create(['email' => 'slamet@example.com']);

    $this->actingAs(makeAdmin())
        ->patch(route('admin.registrations.reject', $registration), ['admin_note' => 'Kuota sudah penuh'])
        ->assertSessionHasNoErrors();

    $registration->refresh();
    expect($registration->status)->toBe('ditolak')
        ->and($registration->admin_note)->toBe('Kuota sudah penuh')
        ->and($registration->processed_at)->not->toBeNull();

    expect(User::where('email', 'slamet@example.com')->exists())->toBeFalse();
    expect(Student::count())->toBe(0);

    Mail::assertSent(RegistrationRejected::class, fn ($mail) => $mail->hasTo('slamet@example.com'));
});

it('mewajibkan alasan saat menolak', function () {
    $registration = Registration::factory()->create();

    $this->actingAs(makeAdmin())
        ->patch(route('admin.registrations.reject', $registration), ['admin_note' => ''])
        ->assertSessionHasErrors('admin_note');

    expect($registration->refresh()->status)->toBe('menunggu');
    Mail::assertNotSent(RegistrationRejected::class);
});

it('tidak bisa menolak pendaftaran yang sudah diproses', function () {
    $registration = Registration::factory()->diterima()->create();

    $this->actingAs(makeAdmin())
        ->patch(route('admin.registrations.reject', $registration), ['admin_note' => 'Terlambat'])
        ->assertSessionHasErrors('registration');

    expect($registration->refresh()->status)->toBe('diterima');
});

it('menyembunyikan form keputusan untuk pendaftaran yang sudah diproses', function () {
    $registration = Registration::factory()->ditolak('Kuota penuh')->create();

    $this->actingAs(makeAdmin())
        ->get(route('admin.registrations.show', $registration))
        ->assertOk()
        ->assertSee('Kuota penuh')
        ->assertDontSee('Terima pendaftaran');
});

it('menampilkan jumlah pendaftaran menunggu di dashboard admin', function () {
    Registration::factory()->count(3)->create();
    Registration::factory()->diterima()->create();

    $this->actingAs(makeAdmin())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Pendaftaran')
        ->assertViewHas('stats', fn ($stats) => $stats['registrations'] === 3);
});

it('menampilkan tombol kembali di halaman pendaftaran admin', function () {
    $registration = Registration::factory()->create();
    $admin = makeAdmin();

    $this->actingAs($admin)->get(route('admin.registrations.index'))
        ->assertOk()->assertSee('Kembali ke dashboard');

    $this->actingAs($admin)->get(route('admin.registrations.show', $registration))
        ->assertOk()->assertSee('Kembali ke daftar');
});

it('menampilkan isi email pendaftaran diterima untuk akun baru', function () {
    $student = Student::factory()->create(['name' => 'Budi Santoso', 'nis' => '260106', 'kelas' => '7A']);
    $registration = Registration::factory()->create(['guardian_name' => 'Slamet Santoso', 'email' => 'slamet@example.com']);

    $mail = new RegistrationApproved($registration, $student, true, 'https://myschool.test/reset-password/abc123');

    $mail->assertSeeInHtml('Budi Santoso');
    $mail->assertSeeInHtml('260106');
    $mail->assertSeeInHtml('slamet@example.com');
    $mail->assertSeeInHtml('reset-password/abc123');
});

it('menampilkan isi email pendaftaran diterima untuk akun yang sudah ada', function () {
    $student = Student::factory()->create(['name' => 'Budi Santoso']);
    $registration = Registration::factory()->create();

    $mail = new RegistrationApproved($registration, $student, false);

    $mail->assertSeeInHtml('ditambahkan ke akun Anda');
    $mail->assertDontSeeInHtml('reset-password');
});

it('menampilkan alasan di email penolakan', function () {
    $registration = Registration::factory()->ditolak('Kuota penuh')->create(['student_name' => 'Budi Santoso']);

    $mail = new RegistrationRejected($registration);

    $mail->assertSeeInHtml('Budi Santoso');
    $mail->assertSeeInHtml('Kuota penuh');
});