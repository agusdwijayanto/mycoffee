@extends('layouts.public')

@section('title', 'Beranda')

@section('content')
@if($banner)
    <div class="promo-banner">{{ $banner }}</div>
@endif
<section class="hero">
    @if($hero)
        <img src="{{ $hero->fotoUrl() }}" alt="{{ $hero->judul }}">
    @endif
    <div class="hero-text">
        <div class="container">
            <h1>{{ $settings['brand_name'] }}</h1>
            <p>{{ $settings['tagline'] }} — buka setiap hari {{ $settings['jam_buka'] }} WIB.</p>
            <p style="margin-top:16px">
                <a href="{{ route('menu') }}" class="btn btn-aksen">{{ __('publik.lihat_menu') }}</a>
                <a href="{{ route('kontak') }}" class="btn btn-outline" style="color:#fff;border-color:#fff">{{ __('publik.kontak') }}</a>
            </p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section-title">Selamat Datang di {{ $settings['brand_name'] }}</h2>
        <p class="section-sub">
            Kedai kopi bernuansa kayu hangat di Jakarta. Kami menyajikan kopi dari biji pilihan,
            makanan rumahan, dan tempat yang nyaman untuk bekerja maupun bersantai bersama teman.
        </p>
        <a href="{{ route('menu') }}" class="btn btn-aksen">{{ __('publik.lihat_menu') }}</a>
        <a href="{{ route('tentang') }}" class="btn btn-outline">{{ __('publik.tentang_kami') }}</a>
    </div>
</section>

<section class="section alt">
    <div class="container">
        <h2 class="section-title">Menu Unggulan</h2>
        <p class="section-sub">Pilihan favorit pelanggan kami. Menu yang habis tetap tampil dengan stiker HABIS.</p>
        <div class="grid">
            @foreach($unggulan as $menu)
                @include('partials.menu-card', ['menu' => $menu])
            @endforeach
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section-title">Suasana Kedai</h2>
        <p class="section-sub">Intip suasana {{ $settings['brand_name'] }} sebelum datang.</p>
        <div class="strip-gallery">
            @foreach($galleryPilihan as $g)
                <a href="{{ route('gallery') }}" title="{{ $g->judul }}">
                    <img src="{{ $g->fotoUrl() }}" alt="{{ $g->judul }}" loading="lazy">
                </a>
            @endforeach
        </div>
        <p style="margin-top:20px"><a href="{{ route('gallery') }}" class="btn">Lihat Semua Gallery</a></p>
    </div>
</section>

@if($testimoni->isNotEmpty())
<section class="section alt">
    <div class="container">
        <h2 class="section-title">Kata Pelanggan</h2>
        <p class="section-sub">Testimoni yang sudah disetujui admin.</p>
        <div class="grid">
            @foreach($testimoni as $t)
                <div class="testimoni-card">
                    <div class="bintang">{{ str_repeat('★', $t->rating) }}</div>
                    <p>"{{ $t->isi }}"</p>
                    <strong>— {{ $t->nama }}</strong>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="section alt">
    <div class="container">
        <h2 class="section-title">Kunjungi Kami</h2>
        <div class="info-grid">
            <div class="info-box">
                <h3>Alamat</h3>
                <p>{{ $settings['alamat'] }}</p>
            </div>
            <div class="info-box">
                <h3>Jam Buka</h3>
                <p>Setiap hari, {{ $settings['jam_buka'] }} WIB</p>
                @if(!empty($settings['jam_buka_libur']))
                    <p>Hari libur: {{ $settings['jam_buka_libur'] }}</p>
                @endif
            </div>
            <div class="info-box">
                <h3>Kontak</h3>
                <p>WhatsApp: {{ $settings['whatsapp'] }}<br>Email: {{ $settings['email'] }}</p>
            </div>
        </div>
    </div>
</section>
@endsection
