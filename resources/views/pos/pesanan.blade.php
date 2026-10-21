@extends('layouts.admin')

@section('title', 'Pesanan Terbuka')

@section('content')
<h1>Pesanan Terbuka (Pending)</h1>
<p style="color:var(--redup)">Pesanan yang ditahan per meja/nama. Stok sudah diamankan sejak pesanan dibuat. Buka pesanan untuk menambah item atau membayarnya.</p>

@forelse($pendings as $t)
    <div class="panel">
        <h2 style="margin-top:0">Pesanan #{{ $t->id }}
            @if($t->queue_number) — Antrean {{ $t->queue_number }} @endif
        </h2>
        <p>
            {{ $t->created_at->format('d-m-Y H:i') }} — Kasir: {{ $t->user->name }}<br>
            Pelanggan: <strong>{{ $t->customer_name ?: '-' }}</strong> — Meja: <strong>{{ $t->table_number ?: '-' }}</strong> —
            Kanal: {{ $t->channel }} — Total sementara: <strong>{{ \App\Support\Format::rupiah($t->total) }}</strong>
        </p>
        <table class="tabel">
            <tr><th>Menu</th><th>Catatan</th><th>Qty</th><th>Subtotal</th></tr>
            @foreach($t->items as $item)
                <tr>
                    <td>{{ $item->nama_snapshot }}</td>
                    <td>{{ $item->ringkasanCatatan() ?: '-' }}</td>
                    <td>{{ $item->qty }}</td>
                    <td>{{ \App\Support\Format::rupiah($item->subtotal) }}</td>
                </tr>
            @endforeach
        </table>
        <p style="margin-top:12px">
            <form class="form-inline" method="POST" action="{{ route('pos.pesanan.buka', $t) }}">
                @csrf
                <button class="btn btn-aksen" type="submit">Buka &amp; Bayar / Tambah Item</button>
            </form>
            <a class="btn btn-outline" href="{{ route('pos.struk', $t) }}">Lihat Struk</a>
            <form class="form-inline" method="POST" action="{{ route('pos.pesanan.batal', $t) }}" onsubmit="return confirm('Batalkan pesanan ini? Stok akan dikembalikan.')">
                @csrf
                <button class="btn btn-bahaya" type="submit">Batalkan Pesanan</button>
            </form>
        </p>
    </div>
@empty
    <div class="panel"><p class="kosong">Tidak ada pesanan terbuka saat ini.</p></div>
@endforelse

<p><a class="btn btn-outline" href="{{ route('pos.index') }}">Kembali ke POS</a></p>
@endsection
