@extends('layouts.public')

@section('title', 'Menu')

@section('content')
<section class="section">
    <div class="container">
        <h2 class="section-title">Menu {{ $settings['brand_name'] }}</h2>
        <p class="section-sub">Menu terhubung langsung dengan kasir. Menu yang habis tetap tampil dengan stiker HABIS.</p>

        <div class="filter-bar">
            <a href="{{ route('menu', array_filter(['q' => request('q')])) }}" class="chip {{ !request('kategori') ? 'aktif' : '' }}">Semua</a>
            @foreach($categories as $cat)
                <a href="{{ route('menu', array_filter(['kategori' => $cat->slug, 'q' => request('q')])) }}"
                   class="chip {{ request('kategori') === $cat->slug ? 'aktif' : '' }}">{{ $cat->nama }}</a>
            @endforeach
            <form class="search-form" method="GET" action="{{ route('menu') }}">
                @if(request('kategori'))
                    <input type="hidden" name="kategori" value="{{ request('kategori') }}">
                @endif
                <input class="input" style="width:220px" type="text" name="q" value="{{ request('q') }}" placeholder="Cari menu...">
                <button class="btn btn-kecil" type="submit">Cari</button>
            </form>
        </div>

        @if($menus->isEmpty())
            <p class="kosong">Tidak ada menu yang cocok dengan pencarian kamu.</p>
        @elseif(!request('kategori') && !request('q'))
            @foreach($categories as $cat)
                @php $isiKategori = $menus->where('category_id', $cat->id); @endphp
                @if($isiKategori->isNotEmpty())
                    <h3 class="menu-kat-judul">{{ $cat->nama }}</h3>
                    <div class="grid" style="margin-bottom:34px">
                        @foreach($isiKategori as $menu)
                            @include('partials.menu-card', ['menu' => $menu, 'besar' => true])
                        @endforeach
                    </div>
                @endif
            @endforeach
        @else
            <div class="grid">
                @foreach($menus as $menu)
                    @include('partials.menu-card', ['menu' => $menu, 'besar' => true])
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
