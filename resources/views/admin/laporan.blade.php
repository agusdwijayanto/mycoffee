@extends('layouts.admin')

@section('title', 'Laporan')

@section('content')
<h1>Laporan Penjualan</h1>

<div class="stat-grid">
    <div class="stat-card">
        <div class="angka">{{ \App\Support\Format::rupiah($omzetHariIni) }}</div>
        <div class="label">Omzet Hari Ini</div>
    </div>
    <div class="stat-card">
        <div class="angka">{{ \App\Support\Format::rupiah($omzetBulanIni) }}</div>
        <div class="label">Omzet Bulan Ini</div>
    </div>
    <div class="stat-card">
        <div class="angka">{{ $transaksiBulanIni }}</div>
        <div class="label">Transaksi Bulan Ini</div>
    </div>
</div>

<div class="panel">
    <h2 style="margin-top:0">Filter Rentang Tanggal</h2>
    <form method="GET" action="{{ route('admin.laporan') }}" class="form-inline-flex">
        <label>Dari <input class="input" type="date" name="dari" value="{{ $dari }}"></label>
        <label>Sampai <input class="input" type="date" name="sampai" value="{{ $sampai }}"></label>
        <button class="btn btn-kecil" type="submit">Terapkan</button>
        <a class="btn btn-kecil btn-aksen" href="{{ route('admin.laporan.csv', ['dari' => $dari, 'sampai' => $sampai]) }}">Ekspor CSV</a>
        @php
            $teksRekap = 'Rekap '.$settings['brand_name'].' hari ini ('.now()->translatedFormat('d M Y').'): Omzet '.\App\Support\Format::rupiah($omzetHariIni).', '.$transaksiHariIni.' transaksi, menu terlaris '.($menuTerlaris->first()->nama_snapshot ?? '-').', menu habis '.$menuHabis->count().' item.';
        @endphp
        <a class="btn btn-kecil btn-wa" target="_blank" rel="noopener" href="{{ \App\Support\Wa::tautan($settings['whatsapp_pemilik'] ?? $settings['whatsapp'], $teksRekap) }}">Kirim Rekap via WhatsApp</a>
    </form>
    <p style="color:var(--redup); font-size:13px">Ringkasan di bawah mengikuti rentang {{ $dari }} s/d {{ $sampai }} dan hanya menghitung transaksi berstatus paid.</p>
</div>

<div class="stat-grid">
    <div class="stat-card">
        <div class="angka">{{ \App\Support\Format::rupiah($omzetRentang) }}</div>
        <div class="label">Omzet Rentang Terpilih</div>
    </div>
    <div class="stat-card">
        <div class="angka">{{ $jumlahRentang }}</div>
        <div class="label">Transaksi Rentang Terpilih</div>
    </div>
    <div class="stat-card">
        <div class="angka">{{ \App\Support\Format::rupiah($labaKotor) }}</div>
        <div class="label">Laba Kotor Rentang</div>
    </div>
    <div class="stat-card">
        <div class="angka">{{ \App\Support\Format::rupiah($labaBersih) }}</div>
        <div class="label">Laba Bersih (setelah pengeluaran {{ \App\Support\Format::rupiah($pengeluaran) }})</div>
    </div>
</div>

<div class="panel">
    <h2>Omzet per Metode Bayar (rentang)</h2>
    <table class="tabel">
        <tr><th>Metode</th><th>Omzet</th></tr>
        <tr><td>Tunai</td><td>{{ \App\Support\Format::rupiah($perMetode['tunai']) }}</td></tr>
        <tr><td>QRIS</td><td>{{ \App\Support\Format::rupiah($perMetode['qris']) }}</td></tr>
    </table>
</div>

<div class="panel">
    <h2>Omzet per Kanal Penjualan (rentang)</h2>
    <table class="tabel">
        <tr><th>Kanal</th><th>Jumlah Transaksi</th><th>Omzet</th></tr>
        @foreach(['dine_in' => 'Makan di Tempat', 'takeaway' => 'Bawa Pulang', 'gofood' => 'GoFood', 'grabfood' => 'GrabFood'] as $kode => $label)
            <tr>
                <td>{{ $label }}</td>
                <td>{{ $perKanalJumlah[$kode] ?? 0 }}</td>
                <td>{{ \App\Support\Format::rupiah($perKanal[$kode] ?? 0) }}</td>
            </tr>
        @endforeach
    </table>
</div>

<div class="panel">
    <h2>Omzet per Kasir (rentang)</h2>
    @if($perKasir->isEmpty())
        <p class="kosong">Belum ada penjualan pada rentang ini.</p>
    @else
        <table class="tabel">
            <tr><th>Kasir</th><th>Jumlah Transaksi</th><th>Omzet</th></tr>
            @foreach($perKasir as $k)
                <tr><td>{{ $k->nama }}</td><td>{{ $k->jumlah }}</td><td>{{ \App\Support\Format::rupiah($k->omzet) }}</td></tr>
            @endforeach
        </table>
    @endif
</div>

<div class="panel">
    <h2>Omzet 7 Hari Terakhir</h2>
    <table class="tabel">
        <tr><th>Tanggal</th><th>Jumlah Transaksi</th><th>Omzet</th></tr>
        @foreach($perHari as $h)
            <tr>
                <td>{{ $h['label'] }}</td>
                <td>{{ $h['transaksi'] }}</td>
                <td>{{ \App\Support\Format::rupiah($h['omzet']) }}</td>
            </tr>
        @endforeach
    </table>
</div>

<div class="panel">
    <h2>Menu Terlaris (rentang)</h2>
    @if($menuTerlarisRentang->isEmpty())
        <p class="kosong">Belum ada penjualan pada rentang ini.</p>
    @else
        <table class="tabel">
            <tr><th>Menu</th><th>Terjual</th><th>Subtotal</th></tr>
            @foreach($menuTerlarisRentang as $m)
                <tr>
                    <td>{{ $m->nama_snapshot }}</td>
                    <td>{{ $m->total_qty }}</td>
                    <td>{{ \App\Support\Format::rupiah($m->total_subtotal) }}</td>
                </tr>
            @endforeach
        </table>
    @endif
</div>

<p style="color:var(--redup); font-size:14px">Semua angka di halaman ini dihitung langsung dari tabel transaksi dan isi transaksi. Transaksi void dan pending tidak dihitung sebagai penjualan.</p>
@endsection
