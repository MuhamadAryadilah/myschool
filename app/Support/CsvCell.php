<?php

namespace App\Support;

class CsvCell
{
    /**
     * Cegah teks berawalan karakter rumus dijalankan sebagai rumus oleh Excel.
     */
    public static function safe(mixed $value): string
    {
        $value = (string) $value;

        return $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)
            ? "'".$value
            : $value;
    }
}