<?php

it('menyertakan langkah masukan di panduan saat link formulir diisi', function () {
    config(['demo.enabled' => true, 'demo.feedback_url' => 'https://contoh.test/form']);

    $this->get('/')->assertOk()
        ->assertSee('data-feedback-url="https://contoh.test/form"', false);
    $this->get('/login')->assertOk()->assertSee('data-demo-feedback', false);
});