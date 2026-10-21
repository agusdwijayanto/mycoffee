<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $fillable = [
        'category_id', 'nama', 'deskripsi', 'harga', 'harga_modal', 'harga_large',
        'foto', 'stok', 'is_available', 'tampil_dari', 'tampil_sampai',
    ];

    protected function casts(): array
    {
        return [
            'harga' => 'integer',
            'harga_modal' => 'integer',
            'harga_large' => 'integer',
            'stok' => 'integer',
            'is_available' => 'boolean',
            'tampil_dari' => 'date',
            'tampil_sampai' => 'date',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function transactionItems(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'menu_ingredients')
            ->withPivot('qty_per_porsi')->withTimestamps();
    }

    public function stockLogs(): HasMany
    {
        return $this->hasMany(StockLog::class);
    }

    /** Apakah menu sedang dalam rentang tampil (menu musiman). */
    public function sedangTampil(): bool
    {
        $hariIni = today();

        if ($this->tampil_dari && $hariIni->lt($this->tampil_dari)) {
            return false;
        }

        if ($this->tampil_sampai && $hariIni->gt($this->tampil_sampai)) {
            return false;
        }

        return true;
    }

    public function scopeTampilSekarang(Builder $query): Builder
    {
        $hariIni = today()->toDateString();

        return $query->where(fn ($q) => $q->whereNull('tampil_dari')->orWhere('tampil_dari', '<=', $hariIni))
            ->where(fn ($q) => $q->whereNull('tampil_sampai')->orWhere('tampil_sampai', '>=', $hariIni));
    }

    public function isMinuman(): bool
    {
        return in_array($this->category?->slug, ['kopi', 'non-kopi'], true);
    }

    /** Stok bahan baku cukup untuk sejumlah porsi tertentu. */
    public function bahanCukup(int $porsi = 1): bool
    {
        foreach ($this->ingredients as $bahan) {
            if ((float) $bahan->stok < (float) $bahan->pivot->qty_per_porsi * $porsi) {
                return false;
            }
        }

        return true;
    }

    /**
     * Menu dianggap habis bila stok nol, dinonaktifkan manual oleh admin,
     * atau stok bahan baku tidak cukup untuk satu porsi.
     */
    public function getHabisAttribute(): bool
    {
        return $this->stok <= 0 || ! $this->is_available || ! $this->bahanCukup(1);
    }

    /** Stok menipis: masih ada stok tetapi di bawah/di sama batas minimum (dan tidak habis). */
    public function stokMenipis(int $batas): bool
    {
        return $this->stok > 0 && $this->stok <= $batas && $this->is_available;
    }

    public function fotoUrl(): string
    {
        return $this->foto ? asset('images/menu/'.$this->foto) : asset('images/menu/espresso.webp');
    }
}
