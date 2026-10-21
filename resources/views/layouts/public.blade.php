<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', $settings['brand_name']) — {{ $settings['brand_name'] }}</title>
    <meta name="description" content="{{ $settings['brand_name'] }} — {{ $settings['tagline'] }}. {{ $settings['alamat'] }}.">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $settings['brand_name'] }}">
    <meta property="og:title" content="{{ $settings['brand_name'] }} — {{ $settings['tagline'] }}">
    <meta property="og:description" content="Kedai kopi di {{ $settings['alamat'] }}. Buka {{ $settings['jam_buka'] }} WIB. Lihat menu terbaru kami.">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('images/gallery/tampilan-depan-mycoffee.webp') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<header class="topbar">
    <div class="container topbar-in">
        <a class="brand" href="{{ route('home') }}">
            {{ $settings['brand_name'] }}
            <small>{{ $settings['tagline'] }}</small>
        </a>
        <nav class="nav">
            <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'aktif' : '' }}">{{ __('publik.beranda') }}</a>
            <a href="{{ route('tentang') }}" class="{{ request()->routeIs('tentang') ? 'aktif' : '' }}">{{ __('publik.tentang') }}</a>
            <a href="{{ route('menu') }}" class="{{ request()->routeIs('menu') ? 'aktif' : '' }}">{{ __('publik.menu') }}</a>
            <a href="{{ route('gallery') }}" class="{{ request()->routeIs('gallery') ? 'aktif' : '' }}">{{ __('publik.gallery') }}</a>
            <a href="{{ route('kontak') }}" class="{{ request()->routeIs('kontak') ? 'aktif' : '' }}">{{ __('publik.kontak') }}</a>
            <span class="lang-switch">
                <a href="{{ route('bahasa', 'id') }}" class="{{ app()->getLocale() === 'id' ? 'aktif' : '' }}">ID</a>
                <a href="{{ route('bahasa', 'en') }}" class="{{ app()->getLocale() === 'en' ? 'aktif' : '' }}">EN</a>
            </span>
            @auth
                <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('pos.index') }}">{{ __('publik.panel') }}</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="linklike">{{ __('publik.keluar') }}</button>
                </form>
            @else
                <a href="{{ route('login') }}">{{ __('publik.login') }}</a>
            @endauth
        </nav>
    </div>
</header>

<main>
    @yield('content')
</main>

<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div>
                <h4>{{ $settings['brand_name'] }}</h4>
                <p>{{ $settings['tagline'] }}</p>
                <p>Instagram: {{ $settings['instagram'] }}</p>
            </div>
            <div>
                <h4>{{ __('publik.kontak') }}</h4>
                <p>{{ $settings['alamat'] }}</p>
                <p>Telepon: {{ $settings['telepon'] }}</p>
                <p>WhatsApp: {{ $settings['whatsapp'] }}</p>
                <p>Email: {{ $settings['email'] }}</p>
            </div>
            <div>
                <h4>{{ __('publik.jam_buka') }}</h4>
                <p>{{ __('publik.setiap_hari') }}</p>
                <p>{{ $settings['jam_buka'] }} WIB</p>
                @if(!empty($settings['jam_buka_libur']))
                    <p>Hari libur: {{ $settings['jam_buka_libur'] }}</p>
                @endif
            </div>
        </div>
        <p class="copy">© {{ date('Y') }} {{ $settings['brand_name'] }}. Semua data di situs ini adalah data dummy untuk portofolio.</p>
    </div>
</footer>
</body>
</html>
