<?php

use App\Models\AppSetting;
use App\Models\Registration;
use App\Models\RegistrationStep;
use App\Support\RegistrationAge;
use Database\Seeders\DemoSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

beforeEach(fn () => Mail::fake());

function payloadDaftarAturan(array $override = []): array
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

function batasUsia(?int $min, ?int $max, ?string $tanggal = null): void
{
    AppSetting::put('reg_min_age', $min);
    AppSetting::put('reg_max_age', $max);
    AppSetting::put('reg_age_date', $tanggal);
}

function buatLangkah(string $judul, int $urutan = 1, bool $aktif = true): RegistrationStep
{
    return RegistrationStep::create(['title' => $judul, 'sort_order' => $urutan, 'is_active' => $aktif]);
}

// ---------- Batas usia ----------

it('menghitung usia dengan benar di tanggal acuan', function () {
    $acuan = Carbon::parse('2026-07-01');

    expect(RegistrationAge::ageOn(Carbon::parse('2014-07-01'), $acuan))->toBe(12)
        ->and(RegistrationAge::ageOn(Carbon::parse('2014-07-02'), $acuan))->toBe(11)
        ->and(RegistrationAge::ageOn(Carbon::parse('2014-06-30'), $acuan))->toBe(12);
});

it('tidak membatasi usia kalau batas belum diatur', function () {
    $this->post(route('registration.store'), payloadDaftarAturan(['birth_date' => '2001-02-01']))
        ->assertSessionHasNoErrors();

    expect(Registration::count())->toBe(1);
});

it('menerima pendaftar yang usianya di dalam rentang', function () {
    batasUsia(11, 14, '2027-07-01');

    $this->post(route('registration.store'), payloadDaftarAturan(['birth_date' => '2014-05-10']))
        ->assertSessionHasNoErrors();

    expect(Registration::count())->toBe(1);
});

it('menolak pendaftar yang terlalu muda', function () {
    batasUsia(11, 14, '2027-07-01');

    $this->post(route('registration.store'), payloadDaftarAturan(['birth_date' => '2019-01-01']))
        ->assertSessionHasErrors('birth_date');

    expect(Registration::count())->toBe(0);
});

it('menolak pendaftar yang terlalu tua', function () {
    batasUsia(11, 14, '2027-07-01');

    $this->post(route('registration.store'), payloadDaftarAturan(['birth_date' => '2008-01-01']))
        ->assertSessionHasErrors('birth_date');

    expect(Registration::count())->toBe(0);
});

it('menerima usia tepat di batas dan menolak sehari kurang', function () {
    batasUsia(12, 12, '2026-07-01');

    $this->post(route('registration.store'), payloadDaftarAturan(['birth_date' => '2014-07-01']))
        ->assertSessionHasNoErrors();

    $this->post(route('registration.store'), payloadDaftarAturan([
        'birth_date' => '2014-07-02',
        'student_name' => 'Anak Lain',
    ]))->assertSessionHasErrors('birth_date');

    expect(Registration::count())->toBe(1);
});

it('hanya memakai batas minimal kalau maksimal kosong', function () {
    batasUsia(11, null, '2026-07-01');

    $this->post(route('registration.store'), payloadDaftarAturan(['birth_date' => '2001-06-01']))
        ->assertSessionHasNoErrors();
});

it('menampilkan syarat usia di formulir kalau batas diatur', function () {
    batasUsia(11, 14, '2027-07-01');

    $this->get(route('registration.create'))
        ->assertOk()
        ->assertSee('11 sampai 14 tahun');
});

it('tidak menampilkan syarat usia kalau batas kosong', function () {
    $this->get(route('registration.create'))
        ->assertOk()
        ->assertDontSee('Usia calon siswa yang dapat mendaftar');
});

it('melarang tamu dan orang tua membuka pengaturan pendaftaran', function () {
    $this->get(route('admin.registration-settings.edit'))->assertRedirect('/login');

    $parent = makeParent();
    $this->actingAs($parent)->get(route('admin.registration-settings.edit'))->assertForbidden();
    $this->actingAs($parent)
        ->put(route('admin.registration-settings.update'), ['min_age' => 5])
        ->assertForbidden();

    expect(RegistrationAge::limits()['min'])->toBeNull();
});

it('menampilkan halaman pengaturan pendaftaran untuk admin', function () {
    $this->actingAs(makeAdmin())
        ->get(route('admin.registration-settings.edit'))
        ->assertOk()
        ->assertSee('Batas usia pendaftar')
        ->assertSee('Alur setelah mendaftar');
});

it('menyimpan batas usia dari admin', function () {
    $this->actingAs(makeAdmin())
        ->put(route('admin.registration-settings.update'), [
            'min_age' => 11,
            'max_age' => 14,
            'age_reference_date' => '2027-07-01',
        ])
        ->assertRedirect(route('admin.registration-settings.edit'))
        ->assertSessionHasNoErrors();

    expect(RegistrationAge::limits())->toBe(['min' => 11, 'max' => 14, 'date' => '2027-07-01']);
});

it('menolak usia maksimal yang lebih kecil dari minimal', function () {
    $this->actingAs(makeAdmin())
        ->put(route('admin.registration-settings.update'), ['min_age' => 14, 'max_age' => 11])
        ->assertSessionHasErrors('max_age');

    expect(RegistrationAge::limits()['min'])->toBeNull();
});

it('menolak batas usia yang bukan angka', function () {
    $this->actingAs(makeAdmin())
        ->put(route('admin.registration-settings.update'), ['min_age' => 'abc'])
        ->assertSessionHasErrors('min_age');
});

it('mengosongkan batas usia', function () {
    batasUsia(11, 14, '2027-07-01');

    $this->actingAs(makeAdmin())
        ->put(route('admin.registration-settings.update'), ['min_age' => '', 'max_age' => '', 'age_reference_date' => ''])
        ->assertSessionHasNoErrors();

    expect(RegistrationAge::limits())->toBe(['min' => null, 'max' => null, 'date' => null]);
});

// ---------- Alur setelah mendaftar ----------

it('melarang tamu dan orang tua mengelola alur pendaftaran', function () {
    $this->get(route('admin.registration-steps.index'))->assertRedirect('/login');

    $parent = makeParent();
    $this->actingAs($parent)->get(route('admin.registration-steps.index'))->assertForbidden();
    $this->actingAs($parent)
        ->post(route('admin.registration-steps.store'), ['title' => 'Penyusup'])
        ->assertForbidden();

    expect(RegistrationStep::count())->toBe(0);
});

it('menambah langkah baru', function () {
    $this->actingAs(makeAdmin())
        ->post(route('admin.registration-steps.store'), [
            'title' => 'Tes Seleksi',
            'description' => 'Datang pukul 08.00',
            'sort_order' => 2,
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.registration-steps.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('registration_steps', ['title' => 'Tes Seleksi', 'sort_order' => 2, 'is_active' => 1]);
});

it('mewajibkan judul langkah', function () {
    $this->actingAs(makeAdmin())
        ->post(route('admin.registration-steps.store'), ['title' => ''])
        ->assertSessionHasErrors('title');

    expect(RegistrationStep::count())->toBe(0);
});

it('mengisi urutan otomatis kalau dikosongkan', function () {
    $admin = makeAdmin();

    $this->actingAs($admin)->post(route('admin.registration-steps.store'), ['title' => 'Satu']);
    $this->actingAs($admin)->post(route('admin.registration-steps.store'), ['title' => 'Dua']);

    expect(RegistrationStep::orderBy('id')->pluck('sort_order')->all())->toBe([1, 2]);
});

it('mengubah dan menonaktifkan langkah', function () {
    $step = buatLangkah('Lama');

    $this->actingAs(makeAdmin())
        ->put(route('admin.registration-steps.update', $step), ['title' => 'Judul Baru', 'is_active' => '0'])
        ->assertRedirect(route('admin.registration-steps.index'))
        ->assertSessionHasNoErrors();

    $step->refresh();
    expect($step->title)->toBe('Judul Baru')
        ->and($step->is_active)->toBeFalse();
});

it('menghapus langkah', function () {
    $step = buatLangkah('Hapus Aku');

    $this->actingAs(makeAdmin())
        ->delete(route('admin.registration-steps.destroy', $step))
        ->assertRedirect(route('admin.registration-steps.index'));

    expect(RegistrationStep::count())->toBe(0);
});

it('menampilkan langkah aktif sesuai urutan di halaman setelah pendaftaran', function () {
    buatLangkah('Langkah Kedua', 2);
    buatLangkah('Langkah Pertama', 1);
    buatLangkah('Langkah Tersembunyi', 3, false);

    $this->followingRedirects()
        ->post(route('registration.store'), payloadDaftarAturan())
        ->assertOk()
        ->assertSee('Langkah berikutnya')
        ->assertSeeInOrder(['Langkah Pertama', 'Langkah Kedua'])
        ->assertDontSee('Langkah Tersembunyi');
});

it('menampilkan alur di formulir pendaftaran', function () {
    buatLangkah('Bayar Formulir');

    $this->get(route('registration.create'))
        ->assertOk()
        ->assertSee('Alur setelah mendaftar')
        ->assertSee('Bayar Formulir');
});

it('tidak menampilkan bagian alur kalau tidak ada langkah aktif', function () {
    buatLangkah('Nonaktif', 1, false);

    $this->get(route('registration.create'))
        ->assertOk()
        ->assertDontSee('Alur setelah mendaftar');
});

it('menampilkan tab pengaturan pendaftaran di halaman Pendaftaran', function () {
    $this->actingAs(makeAdmin())
        ->get(route('admin.registrations.index'))
        ->assertOk()
        ->assertSee('Pengaturan')
        ->assertSee(route('admin.registration-settings.edit'), false);
});

it('mengisi aturan dan alur contoh di data demo', function () {
    Storage::fake('local');

    $this->seed(DemoSeeder::class);

    expect(RegistrationStep::count())->toBe(5)
        ->and(RegistrationAge::limits()['min'])->toBe(11)
        ->and(RegistrationAge::limits()['max'])->toBe(14);
});