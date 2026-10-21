@extends('layouts.public')

@section('title', 'Tentang')

@section('content')
<section class="section">
    <div class="container">
        <h2 class="section-title">Tentang {{ $settings['brand_name'] }}</h2>
        <p>
            {{ $settings['brand_name'] }} adalah kedai kopi dengan nuansa kayu hangat dan suasana rumah yang tenang
            di tengah Jakarta. Nama {{ $settings['brand_name'] }} dipilih karena sederhana dan universal:
            mudah diingat siapa pun, di mana pun.
        </p>
        <p>
            Kami percaya kopi yang baik tidak harus rumit. Biji kopi pilihan diseduh dengan teliti oleh barista kami,
            ditemani makanan rumahan seperti nasi goreng dan pasta, serta camilan manis untuk menutup hari.
            Ruang kami dilengkapi rak buku, tanaman hijau, dan tempat duduk yang nyaman untuk membaca,
            bekerja, atau sekadar berbincang.
        </p>
        <p>
            Menu di situs ini terhubung langsung dengan kasir kami. Kalau sebuah menu habis terjual,
            menu tersebut tetap tampil di halaman Menu dengan stiker HABIS, supaya kamu tahu menu itu ada,
            hanya sedang tidak tersedia hari ini.
        </p>
    </div>
</section>

<section class="section alt">
    <div class="container">
        <h2 class="section-title">Suasana Kami</h2>
        <p class="section-sub">Beberapa sudut favorit di {{ $settings['brand_name'] }}.</p>
        <div class="gallery-grid">
            @foreach($gallery as $g)
                <figure>
                    <img src="{{ $g->fotoUrl() }}" alt="{{ $g->judul }}">
                    <figcaption>{{ $g->judul }}</figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>
@endsection
