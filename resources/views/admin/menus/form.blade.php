@extends('layouts.admin')

@section('title', $menu->exists ? 'Ubah Menu' : 'Tambah Menu')

@section('content')
<h1>{{ $menu->exists ? 'Ubah Menu' : 'Tambah Menu' }}</h1>
<div class="panel" style="max-width:680px">
    <form method="POST" enctype="multipart/form-data"
          action="{{ $menu->exists ? route('admin.menus.update', $menu) : route('admin.menus.store') }}">
        @csrf
        @if($menu->exists) @method('PUT') @endif

        <div class="form-group">
            <label for="nama">Nama Menu</label>
            <input class="input" id="nama" name="nama" value="{{ old('nama', $menu->nama) }}" required>
        </div>
        <div class="form-group">
            <label for="category_id">Kategori</label>
            <select class="input" id="category_id" name="category_id" required>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('category_id', $menu->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->nama }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="deskripsi">Deskripsi</label>
            <textarea class="input" id="deskripsi" name="deskripsi" rows="3">{{ old('deskripsi', $menu->deskripsi) }}</textarea>
        </div>
        <div class="form-group">
            <label for="harga">Harga Jual Regular (Rp)</label>
            <input class="input" id="harga" type="number" min="0" name="harga" value="{{ old('harga', $menu->harga) }}" required>
        </div>
        <div class="form-group">
            <label for="harga_modal">Harga Modal (Rp, untuk hitung laba kotor)</label>
            <input class="input" id="harga_modal" type="number" min="0" name="harga_modal" value="{{ old('harga_modal', $menu->harga_modal ?? 0) }}">
        </div>
        <div class="form-group">
            <label for="harga_large">Tambahan Harga Ukuran Large (Rp, khusus minuman — kosongkan bila tidak ada)</label>
            <input class="input" id="harga_large" type="number" min="0" name="harga_large" value="{{ old('harga_large', $menu->harga_large) }}">
        </div>
        <div class="form-group">
            <label for="stok">Stok</label>
            <input class="input" id="stok" type="number" min="0" name="stok" value="{{ old('stok', $menu->stok ?? 20) }}" required>
            @if($menu->exists)
                <small style="color:var(--redup)">Mengubah stok di sini tercatat otomatis di Riwayat Stok sebagai perubahan manual.</small>
            @endif
        </div>
        <div class="form-group">
            <label>Periode Tampil (menu musiman — kosongkan untuk selalu tampil)</label>
            <div class="form-inline-flex">
                <input class="input" type="date" name="tampil_dari" value="{{ old('tampil_dari', $menu->tampil_dari?->format('Y-m-d')) }}">
                <span>s/d</span>
                <input class="input" type="date" name="tampil_sampai" value="{{ old('tampil_sampai', $menu->tampil_sampai?->format('Y-m-d')) }}">
            </div>
        </div>
        <div class="form-group">
            <label for="foto">Foto Menu (opsional{{ $menu->exists ? ', kosongkan bila tidak diganti' : '' }})</label>
            @if($menu->exists && $menu->foto)
                <p><img src="{{ $menu->fotoUrl() }}" alt="{{ $menu->nama }}" style="width:140px; border-radius:8px"></p>
            @endif
            <input class="input" id="foto" type="file" name="foto" accept="image/*">
        </div>

        @if($ingredients->isNotEmpty())
            <div class="form-group">
                <label>Resep Bahan Baku (takaran per porsi, kosongkan/0 bila tidak memakai)</label>
                @php $resepMenu = $menu->exists ? $menu->ingredients->pluck('pivot.qty_per_porsi', 'id') : collect(); @endphp
                @foreach($ingredients as $bahan)
                    <div class="form-inline-flex" style="margin-bottom:6px">
                        <span style="min-width:160px">{{ $bahan->nama }} (stok {{ rtrim(rtrim(number_format($bahan->stok, 2, ',', '.'), '0'), ',') }} {{ $bahan->satuan }})</span>
                        <input class="input" style="width:140px" type="number" step="0.01" min="0" name="resep[{{ $bahan->id }}]"
                               value="{{ old('resep.'.$bahan->id, $resepMenu[$bahan->id] ?? '') }}" placeholder="takaran">
                        <span>{{ $bahan->satuan }}/porsi</span>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="form-group">
            <label style="font-weight:normal">
                <input type="checkbox" name="is_available" value="1" {{ old('is_available', $menu->exists ? $menu->is_available : true) ? 'checked' : '' }}>
                Menu tersedia (matikan untuk membuat menu berstatus HABIS secara manual)
            </label>
        </div>
        <button class="btn" type="submit">Simpan</button>
        <a class="btn btn-outline" href="{{ route('admin.menus.index') }}">Batal</a>
    </form>
</div>
@endsection
