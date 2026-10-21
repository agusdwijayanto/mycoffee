@extends('layouts.admin')

@section('title', 'Bundel')

@section('content')
<h1>Paket Bundel</h1>
<div class="filter-bar">
    <a class="btn" href="{{ route('admin.bundles.create') }}">Tambah Bundel</a>
</div>
<table class="tabel">
    <tr><th>Nama</th><th>Komponen</th><th>Harga Komponen</th><th>Harga Bundel</th><th>Hemat</th><th>Status</th><th>Aksi</th></tr>
    @forelse($bundles as $b)
        <tr>
            <td>{{ $b->nama }}</td>
            <td>{{ $b->items->map(fn($i) => $i->qty.'x '.$i->menu->nama)->join(', ') }}</td>
            <td>{{ \App\Support\Format::rupiah($b->hargaKomponen()) }}</td>
            <td>{{ \App\Support\Format::rupiah($b->harga) }}</td>
            <td>{{ \App\Support\Format::rupiah(max(0, $b->hargaKomponen() - $b->harga)) }}</td>
            <td>{{ $b->aktif ? 'Aktif' : 'Nonaktif' }}</td>
            <td>
                <div class="aksi">
                    <a class="btn btn-kecil btn-outline" href="{{ route('admin.bundles.edit', $b) }}">Ubah</a>
                    <form class="form-inline" method="POST" action="{{ route('admin.bundles.destroy', $b) }}" onsubmit="return confirm('Hapus bundel ini?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-kecil btn-bahaya" type="submit">Hapus</button>
                    </form>
                </div>
            </td>
        </tr>
    @empty
        <tr><td colspan="7" class="kosong">Belum ada bundel.</td></tr>
    @endforelse
</table>
@endsection
