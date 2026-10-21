@extends('layouts.admin')

@section('title', 'Ubah Gallery')

@section('content')
<h1>Ubah Foto Gallery</h1>
<div class="panel" style="max-width:640px">
    <p><img src="{{ $gallery->fotoUrl() }}" alt="{{ $gallery->judul }}" style="max-width:280px; border-radius:8px"></p>
    <form method="POST" enctype="multipart/form-data" action="{{ route('admin.galleries.update', $gallery) }}">
        @csrf @method('PUT')
        <div class="form-group">
            <label for="judul">Judul</label>
            <input class="input" id="judul" name="judul" value="{{ old('judul', $gallery->judul) }}" required>
        </div>
        <div class="form-group">
            <label for="foto_file">Ganti dengan file yang ada</label>
            <select class="input" id="foto_file" name="foto_file">
                <option value="">— tetap pakai foto sekarang —</option>
                @foreach($fileTersedia as $f)
                    <option value="{{ $f }}" {{ $gallery->foto === $f ? 'selected' : '' }}>{{ $f }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="foto_upload">atau unggah foto baru</label>
            <input class="input" id="foto_upload" type="file" name="foto_upload" accept="image/*">
        </div>
        <button class="btn" type="submit">Simpan</button>
        <a class="btn btn-outline" href="{{ route('admin.galleries.index') }}">Batal</a>
    </form>
</div>
@endsection
