@extends('layouts.admin')

@section('title', 'Kategori')

@section('content')
<h1>Kelola Kategori</h1>
<p><a class="btn" href="{{ route('admin.categories.create') }}">Tambah Kategori</a></p>

<table class="tabel">
    <tr><th>Nama</th><th>Slug</th><th>Jumlah Menu</th><th>Aksi</th></tr>
    @forelse($categories as $cat)
        <tr>
            <td>{{ $cat->nama }}</td>
            <td>{{ $cat->slug }}</td>
            <td>{{ $cat->menus_count }}</td>
            <td>
                <div class="aksi">
                    <a class="btn btn-kecil btn-outline" href="{{ route('admin.categories.edit', $cat) }}">Ubah</a>
                    <form class="form-inline" method="POST" action="{{ route('admin.categories.destroy', $cat) }}" onsubmit="return confirm('Hapus kategori ini? Menu di dalamnya ikut terhapus.')">
                        @csrf @method('DELETE')
                        <button class="btn btn-kecil btn-bahaya" type="submit">Hapus</button>
                    </form>
                </div>
            </td>
        </tr>
    @empty
        <tr><td colspan="4" class="kosong">Belum ada kategori.</td></tr>
    @endforelse
</table>
@endsection
