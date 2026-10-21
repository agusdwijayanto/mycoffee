@extends('layouts.admin')

@section('title', 'Pengaturan')

@section('content')
<h1>Pengaturan Brand</h1>
<p style="color:var(--redup)">Perubahan di halaman ini langsung tampil di situs publik, termasuk nama brand di header dan footer. Data saat ini adalah data dummy.</p>
<div class="panel" style="max-width:680px">
    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf @method('PUT')
        <div class="form-group">
            <label for="brand_name">Nama Brand</label>
            <input class="input" id="brand_name" name="brand_name" value="{{ old('brand_name', $settings['brand_name']) }}" required>
        </div>
        <div class="form-group">
            <label for="tagline">Tagline</label>
            <input class="input" id="tagline" name="tagline" value="{{ old('tagline', $settings['tagline']) }}">
        </div>
        <div class="form-group">
            <label for="alamat">Alamat</label>
            <input class="input" id="alamat" name="alamat" value="{{ old('alamat', $settings['alamat']) }}">
        </div>
        <div class="form-group">
            <label for="telepon">Telepon</label>
            <input class="input" id="telepon" name="telepon" value="{{ old('telepon', $settings['telepon']) }}">
        </div>
        <div class="form-group">
            <label for="whatsapp">WhatsApp</label>
            <input class="input" id="whatsapp" name="whatsapp" value="{{ old('whatsapp', $settings['whatsapp']) }}">
        </div>
        <div class="form-group">
            <label for="whatsapp_pemilik">WhatsApp Pemilik (tujuan rekap laporan)</label>
            <input class="input" id="whatsapp_pemilik" name="whatsapp_pemilik" value="{{ old('whatsapp_pemilik', $settings['whatsapp_pemilik'] ?? '') }}">
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input class="input" id="email" type="email" name="email" value="{{ old('email', $settings['email']) }}">
        </div>
        <div class="form-group">
            <label for="jam_buka">Jam Buka</label>
            <input class="input" id="jam_buka" name="jam_buka" value="{{ old('jam_buka', $settings['jam_buka']) }}">
        </div>
        <div class="form-group">
            <label for="jam_buka_libur">Jam Buka Hari Libur (opsional, tampil di Kontak)</label>
            <input class="input" id="jam_buka_libur" name="jam_buka_libur" value="{{ old('jam_buka_libur', $settings['jam_buka_libur'] ?? '') }}" placeholder="contoh: 09.00 - 21.00">
        </div>
        <div class="form-group">
            <label for="instagram">Instagram</label>
            <input class="input" id="instagram" name="instagram" value="{{ old('instagram', $settings['instagram']) }}">
        </div>

        <hr>
        <h3>Operasional &amp; Promo</h3>
        <div class="form-group">
            <label for="tax_percent">Pajak (%) — diterapkan ke semua transaksi setelah diskon</label>
            <input class="input" id="tax_percent" type="number" step="0.1" min="0" max="100" name="tax_percent" value="{{ old('tax_percent', $settings['tax_percent'] ?? 0) }}">
        </div>
        <div class="form-group">
            <label for="stok_minimum">Batas Stok Menipis (peringatan di Dashboard)</label>
            <input class="input" id="stok_minimum" type="number" min="0" name="stok_minimum" value="{{ old('stok_minimum', $settings['stok_minimum'] ?? 5) }}">
        </div>
        <div class="form-group">
            <label for="promo_b2g1_menu_id">Promo Beli 2 Gratis 1 untuk Menu</label>
            <select class="input" id="promo_b2g1_menu_id" name="promo_b2g1_menu_id">
                <option value="">— Nonaktif —</option>
                @foreach($menus as $m)
                    <option value="{{ $m->id }}" {{ (string) old('promo_b2g1_menu_id', $settings['promo_b2g1_menu_id'] ?? '') === (string) $m->id ? 'selected' : '' }}>{{ $m->nama }}</option>
                @endforeach
            </select>
            <small style="color:var(--redup)">Bila aktif, pembelian menu ini 3 atau lebih dalam satu transaksi mendapat potongan 1x harga menu.</small>
        </div>
        <div class="form-group">
            <label for="promo_banner_text">Teks Banner Promo (Beranda)</label>
            <input class="input" id="promo_banner_text" name="promo_banner_text" value="{{ old('promo_banner_text', $settings['promo_banner_text'] ?? '') }}" placeholder="contoh: Promo akhir pekan — semua kopi diskon 10%!">
        </div>
        <div class="form-group">
            <label>Periode Banner Promo</label>
            <div class="form-inline-flex">
                <input class="input" type="date" name="promo_banner_dari" value="{{ old('promo_banner_dari', $settings['promo_banner_dari'] ?? '') }}">
                <span>s/d</span>
                <input class="input" type="date" name="promo_banner_sampai" value="{{ old('promo_banner_sampai', $settings['promo_banner_sampai'] ?? '') }}">
            </div>
        </div>
        <button class="btn" type="submit">Simpan Pengaturan</button>
    </form>
</div>

<div class="panel" style="max-width:680px">
    <h2 style="margin-top:0">QR Menu untuk Meja</h2>
    <p>Cetak kode QR ini dan tempel di meja pelanggan. Kode mengarah ke halaman Menu publik yang selalu sinkron dengan stok kasir:</p>
    <p><strong>{{ $urlMenu }}</strong></p>
    @if($qrSvg)
        <div class="qr-menu">{!! $qrSvg !!}</div>
        <p><button class="btn btn-kecil" onclick="window.print()">Cetak QR</button></p>
    @else
        <p class="kosong">Gambar QR tidak dapat dibuat di server ini. Gunakan tautan teks di atas untuk dicetak sebagai kartu URL.</p>
    @endif
</div>
@endsection
