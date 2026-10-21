@extends('layouts.admin')

@section('title', $info['judul'])

@section('content')
<div class="banner-segera">
    <h1>SEGERA HADIR</h1>
    <p>Halaman ini adalah pratinjau static fitur <strong>{{ $info['judul'] }}</strong>. Belum berfungsi dan tidak terhubung ke data asli.</p>
</div>

<h1>{{ $info['judul'] }}</h1>
<p>{{ $info['ringkas'] }}</p>

<div class="panel">
    <h2 style="margin-top:0">Contoh Tampilan (data DUMMY)</h2>
    @if($slug === 'multi-cabang')
        <table class="tabel">
            <tr><th>Cabang</th><th>Omzet Hari Ini (DUMMY)</th><th>Transaksi (DUMMY)</th></tr>
            <tr><td>MyCoffee Jakarta Pusat (DUMMY)</td><td>Rp 1.250.000</td><td>48</td></tr>
            <tr><td>MyCoffee Jakarta Selatan (DUMMY)</td><td>Rp 980.000</td><td>37</td></tr>
            <tr><td>MyCoffee Tangerang (DUMMY)</td><td>Rp 760.000</td><td>29</td></tr>
        </table>
    @elseif($slug === 'aplikasi-pelanggan')
        <p><strong>Contoh alur (DUMMY):</strong> Pelanggan membuka aplikasi, memilih menu, membayar, lalu menerima nomor antrean untuk mengambil pesanan di toko.</p>
        <div class="stat-grid">
            <div class="stat-card"><div class="angka">128</div><div class="label">Unduhan (DUMMY)</div></div>
            <div class="stat-card"><div class="angka">36</div><div class="label">Pesanan via Aplikasi Hari Ini (DUMMY)</div></div>
        </div>
    @elseif($slug === 'payment-gateway')
        <table class="tabel">
            <tr><th>Penyedia</th><th>Status (DUMMY)</th></tr>
            <tr><td>QRIS Otomatis</td><td>Belum terhubung (DUMMY)</td></tr>
            <tr><td>E-Wallet</td><td>Belum terhubung (DUMMY)</td></tr>
            <tr><td>Virtual Account</td><td>Belum terhubung (DUMMY)</td></tr>
        </table>
        <p>Mode live membutuhkan akun bisnis dan kunci server dari penyedia payment gateway.</p>
    @elseif($slug === 'integrasi-akuntansi')
        <table class="tabel">
            <tr><th>Data</th><th>Status Sinkron (DUMMY)</th></tr>
            <tr><td>Penjualan harian</td><td>Menunggu integrasi (DUMMY)</td></tr>
            <tr><td>Pengeluaran</td><td>Menunggu integrasi (DUMMY)</td></tr>
            <tr><td>Laporan pajak bulanan</td><td>Menunggu integrasi (DUMMY)</td></tr>
        </table>
    @else
        <table class="tabel">
            <tr><th>Menu</th><th>Perkiraan Kebutuhan Besok (DUMMY)</th></tr>
            <tr><td>Latte</td><td>24 porsi (DUMMY)</td></tr>
            <tr><td>Cappuccino</td><td>18 porsi (DUMMY)</td></tr>
            <tr><td>Nasi Goreng</td><td>11 porsi (DUMMY)</td></tr>
        </table>
        <p>Ramalan dihitung dari pola transaksi beberapa minggu terakhir (contoh angka di atas adalah DUMMY).</p>
    @endif
    <p style="margin-top:14px">
        <button class="btn" disabled title="Fitur belum tersedia">Tombol Contoh (nonaktif)</button>
        <a class="btn btn-outline" href="{{ route('admin.pengembangan.index') }}">Kembali ke Pengembangan Berikutnya</a>
    </p>
</div>
@endsection
