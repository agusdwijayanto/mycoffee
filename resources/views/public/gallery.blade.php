@extends('layouts.public')

@section('title', 'Gallery')

@section('content')
<section class="section">
    <div class="container">
        <h2 class="section-title">Gallery {{ $settings['brand_name'] }}</h2>
        <p class="section-sub">Suasana kedai, dari pagi yang cerah sampai malam yang hangat.</p>
        <div class="gallery-grid">
            @foreach($galleries as $g)
                <figure>
                    <img src="{{ $g->fotoUrl() }}" alt="{{ $g->judul }}">
                    <figcaption>{{ $g->judul }}</figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>
@endsection
