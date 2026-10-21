<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Struk Barista #{{ $transaction->id }} — {{ $settings['brand_name'] }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<div class="struk-wrap">
    <div class="struk struk-barista">
        <h1>BARISTA</h1>
        <p class="tengah">
            Pesanan #{{ $transaction->id }}
            @if($transaction->queue_number) — Antrean {{ $transaction->queue_number }} @endif
            <br>{{ $transaction->created_at->format('d-m-Y H:i') }}
            @if($transaction->table_number)<br>Meja: <strong>{{ $transaction->table_number }}</strong>@endif
            @if($transaction->customer_name)<br>Nama: <strong>{{ $transaction->customer_name }}</strong>@endif
        </p>
        <hr>
        <table>
            @foreach($transaction->items as $item)
                <tr>
                    <td>
                        <strong>{{ $item->qty }}x {{ $item->nama_snapshot }}</strong>
                        @if($item->size && $item->size !== 'Regular')<br><small>Ukuran: {{ $item->size }}</small>@endif
                        @if($item->sugar_level)<br><small>Manis: {{ $item->sugar_level }}</small>@endif
                        @if($item->ice_level)<br><small>Es: {{ $item->ice_level }}</small>@endif
                        @foreach($item->addons_snapshot ?? [] as $addon)
                            <br><small>+ {{ $addon['nama'] }}</small>
                        @endforeach
                        @if($item->note)<br><small><em>Catatan: {{ $item->note }}</em></small>@endif
                    </td>
                </tr>
            @endforeach
        </table>
        <hr>
        <p class="tengah">Status: {{ strtoupper($transaction->status_pesanan) }}</p>
    </div>
    <p class="no-print" style="text-align:center; margin-top:16px">
        <button class="btn" onclick="window.print()">Cetak Struk Barista</button>
        <a class="btn btn-outline" href="{{ route('barista.index') }}">Layar Barista</a>
        <a class="btn btn-outline" href="{{ route('pos.struk', $transaction) }}">Struk Kasir</a>
    </p>
</div>
</body>
</html>
