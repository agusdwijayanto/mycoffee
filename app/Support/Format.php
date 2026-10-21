<?php

namespace App\Support;

class Format
{
    public static function rupiah(int|float $angka): string
    {
        return 'Rp '.number_format((float) $angka, 0, ',', '.');
    }
}
