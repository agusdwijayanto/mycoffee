@extends('layouts.admin')

@section('title', $category->exists ? 'Ubah Kategori' : 'Tambah Kategori')

@section('content')
<h1>{{ $category->exists ? 'Ubah Kategori' : 'Tambah Kategori' }}</h1>
<div class="panel" style="max-width:520px">
    <form method="POST" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}">
        @csrf
        @if($category->exists) @method('PUT') @endif
        <div class="form-group">
            <label for="nama">Nama Kategori</label>
            <input class="input" id="nama" name="nama" value="{{ old('nama', $category->nama) }}" required>
        </div>
        <button class="btn" type="submit">Simpan</button>
        <a class="btn btn-outline" href="{{ route('admin.categories.index') }}">Batal</a>
    </form>
</div>
@endsection
