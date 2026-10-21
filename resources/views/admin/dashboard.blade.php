@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<h1>Dashboard</h1>

<div class="panel absensi-panel">
    <h2 style="margin-top:0">Absensi Saya Hari Ini</h2>
    @if(!$absensiHariIni)
        <p>Kamu belum absen masuk hari ini.</p>
    @elseif(!$absensiHariIni->jam_keluar_at)
        <p>Sudah masuk pukul <strong>{{ $absensiHariIni->jam_masuk_at->format('H:i') }}</strong>, belum absen pulang.</p>
    @else
        <p>Masuk {{ $absensiHariIni->jam_masuk_at->format('H:i') }} — Pulang {{ $absensiHariIni->jam_keluar_at->format('H:i') }}. Absensi hari ini lengkap.</p>
    @endif
    <p style="color:var(--redup); font-size:13px">Gunakan tombol absen di bagian bawah menu samping untuk mencatat masuk/pulang.</p>
</div>

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
        <div class="angka">{{ $transaksiHariIni }}</div>
        <div class="label">Transaksi Hari Ini</div>
    </div>
    <div class="stat-card">
        <div class="angka">{{ $transaksiBulanIni }}</div>
        <div class="label">Transaksi Bulan Ini</div>
    </div>
</div>

<div class="panel">
    <h2>Menu Terlaris</h2>
    @if($menuTerlaris->isEmpty())
        <p class="kosong">Belum ada penjualan.</p>
    @else
        <table class="tabel">
            <tr><th>Menu</th><th>Terjual</th><th>Subtotal</th></tr>
            @foreach($menuTerlaris as $m)
                <tr>
                    <td>{{ $m->nama_snapshot }}</td>
                    <td>{{ $m->total_qty }}</td>
                    <td>{{ \App\Support\Format::rupiah($m->total_subtotal) }}</td>
                </tr>
            @endforeach
        </table>
    @endif
</div>

<div class="panel">
    <h2>Stok Menipis (stok 1 s/d {{ $batasMenipis }})</h2>
    @if($stokMenipis->isEmpty())
        <p>Tidak ada menu yang stoknya menipis.</p>
    @else
        <table class="tabel">
            <tr><th>Menu</th><th>Kategori</th><th>Stok</th><th>Status</th></tr>
            @foreach($stokMenipis as $m)
                <tr>
                    <td>{{ $m->nama }}</td>
                    <td>{{ $m->category->nama }}</td>
                    <td>{{ $m->stok }}</td>
                    <td><span class="badge badge-menipis">MENIPIS</span></td>
                </tr>
            @endforeach
        </table>
        <p style="color:var(--redup); font-size:13px">Batas stok menipis diatur di Pengaturan (stok_minimum, bawaan 5). Segera restok sebelum benar-benar habis.</p>
    @endif
</div>

<div class="panel">
    <h2>Menu Habis</h2>
    @if($menuHabis->isEmpty())
        <p>Semua menu tersedia.</p>
    @else
        <table class="tabel">
            <tr><th>Menu</th><th>Kategori</th><th>Stok</th><th>Status</th></tr>
            @foreach($menuHabis as $m)
                <tr>
                    <td>{{ $m->nama }}</td>
                    <td>{{ $m->category->nama }}</td>
                    <td>{{ $m->stok }}</td>
                    <td><span class="badge badge-habis">HABIS</span></td>
                </tr>
            @endforeach
        </table>
    @endif
</div>

<div class="panel">
    <h2>Transaksi Terbaru</h2>
    @if($transaksiTerbaru->isEmpty())
        <p class="kosong">Belum ada transaksi.</p>
    @else
        <table class="tabel">
            <tr><th>Waktu</th><th>Kasir</th><th>Status</th><th>Total</th><th></th></tr>
            @foreach($transaksiTerbaru as $t)
                <tr>
                    <td>{{ $t->created_at->format('d-m-Y H:i') }}</td>
                    <td>{{ $t->user->name }}</td>
                    <td>{{ strtoupper($t->status) }}</td>
                    <td>{{ \App\Support\Format::rupiah($t->total) }}</td>
                    <td><a href="{{ route('admin.transactions.show', $t) }}">Detail</a></td>
                </tr>
            @endforeach
        </table>
    @endif
</div>
@endsection
