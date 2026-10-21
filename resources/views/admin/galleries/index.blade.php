@extends('layouts.admin')

@section('title', 'Gallery')

@section('content')
<h1>Kelola Gallery</h1>

<div class="panel" style="max-width:640px">
    <h2>Tambah Foto</h2>
    <form method="POST" enctype="multipart/form-data" action="{{ route('admin.galleries.store') }}">
        @csrf
        <div class="form-group">
            <label for="judul">Judul</label>
            <input class="input" id="judul" name="judul" value="{{ old('judul') }}" required>
        </div>
        <div class="form-group">
            <label for="foto_file">Pilih dari file yang ada</label>
            <select class="input" id="foto_file" name="foto_file">
                <option value="">— pilih file —</option>
                @foreach($fileTersedia as $f)
                    <option value="{{ $f }}">{{ $f }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="foto_upload">atau unggah foto baru</label>
            <input class="input" id="foto_upload" type="file" name="foto_upload" accept="image/*">
        </div>
        <button class="btn" type="submit">Tambah</button>
    </form>
</div>

<div class="gallery-grid">
    @forelse($galleries as $g)
        <figure>
            <img src="{{ $g->fotoUrl() }}" alt="{{ $g->judul }}">
            <figcaption>
                {{ $g->judul }}<br>
                <a href="{{ route('admin.galleries.edit', $g) }}" style="color:#ffd9a0">Ubah</a>
                <form class="form-inline" method="POST" action="{{ route('admin.galleries.destroy', $g) }}" onsubmit="return confirm('Hapus foto ini dari gallery?')">
                    @csrf @method('DELETE')
                    <button type="submit" style="background:none;border:0;color:#ffb3ab;cursor:pointer;font:inherit">Hapus</button>
                </form>
            </figcaption>
        </figure>
    @empty
        <p class="kosong">Belum ada foto gallery.</p>
    @endforelse
</div>
@endsection
