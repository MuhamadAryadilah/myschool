<?php

use App\Mail\InvoiceReminder;
use App\Models\Extracurricular;
use App\Models\Invoice;
use App\Models\Student;
use Illuminate\Support\Facades\Mail;

it('menyaring siswa yang belum punya eskul', function () {
    $eskul = Extracurricular::create(['name' => 'Pramuka Filter', 'is_active' => true]);
    Student::factory()->create(['name' => 'Siswa Tanpa Eskul Xq']);
    Student::factory()->create(['name' => 'Siswa Punya Eskul Zr'])->extracurriculars()->attach($eskul);

    $this->actingAs(makeAdmin())
        ->get(route('admin.students.index', ['eskul' => 'none']))
        ->assertOk()
        ->assertSee('Siswa Tanpa Eskul Xq')
        ->assertDontSee('Siswa Punya Eskul Zr');
});

it('menyaring siswa berdasarkan eskul tertentu', function () {
    $a = Extracurricular::create(['name' => 'Eskul Alfa', 'is_active' => true]);
    $b = Extracurricular::create(['name' => 'Eskul Beta', 'is_active' => true]);
    Student::factory()->create(['name' => 'Siswa Alfa Xq'])->extracurriculars()->attach($a);
    Student::factory()->create(['name' => 'Siswa Beta Zr'])->extracurriculars()->attach($b);

    $this->actingAs(makeAdmin())
        ->get(route('admin.students.index', ['eskul' => $a->id]))
        ->assertOk()
        ->assertSee('Siswa Alfa Xq')
        ->assertDontSee('Siswa Beta Zr');
});

it('mengirim pengingat tagihan hanya sekali per tagihan', function () {
    Mail::fake();

    $parent = makeParent();
    $student = Student::factory()->for($parent, 'parent')->create();
    Invoice::factory()->for($student)->create([
        'status' => 'belum_lunas',
        'due_date' => today()->addDays(2),
    ]);

    $this->artisan('invoices:send-reminders')->assertSuccessful();
    $this->artisan('invoices:send-reminders')->assertSuccessful();

    Mail::assertSent(InvoiceReminder::class, 1);
});