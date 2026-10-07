<?php

return [
    // true hanya di web demo. Di web asli harus false.
    'enabled' => (bool) env('DEMO_MODE', false),

    // Password semua akun demo
    'password' => 'demo1234',

    // Link Google Form untuk tombol "Beri masukan"
    'feedback_url' => env('FEEDBACK_URL'),
];