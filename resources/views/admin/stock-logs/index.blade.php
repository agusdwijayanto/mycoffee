@extends('layouts.admin')

@section('title', 'Riwayat Stok')

@section('content')
<h1>Riwayat Perubahan Stok</h1>
<div class="filter-bar">
    <form class="search-form" method="GET" action="{{ route('admin.stok.index') }}">
        <select class="input" name="menu_id" style="width:220px">
            <option value="">Semua menu</option>
            @foreach($menus as $m)
                <option value="{{ $m->id }}" {{ (string) request('menu_id') === (string) $m->id ? 'selected' : '' }}>{{ $m->nama }}</option>
            @endforeach
        </select>
        <button class="btn btn-kecil" type="submit">Filter</button>
    </form>
</div>
<table class="tabel">
    <tr><th>Waktu</th><th>Menu</th><th>Jenis</th><th>Perubahan</th><th>Stok Sebelum</th><th>Stok Sesudah</th><th>Oleh</th><th>Keterangan</th></tr>
    @forelse($logs as $log)
        <tr>
            <td>{{ $log->created_at->format('d-m-Y H:i') }}</td>
            <td>{{ $log->menu->nama }}</td>
            <td>{{ ucfirst($log->jenis) }}</td>
            <td>{{ $log->perubahan > 0 ? '+' : '' }}{{ $log->perubahan }}</td>
            <td>{{ $log->stok_sebelum }}</td>
            <td>{{ $log->stok_sesudah }}</td>
            <td>{{ $log->user->name ?? '-' }}</td>
            <td>{{ $log->keterangan ?? '-' }}</td>
        </tr>
    @empty
        <tr><td colspan="8" class="kosong">Belum ada riwayat perubahan stok.</td></tr>
    @endforelse
</table>
<div class="pagination-wrap">{{ $logs->links() }}</div>
@endsection
