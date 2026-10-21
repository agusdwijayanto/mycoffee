<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Panel') — {{ $settings['brand_name'] }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<div class="admin-wrap" id="admin-wrap">
    <div class="sidebar-overlay" id="sidebar-overlay"></div>
    <aside class="sidebar" id="sidebar">
        <button type="button" class="sidebar-tutup" id="sidebar-tutup" aria-label="Tutup menu">&times;</button>
        <a class="brand" href="{{ route('home') }}">{{ $settings['brand_name'] }}</a>
        @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'aktif' : '' }}">Dashboard</a>
            <a href="{{ route('admin.laporan') }}" class="{{ request()->routeIs('admin.laporan') ? 'aktif' : '' }}">Laporan</a>
        @endif
        <a href="{{ route('pos.index') }}" class="{{ request()->routeIs('pos.index') ? 'aktif' : '' }}">POS Kasir</a>
        <a href="{{ route('pos.pesanan') }}" class="{{ request()->routeIs('pos.pesanan') ? 'aktif' : '' }}">Pesanan Terbuka
            @php $jp = \App\Models\Transaction::where('status', 'pending')->count(); @endphp
            @if($jp > 0) <span class="badge badge-menipis">{{ $jp }}</span> @endif
        </a>
        <a href="{{ route('barista.index') }}" class="{{ request()->routeIs('barista.*') ? 'aktif' : '' }}">Layar Barista</a>
        <a href="{{ route('shift.index') }}" class="{{ request()->routeIs('shift.*') ? 'aktif' : '' }}">Shift Saya</a>
        <a href="{{ route('admin.transactions.index') }}" class="{{ request()->routeIs('admin.transactions.*') ? 'aktif' : '' }}">Riwayat Transaksi</a>
        @if(auth()->user()->isAdmin())
            <span class="side-judul">Kelola</span>
            <a href="{{ route('admin.categories.index') }}" class="{{ request()->routeIs('admin.categories.*') ? 'aktif' : '' }}">Kategori</a>
            <a href="{{ route('admin.menus.index') }}" class="{{ request()->routeIs('admin.menus.*') ? 'aktif' : '' }}">Kelola Menu</a>
            <a href="{{ route('admin.ingredients.index') }}" class="{{ request()->routeIs('admin.ingredients.*') ? 'aktif' : '' }}">Bahan Baku</a>
            <a href="{{ route('admin.stok.index') }}" class="{{ request()->routeIs('admin.stok.*') ? 'aktif' : '' }}">Riwayat Stok</a>
            <a href="{{ route('admin.addons.index') }}" class="{{ request()->routeIs('admin.addons.*') ? 'aktif' : '' }}">Add-on</a>
            <a href="{{ route('admin.bundles.index') }}" class="{{ request()->routeIs('admin.bundles.*') ? 'aktif' : '' }}">Bundel</a>
            <a href="{{ route('admin.vouchers.index') }}" class="{{ request()->routeIs('admin.vouchers.*') ? 'aktif' : '' }}">Voucher</a>
            <a href="{{ route('admin.expenses.index') }}" class="{{ request()->routeIs('admin.expenses.*') ? 'aktif' : '' }}">Pengeluaran</a>
            <a href="{{ route('admin.galleries.index') }}" class="{{ request()->routeIs('admin.galleries.*') ? 'aktif' : '' }}">Gallery</a>
            <span class="side-judul">Pelanggan</span>
            <a href="{{ route('admin.testimonials.index') }}" class="{{ request()->routeIs('admin.testimonials.*') ? 'aktif' : '' }}">Testimoni</a>
            <a href="{{ route('admin.reservations.index') }}" class="{{ request()->routeIs('admin.reservations.*') ? 'aktif' : '' }}">Reservasi</a>
            <a href="{{ route('admin.schedules.index') }}" class="{{ request()->routeIs('admin.schedules.*') ? 'aktif' : '' }}">Jadwal Karyawan</a>
            <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'aktif' : '' }}">Pengguna & PIN</a>
            <a href="{{ route('admin.pengembangan.index') }}" class="{{ request()->routeIs('admin.pengembangan.*') ? 'aktif' : '' }}">Pengembangan Berikutnya</a>
            <a href="{{ route('admin.settings.edit') }}" class="{{ request()->routeIs('admin.settings.*') ? 'aktif' : '' }}">Pengaturan</a>
        @endif
        <a href="{{ route('home') }}">Lihat Situs</a>
        <a href="{{ asset('manual-book.html') }}" target="_blank" rel="noopener">Manual Book</a>
        <div class="siapa">
            {{ auth()->user()->name }}<br>
            Peran: {{ auth()->user()->role }}
            @php $absen = \App\Models\Attendance::where('user_id', auth()->id())->whereDate('tanggal', today())->first(); @endphp
            <form method="POST" action="{{ route('absensi.toggle') }}" style="margin:8px 0">
                @csrf
                @if(!$absen)
                    <button type="submit" class="btn btn-kecil btn-aksen">Absen Masuk</button>
                @elseif(!$absen->jam_keluar_at)
                    <span class="absen-info">Masuk: {{ $absen->jam_masuk_at->format('H:i') }}</span>
                    <button type="submit" class="btn btn-kecil">Absen Pulang</button>
                @else
                    <span class="absen-info">Absen lengkap: {{ $absen->jam_masuk_at->format('H:i') }} - {{ $absen->jam_keluar_at->format('H:i') }}</span>
                @endif
            </form>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">Keluar</button>
            </form>
        </div>
    </aside>
    <main class="admin-main">
        <div class="admin-header">
            <button type="button" class="tombol-menu" id="tombol-menu" aria-label="Buka menu">&#9776;</button>
            <span class="admin-header-judul">{{ $settings['brand_name'] }} — Panel</span>
        </div>
        @if(session('sukses'))
            <div class="alert alert-sukses">{{ session('sukses') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-error">
                <ul>
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @yield('content')
    </main>
</div>
<script>
    // Drawer sidebar (pola AdminLTE): hanya aktif di layar <= 992px lewat CSS.
    (function () {
        var wrap = document.getElementById('admin-wrap');
        var buka = document.getElementById('tombol-menu');
        var tutup = document.getElementById('sidebar-tutup');
        var overlay = document.getElementById('sidebar-overlay');
        function setDrawer(terbuka) { wrap.classList.toggle('sidebar-terbuka', terbuka); }
        if (buka) buka.addEventListener('click', function () { setDrawer(true); });
        if (tutup) tutup.addEventListener('click', function () { setDrawer(false); });
        if (overlay) overlay.addEventListener('click', function () { setDrawer(false); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setDrawer(false); });
    })();
</script>
</body>
</html>
