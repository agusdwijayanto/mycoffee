@extends('layouts.admin')

@section('title', 'Kelola Menu')

@section('content')
<h1>Kelola Menu</h1>
<div class="filter-bar">
    <a class="btn" href="{{ route('admin.menus.create') }}">Tambah Menu</a>
    <form class="search-form" method="GET" action="{{ route('admin.menus.index') }}">
        <input class="input" style="width:220px" type="text" name="q" value="{{ request('q') }}" placeholder="Cari menu...">
        <button class="btn btn-kecil" type="submit">Cari</button>
    </form>
</div>

<table class="tabel">
    <tr><th>Foto</th><th>Nama</th><th>Kategori</th><th>Harga</th><th>Modal</th><th>Stok</th><th>Status</th><th>Aksi</th></tr>
    @forelse($menus as $m)
        <tr>
            <td><img class="thumb" src="{{ $m->fotoUrl() }}" alt="{{ $m->nama }}"></td>
            <td>{{ $m->nama }}
                @if($m->tampil_dari || $m->tampil_sampai)<br><small>Musiman: {{ $m->tampil_dari?->format('d-m-Y') ?? '...' }} s/d {{ $m->tampil_sampai?->format('d-m-Y') ?? '...' }}</small>@endif
                @if($m->ingredients->isNotEmpty())<br><small>Resep: {{ $m->ingredients->map(fn($b) => $b->nama.' '.$b->pivot->qty_per_porsi.$b->satuan)->join(', ') }}</small>@endif
            </td>
            <td>{{ $m->category->nama }}</td>
            <td>{{ \App\Support\Format::rupiah($m->harga) }}@if($m->harga_large) <br><small>Large +{{ \App\Support\Format::rupiah($m->harga_large) }}</small>@endif</td>
            <td>{{ \App\Support\Format::rupiah($m->harga_modal) }}</td>
            <td>{{ $m->stok }}</td>
            <td>
                @if($m->habis)
                    <span class="badge badge-habis">HABIS</span>
                @elseif($m->stokMenipis((int)($settings['stok_minimum'] ?? 5)))
                    <span class="badge badge-menipis">MENIPIS</span>
                @else
                    <span class="badge badge-tersedia">TERSEDIA</span>
                @endif
            </td>
            <td>
                <div class="aksi">
                    <a class="btn btn-kecil btn-outline" href="{{ route('admin.menus.edit', $m) }}">Ubah</a>
                    <a class="btn btn-kecil btn-outline" href="{{ route('admin.stok.index', ['menu_id' => $m->id]) }}">Riwayat Stok</a>
                    <form class="form-inline" method="POST" action="{{ route('admin.menus.toggle', $m) }}">
                        @csrf
                        <button class="btn btn-kecil" type="submit">{{ $m->is_available ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                    </form>
                    <form class="form-inline" method="POST" action="{{ route('admin.menus.destroy', $m) }}" onsubmit="return confirm('Hapus menu ini?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-kecil btn-bahaya" type="submit">Hapus</button>
                    </form>
                </div>
            </td>
        </tr>
    @empty
        <tr><td colspan="8" class="kosong">Belum ada menu.</td></tr>
    @endforelse
</table>

<div class="pagination-wrap">{{ $menus->links() }}</div>
@endsection
