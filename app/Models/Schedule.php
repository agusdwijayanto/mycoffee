<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schedule extends Model
{
    protected $fillable = ['user_id', 'hari', 'jam_masuk', 'jam_selesai'];

    protected function casts(): array
    {
        return ['hari' => 'integer'];
    }

    public static function namaHari(int $hari): string
    {
        return ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][$hari] ?? '-';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
