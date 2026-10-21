@extends('layouts.admin')

@section('title', 'Detail Transaksi')

@section('content')
<h1>Transaksi #{{ $transaction->id }}
    <span class="badge {{ $transaction->status === 'paid' ? 'badge-tersedia' : ($transaction->status === 'pending' ? 'badge-menipis' : 'badge-habis') }}">{{ strtoupper($transaction->status) }}</span>
</h1>
<div class="panel" style="max-width:680px">
    <p>
        Waktu: {{ $transaction->created_at->format('d-m-Y H:i') }}<br>
        Kasir: {{ $transaction->user->name }} ({{ $transaction->user->role }})<br>
        @if($transaction->queue_number) Nomor Antrean: <strong>{{ $transaction->queue_number }}</strong><br> @endif
        @if($transaction->customer_name) Pelanggan: {{ $transaction->customer_name }}<br> @endif
        @if($transaction->table_number) Meja: {{ $transaction->table_number }}<br> @endif
        Kanal: {{ ['dine_in' => 'Makan di Tempat', 'takeaway' => 'Bawa Pulang', 'gofood' => 'GoFood', 'grabfood' => 'GrabFood'][$transaction->channel] ?? $transaction->channel }}<br>
        Metode Bayar: {{ $transaction->payment_method ? strtoupper($transaction->payment_method) : '-' }}<br>
        Status Pesanan (barista): {{ strtoupper($transaction->status_pesanan) }}
        @if($transaction->status === 'void' && $transaction->void_reason)
            <br>Alasan Void: <strong>{{ $transaction->void_reason }}</strong>
        @endif
    </p>
    <table class="tabel">
        <tr><th>Menu</th><th>Catatan</th><th>Harga</th><th>Qty</th><th>Subtotal</th></tr>
        @foreach($transaction->items as $item)
            <tr>
                <td>{{ $item->nama_snapshot }}</td>
                <td>{{ $item->ringkasanCatatan() ?: '-' }}</td>
                <td>{{ \App\Support\Format::rupiah($item->harga_snapshot) }}</td>
                <td>{{ $item->qty }}</td>
                <td>{{ \App\Support\Format::rupiah($item->subtotal) }}</td>
            </tr>
        @endforeach
        <tr><td colspan="4">Subtotal</td><td>{{ \App\Support\Format::rupiah($transaction->subtotal ?: $transaction->total) }}</td></tr>
        @if($transaction->discount_amount > 0)
            <tr><td colspan="4">Diskon{{ $transaction->discount_note ? ' ('.$transaction->discount_note.')' : '' }}</td><td>-{{ \App\Support\Format::rupiah($transaction->discount_amount) }}</td></tr>
        @endif
        @if($transaction->tax_amount > 0)
            <tr><td colspan="4">Pajak</td><td>{{ \App\Support\Format::rupiah($transaction->tax_amount) }}</td></tr>
        @endif
        <tr>
            <td colspan="4"><strong>Total</strong></td>
            <td><strong>{{ \App\Support\Format::rupiah($transaction->total) }}</strong></td>
        </tr>
        <tr><td colspan="4">Bayar</td><td>{{ \App\Support\Format::rupiah($transaction->bayar) }}</td></tr>
        <tr><td colspan="4">Kembalian</td><td>{{ \App\Support\Format::rupiah($transaction->kembalian) }}</td></tr>
    </table>
    <p style="margin-top:16px">
        <a class="btn" href="{{ route('pos.struk', $transaction) }}">Struk Kasir</a>
        <a class="btn btn-outline" href="{{ route('pos.struk.barista', $transaction) }}">Struk Barista</a>
        <a class="btn btn-outline" href="{{ route('admin.transactions.index') }}">Kembali</a>
    </p>

    @if(auth()->user()->isAdmin() && $transaction->status === 'paid')
        <hr>
        <h3>Void Transaksi (khusus admin)</h3>
        <p style="color:var(--redup)">Void membatalkan transaksi ini: stok menu dan bahan dikembalikan, dan transaksi dikeluarkan dari semua angka laporan.</p>
        <form method="POST" action="{{ route('admin.transactions.void', $transaction) }}" onsubmit="return confirm('Void transaksi ini? Stok akan dikembalikan.')">
            @csrf
            <div class="form-group">
                <label for="void_reason">Alasan Void (wajib)</label>
                <input class="input" id="void_reason" name="void_reason" required maxlength="255" placeholder="contoh: salah input pesanan">
            </div>
            <button class="btn btn-bahaya" type="submit">Void Transaksi</button>
        </form>
    @endif
</div>
@endsection
