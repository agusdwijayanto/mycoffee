<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockLog extends Model
{
    protected $fillable = [
        'menu_id', 'user_id', 'perubahan', 'stok_sebelum', 'stok_sesudah', 'jenis', 'keterangan',
    ];

    protected function casts(): array
    {
        return ['perubahan' => 'integer', 'stok_sebelum' => 'integer', 'stok_sesudah' => 'integer'];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
