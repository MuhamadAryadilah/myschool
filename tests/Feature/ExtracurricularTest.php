<?php

use App\Models\Extracurricular;
use App\Models\Registration;
use App\Models\Student;
use Database\Seeders\DemoSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

beforeEach(fn () => Mail::fake());

function dataDaftarEskul(array $override = []): array
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

function idEskul(Student $siswa): array
{
    return $siswa->refresh()->extracurriculars->pluck('id')->sort()->values()->all();
}

it('melarang tamu dan orang tua membuka menu eskul', function () {
    $eskul = Extracurricular::factory()->create();
    $siswa = Student::factory()->create();

    $this->get(route('admin.extracurriculars.index'))->assertRedirect('/login');

    $parent = makeParent();
    $this->actingAs($parent)->get(route('admin.extracurriculars.index'))->assertForbidden();
    $this->actingAs($parent)->get(route('admin.extracurriculars.show', $eskul))->assertForbidden();
    $this->actingAs($parent)->post(route('admin.extracurriculars.store'), ['name' => 'Baru'])->assertForbidden();
    $this->actingAs($parent)
        ->post(route('admin.extracurriculars.members.store', $eskul), ['student_id' => $siswa->id])
        ->assertForbidden();
    $this->actingAs($parent)
        ->patch(route('admin.students.extracurricular.update', $siswa), ['from' => $eskul->id, 'extracurricular_id' => $eskul->id])
        ->assertForbidden();

    expect(idEskul($siswa))->toBe([]);
    expect(Extracurricular::count())->toBe(1);
});

it('menampilkan daftar eskul dengan jumlah anggota', function () {
    $pramuka = Extracurricular::factory()->create(['name' => 'Pramuka']);
    Extracurricular::factory()->create(['name' => 'Basket']);
    Student::factory()->count(2)->create()->each(fn ($s) => $s->extracurriculars()->attach($pramuka));
    Student::factory()->create();

    $this->actingAs(makeAdmin())
        ->get(route('admin.extracurriculars.index'))
        ->assertOk()
        ->assertSee('Pramuka')
        ->assertSee('Basket')
        ->assertSee('1 siswa belum punya eskul')
        ->assertViewHas('extracurriculars', fn ($list) => $list->firstWhere('name', 'Pramuka')->students_count === 2);
});

it('menampilkan menu Eskul di navigasi admin', function () {
    $this->actingAs(makeAdmin())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Eskul');
});

it('menambah eskul baru', function () {
    $this->actingAs(makeAdmin())
        ->post(route('admin.extracurriculars.store'), [
            'name' => 'Robotik',
            'description' => 'Belajar merakit robot',
            'schedule' => 'Sabtu, 08.00-10.00',
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.extracurriculars.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('extracurriculars', ['name' => 'Robotik', 'is_active' => 1]);
});

it('menolak eskul tanpa nama atau dengan nama kembar', function () {
    Extracurricular::factory()->create(['name' => 'Pramuka']);
    $admin = makeAdmin();

    $this->actingAs($admin)->post(route('admin.extracurriculars.store'), ['name' => ''])->assertSessionHasErrors('name');
    $this->actingAs($admin)->post(route('admin.extracurriculars.store'), ['name' => 'Pramuka'])->assertSessionHasErrors('name');

    expect(Extracurricular::count())->toBe(1);
});

it('mengubah dan menonaktifkan eskul', function () {
    $eskul = Extracurricular::factory()->create(['name' => 'Pramuka']);

    $this->actingAs(makeAdmin())
        ->put(route('admin.extracurriculars.update', $eskul), ['name' => 'Pramuka Penggalang', 'is_active' => '0'])
        ->assertRedirect(route('admin.extracurriculars.index'))
        ->assertSessionHasNoErrors();

    $eskul->refresh();
    expect($eskul->name)->toBe('Pramuka Penggalang')
        ->and($eskul->is_active)->toBeFalse();
});

it('menolak menghapus eskul yang masih punya anggota', function () {
    $eskul = Extracurricular::factory()->create();
    Student::factory()->create()->extracurriculars()->attach($eskul);

    $this->actingAs(makeAdmin())
        ->delete(route('admin.extracurriculars.destroy', $eskul))
        ->assertSessionHasErrors('extracurricular');

    expect(Extracurricular::count())->toBe(1);
});

it('menghapus eskul yang kosong', function () {
    $eskul = Extracurricular::factory()->create();

    $this->actingAs(makeAdmin())
        ->delete(route('admin.extracurriculars.destroy', $eskul))
        ->assertRedirect(route('admin.extracurriculars.index'));

    expect(Extracurricular::count())->toBe(0);
});

it('menampilkan anggota di halaman eskul', function () {
    $pramuka = Extracurricular::factory()->create(['name' => 'Pramuka']);
    $basket = Extracurricular::factory()->create(['name' => 'Basket']);
    Student::factory()->create(['name' => 'Anggota Pramuka'])->extracurriculars()->attach($pramuka);
    Student::factory()->create(['name' => 'Anggota Basket'])->extracurriculars()->attach($basket);

    $this->actingAs(makeAdmin())
        ->get(route('admin.extracurriculars.show', $pramuka))
        ->assertOk()
        ->assertSee('Anggota Pramuka')
        ->assertViewHas('extracurricular', fn ($e) => $e->students->pluck('name')->all() === ['Anggota Pramuka']);
});

it('menambahkan siswa ke eskul', function () {
    $eskul = Extracurricular::factory()->create();
    $siswa = Student::factory()->create();

    $this->actingAs(makeAdmin())
        ->post(route('admin.extracurriculars.members.store', $eskul), ['student_id' => $siswa->id])
        ->assertSessionHasNoErrors();

    expect(idEskul($siswa))->toBe([$eskul->id]);
});

it('menolak menambah anggota ke eskul nonaktif', function () {
    $eskul = Extracurricular::factory()->inactive()->create();
    $siswa = Student::factory()->create();

    $this->actingAs(makeAdmin())
        ->post(route('admin.extracurriculars.members.store', $eskul), ['student_id' => $siswa->id])
        ->assertSessionHasErrors('student_id');

    expect(idEskul($siswa))->toBe([]);
});

it('membolehkan satu siswa ikut beberapa eskul', function () {
    $a = Extracurricular::factory()->create();
    $b = Extracurricular::factory()->create();
    $siswa = Student::factory()->create();
    $admin = makeAdmin();

    $this->actingAs($admin)->post(route('admin.extracurriculars.members.store', $a), ['student_id' => $siswa->id]);
    $this->actingAs($admin)->post(route('admin.extracurriculars.members.store', $b), ['student_id' => $siswa->id]);

    expect(idEskul($siswa))->toBe(collect([$a->id, $b->id])->sort()->values()->all());
});

it('tidak menggandakan anggota kalau ditambah dua kali', function () {
    $eskul = Extracurricular::factory()->create();
    $siswa = Student::factory()->create();
    $admin = makeAdmin();

    $this->actingAs($admin)->post(route('admin.extracurriculars.members.store', $eskul), ['student_id' => $siswa->id]);
    $this->actingAs($admin)
        ->post(route('admin.extracurriculars.members.store', $eskul), ['student_id' => $siswa->id])
        ->assertSessionHas('success');

    expect(idEskul($siswa))->toBe([$eskul->id])
        ->and(DB::table('extracurricular_student')->count())->toBe(1);
});

it('memindahkan siswa ke eskul lain', function () {
    $a = Extracurricular::factory()->create();
    $b = Extracurricular::factory()->create();
    $siswa = Student::factory()->create();
    $siswa->extracurriculars()->attach($a);

    $this->actingAs(makeAdmin())
        ->patch(route('admin.students.extracurricular.update', $siswa), ['from' => $a->id, 'extracurricular_id' => $b->id])
        ->assertSessionHasNoErrors();

    expect(idEskul($siswa))->toBe([$b->id]);
});

it('memindahkan siswa tanpa mengubah eskul lainnya', function () {
    $a = Extracurricular::factory()->create();
    $b = Extracurricular::factory()->create();
    $c = Extracurricular::factory()->create();
    $siswa = Student::factory()->create();
    $siswa->extracurriculars()->attach([$a->id, $c->id]);

    $this->actingAs(makeAdmin())
        ->patch(route('admin.students.extracurricular.update', $siswa), ['from' => $a->id, 'extracurricular_id' => $b->id])
        ->assertSessionHasNoErrors();

    expect(idEskul($siswa))->toBe(collect([$b->id, $c->id])->sort()->values()->all());
});

it('mengeluarkan siswa dari eskul', function () {
    $a = Extracurricular::factory()->create();
    $siswa = Student::factory()->create();
    $siswa->extracurriculars()->attach($a);

    $this->actingAs(makeAdmin())
        ->patch(route('admin.students.extracurricular.update', $siswa), ['from' => $a->id, 'extracurricular_id' => ''])
        ->assertSessionHasNoErrors();

    expect(idEskul($siswa))->toBe([]);
});

it('mengeluarkan siswa dari satu eskul tanpa memengaruhi eskul lainnya', function () {
    $a = Extracurricular::factory()->create();
    $b = Extracurricular::factory()->create();
    $siswa = Student::factory()->create();
    $siswa->extracurriculars()->attach([$a->id, $b->id]);

    $this->actingAs(makeAdmin())
        ->patch(route('admin.students.extracurricular.update', $siswa), ['from' => $a->id, 'extracurricular_id' => ''])
        ->assertSessionHasNoErrors();

    expect(idEskul($siswa))->toBe([$b->id]);
});

it('menolak memindahkan siswa ke eskul nonaktif atau yang tidak ada', function () {
    $a = Extracurricular::factory()->create();
    $nonaktif = Extracurricular::factory()->inactive()->create();
    $siswa = Student::factory()->create();
    $siswa->extracurriculars()->attach($a);
    $admin = makeAdmin();

    $this->actingAs($admin)
        ->patch(route('admin.students.extracurricular.update', $siswa), ['from' => $a->id, 'extracurricular_id' => $nonaktif->id])
        ->assertSessionHasErrors('extracurricular_id');

    $this->actingAs($admin)
        ->patch(route('admin.students.extracurricular.update', $siswa), ['from' => $a->id, 'extracurricular_id' => 9999])
        ->assertSessionHasErrors('extracurricular_id');

    expect(idEskul($siswa))->toBe([$a->id]);
});

it('menampilkan hanya eskul aktif di formulir pendaftaran', function () {
    Extracurricular::factory()->create(['name' => 'Pramuka']);
    Extracurricular::factory()->inactive()->create(['name' => 'Karate Lama']);

    $this->get(route('registration.create'))
        ->assertOk()
        ->assertSee('Ekstrakurikuler pilihan')
        ->assertSee('Pramuka')
        ->assertDontSee('Karate Lama');
});

it('menyembunyikan pilihan eskul kalau belum ada eskul aktif', function () {
    Extracurricular::factory()->inactive()->create();

    $this->get(route('registration.create'))
        ->assertOk()
        ->assertDontSee('Ekstrakurikuler pilihan');
});

it('menampilkan dan menyaring eskul di daftar siswa', function () {
    $pramuka = Extracurricular::factory()->create(['name' => 'Pramuka']);
    Student::factory()->create(['name' => 'Anggota Pramuka'])->extracurriculars()->attach($pramuka);
    Student::factory()->create(['name' => 'Siswa Tanpa Eskul']);
    $admin = makeAdmin();

    $this->actingAs($admin)->get('/admin/students')->assertSee('Pramuka');

    $this->actingAs($admin)->get('/admin/students?eskul='.$pramuka->id)
        ->assertSee('Anggota Pramuka')->assertDontSee('Siswa Tanpa Eskul');

    $this->actingAs($admin)->get('/admin/students?eskul=none')
        ->assertSee('Siswa Tanpa Eskul')->assertDontSee('Anggota Pramuka');
});

it('menampilkan semua eskul siswa di daftar siswa dan menyaring lewat salah satunya', function () {
    $a = Extracurricular::factory()->create(['name' => 'Eskul Alfa Uji']);
    $b = Extracurricular::factory()->create(['name' => 'Eskul Beta Uji']);
    Student::factory()->create(['name' => 'Siswa Dua Eskul'])->extracurriculars()->attach([$a->id, $b->id]);
    Student::factory()->create(['name' => 'Siswa Satu Eskul'])->extracurriculars()->attach($a);
    $admin = makeAdmin();

    $this->actingAs($admin)->get('/admin/students')
        ->assertSee('Eskul Alfa Uji')->assertSee('Eskul Beta Uji');

    $this->actingAs($admin)->get('/admin/students?eskul='.$b->id)
        ->assertSee('Siswa Dua Eskul')->assertDontSee('Siswa Satu Eskul');
});

it('menyimpan beberapa eskul sekaligus lewat form tambah siswa', function () {
    $a = Extracurricular::factory()->create();
    $b = Extracurricular::factory()->create();

    $this->actingAs(makeAdmin())
        ->post(route('admin.students.store'), [
            'name' => 'Siswa Baru',
            'nis' => '99001',
            'kelas' => '7A',
            'extracurricular_ids' => [$a->id, $b->id],
        ])
        ->assertSessionHasNoErrors();

    expect(idEskul(Student::where('nis', '99001')->first()))
        ->toBe(collect([$a->id, $b->id])->sort()->values()->all());
});

it('menolak eskul nonaktif saat menambah siswa lewat form', function () {
    $nonaktif = Extracurricular::factory()->inactive()->create();

    $this->actingAs(makeAdmin())
        ->post(route('admin.students.store'), [
            'name' => 'Siswa Baru',
            'nis' => '99002',
            'kelas' => '7A',
            'extracurricular_ids' => [$nonaktif->id],
        ])
        ->assertSessionHasErrors('extracurricular_ids.0');

    expect(Student::count())->toBe(0);
});

it('mengganti eskul lewat form ubah siswa', function () {
    $a = Extracurricular::factory()->create();
    $b = Extracurricular::factory()->create();
    $siswa = Student::factory()->create();
    $siswa->extracurriculars()->attach($a);

    $this->actingAs(makeAdmin())
        ->patch(route('admin.students.update', $siswa), [
            'name' => $siswa->name,
            'nis' => $siswa->nis,
            'kelas' => $siswa->kelas,
            'user_id' => $siswa->user_id,
            'extracurricular_ids' => [$b->id],
        ])
        ->assertSessionHasNoErrors();

    expect(idEskul($siswa))->toBe([$b->id]);
});

it('mengosongkan eskul lewat form ubah siswa', function () {
    $a = Extracurricular::factory()->create();
    $siswa = Student::factory()->create();
    $siswa->extracurriculars()->attach($a);

    $this->actingAs(makeAdmin())
        ->patch(route('admin.students.update', $siswa), [
            'name' => $siswa->name,
            'nis' => $siswa->nis,
            'kelas' => $siswa->kelas,
            'user_id' => $siswa->user_id,
        ])
        ->assertSessionHasNoErrors();

    expect(idEskul($siswa))->toBe([]);
});

it('tetap membolehkan menyimpan siswa yang eskulnya sudah nonaktif', function () {
    $lama = Extracurricular::factory()->inactive()->create();
    $siswa = Student::factory()->create();
    $siswa->extracurriculars()->attach($lama);

    $this->actingAs(makeAdmin())
        ->patch(route('admin.students.update', $siswa), [
            'name' => 'Nama Baru',
            'nis' => $siswa->nis,
            'kelas' => $siswa->kelas,
            'user_id' => $siswa->user_id,
            'extracurricular_ids' => [$lama->id],
        ])
        ->assertSessionHasNoErrors();

    expect(idEskul($siswa))->toBe([$lama->id]);
});

it('mengekspor semua eskul siswa dalam satu kolom', function () {
    $a = Extracurricular::factory()->create(['name' => 'Eskul Ekspor Alfa']);
    $b = Extracurricular::factory()->create(['name' => 'Eskul Ekspor Beta']);
    Student::factory()->create(['name' => 'Siswa Ekspor'])->extracurriculars()->attach([$a->id, $b->id]);

    $csv = $this->actingAs(makeAdmin())->get(route('admin.students.export'))->streamedContent();

    expect($csv)->toContain('Eskul Ekspor Alfa')->toContain('Eskul Ekspor Beta');
});

it('mengisi eskul di data demo', function () {
    Storage::fake('local');

    $this->seed(DemoSeeder::class);

    expect(Extracurricular::count())->toBe(5)
        ->and(Student::has('extracurriculars')->count())->toBe(8)
        ->and(DB::table('extracurricular_student')->count())->toBe(11);
});