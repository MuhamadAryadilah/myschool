<?php

use Illuminate\Support\Facades\Hash;

it('admin mereset password orang tua tanpa tahu password lama', function () {
    config(['demo.enabled' => false]);
    $parent = makeParent();
    $lama = $parent->password;

    $this->actingAs(makeAdmin())
        ->patch(route('admin.parents.reset-password', $parent))
        ->assertRedirect(route('admin.parents.index'))
        ->assertSessionHas('reset_password');

    $baru = session('reset_password')['password'];
    expect($baru)->toHaveLength(8);
    expect($parent->fresh()->password)->not->toBe($lama);
    expect(Hash::check($baru, $parent->fresh()->password))->toBeTrue();
});

it('menampilkan password baru sekali di daftar orang tua', function () {
    config(['demo.enabled' => false]);
    $parent = makeParent();

    $this->actingAs(makeAdmin())->followingRedirects()
        ->patch(route('admin.parents.reset-password', $parent))
        ->assertOk()
        ->assertSee('Password baru untuk')
        ->assertSee('Salin password');
});

it('melarang orang tua dan menolak akun admin untuk reset password', function () {
    config(['demo.enabled' => false]);
    $parent = makeParent();

    $this->actingAs(makeParent())->patch(route('admin.parents.reset-password', $parent))->assertForbidden();
    $this->actingAs(makeAdmin())->patch(route('admin.parents.reset-password', makeAdmin()))->assertNotFound();
});

it('menolak reset password saat mode demo', function () {
    config(['demo.enabled' => true]);

    $this->actingAs(makeAdmin())->patch(route('admin.parents.reset-password', makeParent()))->assertForbidden();
});