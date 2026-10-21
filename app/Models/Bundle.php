<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bundle extends Model
{
    protected $fillable = ['nama', 'harga', 'aktif'];

    protected function casts(): array
    {
        return ['harga' => 'integer', 'aktif' => 'boolean'];
    }

    public function items(): HasMany
    {
        return $this->hasMany(BundleItem::class);
    }

    /** Total harga komponen bila dibeli satuan. */
    public function hargaKomponen(): int
    {
        return (int) $this->items->sum(fn ($i) => $i->menu->harga * $i->qty);
    }
}
