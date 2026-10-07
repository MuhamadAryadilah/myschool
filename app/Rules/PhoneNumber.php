<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PhoneNumber implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $valid = is_string($value)
            && preg_match('/^[0-9+\-\s()]{8,20}$/', $value) === 1
            && strlen(preg_replace('/\D+/', '', $value)) >= 8;

        if (! $valid) {
            $fail('Nomor HP tidak valid.');
        }
    }
}