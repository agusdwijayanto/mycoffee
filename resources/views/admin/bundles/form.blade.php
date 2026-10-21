@extends('layouts.admin')

@section('title', $bundle->exists ? 'Ubah Bundel' : 'Tambah Bundel')

@section('content')
<h1>{{ $bundle->exists ? 'Ubah Bundel' : 'Tambah Bundel' }}</h1>
<div class="panel" style="max-width:640px">
    <form method="POST" action="{{ $bundle->exists ? route('admin.bundles.update', $bundle) : route('admin.bundles.store') }}">
        @csrf
        @if($bundle->exists) @method('PUT') @endif
        <div class="form-group">
            <label for="nama">Nama Bundel</label>
            <input class="input" id="nama" name="nama" value="{{ old('nama', $bundle->nama) }}" required>
        </div>
        <div class="form-group">
            <label for="harga">Harga Bundel (Rp)</label>
            <input class="input" id="harga" type="number" min="0" name="harga" value="{{ old('harga', $bundle->harga) }}" required>
        </div>
        <div class="form-group">
            <label>Komponen (pilih menu dan jumlahnya)</label>
            @php $terpilih = $bundle->exists ? $bundle->items->pluck('qty', 'menu_id') : collect(); @endphp
            @for($r = 0; $r < 4; $r++)
                <div class="form-inline-flex" style="margin-bottom:6px">
                    <select class="input" name="komponen[{{ $r }}][menu_id]">
                        <option value="">— pilih menu —</option>
                        @foreach($menus as $m)
                            <option value="{{ $m->id }}" {{ ($terpilih->keys()[$r] ?? null) == $m->id ? 'selected' : '' }}>{{ $m->nama }} ({{ \App\Support\Format::rupiah($m->harga) }})</option>
                        @endforeach
                    </select>
                    <input class="input" style="width:90px" type="number" min="1" name="komponen[{{ $r }}][qty]" value="{{ $terpilih->values()[$r] ?? 1 }}">
                </div>
            @endfor
        </div>
        <div class="form-group">
            <label style="font-weight:normal"><input type="checkbox" name="aktif" value="1" {{ old('aktif', $bundle->exists ? $bundle->aktif : true) ? 'checked' : '' }}> Aktif (tampil di POS)</label>
        </div>
        <button class="btn" type="submit">Simpan</button>
        <a class="btn btn-outline" href="{{ route('admin.bundles.index') }}">Batal</a>
    </form>
</div>
@endsection
