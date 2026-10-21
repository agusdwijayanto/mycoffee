<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = [
        'nama', 'whatsapp', 'tanggal', 'jam', 'jumlah_orang', 'catatan', 'status',
    ];

    protected function casts(): array
    {
        return ['tanggal' => 'date', 'jumlah_orang' => 'integer'];
    }
}
