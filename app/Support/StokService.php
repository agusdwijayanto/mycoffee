<?php

namespace App\Support;

use App\Models\Menu;
use App\Models\StockLog;
use App\Models\User;

/**
 * Semua perubahan stok menu dan bahan baku lewat sini,
 * supaya riwayat stok (stock_logs) selalu tercatat.
 */
class StokService
{
    /** Ubah stok menu sebesar $delta (negatif = berkurang) dan catat log. */
    public static function ubahStokMenu(Menu $menu, int $delta, string $jenis, ?User $user = null, ?string $keterangan = null): void
    {
        if ($delta === 0) {
            return;
        }

        $sebelum = $menu->stok;
        $menu->increment('stok', $delta);
        $menu->refresh();

        StockLog::create([
            'menu_id' => $menu->id,
            'user_id' => $user?->id,
            'perubahan' => $delta,
            'stok_sebelum' => $sebelum,
            'stok_sesudah' => $menu->stok,
            'jenis' => $jenis,
            'keterangan' => $keterangan,
        ]);
    }

    /** Kurangi stok bahan baku sesuai resep menu untuk sejumlah porsi. */
    public static function kurangiBahan(Menu $menu, int $porsi): void
    {
        foreach ($menu->ingredients as $bahan) {
            $bahan->decrement('stok', (float) $bahan->pivot->qty_per_porsi * $porsi);
        }
    }

    /** Kembalikan stok bahan baku (void/batal). */
    public static function kembalikanBahan(Menu $menu, int $porsi): void
    {
        foreach ($menu->ingredients as $bahan) {
            $bahan->increment('stok', (float) $bahan->pivot->qty_per_porsi * $porsi);
        }
    }
}
