@extends('layouts.admin')

@section('title', 'Testimoni')

@section('content')
<h1>Testimoni Pelanggan</h1>
<p style="color:var(--redup)">Testimoni baru dari situs berstatus menunggu. Hanya testimoni yang disetujui yang tampil di Beranda.</p>
<table class="tabel">
    <tr><th>Waktu</th><th>Nama</th><th>Rating</th><th>Isi</th><th>Status</th><th>Aksi</th></tr>
    @forelse($testimonials as $t)
        <tr>
            <td>{{ $t->created_at->format('d-m-Y H:i') }}</td>
            <td>{{ $t->nama }}</td>
            <td>{{ str_repeat('★', $t->rating) }}</td>
            <td>{{ $t->isi }}</td>
            <td>{{ $t->disetujui ? 'Tampil' : 'Menunggu' }}</td>
            <td>
                <div class="aksi">
                    @if(!$t->disetujui)
                        <form class="form-inline" method="POST" action="{{ route('admin.testimonials.setujui', $t) }}">
                            @csrf
                            <button class="btn btn-kecil" type="submit">Setujui</button>
                        </form>
                    @endif
                    <form class="form-inline" method="POST" action="{{ route('admin.testimonials.destroy', $t) }}" onsubmit="return confirm('Hapus testimoni ini?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-kecil btn-bahaya" type="submit">Hapus</button>
                    </form>
                </div>
            </td>
        </tr>
    @empty
        <tr><td colspan="6" class="kosong">Belum ada testimoni.</td></tr>
    @endforelse
</table>
<div class="pagination-wrap">{{ $testimonials->links() }}</div>
@endsection
