@extends('layouts.admin')

@section('title', 'Pengeluaran')

@section('content')
<h1>Pengeluaran</h1>
<div class="filter-bar">
    <a class="btn" href="{{ route('admin.expenses.create') }}">Catat Pengeluaran</a>
</div>
<table class="tabel">
    <tr><th>Tanggal</th><th>Judul</th><th>Kategori</th><th>Nominal</th><th>Catatan</th><th>Aksi</th></tr>
    @forelse($expenses as $e)
        <tr>
            <td>{{ $e->tanggal->format('d-m-Y') }}</td>
            <td>{{ $e->judul }}</td>
            <td>{{ $e->kategori }}</td>
            <td>{{ \App\Support\Format::rupiah($e->nominal) }}</td>
            <td>{{ $e->catatan ?? '-' }}</td>
            <td>
                <div class="aksi">
                    <a class="btn btn-kecil btn-outline" href="{{ route('admin.expenses.edit', $e) }}">Ubah</a>
                    <form class="form-inline" method="POST" action="{{ route('admin.expenses.destroy', $e) }}" onsubmit="return confirm('Hapus pengeluaran ini?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-kecil btn-bahaya" type="submit">Hapus</button>
                    </form>
                </div>
            </td>
        </tr>
    @empty
        <tr><td colspan="6" class="kosong">Belum ada pengeluaran.</td></tr>
    @endforelse
</table>
<div class="pagination-wrap">{{ $expenses->links() }}</div>
@endsection
