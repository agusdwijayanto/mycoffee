<?php

namespace App\Support;

class Wa
{
    /** Normalisasi nomor WhatsApp Indonesia: buang non-digit, awalan 0 menjadi 62. */
    public static function normalisasi(?string $nomor): string
    {
        $digits = preg_replace('/\D+/', '', (string) $nomor);

        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        }

        return $digits;
    }

    public static function tautan(?string $nomor, ?string $pesan = null): string
    {
        $url = 'https://wa.me/'.static::normalisasi($nomor);

        if ($pesan) {
            $url .= '?text='.urlencode($pesan);
        }

        return $url;
    }
}
