@extends('layouts.admin')

@section('title', 'Pengembangan Berikutnya')

@section('content')
<h1>Pengembangan Berikutnya</h1>
<p style="color:var(--redup)">Area pratinjau rencana Versi 4. Semua halaman di area ini adalah <strong>tampilan static (SEGERA HADIR)</strong>: belum ada data asli, belum ada logika, dan tombol di dalamnya tidak menjalankan apa pun.</p>
<div class="grid">
    @foreach($fitur as $slug => $info)
        <div class="card-dev">
            <span class="badge badge-dev">SEGERA HADIR</span>
            <h3>{{ $info['judul'] }}</h3>
            <p>{{ $info['ringkas'] }}</p>
            <a class="btn btn-outline btn-kecil" href="{{ route('admin.pengembangan.show', $slug) }}">Lihat Pratinjau</a>
        </div>
    @endforeach
</div>
@endsection
