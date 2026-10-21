<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shift extends Model
{
    protected $fillable = [
        'user_id', 'opened_at', 'closed_at', 'modal_awal', 'uang_fisik', 'selisih', 'catatan',
    ];

    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'modal_awal' => 'integer',
            'uang_fisik' => 'integer',
            'selisih' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function terbuka(): bool
    {
        return $this->closed_at === null;
    }

    /** Total penjualan (paid) user pemilik shift sejak shift dibuka s/d waktu tutup (atau sekarang). */
    public function ringkasanPenjualan(): array
    {
        $sampai = $this->closed_at ?? now();

        $q = Transaction::where('user_id', $this->user_id)
            ->where('status', 'paid')
            ->whereBetween('created_at', [$this->opened_at, $sampai]);

        return [
            'tunai' => (int) (clone $q)->where('payment_method', 'tunai')->sum('total'),
            'qris' => (int) (clone $q)->where('payment_method', 'qris')->sum('total'),
            'jumlah' => (clone $q)->count(),
        ];
    }
}
