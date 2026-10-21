@extends('layouts.admin')

@section('title', 'Shift Kasir')

@section('content')
<h1>Shift Kasir</h1>

<div class="panel">
    @if($terbuka)
        <h2 style="margin-top:0">Shift Terbuka</h2>
        <p>
            Dibuka: {{ $terbuka->opened_at->format('d-m-Y H:i') }}<br>
            Modal awal: {{ \App\Support\Format::rupiah($terbuka->modal_awal) }}<br>
            Penjualan selama shift ini: {{ $ringkasanTerbuka['jumlah'] }} transaksi —
            Tunai {{ \App\Support\Format::rupiah($ringkasanTerbuka['tunai']) }},
            QRIS {{ \App\Support\Format::rupiah($ringkasanTerbuka['qris']) }}
        </p>
        <form method="POST" action="{{ route('shift.tutup', $terbuka) }}" style="max-width:420px">
            @csrf
            <div class="form-group">
                <label for="uang_fisik">Uang Fisik di Laci (tunai, Rp)</label>
                <input class="input" id="uang_fisik" type="number" name="uang_fisik" min="0" required placeholder="hitung uang tunai di laci">
            </div>
            <div class="form-group">
                <label for="catatan">Catatan (opsional)</label>
                <input class="input" id="catatan" type="text" name="catatan" maxlength="255">
            </div>
            <button class="btn btn-aksen" type="submit">Tutup Shift</button>
            <p style="color:var(--redup); font-size:13px">Selisih dihitung dari uang fisik dikurangi total tunai menurut sistem (modal awal tidak dihitung sebagai penjualan).</p>
        </form>
    @else
        <h2 style="margin-top:0">Belum Ada Shift Terbuka</h2>
        <form method="POST" action="{{ route('shift.buka') }}" style="max-width:420px">
            @csrf
            <div class="form-group">
                <label for="modal_awal">Modal Awal (Rp)</label>
                <input class="input" id="modal_awal" type="number" name="modal_awal" min="0" value="100000" required>
            </div>
            <button class="btn btn-aksen" type="submit">Buka Shift</button>
        </form>
    @endif
</div>

<div class="panel">
    <h2>Riwayat Shift</h2>
    <table class="tabel">
        <tr><th>Kasir</th><th>Dibuka</th><th>Ditutup</th><th>Modal</th><th>Uang Fisik</th><th>Selisih</th><th>Catatan</th></tr>
        @forelse($shifts as $s)
            <tr>
                <td>{{ $s->user->name }}</td>
                <td>{{ $s->opened_at->format('d-m-Y H:i') }}</td>
                <td>{{ $s->closed_at?->format('d-m-Y H:i') ?? 'Masih terbuka' }}</td>
                <td>{{ \App\Support\Format::rupiah($s->modal_awal) }}</td>
                <td>{{ $s->uang_fisik !== null ? \App\Support\Format::rupiah($s->uang_fisik) : '-' }}</td>
                <td>{{ $s->selisih !== null ? \App\Support\Format::rupiah($s->selisih) : '-' }}</td>
                <td>{{ $s->catatan ?? '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="7" class="kosong">Belum ada shift.</td></tr>
        @endforelse
    </table>
    <div class="pagination-wrap">{{ $shifts->links() }}</div>
</div>
@endsection
