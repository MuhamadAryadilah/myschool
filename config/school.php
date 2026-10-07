<?php

return [
    'name'    => env('SCHOOL_NAME', config('app.name')),
    'tagline' => env('SCHOOL_TAGLINE', 'Mendidik dengan hati, membimbing dengan ilmu, mewujudkan generasi yang berakhlak dan unggul.'),
    'phone'   => env('SCHOOL_PHONE', '+62 812-0000-0000'),
    'email'   => env('SCHOOL_EMAIL', 'info@contoh.sch.id'),
    'whatsapp' => env('SCHOOL_WHATSAPP'),
    'address' => env('SCHOOL_ADDRESS', 'Jl. Contoh No. 1, Kota Contoh'),
    'social'  => [
        'facebook'  => env('SCHOOL_FACEBOOK'),
        'instagram' => env('SCHOOL_INSTAGRAM'),
        'x'         => env('SCHOOL_X'),
    ],
];