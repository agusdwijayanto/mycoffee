@extends('layouts.admin')

@section('title', 'Bahan Baku')

@section('content')
<h1>Bahan Baku</h1>
<p style="color:var(--redup)">Stok bahan berkurang otomatis mengikuti resep tiap menu saat terjadi penjualan. Menu menjadi HABIS bila bahan untuk satu porsi tidak cukup.</p>
<div class="panel" style="max-width:560px">
    <h2 style="margin-top:0">{{ isset($ingredient) ? 'Ubah Bahan' : 'Tambah Bahan' }}</h2>
    <form method="POST" action="{{ isset($ingredient) ? route('admin.ingredients.update', $ingredient) : route('admin.ingredients.store') }}">
        @csrf
        @if(isset($ingredient)) @method('PUT') @endif
        <div class="form-group">
            <label for="nama">Nama Bahan</label>
            <input class="input" id="nama" name="nama" value="{{ old('nama', $ingredient->nama ?? '') }}" required>
        </div>
        <div class="form-group">
            <label for="satuan">Satuan</label>
            <input class="input" id="satuan" name="satuan" value="{{ old('satuan', $ingredient->satuan ?? '') }}" placeholder="ml, gr, pcs" required>
        </div>
        <div class="form-group">
            <label for="stok">Stok</label>
            <input class="input" id="stok" type="number" step="0.01" min="0" name="stok" value="{{ old('stok', $ingredient->stok ?? 0) }}" required>
        </div>
        <button class="btn" type="submit">Simpan</button>
        @if(isset($ingredient)) <a class="btn btn-outline" href="{{ route('admin.ingredients.index') }}">Batal</a> @endif
    </form>
</div>
<table class="tabel">
    <tr><th>Nama</th><th>Satuan</th><th>Stok</th><th>Dipakai di Menu</th><th>Aksi</th></tr>
    @forelse($ingredients as $b)
        <tr>
            <td>{{ $b->nama }}</td>
            <td>{{ $b->satuan }}</td>
            <td>{{ rtrim(rtrim(number_format($b->stok, 2, ',', '.'), '0'), ',') }}</td>
            <td>{{ $b->menus->map(fn($m) => $m->nama)->join(', ') ?: '-' }}</td>
            <td>
                <div class="aksi">
                    <a class="btn btn-kecil btn-outline" href="{{ route('admin.ingredients.edit', $b) }}">Ubah</a>
                    <form class="form-inline" method="POST" action="{{ route('admin.ingredients.destroy', $b) }}" onsubmit="return confirm('Hapus bahan ini?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-kecil btn-bahaya" type="submit">Hapus</button>
                    </form>
                </div>
            </td>
        </tr>
    @empty
        <tr><td colspan="5" class="kosong">Belum ada bahan baku.</td></tr>
    @endforelse
</table>
@endsection
