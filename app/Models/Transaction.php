<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    protected $fillable = [
        'user_id', 'payment_method', 'customer_name', 'table_number', 'channel',
        'status', 'void_reason', 'queue_number', 'status_pesanan',
        'subtotal', 'discount_amount', 'tax_amount', 'tax_percent_snapshot',
        'voucher_code', 'discount_note', 'total', 'bayar', 'kembalian',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'integer',
            'bayar' => 'integer',
            'kembalian' => 'integer',
            'subtotal' => 'integer',
            'discount_amount' => 'integer',
            'tax_amount' => 'integer',
            'tax_percent_snapshot' => 'float',
            'queue_number' => 'integer',
        ];
    }

    /** Hanya transaksi lunas yang dihitung sebagai penjualan. */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 'paid');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    /** Nomor antrean berikutnya untuk hari ini (urut per hari, reset tiap hari). */
    public static function nomorAntreanBerikutnya(): int
    {
        return (int) static::whereDate('created_at', today())->max('queue_number') + 1;
    }

    /** Laba kotor transaksi ini dari snapshot harga dan harga modal item. */
    public function labaKotor(): int
    {
        return (int) $this->items->sum(fn ($i) => ($i->harga_snapshot - $i->harga_modal_snapshot) * $i->qty);
    }
}
