<?php

it('membatasi permintaan lupa password per IP', function () {
    for ($i = 0; $i < 5; $i++) {
        $this->post(route('password.email'), ['email' => 'tidak-ada@example.com']);
    }

    $this->post(route('password.email'), ['email' => 'tidak-ada@example.com'])
        ->assertStatus(429);
});

it('membatasi permintaan reset password per IP', function () {
    $data = [
        'token'                 => 'token-palsu',
        'email'                 => 'tidak-ada@example.com',
        'password'              => 'PasswordBaru123!',
        'password_confirmation' => 'PasswordBaru123!',
    ];

    for ($i = 0; $i < 5; $i++) {
        $this->post(route('password.store'), $data);
    }

    $this->post(route('password.store'), $data)->assertStatus(429);
});