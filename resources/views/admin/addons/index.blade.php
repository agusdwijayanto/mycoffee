@extends('layouts.admin')

@section('title', 'Add-on')

@section('content')
<h1>Add-on Berbayar</h1>
<div class="panel" style="max-width:560px">
    <h2 style="margin-top:0">{{ isset($addon) ? 'Ubah Add-on' : 'Tambah Add-on' }}</h2>
    <form method="POST" action="{{ isset($addon) ? route('admin.addons.update', $addon) : route('admin.addons.store') }}">
        @csrf
        @if(isset($addon)) @method('PUT') @endif
        <div class="form-group">
            <label for="nama">Nama</label>
            <input class="input" id="nama" name="nama" value="{{ old('nama', $addon->nama ?? '') }}" required>
        </div>
        <div class="form-group">
            <label for="harga">Harga (Rp)</label>
            <input class="input" id="harga" type="number" min="0" name="harga" value="{{ old('harga', $addon->harga ?? 0) }}" required>
        </div>
        <div class="form-group">
            <label style="font-weight:normal"><input type="checkbox" name="aktif" value="1" {{ old('aktif', $addon->aktif ?? true) ? 'checked' : '' }}> Aktif (bisa dipilih di POS)</label>
        </div>
        <button class="btn" type="submit">Simpan</button>
        @if(isset($addon)) <a class="btn btn-outline" href="{{ route('admin.addons.index') }}">Batal</a> @endif
    </form>
</div>
<table class="tabel">
    <tr><th>Nama</th><th>Harga</th><th>Status</th><th>Aksi</th></tr>
    @forelse($addons as $a)
        <tr>
            <td>{{ $a->nama }}</td>
            <td>{{ \App\Support\Format::rupiah($a->harga) }}</td>
            <td>{{ $a->aktif ? 'Aktif' : 'Nonaktif' }}</td>
            <td>
                <div class="aksi">
                    <a class="btn btn-kecil btn-outline" href="{{ route('admin.addons.edit', $a) }}">Ubah</a>
                    <form class="form-inline" method="POST" action="{{ route('admin.addons.destroy', $a) }}" onsubmit="return confirm('Hapus add-on ini?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-kecil btn-bahaya" type="submit">Hapus</button>
                    </form>
                </div>
            </td>
        </tr>
    @empty
        <tr><td colspan="4" class="kosong">Belum ada add-on.</td></tr>
    @endforelse
</table>
@endsection
