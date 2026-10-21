@extends('layouts.admin')

@section('title', 'POS Kasir')

@section('content')
<h1>POS Kasir</h1>

@if($pendingDibuka)
    <div class="alert alert-sukses">
        Kamu sedang membuka <strong>Pesanan #{{ $pendingDibuka->id }}</strong>
        ({{ $pendingDibuka->customer_name ?: '-' }} / Meja {{ $pendingDibuka->table_number ?: '-' }}).
        Ubah isi keranjang lalu gunakan form bayar untuk menyelesaikan, atau simpan perubahan dari tombol di keranjang.
    </div>
@endif

<div class="panel shift-panel">
    @if($shiftTerbuka)
        <span><strong>Shift terbuka</strong> sejak {{ $shiftTerbuka->opened_at->format('d-m-Y H:i') }} dengan modal awal {{ \App\Support\Format::rupiah($shiftTerbuka->modal_awal) }}.</span>
        <a class="btn btn-kecil" href="{{ route('shift.index') }}">Kelola / Tutup Shift</a>
    @else
        <form method="POST" action="{{ route('shift.buka') }}" class="form-inline-flex">
            @csrf
            <label for="modal_awal"><strong>Buka shift</strong> — modal awal (Rp)</label>
            <input class="input" style="width:160px" id="modal_awal" type="number" name="modal_awal" min="0" value="100000">
            <button class="btn btn-kecil btn-aksen" type="submit">Buka Shift</button>
            <span style="color:var(--redup)">Shift bersifat opsional, kasir tetap bisa bertransaksi tanpa shift.</span>
        </form>
    @endif
    <a class="btn btn-kecil btn-outline" href="{{ route('pos.pesanan') }}">Pesanan Terbuka ({{ $jumlahPending }})</a>
    <a class="btn btn-kecil btn-outline" href="{{ route('barista.index') }}">Layar Barista</a>
</div>

<div class="filter-bar">
    <a href="{{ route('pos.index', array_filter(['q' => request('q')])) }}" class="chip {{ !request('kategori') ? 'aktif' : '' }}">Semua</a>
    @foreach($categories as $cat)
        <a href="{{ route('pos.index', array_filter(['kategori' => $cat->slug, 'q' => request('q')])) }}"
           class="chip {{ request('kategori') === $cat->slug ? 'aktif' : '' }}">{{ $cat->nama }}</a>
    @endforeach
    <form class="search-form" method="GET" action="{{ route('pos.index') }}">
        @if(request('kategori'))
            <input type="hidden" name="kategori" value="{{ request('kategori') }}">
        @endif
        <input class="input" style="width:200px" type="text" name="q" value="{{ request('q') }}" placeholder="Cari menu...">
        <button class="btn btn-kecil" type="submit">Cari</button>
    </form>
</div>

<div class="pos-wrap">
    <div>
        @if($bundles->isNotEmpty())
            <h3 style="margin-top:0">Paket Bundel</h3>
            <div class="pos-menu-grid" style="margin-bottom:18px">
                @foreach($bundles as $b)
                    <form method="POST" action="{{ route('pos.tambahBundel') }}">
                        @csrf
                        <input type="hidden" name="bundle_id" value="{{ $b->id }}">
                        <button type="submit" class="pos-item bundel">
                            <div class="pi-isi">
                                <h4>{{ $b->nama }}</h4>
                                <span class="price">{{ \App\Support\Format::rupiah($b->harga) }}</span><br>
                                <span class="stok">{{ $b->items->map(fn($i) => $i->qty.'x '.$i->menu->nama)->join(', ') }} — klik untuk tambah</span>
                            </div>
                        </button>
                    </form>
                @endforeach
            </div>
        @endif

        <div class="pos-menu-grid">
            @forelse($menus as $m)
                @if($m->habis)
                    <div class="pos-item habis">
                        <span class="stiker-mini">HABIS</span>
                        <img src="{{ $m->fotoUrl() }}" alt="{{ $m->nama }}">
                        <div class="pi-isi">
                            <h4>{{ $m->nama }}</h4>
                            <span class="price">{{ \App\Support\Format::rupiah($m->harga) }}</span><br>
                            <span class="stok">Stok: {{ $m->stok }} — habis, tidak bisa dijual</span>
                        </div>
                    </div>
                @elseif($m->isMinuman())
                    <button type="button" class="pos-item tombol-opsi"
                        data-opsi-tambah
                        data-menu="{{ json_encode(['id' => $m->id, 'nama' => $m->nama, 'harga_large' => $m->harga_large]) }}">
                        <img src="{{ $m->fotoUrl() }}" alt="{{ $m->nama }}">
                        <div class="pi-isi">
                            <h4>{{ $m->nama }}</h4>
                            <span class="price">{{ \App\Support\Format::rupiah($m->harga) }}</span><br>
                            <span class="stok">Stok: {{ $m->stok }} — ketuk untuk pilih opsi</span>
                        </div>
                    </button>
                @else
                    <form method="POST" action="{{ route('pos.tambah') }}">
                        @csrf
                        <input type="hidden" name="menu_id" value="{{ $m->id }}">
                        <button type="submit" class="pos-item">
                            <img src="{{ $m->fotoUrl() }}" alt="{{ $m->nama }}">
                            <div class="pi-isi">
                                <h4>{{ $m->nama }}</h4>
                                <span class="price">{{ \App\Support\Format::rupiah($m->harga) }}</span><br>
                                <span class="stok">Stok: {{ $m->stok }} — klik foto/nama untuk tambah</span>
                            </div>
                        </button>
                    </form>
                @endif
            @empty
                <p class="kosong">Tidak ada menu yang cocok.</p>
            @endforelse
        </div>
        <p style="color:var(--redup); font-size:13px">
            Minuman: ketuk kartu untuk membuka pilihan ukuran, level manis, level es, add-on, dan catatan.
            Makanan dan dessert langsung masuk keranjang sekali ketuk; catatan bisa ditambah lewat tombol Ubah di keranjang.
        </p>
    </div>

    <div class="cart-box" id="cart-box">
        <h2>Keranjang</h2>
        @if($cart->isEmpty())
            <p style="color:var(--redup)">Keranjang masih kosong. Klik menu untuk menambahkan.</p>
        @else
            @foreach($cart as $row)
                <div class="cart-row">
                    <div>
                        <strong>{{ $row['judul'] }}</strong>
                        @if($row['ringkasan'])<br><small>{{ $row['ringkasan'] }}</small>@endif
                        <br>{{ \App\Support\Format::rupiah($row['subtotal']) }}
                        @if(!$row['dari_bundel'])
                            <br><button type="button" class="btn btn-kecil btn-outline btn-ubah"
                                data-opsi-ubah
                                data-baris="{{ json_encode(['key' => $row['key'], 'nama' => $row['judul'], 'minuman' => $row['minuman'], 'harga_large' => $row['harga_large'], 'size' => $row['size'], 'sugar_level' => $row['sugar_level'], 'ice_level' => $row['ice_level'], 'note' => $row['note'], 'addon_ids' => $row['addon_ids'], 'qty' => $row['qty']]) }}">Ubah</button>
                        @endif
                    </div>
                    <div class="qty-ctrl">
                        <form method="POST" action="{{ route('pos.qty', $row['key']) }}">
                            @csrf
                            <input type="hidden" name="qty" value="{{ $row['qty'] - 1 }}">
                            <button class="qty-btn" type="submit">−</button>
                        </form>
                        <span>{{ $row['qty'] }}</span>
                        <form method="POST" action="{{ route('pos.qty', $row['key']) }}">
                            @csrf
                            <input type="hidden" name="qty" value="{{ $row['qty'] + 1 }}">
                            <button class="qty-btn" type="submit">+</button>
                        </form>
                        <form method="POST" action="{{ route('pos.hapus', $row['key']) }}">
                            @csrf
                            <button class="qty-btn" type="submit" title="Hapus item">×</button>
                        </form>
                    </div>
                </div>
            @endforeach

            <div class="cart-total">
                <span>Subtotal</span>
                <span>{{ \App\Support\Format::rupiah($subtotal) }}</span>
            </div>
            <p style="color:var(--redup); font-size:13px; margin:4px 0 12px">
                Diskon, voucher, promo, dan pajak ({{ $settings['tax_percent'] ?? 0 }}%) dihitung saat pembayaran.
            </p>

            <form method="POST" action="{{ route('pos.checkout') }}" id="form-bayar">
                @csrf
                <div class="form-group">
                    <label for="customer_name">Nama Pelanggan (opsional)</label>
                    <input class="input" id="customer_name" type="text" name="customer_name" value="{{ old('customer_name', $pendingDibuka?->customer_name) }}" maxlength="100">
                </div>
                <div class="form-group">
                    <label for="table_number">Nomor Meja (opsional)</label>
                    <input class="input" id="table_number" type="text" name="table_number" value="{{ old('table_number', $pendingDibuka?->table_number) }}" maxlength="20">
                </div>
                <div class="form-group">
                    <label for="channel">Kanal Penjualan</label>
                    <select class="input" id="channel" name="channel">
                        <option value="dine_in">Makan di Tempat</option>
                        <option value="takeaway">Bawa Pulang</option>
                        <option value="gofood">GoFood</option>
                        <option value="grabfood">GrabFood</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="discount_percent">Diskon (%)</label>
                    <input class="input" id="discount_percent" type="number" name="discount_percent" min="0" max="100" step="0.5" value="{{ old('discount_percent', 0) }}">
                </div>
                <div class="form-group">
                    <label for="voucher_code">Kode Voucher (opsional)</label>
                    <input class="input" id="voucher_code" type="text" name="voucher_code" value="{{ old('voucher_code') }}" placeholder="contoh: HEMAT10">
                </div>
                <div class="form-group">
                    <label>Metode Bayar</label>
                    <label class="cek"><input type="radio" name="payment_method" value="tunai" checked onchange="toggleBayar()"> Tunai</label>
                    <label class="cek"><input type="radio" name="payment_method" value="qris" onchange="toggleBayar()"> QRIS</label>
                </div>
                <div class="form-group" id="grup-bayar">
                    <label for="bayar">Uang Bayar (tunai)</label>
                    <input class="input" id="bayar" type="number" name="bayar" min="0" value="{{ old('bayar') }}" placeholder="contoh: 50000">
                </div>
                <div class="qris-box" id="grup-qris" style="display:none">
                    <div class="qris-pola">▦ ▦ ▦<br>▦ ▦ ▦<br>▦ ▦ ▦</div>
                    <strong>QRIS DUMMY - contoh</strong>
                    <p>Kode QR ini hanya contoh tampilan untuk portofolio, bukan QR pembayaran asli. Pembayaran QRIS dianggap pas.</p>
                </div>
                <button class="btn btn-aksen" type="submit" style="width:100%">Bayar &amp; Simpan Transaksi</button>
            </form>

            @if($pendingDibuka)
                <form method="POST" action="{{ route('pos.pesanan.perbarui', $pendingDibuka) }}" style="margin-top:8px">
                    @csrf
                    <button class="btn btn-outline btn-kecil" type="submit" style="width:100%">Simpan Perubahan Pesanan (tetap pending)</button>
                </form>
            @else
                <form method="POST" action="{{ route('pos.simpanPending') }}" style="margin-top:8px" id="form-pending">
                    @csrf
                    <input type="hidden" name="customer_name" id="pending-nama">
                    <input type="hidden" name="table_number" id="pending-meja">
                    <input type="hidden" name="channel" id="pending-kanal" value="dine_in">
                    <input type="hidden" name="discount_percent" id="pending-diskon" value="0">
                    <input type="hidden" name="voucher_code" id="pending-voucher">
                    <button class="btn btn-outline btn-kecil" type="submit" style="width:100%" onclick="salinKePending()">Tahan sebagai Pesanan (bayar belakangan)</button>
                </form>
            @endif

            <form method="POST" action="{{ route('pos.kosongkan') }}" style="margin-top:8px">
                @csrf
                <button class="btn btn-outline btn-kecil" type="submit" style="width:100%">Kosongkan Keranjang</button>
            </form>
            <script>
                function toggleBayar() {
                    var qris = document.querySelector('input[name="payment_method"][value="qris"]').checked;
                    document.getElementById('grup-bayar').style.display = qris ? 'none' : 'block';
                    document.getElementById('grup-qris').style.display = qris ? 'block' : 'none';
                    document.getElementById('bayar').required = !qris;
                }
                function salinKePending() {
                    document.getElementById('pending-nama').value = document.getElementById('customer_name').value;
                    document.getElementById('pending-meja').value = document.getElementById('table_number').value;
                    document.getElementById('pending-kanal').value = document.getElementById('channel').value;
                    document.getElementById('pending-diskon').value = document.getElementById('discount_percent').value;
                    document.getElementById('pending-voucher').value = document.getElementById('voucher_code').value;
                }
                toggleBayar();
            </script>
        @endif
    </div>
</div>

@if($cart->isNotEmpty())
    <button type="button" class="bar-keranjang" id="bar-keranjang">
        <span>Keranjang ({{ $cart->sum('qty') }} item)</span>
        <strong>{{ \App\Support\Format::rupiah($subtotal) }} — ketuk untuk buka</strong>
    </button>
@endif

<div class="modal-opsi" id="modal-opsi" hidden>
    <div class="modal-latar" data-tutup-opsi></div>
    <div class="modal-kotak" role="dialog" aria-modal="true" aria-labelledby="modal-judul">
        <h3 id="modal-judul">Pilih Opsi</h3>
        <form method="POST" id="form-opsi" action="{{ route('pos.tambah') }}">
            @csrf
            <input type="hidden" name="menu_id" id="opsi-menu-id">
            <div class="form-group grup-minuman" id="grup-ukuran">
                <label for="opsi-size">Ukuran</label>
                <select class="input" id="opsi-size" name="size">
                    <option value="Regular">Regular</option>
                    <option value="Large" id="opsi-size-large">Large</option>
                </select>
            </div>
            <div class="form-group grup-minuman">
                <label for="opsi-sugar">Level Manis</label>
                <select class="input" id="opsi-sugar" name="sugar_level">
                    <option value="">Bawaan</option>
                    <option value="Normal">Normal</option>
                    <option value="Half">Half</option>
                    <option value="Low">Low</option>
                    <option value="No">No</option>
                </select>
            </div>
            <div class="form-group grup-minuman">
                <label for="opsi-ice">Level Es</label>
                <select class="input" id="opsi-ice" name="ice_level">
                    <option value="">Bawaan</option>
                    <option value="Normal">Normal</option>
                    <option value="Half">Half</option>
                    <option value="Low">Low</option>
                    <option value="No">No</option>
                </select>
            </div>
            <div class="form-group grup-minuman">
                <label>Add-on</label>
                @foreach($addons as $ad)
                    <label class="cek"><input type="checkbox" name="addons[]" value="{{ $ad->id }}" class="opsi-addon"> {{ $ad->nama }} (+{{ \App\Support\Format::rupiah($ad->harga) }})</label>
                @endforeach
            </div>
            <div class="form-group">
                <label for="opsi-note">Catatan</label>
                <input class="input" id="opsi-note" type="text" name="note" placeholder="Catatan item..." maxlength="255">
            </div>
            <div class="form-group">
                <label for="opsi-qty">Jumlah</label>
                <input class="input" id="opsi-qty" type="number" name="qty" min="1" max="99" value="1">
            </div>
            <div class="modal-aksi">
                <button type="button" class="btn btn-outline" data-tutup-opsi>Batal</button>
                <button type="submit" class="btn btn-aksen" id="opsi-kirim">Tambahkan</button>
            </div>
        </form>
    </div>
</div>

<script>
    (function () {
        var modal = document.getElementById('modal-opsi');
        var form = document.getElementById('form-opsi');
        var judul = document.getElementById('modal-judul');
        var kirim = document.getElementById('opsi-kirim');
        var urlTambah = @json(route('pos.tambah'));
        var urlUbah = @json(url('/pos/ubah'));

        function rupiah(n) { return 'Rp ' + Number(n).toLocaleString('id-ID'); }

        function aturModal(o) {
            form.action = o.aksi;
            judul.textContent = o.judul;
            kirim.textContent = o.kirim;
            document.getElementById('opsi-menu-id').value = o.menuId || '';
            document.querySelectorAll('.grup-minuman').forEach(function (g) {
                g.style.display = o.minuman ? 'block' : 'none';
            });
            document.getElementById('grup-ukuran').style.display =
                (o.minuman && o.hargaLarge !== null && o.hargaLarge !== undefined) ? 'block' : 'none';
            document.getElementById('opsi-size-large').textContent =
                'Large (+' + rupiah(o.hargaLarge || 0) + ')';
            document.getElementById('opsi-size').value = o.size || 'Regular';
            document.getElementById('opsi-sugar').value = o.sugar || '';
            document.getElementById('opsi-ice').value = o.ice || '';
            document.getElementById('opsi-note').value = o.note || '';
            document.getElementById('opsi-qty').value = o.qty || 1;
            document.querySelectorAll('.opsi-addon').forEach(function (c) {
                c.checked = (o.addons || []).map(String).indexOf(c.value) !== -1;
            });
            modal.hidden = false;
        }

        document.addEventListener('click', function (e) {
            var t = e.target.closest('[data-opsi-tambah]');
            if (t) {
                var d = JSON.parse(t.dataset.menu);
                aturModal({
                    aksi: urlTambah, judul: 'Opsi — ' + d.nama, kirim: 'Tambahkan',
                    menuId: d.id, minuman: true, hargaLarge: d.harga_large,
                    size: 'Regular', sugar: 'Normal', ice: 'Normal', note: '', addons: [], qty: 1
                });
                return;
            }
            var u = e.target.closest('[data-opsi-ubah]');
            if (u) {
                var b = JSON.parse(u.dataset.baris);
                aturModal({
                    aksi: urlUbah + '/' + encodeURIComponent(b.key), judul: 'Ubah — ' + b.nama, kirim: 'Simpan Perubahan',
                    menuId: '', minuman: b.minuman, hargaLarge: b.harga_large,
                    size: b.size, sugar: b.sugar_level, ice: b.ice_level, note: b.note, addons: b.addon_ids, qty: b.qty
                });
                return;
            }
            if (e.target.closest('[data-tutup-opsi]')) { modal.hidden = true; }
        });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') modal.hidden = true; });

        var bar = document.getElementById('bar-keranjang');
        var kotak = document.getElementById('cart-box');
        if (bar && kotak) {
            bar.addEventListener('click', function () { kotak.classList.toggle('terbuka'); });
        }
    })();
</script>
@endsection
