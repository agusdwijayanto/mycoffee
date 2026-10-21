@extends('layouts.admin')

@section('title', 'Reservasi')

@section('content')
<h1>Reservasi Meja</h1>
<table class="tabel">
    <tr><th>Tanggal</th><th>Jam</th><th>Nama</th><th>WhatsApp</th><th>Orang</th><th>Catatan</th><th>Status</th><th>Aksi</th></tr>
    @forelse($reservations as $r)
        <tr>
            <td>{{ $r->tanggal->format('d-m-Y') }}</td>
            <td>{{ \Illuminate\Support\Str::substr($r->jam, 0, 5) }}</td>
            <td>{{ $r->nama }}</td>
            <td>{{ $r->whatsapp }}</td>
            <td>{{ $r->jumlah_orang }}</td>
            <td>{{ $r->catatan ?? '-' }}</td>
            <td>{{ ucfirst($r->status) }}</td>
            <td>
                <div class="aksi">
                    <form class="form-inline" method="POST" action="{{ route('admin.reservations.status', $r) }}">
                        @csrf
                        <select class="input input-mini" name="status">
                            @foreach(['menunggu','dikonfirmasi','selesai','dibatalkan'] as $s)
                                <option value="{{ $s }}" {{ $r->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-kecil" type="submit">Ubah</button>
                    </form>
                    <form class="form-inline" method="POST" action="{{ route('admin.reservations.destroy', $r) }}" onsubmit="return confirm('Hapus reservasi ini?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-kecil btn-bahaya" type="submit">Hapus</button>
                    </form>
                </div>
            </td>
        </tr>
    @empty
        <tr><td colspan="8" class="kosong">Belum ada reservasi.</td></tr>
    @endforelse
</table>
<div class="pagination-wrap">{{ $reservations->links() }}</div>
@endsection
