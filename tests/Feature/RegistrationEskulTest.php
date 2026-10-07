<?php

use App\Models\Extracurricular;
use App\Models\Registration;
use App\Models\Student;
use Illuminate\Support\Facades\Mail;

beforeEach(fn () => Mail::fake());

function dataDaftarDuaEskul(array $override = []): array
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

function urutIds(array $ids): array
{
    return collect($ids)->sort()->values()->all();
}

function idEskulPendaftaran(Registration $registration): array
{
    return urutIds($registration->refresh()->extracurriculars->pluck('id')->all());
}

it('menampilkan kotak centang untuk eskul aktif dan menyembunyikan yang nonaktif', function () {
    Extracurricular::factory()->create(['name' => 'Pramuka']);
    Extracurricular::factory()->inactive()->create(['name' => 'Karate Lama']);

    $this->get(route('registration.create'))
        ->assertOk()
        ->assertSee('name="extracurricular_ids[]"', false)
        ->assertSee('Pramuka')
        ->assertSee('Pilih paling banyak 2')
        ->assertDontSee('Karate Lama');
});

it('menyimpan satu eskul pilihan', function () {
    $a = Extracurricular::factory()->create();

    $this->post(route('registration.store'), dataDaftarDuaEskul(['extracurricular_ids' => [$a->id]]))
        ->assertSessionHasNoErrors();

    expect(idEskulPendaftaran(Registration::first()))->toBe([$a->id]);
});

it('menyimpan dua eskul pilihan', function () {
    $a = Extracurricular::factory()->create();
    $b = Extracurricular::factory()->create();

    $this->post(route('registration.store'), dataDaftarDuaEskul(['extracurricular_ids' => [$a->id, $b->id]]))
        ->assertSessionHasNoErrors();

    expect(idEskulPendaftaran(Registration::first()))->toBe(urutIds([$a->id, $b->id]));
});

it('menolak lebih dari dua eskul', function () {
    $eskul = Extracurricular::factory()->count(3)->create();

    $this->post(route('registration.store'), dataDaftarDuaEskul(['extracurricular_ids' => $eskul->pluck('id')->all()]))
        ->assertSessionHasErrors('extracurricular_ids');

    expect(Registration::count())->toBe(0);
});

it('menolak eskul yang sama dipilih dua kali', function () {
    $a = Extracurricular::factory()->create();

    $this->post(route('registration.store'), dataDaftarDuaEskul(['extracurricular_ids' => [$a->id, $a->id]]))
        ->assertSessionHasErrors('extracurricular_ids.0');

    expect(Registration::count())->toBe(0);
});

it('menolak eskul nonaktif atau yang tidak ada', function () {
    $nonaktif = Extracurricular::factory()->inactive()->create();

    $this->post(route('registration.store'), dataDaftarDuaEskul(['extracurricular_ids' => [$nonaktif->id]]))
        ->assertSessionHasErrors('extracurricular_ids.0');

    $this->post(route('registration.store'), dataDaftarDuaEskul(['extracurricular_ids' => [9999]]))
        ->assertSessionHasErrors('extracurricular_ids.0');

    expect(Registration::count())->toBe(0);
});

it('mengizinkan pendaftaran tanpa eskul', function () {
    $this->post(route('registration.store'), dataDaftarDuaEskul())->assertSessionHasNoErrors();

    $registration = Registration::first();
    expect($registration)->not->toBeNull()
        ->and(idEskulPendaftaran($registration))->toBe([]);
});

it('mengabaikan isian kosong di daftar eskul', function () {
    $a = Extracurricular::factory()->create();

    $this->post(route('registration.store'), dataDaftarDuaEskul(['extracurricular_ids' => ['', $a->id]]))
        ->assertSessionHasNoErrors();

    expect(idEskulPendaftaran(Registration::first()))->toBe([$a->id]);
});

it('menolak pilihan eskul yang bukan daftar', function () {
    $a = Extracurricular::factory()->create();

    $this->post(route('registration.store'), dataDaftarDuaEskul(['extracurricular_ids' => $a->id]))
        ->assertSessionHasErrors('extracurricular_ids');

    expect(Registration::count())->toBe(0);
});

it('mencatat semua eskul pilihan ke siswa saat pendaftaran diterima', function () {
    $a = Extracurricular::factory()->create();
    $b = Extracurricular::factory()->create();
    $registration = Registration::factory()->create();
    $registration->extracurriculars()->attach([$a->id, $b->id]);

    $this->actingAs(makeAdmin())
        ->patch(route('admin.registrations.approve', $registration), ['nis' => '280001', 'kelas' => '7A'])
        ->assertSessionHasNoErrors();

    $siswa = Student::where('nis', '280001')->first();
    expect(urutIds($siswa->extracurriculars->pluck('id')->all()))->toBe(urutIds([$a->id, $b->id]));
});

it('hanya mencatat eskul yang masih aktif saat pendaftaran diterima', function () {
    $a = Extracurricular::factory()->create();
    $b = Extracurricular::factory()->create();
    $registration = Registration::factory()->create();
    $registration->extracurriculars()->attach([$a->id, $b->id]);
    $b->update(['is_active' => false]);

    $this->actingAs(makeAdmin())
        ->patch(route('admin.registrations.approve', $registration), ['nis' => '280002', 'kelas' => '7A'])
        ->assertSessionHasNoErrors();

    $siswa = Student::where('nis', '280002')->first();
    expect($siswa->extracurriculars->pluck('id')->all())->toBe([$a->id]);
});

it('menampilkan semua eskul pilihan di detail pendaftaran', function () {
    $a = Extracurricular::factory()->create(['name' => 'Pramuka']);
    $b = Extracurricular::factory()->create(['name' => 'Robotik']);
    $dengan = Registration::factory()->create();
    $dengan->extracurriculars()->attach([$a->id, $b->id]);
    $tanpa = Registration::factory()->create();
    $admin = makeAdmin();

    $this->actingAs($admin)->get(route('admin.registrations.show', $dengan))
        ->assertOk()
        ->assertSee('Ekstrakurikuler pilihan')
        ->assertSee('Pramuka')
        ->assertSee('Robotik');

    $this->actingAs($admin)->get(route('admin.registrations.show', $tanpa))
        ->assertOk()
        ->assertSee('Belum memilih');
});