<?php

namespace App\Support;

use App\Models\Menu;
use App\Models\Setting;
use App\Models\Voucher;

/**
 * Satu-satunya tempat perhitungan uang transaksi:
 * subtotal, diskon (persen + voucher + beli-2-gratis-1 + bundel), pajak, total.
 *
 * Baris (line) yang diterima berbentuk array:
 * ['menu' => Menu, 'qty' => int, 'harga_satuan' => int, 'subtotal' => int, 'diskon_bundel' => int]
 * harga_satuan sudah termasuk tambahan ukuran Large dan add-on per item.
 */
class HitungTransaksi
{
    /**
     * @param  array<int, array<string, mixed>>  $lines
     * @return array{subtotal:int, discount_amount:int, tax_amount:int, tax_percent:float, total:int, catatan_diskon:array<int,string>, voucher:?Voucher}
     */
    public static function hitung(array $lines, float $diskonPersen = 0, ?string $kodeVoucher = null): array
    {
        $subtotal = (int) collect($lines)->sum('subtotal');
        $catatan = [];
        $diskon = 0;

        // Diskon persen per transaksi
        if ($diskonPersen > 0 && $subtotal > 0) {
            $d = (int) round($subtotal * $diskonPersen / 100);
            $diskon += $d;
            $catatan[] = 'Diskon '.$diskonPersen.'%';
        }

        // Diskon bundel (selisih harga komponen vs harga bundel), melekat di baris
        $diskonBundel = (int) collect($lines)->sum('diskon_bundel');
        if ($diskonBundel > 0) {
            $diskon += $diskonBundel;
            $catatan[] = 'Hemat Bundel';
        }

        // Beli 2 gratis 1 untuk menu yang dipilih admin di Pengaturan
        $b2g1Id = Setting::get('promo_b2g1_menu_id');
        if ($b2g1Id) {
            $qtyMenu = (int) collect($lines)
                ->filter(fn ($l) => (int) $l['menu']->id === (int) $b2g1Id)
                ->sum('qty');
            if ($qtyMenu >= 3) {
                $menuPromo = Menu::find((int) $b2g1Id);
                if ($menuPromo) {
                    $diskon += (int) $menuPromo->harga;
                    $catatan[] = 'Beli 2 Gratis 1 ('.$menuPromo->nama.')';
                }
            }
        }

        // Voucher
        $voucher = null;
        if ($kodeVoucher) {
            $voucher = Voucher::where('kode', strtoupper(trim($kodeVoucher)))->first();
            if ($voucher && $voucher->masihBerlaku() && $subtotal > 0) {
                if ($voucher->tipe === 'persen') {
                    $diskon += (int) round($subtotal * $voucher->nilai / 100);
                } else {
                    $diskon += (int) $voucher->nilai;
                }
                $catatan[] = 'Voucher '.$voucher->kode;
            } else {
                $voucher = null;
            }
        }

        $diskon = min($diskon, $subtotal);

        $taxPercent = (float) (Setting::get('tax_percent', '0') ?: 0);
        $tax = (int) round(($subtotal - $diskon) * $taxPercent / 100);
        $total = $subtotal - $diskon + $tax;

        return [
            'subtotal' => $subtotal,
            'discount_amount' => $diskon,
            'tax_amount' => $tax,
            'tax_percent' => $taxPercent,
            'total' => $total,
            'catatan_diskon' => $catatan,
            'voucher' => $voucher,
        ];
    }

    /** Validasi kode voucher tanpa menghitung; null bila valid, pesan error bila tidak. */
    public static function validasiVoucher(?string $kode): ?string
    {
        if (! $kode) {
            return null;
        }

        $voucher = Voucher::where('kode', strtoupper(trim($kode)))->first();

        if (! $voucher) {
            return 'Kode voucher tidak ditemukan.';
        }

        if (! $voucher->masihBerlaku()) {
            return 'Voucher sudah tidak aktif atau kedaluwarsa.';
        }

        return null;
    }
}
