<?php

test('halaman daftar tidak tersedia', function () {
    $this->get('/register')->assertNotFound();
});

test('pendaftaran mandiri tidak bisa dilakukan', function () {
    $this->post('/register', [
        'name' => 'Penyusup',
        'email' => 'penyusup@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertNotFound();

    $this->assertDatabaseMissing('users', ['email' => 'penyusup@example.com']);
});