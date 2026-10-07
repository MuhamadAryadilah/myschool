<?php

it('menutup pendaftaran akun mandiri', function () {
    $this->get('/register')->assertNotFound();

    $this->post('/register', [
        'name' => 'Penyusup',
        'email' => 'penyusup@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertNotFound();

    $this->assertDatabaseMissing('users', ['email' => 'penyusup@example.com']);
});