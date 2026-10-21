<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

/**
 * Area "Pengembangan Berikutnya" (Versi 4): pratinjau STATIC.
 * Tanpa tabel, tanpa logika, tanpa aksi yang berfungsi.
 */
class PengembanganController extends Controller
{
    public static function daftar(): array
    {
        return [
            'multi-cabang' => [
                'judul' => 'Multi Cabang',
                'ringkas' => 'Satu pemilik, banyak toko, stok dan laporan terpisah per cabang dengan ringkasan gabungan.',
            ],
            'aplikasi-pelanggan' => [
                'judul' => 'Aplikasi Pelanggan',
                'ringkas' => 'Pelanggan memesan dari HP, membayar di aplikasi, dan mengambil pesanan di toko.',
            ],
            'payment-gateway' => [
                'judul' => 'Payment Gateway (Mode Live)',
                'ringkas' => 'Pembayaran QRIS dan e-wallet otomatis lewat penyedia payment gateway dengan akun bisnis asli.',
            ],
            'integrasi-akuntansi' => [
                'judul' => 'Integrasi Akuntansi',
                'ringkas' => 'Transaksi penjualan dan pengeluaran tersinkron otomatis ke aplikasi akuntansi dan pajak.',
            ],
            'ramalan-penjualan' => [
                'judul' => 'Ramalan Penjualan',
                'ringkas' => 'Perkiraan kebutuhan stok dan penjualan besok berdasarkan pola transaksi yang sudah lewat.',
            ],
        ];
    }

    public function index()
    {
        $fitur = static::daftar();

        return view('admin.pengembangan.index', compact('fitur'));
    }

    public function show(string $slug)
    {
        $fitur = static::daftar();
        abort_unless(isset($fitur[$slug]), 404);

        return view('admin.pengembangan.show', ['info' => $fitur[$slug], 'slug' => $slug]);
    }
}
