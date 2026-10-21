@extends('layouts.admin')

@section('title', $expense->exists ? 'Ubah Pengeluaran' : 'Catat Pengeluaran')

@section('content')
<h1>{{ $expense->exists ? 'Ubah Pengeluaran' : 'Catat Pengeluaran' }}</h1>
<div class="panel" style="max-width:560px">
    <form method="POST" action="{{ $expense->exists ? route('admin.expenses.update', $expense) : route('admin.expenses.store') }}">
        @csrf
        @if($expense->exists) @method('PUT') @endif
        <div class="form-group">
            <label for="judul">Judul</label>
            <input class="input" id="judul" name="judul" value="{{ old('judul', $expense->judul) }}" required>
        </div>
        <div class="form-group">
            <label for="kategori">Kategori</label>
            <input class="input" id="kategori" name="kategori" value="{{ old('kategori', $expense->kategori) }}" placeholder="Bahan Baku, Operasional, Gaji..." required>
        </div>
        <div class="form-group">
            <label for="nominal">Nominal (Rp)</label>
            <input class="input" id="nominal" type="number" min="0" name="nominal" value="{{ old('nominal', $expense->nominal) }}" required>
        </div>
        <div class="form-group">
            <label for="tanggal">Tanggal</label>
            <input class="input" id="tanggal" type="date" name="tanggal" value="{{ old('tanggal', $expense->tanggal?->format('Y-m-d') ?? date('Y-m-d')) }}" required>
        </div>
        <div class="form-group">
            <label for="catatan">Catatan</label>
            <textarea class="input" id="catatan" name="catatan" rows="2">{{ old('catatan', $expense->catatan) }}</textarea>
        </div>
        <button class="btn" type="submit">Simpan</button>
        <a class="btn btn-outline" href="{{ route('admin.expenses.index') }}">Batal</a>
    </form>
</div>
@endsection
