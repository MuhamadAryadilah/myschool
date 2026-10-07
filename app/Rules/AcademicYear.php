<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AcademicYear implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $valid = is_string($value)
            && preg_match('/^(\d{4})\/(\d{4})$/', $value, $m) === 1
            && (int) $m[2] === (int) $m[1] + 1
            && (int) $m[1] >= 2000
            && (int) $m[1] <= 2100;

        if (! $valid) {
            $fail('Tahun ajaran harus berformat 2026/2027 (dua tahun berurutan).');
        }
    }
}