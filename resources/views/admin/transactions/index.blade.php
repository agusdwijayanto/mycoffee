@extends('layouts.admin')

@section('title', 'Riwayat Transaksi')

@section('content')
<h1>Riwayat Transaksi</h1>
<table class="tabel">
    <tr><th>No</th><th>Waktu</th><th>Kasir</th><th>Metode</th><th>Kanal</th><th>Status</th><th>Total</th><th>Bayar</th><th>Kembalian</th><th></th></tr>
    @forelse($transactions as $t)
        <tr>
            <td>#{{ $t->id }}</td>
            <td>{{ $t->created_at->format('d-m-Y H:i') }}</td>
            <td>{{ $t->user->name }}</td>
            <td>{{ $t->payment_method ? strtoupper($t->payment_method) : '-' }}</td>
            <td>{{ ['dine_in' => 'Dine-in', 'takeaway' => 'Takeaway', 'gofood' => 'GoFood', 'grabfood' => 'GrabFood'][$t->channel] ?? $t->channel }}</td>
            <td><span class="badge {{ $t->status === 'paid' ? 'badge-tersedia' : ($t->status === 'pending' ? 'badge-menipis' : 'badge-habis') }}">{{ strtoupper($t->status) }}</span></td>
            <td>{{ \App\Support\Format::rupiah($t->total) }}</td>
            <td>{{ \App\Support\Format::rupiah($t->bayar) }}</td>
            <td>{{ \App\Support\Format::rupiah($t->kembalian) }}</td>
            <td>
                <a class="btn btn-kecil btn-outline" href="{{ route('admin.transactions.show', $t) }}">Detail</a>
                <a class="btn btn-kecil" href="{{ route('pos.struk', $t) }}">Struk</a>
            </td>
        </tr>
    @empty
        <tr><td colspan="10" class="kosong">Belum ada transaksi.</td></tr>
    @endforelse
</table>
<div class="pagination-wrap">{{ $transactions->links() }}</div>
@endsection
