<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = ['user_id', 'tanggal', 'jam_masuk_at', 'jam_keluar_at'];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'jam_masuk_at' => 'datetime',
            'jam_keluar_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
