<?php

use App\Models\Student;
use App\Models\User;

beforeEach(function () {
    $this->admin = makeAdmin();

    $this->hendra = User::factory()->create([
        'role' => 'orang_tua', 'name' => 'Hendra Gunawan Wijaya', 'email' => 'wali.unik@example.test',
    ]);
    $this->rina = User::factory()->create([
        'role' => 'orang_tua', 'name' => 'Rina Kusumawardani', 'email' => 'rina.k@example.test',
    ]);

    Student::factory()->for($this->hendra, 'parent')->create(['name' => 'Anak Cendekia']);
    Student::factory()->for($this->rina, 'parent')->create(['name' => 'Putra Bahari']);
});

it('mencari orang tua berdasarkan nama', function () {
    $this->actingAs($this->admin)
        ->get('/admin/parents?q=Gunawan')
        ->assertSee('Hendra Gunawan Wijaya')
        ->assertDontSee('Rina Kusumawardani');
});

it('mencari orang tua berdasarkan email', function () {
    $this->actingAs($this->admin)
        ->get('/admin/parents?q=wali.unik')
        ->assertSee('Hendra Gunawan Wijaya')
        ->assertDontSee('Rina Kusumawardani');
});

it('mencari orang tua berdasarkan nama anak', function () {
    $this->actingAs($this->admin)
        ->get('/admin/parents?q=Bahari')
        ->assertSee('Rina Kusumawardani')
        ->assertDontSee('Hendra Gunawan Wijaya');
});

it('menampilkan pesan kosong kalau tidak ada yang cocok', function () {
    $this->actingAs($this->admin)
        ->get('/admin/parents?q=tidakadayangcocok')
        ->assertSee('Tidak ada orang tua yang cocok.');
});

it('hanya menampilkan akun orang tua, bukan admin', function () {
    User::factory()->create(['role' => 'admin', 'name' => 'Pengelola Lain']);

    $this->actingAs($this->admin)
        ->get('/admin/parents')
        ->assertSee('Hendra Gunawan Wijaya')
        ->assertDontSee('Pengelola Lain');
});

it('menampilkan link reset hanya saat pencarian aktif', function () {
    $this->actingAs($this->admin)
        ->get('/admin/parents')
        ->assertDontSee('>Reset</a>', false);

    $this->actingAs($this->admin)
        ->get('/admin/parents?q=Gunawan')
        ->assertSee('>Reset</a>', false);
});

it('membagi daftar orang tua per 15 data', function () {
    User::factory()->count(14)->sequence(
        fn ($sequence) => ['role' => 'orang_tua', 'name' => 'Wali ' . ($sequence->index + 1)]
    )->create();

    $this->actingAs($this->admin)
        ->get('/admin/parents')
        ->assertViewHas('parents', fn ($parents) => $parents->count() === 15 && $parents->total() === 16);

    $this->actingAs($this->admin)
        ->get('/admin/parents?page=2')
        ->assertViewHas('parents', fn ($parents) => $parents->count() === 1);
});

it('mempertahankan kata kunci pencarian di link halaman berikutnya', function () {
    User::factory()->count(16)->sequence(
        fn ($sequence) => ['role' => 'orang_tua', 'name' => 'Wali ' . ($sequence->index + 1)]
    )->create();

    $this->actingAs($this->admin)
        ->get('/admin/parents?q=Wali')
        ->assertSee('q=Wali', false);
});

it('melarang orang tua membuka daftar orang tua', function () {
    $this->actingAs(makeParent())->get('/admin/parents')->assertForbidden();
});