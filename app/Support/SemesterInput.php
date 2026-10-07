<?php

namespace App\Support;

class SemesterInput
{
    public static function messages(): array
    {
        return [
            'semester.in' => 'Pilih semester Ganjil atau Genap.',
            'semester.required_with' => 'Pilih semester untuk tahun ajaran ini.',
            'academic_year.required_with' => 'Isi tahun ajaran untuk semester ini.',
        ];
    }

    public static function attributes(): array
    {
        return [
            'semester' => 'semester',
            'academic_year' => 'tahun ajaran',
        ];
    }
}