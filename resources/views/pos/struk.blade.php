<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Struk #{{ $transaction->id }} — {{ $settings['brand_name'] }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="{{ request('lebar') === '58' ? 'struk-58' : '' }}">
<div class="struk-wrap">
    @if(session('sukses'))
        <div class="alert alert-sukses no-print">{{ session('sukses') }}</div>
    @endif
    <div class="struk">
        <h1>{{ strtoupper($settings['brand_name']) }}</h1>
        <p class="tengah">
            {{ $settings['alamat'] }}<br>
            Telp: {{ $settings['telepon'] }}
        </p>
        @if($transaction->queue_number)
            <div class="antrean">No. Antrean<br><span>{{ $transaction->queue_number }}</span></div>
        @endif
        <hr>
        <table>
            <tr><td>No. Transaksi</td><td class="kanan">#{{ $transaction->id }}</td></tr>
            <tr><td>Tanggal</td><td class="kanan">{{ $transaction->created_at->format('d-m-Y H:i') }}</td></tr>
            <tr><td>Kasir</td><td class="kanan">{{ $transaction->user->name }}</td></tr>
            @if($transaction->customer_name)
                <tr><td>Pelanggan</td><td class="kanan">{{ $transaction->customer_name }}</td></tr>
            @endif
            @if($transaction->table_number)
                <tr><td>Meja</td><td class="kanan">{{ $transaction->table_number }}</td></tr>
            @endif
            <tr><td>Kanal</td><td class="kanan">{{ ['dine_in' => 'Makan di Tempat', 'takeaway' => 'Bawa Pulang', 'gofood' => 'GoFood', 'grabfood' => 'GrabFood'][$transaction->channel] ?? $transaction->channel }}</td></tr>
            <tr><td>Status</td><td class="kanan">{{ strtoupper($transaction->status) }}</td></tr>
        </table>
        <hr>
        <table>
            @foreach($transaction->items as $item)
                <tr>
                    <td>{{ $item->nama_snapshot }}<br>
                        <small>{{ \App\Support\Format::rupiah($item->harga_snapshot) }} x {{ $item->qty }}</small>
                        @if($item->ringkasanCatatan())
                            <br><small><em>{{ $item->ringkasanCatatan() }}</em></small>
                        @endif
                    </td>
                    <td class="kanan">{{ \App\Support\Format::rupiah($item->subtotal) }}</td>
                </tr>
            @endforeach
        </table>
        <hr>
        <table>
            <tr><td>Subtotal</td><td class="kanan">{{ \App\Support\Format::rupiah($transaction->subtotal ?: $transaction->total) }}</td></tr>
            @if($transaction->discount_amount > 0)
                <tr><td>Diskon{{ $transaction->discount_note ? ' ('.$transaction->discount_note.')' : '' }}</td><td class="kanan">-{{ \App\Support\Format::rupiah($transaction->discount_amount) }}</td></tr>
            @endif
            @if($transaction->tax_amount > 0)
                <tr><td>Pajak ({{ rtrim(rtrim(number_format($transaction->tax_percent_snapshot, 2, ',', '.'), '0'), ',') }}%)</td><td class="kanan">{{ \App\Support\Format::rupiah($transaction->tax_amount) }}</td></tr>
            @endif
            <tr class="total"><td>Total</td><td class="kanan">{{ \App\Support\Format::rupiah($transaction->total) }}</td></tr>
            @if($transaction->status === 'paid')
                <tr><td>Bayar ({{ $transaction->payment_method === 'qris' ? 'QRIS' : 'tunai' }})</td><td class="kanan">{{ \App\Support\Format::rupiah($transaction->bayar) }}</td></tr>
                <tr><td>Kembalian</td><td class="kanan">{{ \App\Support\Format::rupiah($transaction->kembalian) }}</td></tr>
            @else
                <tr><td colspan="2" class="tengah"><strong>BELUM DIBAYAR</strong></td></tr>
            @endif
        </table>
        <hr>
        <p class="tengah">Terima kasih sudah mampir.<br>Ditunggu kedatangannya kembali.</p>
    </div>
    <p class="no-print" style="text-align:center; margin-top:16px">
        <button class="btn" onclick="window.print()">Cetak Struk</button>
        <a class="btn btn-outline" href="{{ route('pos.struk', [$transaction, 'lebar' => 58]) }}">Cetak 58mm</a>
        <a class="btn btn-outline" href="{{ route('pos.struk.barista', $transaction) }}">Struk Barista</a>
        <a class="btn btn-outline" href="{{ route('pos.index') }}">Transaksi Baru</a>
    </p>
</div>
</body>
</html>
