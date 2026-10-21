@extends('layouts.admin')

@section('title', 'Layar Barista')

@section('content')
<meta http-equiv="refresh" content="30">
<h1>Layar Barista — Pesanan Hari Ini</h1>
<p style="color:var(--redup)">Halaman ini memuat ulang otomatis tiap 30 detik. Hanya menampilkan isi pesanan, tanpa harga.</p>

<div class="barista-grid">
    @foreach(['baru' => 'Baru', 'dibuat' => 'Sedang Dibuat', 'selesai' => 'Selesai'] as $status => $label)
        <div class="barista-kolom">
            <h2>{{ $label }} ({{ ($pesanan[$status] ?? collect())->count() }})</h2>
            @forelse($pesanan[$status] ?? [] as $t)
                <div class="barista-card">
                    <strong>#{{ $t->id }}@if($t->queue_number) — Antrean {{ $t->queue_number }}@endif</strong>
                    <small>{{ $t->created_at->format('H:i') }}@if($t->table_number) — Meja {{ $t->table_number }}@endif @if($t->customer_name) — {{ $t->customer_name }}@endif</small>
                    <ul>
                        @foreach($t->items as $item)
                            <li>
                                {{ $item->qty }}x {{ $item->nama_snapshot }}
                                @if($item->ringkasanCatatan())<br><em>{{ $item->ringkasanCatatan() }}</em>@endif
                            </li>
                        @endforeach
                    </ul>
                    <form method="POST" action="{{ route('barista.status', $t) }}">
                        @csrf
                        @if($status === 'baru')
                            <input type="hidden" name="status_pesanan" value="dibuat">
                            <button class="btn btn-kecil btn-aksen" type="submit">Mulai Buat</button>
                        @elseif($status === 'dibuat')
                            <input type="hidden" name="status_pesanan" value="selesai">
                            <button class="btn btn-kecil" type="submit">Tandai Selesai</button>
                        @else
                            <input type="hidden" name="status_pesanan" value="dibuat">
                            <button class="btn btn-kecil btn-outline" type="submit">Kembalikan ke Dibuat</button>
                        @endif
                    </form>
                </div>
            @empty
                <p class="kosong">Tidak ada pesanan.</p>
            @endforelse
        </div>
    @endforeach
</div>
@endsection
