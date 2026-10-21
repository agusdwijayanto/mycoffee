@extends('layouts.admin')

@section('title', 'Voucher')

@section('content')
<h1>Voucher Promo</h1>
<div class="panel" style="max-width:560px">
    <h2 style="margin-top:0">{{ isset($voucher) ? 'Ubah Voucher' : 'Tambah Voucher' }}</h2>
    <form method="POST" action="{{ isset($voucher) ? route('admin.vouchers.update', $voucher) : route('admin.vouchers.store') }}">
        @csrf
        @if(isset($voucher)) @method('PUT') @endif
        <div class="form-group">
            <label for="kode">Kode</label>
            <input class="input" id="kode" name="kode" value="{{ old('kode', $voucher->kode ?? '') }}" required style="text-transform:uppercase">
        </div>
        <div class="form-group">
            <label for="tipe">Tipe</label>
            <select class="input" id="tipe" name="tipe">
                <option value="persen" {{ old('tipe', $voucher->tipe ?? '') === 'persen' ? 'selected' : '' }}>Persen (%)</option>
                <option value="nominal" {{ old('tipe', $voucher->tipe ?? '') === 'nominal' ? 'selected' : '' }}>Nominal (Rp)</option>
            </select>
        </div>
        <div class="form-group">
            <label for="nilai">Nilai</label>
            <input class="input" id="nilai" type="number" min="0" name="nilai" value="{{ old('nilai', $voucher->nilai ?? 0) }}" required>
        </div>
        <div class="form-group">
            <label for="berlaku_sampai">Berlaku Sampai (kosongkan bila tanpa batas)</label>
            <input class="input" id="berlaku_sampai" type="date" name="berlaku_sampai" value="{{ old('berlaku_sampai', isset($voucher) ? $voucher->berlaku_sampai?->format('Y-m-d') : '') }}">
        </div>
        <div class="form-group">
            <label style="font-weight:normal"><input type="checkbox" name="aktif" value="1" {{ old('aktif', $voucher->aktif ?? true) ? 'checked' : '' }}> Aktif</label>
        </div>
        <button class="btn" type="submit">Simpan</button>
        @if(isset($voucher)) <a class="btn btn-outline" href="{{ route('admin.vouchers.index') }}">Batal</a> @endif
    </form>
</div>
<table class="tabel">
    <tr><th>Kode</th><th>Tipe</th><th>Nilai</th><th>Berlaku Sampai</th><th>Status</th><th>Aksi</th></tr>
    @forelse($vouchers as $v)
        <tr>
            <td><strong>{{ $v->kode }}</strong></td>
            <td>{{ $v->tipe }}</td>
            <td>{{ $v->tipe === 'persen' ? $v->nilai.'%' : \App\Support\Format::rupiah($v->nilai) }}</td>
            <td>{{ $v->berlaku_sampai?->format('d-m-Y') ?? 'Tanpa batas' }}</td>
            <td>{{ $v->masihBerlaku() ? 'Berlaku' : 'Tidak berlaku' }}</td>
            <td>
                <div class="aksi">
                    <a class="btn btn-kecil btn-outline" href="{{ route('admin.vouchers.edit', $v) }}">Ubah</a>
                    <form class="form-inline" method="POST" action="{{ route('admin.vouchers.destroy', $v) }}" onsubmit="return confirm('Hapus voucher ini?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-kecil btn-bahaya" type="submit">Hapus</button>
                    </form>
                </div>
            </td>
        </tr>
    @empty
        <tr><td colspan="6" class="kosong">Belum ada voucher.</td></tr>
    @endforelse
</table>
@endsection
