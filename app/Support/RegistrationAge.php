<?php

namespace App\Support;

use App\Models\AppSetting;
use Illuminate\Support\Carbon;

class RegistrationAge
{
    /**
     * @return array{min: ?int, max: ?int, date: ?string}
     */
    public static function limits(): array
    {
        $min = AppSetting::read('reg_min_age');
        $max = AppSetting::read('reg_max_age');

        return [
            'min'  => $min !== null ? (int) $min : null,
            'max'  => $max !== null ? (int) $max : null,
            'date' => AppSetting::read('reg_age_date'),
        ];
    }

    public static function ageOn(Carbon $birth, Carbon $reference): int
    {
        $age = $reference->year - $birth->year;

        if ($reference->month < $birth->month
            || ($reference->month === $birth->month && $reference->day < $birth->day)) {
            $age--;
        }

        return $age;
    }

    public static function description(): ?string
    {
        ['min' => $min, 'max' => $max, 'date' => $date] = self::limits();

        if ($min === null && $max === null) {
            return null;
        }

        $acuan = $date
            ? ' (dihitung per '.Carbon::parse($date)->translatedFormat('j F Y').')'
            : ' (dihitung per hari pendaftaran)';

        if ($min !== null && $max !== null) {
            return "Usia calon siswa yang dapat mendaftar: {$min} sampai {$max} tahun{$acuan}.";
        }

        if ($min !== null) {
            return "Usia calon siswa minimal {$min} tahun{$acuan}.";
        }

        return "Usia calon siswa maksimal {$max} tahun{$acuan}.";
    }

    /**
     * Pesan error kalau usia di luar batas, atau null kalau boleh mendaftar.
     */
    public static function check(Carbon $birth): ?string
    {
        ['min' => $min, 'max' => $max, 'date' => $date] = self::limits();

        if ($min === null && $max === null) {
            return null;
        }

        $reference = $date ? Carbon::parse($date)->startOfDay() : Carbon::today();
        $age = self::ageOn($birth, $reference);

        if (($min !== null && $age < $min) || ($max !== null && $age > $max)) {
            return "Usia calon siswa ({$age} tahun) tidak sesuai ketentuan. ".self::description();
        }

        return null;
    }
}