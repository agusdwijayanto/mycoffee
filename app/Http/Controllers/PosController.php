<?php

namespace App\Http\Controllers;

use App\Models\Addon;
use App\Models\Bundle;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Shift;
use App\Models\Transaction;
use App\Support\Format;
use App\Support\HitungTransaksi;
use App\Support\StokService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::orderBy('nama')->get();

        $menus = Menu::with(['category', 'ingredients'])
            ->tampilSekarang()
            ->when($request->filled('kategori'), function ($q) use ($request) {
                $q->whereHas('category', fn ($c) => $c->where('slug', $request->kategori));
            })
            ->when($request->filled('q'), fn ($q) => $q->where('nama', 'like', '%'.$request->q.'%'))
            ->orderBy('nama')
            ->get();

        $addons = Addon::where('aktif', true)->orderBy('nama')->get();
        $bundles = Bundle::with('items.menu')->where('aktif', true)->orderBy('nama')->get();

        [$cartItems, $lines] = $this->isiKeranjang();
        $subtotal = (int) collect($lines)->sum('subtotal');
        $cart = $this->barisKeranjang($cartItems);

        $shiftTerbuka = Shift::where('user_id', $request->user()->id)
            ->whereNull('closed_at')->latest('opened_at')->first();

        $pendingDibuka = null;
        if (session('pending_id')) {
            $pendingDibuka = Transaction::find(session('pending_id'));
            if (! $pendingDibuka || $pendingDibuka->status !== 'pending') {
                session()->forget('pending_id');
                $pendingDibuka = null;
            }
        }

        $jumlahPending = Transaction::where('status', 'pending')->count();

        return view('pos.index', compact(
            'categories', 'menus', 'addons', 'bundles', 'cart', 'subtotal',
            'shiftTerbuka', 'pendingDibuka', 'jumlahPending'
        ));
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'menu_id' => ['required', 'exists:menus,id'],
            'size' => ['nullable', 'in:Regular,Large'],
            'sugar_level' => ['nullable', 'in:No,Low,Half,Normal'],
            'ice_level' => ['nullable', 'in:No,Low,Half,Normal'],
            'note' => ['nullable', 'string', 'max:255'],
            'qty' => ['nullable', 'integer', 'min:1', 'max:99'],
            'addons' => ['nullable', 'array'],
            'addons.*' => ['exists:addons,id'],
        ]);
        $qtyBaru = (int) ($data['qty'] ?? 1);

        $menu = Menu::with('category')->findOrFail($data['menu_id']);

        if (! $menu->sedangTampil()) {
            return back()->withErrors(['keranjang' => 'Menu '.$menu->nama.' sedang tidak ditampilkan.']);
        }

        if ($menu->habis) {
            return back()->withErrors(['keranjang' => 'Menu '.$menu->nama.' sedang habis, tidak bisa ditambahkan ke keranjang.']);
        }

        $minuman = $menu->isMinuman();
        $size = $minuman ? ($data['size'] ?? 'Regular') : 'Regular';
        if ($size === 'Large' && $menu->harga_large === null) {
            $size = 'Regular';
        }
        $sugar = $minuman ? ($data['sugar_level'] ?? null) : null;
        $ice = $minuman ? ($data['ice_level'] ?? null) : null;
        $note = $data['note'] ?? null;
        $addonIds = $minuman ? array_map('intval', $data['addons'] ?? []) : [];
        sort($addonIds);

        $cart = session('cart', []);
        $qtyTotalMenu = $this->qtyMenuDiKeranjang($cart, $menu->id) + $qtyBaru;

        if ($qtyTotalMenu > $menu->stok) {
            return back()->withErrors(['keranjang' => 'Jumlah '.$menu->nama.' tidak boleh melebihi stok ('.$menu->stok.').']);
        }
        if (! $menu->bahanCukup($qtyTotalMenu)) {
            return back()->withErrors(['keranjang' => 'Stok bahan baku '.$menu->nama.' tidak mencukupi.']);
        }

        $key = $this->kunciBaris($menu->id, $size, $sugar, $ice, $note, $addonIds);
        if (isset($cart[$key])) {
            $cart[$key]['qty'] += $qtyBaru;
        } else {
            $cart[$key] = [
                'menu_id' => $menu->id, 'qty' => $qtyBaru, 'size' => $size,
                'sugar_level' => $sugar, 'ice_level' => $ice, 'note' => $note,
                'addon_ids' => $addonIds, 'bundle_id' => null,
            ];
        }
        session(['cart' => $cart]);

        return back()->with('sukses', $menu->nama.' ditambahkan ke keranjang.');
    }

    /** Ubah opsi satu baris keranjang dari pop-up (ukuran, level, add-on, catatan, jumlah). */
    public function updateLine(Request $request, string $key)
    {
        $cart = session('cart', []);

        if (! isset($cart[$key])) {
            return back();
        }
        if (! empty($cart[$key]['bundle_id'])) {
            return back()->withErrors(['keranjang' => 'Item bundel tidak bisa diubah opsinya.']);
        }

        $data = $request->validate([
            'size' => ['nullable', 'in:Regular,Large'],
            'sugar_level' => ['nullable', 'in:No,Low,Half,Normal'],
            'ice_level' => ['nullable', 'in:No,Low,Half,Normal'],
            'note' => ['nullable', 'string', 'max:255'],
            'qty' => ['nullable', 'integer', 'min:1', 'max:99'],
            'addons' => ['nullable', 'array'],
            'addons.*' => ['exists:addons,id'],
        ]);
        $qtyBaru = (int) ($data['qty'] ?? $cart[$key]['qty']);

        $menu = Menu::with('category')->findOrFail($cart[$key]['menu_id']);
        $barisLama = $cart[$key];
        unset($cart[$key]);

        $minuman = $menu->isMinuman();
        $size = $minuman ? ($data['size'] ?? 'Regular') : 'Regular';
        if ($size === 'Large' && $menu->harga_large === null) {
            $size = 'Regular';
        }
        $sugar = $minuman ? ($data['sugar_level'] ?? null) : null;
        $ice = $minuman ? ($data['ice_level'] ?? null) : null;
        $note = $data['note'] ?? null;
        $addonIds = $minuman ? array_map('intval', $data['addons'] ?? []) : [];
        sort($addonIds);

        $qtyTotalMenu = $this->qtyMenuDiKeranjang($cart, $menu->id) + $qtyBaru;
        if ($qtyTotalMenu > $menu->stok || ! $menu->bahanCukup($qtyTotalMenu)) {
            session(['cart' => $cart + [$key => $barisLama]]);

            return back()->withErrors(['keranjang' => 'Jumlah '.$menu->nama.' melebihi stok yang tersedia.']);
        }

        $keyBaru = $this->kunciBaris($menu->id, $size, $sugar, $ice, $note, $addonIds);
        if (isset($cart[$keyBaru])) {
            $cart[$keyBaru]['qty'] += $qtyBaru;
        } else {
            $cart[$keyBaru] = [
                'menu_id' => $menu->id, 'qty' => $qtyBaru, 'size' => $size,
                'sugar_level' => $sugar, 'ice_level' => $ice, 'note' => $note,
                'addon_ids' => $addonIds, 'bundle_id' => null,
            ];
        }
        session(['cart' => $cart]);

        return back()->with('sukses', 'Item '.$menu->nama.' diperbarui.');
    }

    public function addBundle(Request $request)
    {
        $data = $request->validate(['bundle_id' => ['required', 'exists:bundles,id']]);
        $bundle = Bundle::with('items.menu')->findOrFail($data['bundle_id']);

        $cart = session('cart', []);
        foreach ($bundle->items as $komponen) {
            $menu = $komponen->menu;
            $butuh = $komponen->qty + $this->qtyMenuDiKeranjang($cart, $menu->id);
            if ($menu->habis || $butuh > $menu->stok || ! $menu->bahanCukup($butuh)) {
                return back()->withErrors(['keranjang' => 'Komponen bundel '.$menu->nama.' tidak mencukupi stoknya.']);
            }
        }

        $key = 'bundel-'.$bundle->id;
        if (isset($cart[$key])) {
            $cart[$key]['qty']++;
        } else {
            $cart[$key] = [
                'menu_id' => null, 'qty' => 1, 'size' => 'Regular',
                'sugar_level' => null, 'ice_level' => null, 'note' => null,
                'addon_ids' => [], 'bundle_id' => $bundle->id,
            ];
        }
        session(['cart' => $cart]);

        return back()->with('sukses', 'Bundel '.$bundle->nama.' ditambahkan ke keranjang.');
    }

    public function updateQty(Request $request, string $key)
    {
        $data = $request->validate(['qty' => ['required', 'integer', 'min:0']]);
        $cart = session('cart', []);

        if (! isset($cart[$key])) {
            return back();
        }

        if ($data['qty'] === 0) {
            unset($cart[$key]);
            session(['cart' => $cart]);

            return back()->with('sukses', 'Item dihapus dari keranjang.');
        }

        $selisih = $data['qty'] - $cart[$key]['qty'];
        if ($selisih > 0) {
            $menuIds = [];
            if ($cart[$key]['bundle_id']) {
                $bundle = Bundle::with('items.menu')->find($cart[$key]['bundle_id']);
                foreach ($bundle?->items ?? [] as $komponen) {
                    $menuIds[$komponen->menu_id] = $komponen->qty * $selisih;
                }
            } else {
                $menuIds[$cart[$key]['menu_id']] = $selisih;
            }

            foreach ($menuIds as $menuId => $tambah) {
                $menu = Menu::findOrFail($menuId);
                $totalQty = $this->qtyMenuDiKeranjang($cart, $menuId) + $tambah;
                if ($totalQty > $menu->stok) {
                    return back()->withErrors(['keranjang' => 'Jumlah '.$menu->nama.' tidak boleh melebihi stok ('.$menu->stok.').']);
                }
                if (! $menu->bahanCukup($totalQty)) {
                    return back()->withErrors(['keranjang' => 'Stok bahan baku '.$menu->nama.' tidak mencukupi.']);
                }
            }
        }

        $cart[$key]['qty'] = $data['qty'];
        session(['cart' => $cart]);

        return back();
    }

    public function remove(string $key)
    {
        $cart = session('cart', []);
        unset($cart[$key]);
        session(['cart' => $cart]);

        return back()->with('sukses', 'Item dihapus dari keranjang.');
    }

    public function clear()
    {
        session()->forget('cart');

        return back()->with('sukses', 'Keranjang dikosongkan.');
    }

    public function checkout(Request $request)
    {
        if (session('pending_id')) {
            return $this->bayarPending($request, (int) session('pending_id'));
        }

        [$items, $lines] = $this->isiKeranjang();

        if ($items->isEmpty()) {
            return back()->withErrors(['keranjang' => 'Keranjang masih kosong.']);
        }

        $data = $this->validasiBayar($request);
        if ($data instanceof \Illuminate\Http\RedirectResponse) {
            return $data;
        }

        $hitung = HitungTransaksi::hitung($lines, (float) ($data['discount_percent'] ?? 0), $data['voucher_code'] ?? null);

        if ($data['payment_method'] === 'tunai' && (int) ($data['bayar'] ?? 0) < $hitung['total']) {
            return back()->withInput()->withErrors([
                'bayar' => 'Uang bayar kurang dari total belanja ('.Format::rupiah($hitung['total']).'). Transaksi tidak disimpan.',
            ]);
        }

        try {
            $transaction = DB::transaction(function () use ($items, $hitung, $data, $request) {
                $trx = $this->buatTransaksi($items, $hitung, $data, $request, 'paid');
                $this->kurangiStokUntuk($items, $trx, 'jual', $request->user());

                return $trx;
            });
        } catch (\RuntimeException $e) {
            return back()->withErrors(['keranjang' => $e->getMessage()]);
        }

        session()->forget('cart');

        return redirect()->route('pos.struk', $transaction)
            ->with('sukses', 'Transaksi berhasil disimpan.');
    }

    public function simpanPending(Request $request)
    {
        [$items, $lines] = $this->isiKeranjang();

        if ($items->isEmpty()) {
            return back()->withErrors(['keranjang' => 'Keranjang masih kosong.']);
        }

        $data = $request->validate([
            'customer_name' => ['nullable', 'string', 'max:100'],
            'table_number' => ['nullable', 'string', 'max:20'],
            'channel' => ['nullable', 'in:dine_in,takeaway,gofood,grabfood'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'voucher_code' => ['nullable', 'string', 'max:50'],
        ]);

        if (empty($data['customer_name']) && empty($data['table_number'])) {
            return back()->withInput()->withErrors([
                'table_number' => 'Pesanan pending wajib diisi nomor meja atau nama pelanggan.',
            ]);
        }

        if ($errorVoucher = HitungTransaksi::validasiVoucher($data['voucher_code'] ?? null)) {
            return back()->withInput()->withErrors(['voucher_code' => $errorVoucher]);
        }

        $data['payment_method'] = null;
        $hitung = HitungTransaksi::hitung($lines, (float) ($data['discount_percent'] ?? 0), $data['voucher_code'] ?? null);

        try {
            $transaction = DB::transaction(function () use ($items, $hitung, $data, $request) {
                $trx = $this->buatTransaksi($items, $hitung, $data, $request, 'pending');
                $this->kurangiStokUntuk($items, $trx, 'pesanan', $request->user());

                return $trx;
            });
        } catch (\RuntimeException $e) {
            return back()->withErrors(['keranjang' => $e->getMessage()]);
        }

        session()->forget('cart');

        return redirect()->route('pos.pesanan')
            ->with('sukses', 'Pesanan ditahan untuk meja/nama tersebut. Stok sudah diamankan.');
    }

    public function pesanan()
    {
        $pendings = Transaction::with(['user', 'items'])
            ->where('status', 'pending')
            ->latest()
            ->get();

        return view('pos.pesanan', compact('pendings'));
    }

    public function bukaPending(Transaction $transaction)
    {
        if ($transaction->status !== 'pending') {
            return back()->withErrors(['keranjang' => 'Pesanan ini sudah tidak terbuka.']);
        }

        $cart = [];
        foreach ($transaction->items as $item) {
            $addonIds = collect($item->addons_snapshot ?? [])->pluck('id')->map(fn ($i) => (int) $i)->all();
            sort($addonIds);
            $key = $this->kunciBaris($item->menu_id, $item->size ?? 'Regular', $item->sugar_level, $item->ice_level, $item->note, $addonIds);
            if (isset($cart[$key])) {
                $cart[$key]['qty'] += $item->qty;
            } else {
                $cart[$key] = [
                    'menu_id' => $item->menu_id, 'qty' => $item->qty, 'size' => $item->size ?? 'Regular',
                    'sugar_level' => $item->sugar_level, 'ice_level' => $item->ice_level, 'note' => $item->note,
                    'addon_ids' => $addonIds, 'bundle_id' => null,
                ];
            }
        }

        session(['cart' => $cart, 'pending_id' => $transaction->id]);

        return redirect()->route('pos.index')
            ->with('sukses', 'Pesanan #'.$transaction->id.' dibuka di keranjang. Ubah item lalu bayar atau simpan perubahan.');
    }

    public function perbaruiPending(Request $request, Transaction $transaction)
    {
        if ($transaction->status !== 'pending') {
            return back()->withErrors(['keranjang' => 'Pesanan ini sudah tidak terbuka.']);
        }

        [$items] = $this->isiKeranjang();
        if ($items->isEmpty()) {
            return back()->withErrors(['keranjang' => 'Keranjang masih kosong. Untuk membatalkan pesanan gunakan tombol Batalkan Pesanan.']);
        }

        DB::transaction(function () use ($transaction, $items, $request) {
            $this->sesuaikanStokPending($transaction, $items, $request->user());

            $transaction->items()->delete();
            $this->isiItemTransaksi($transaction, $items);

            // Hitung ulang subtotal; diskon lama dipertahankan (dibatasi subtotal baru)
            $subtotal = (int) $items->sum('subtotal');
            $diskon = min((int) $transaction->discount_amount, $subtotal);
            $tax = (int) round(($subtotal - $diskon) * (float) $transaction->tax_percent_snapshot / 100);
            $transaction->update([
                'subtotal' => $subtotal,
                'discount_amount' => $diskon,
                'tax_amount' => $tax,
                'total' => $subtotal - $diskon + $tax,
            ]);
        });

        return redirect()->route('pos.pesanan')
            ->with('sukses', 'Pesanan #'.$transaction->id.' diperbarui.');
    }

    public function batalPending(Request $request, Transaction $transaction)
    {
        if ($transaction->status !== 'pending') {
            return back()->withErrors(['keranjang' => 'Pesanan ini sudah tidak terbuka.']);
        }

        DB::transaction(function () use ($transaction, $request) {
            $this->kembalikanStokTransaksi($transaction, 'batal', $request->user());
            $transaction->update(['status' => 'void', 'void_reason' => 'Pesanan pending dibatalkan.']);
        });

        if ((int) session('pending_id') === $transaction->id) {
            session()->forget(['pending_id', 'cart']);
        }

        return back()->with('sukses', 'Pesanan #'.$transaction->id.' dibatalkan, stok dikembalikan.');
    }

    public function struk(Transaction $transaction)
    {
        $transaction->load(['user', 'items']);

        return view('pos.struk', compact('transaction'));
    }

    public function strukBarista(Transaction $transaction)
    {
        $transaction->load(['user', 'items']);

        return view('pos.struk-barista', compact('transaction'));
    }

    // ---- internal ----

    private function bayarPending(Request $request, int $pendingId)
    {
        $transaction = Transaction::where('status', 'pending')->findOrFail($pendingId);

        [$items] = $this->isiKeranjang();
        if ($items->isEmpty()) {
            return back()->withErrors(['keranjang' => 'Keranjang masih kosong.']);
        }

        $data = $this->validasiBayar($request);
        if ($data instanceof \Illuminate\Http\RedirectResponse) {
            return $data;
        }

        // Samakan isi pesanan dengan keranjang saat ini lebih dulu
        DB::transaction(function () use ($transaction, $items, $request) {
            $this->sesuaikanStokPending($transaction, $items, $request->user());
            $transaction->items()->delete();
            $this->isiItemTransaksi($transaction, $items);

            $subtotal = (int) $items->sum('subtotal');
            $diskon = min((int) $transaction->discount_amount, $subtotal);
            $tax = (int) round(($subtotal - $diskon) * (float) $transaction->tax_percent_snapshot / 100);
            $transaction->update([
                'subtotal' => $subtotal, 'discount_amount' => $diskon,
                'tax_amount' => $tax, 'total' => $subtotal - $diskon + $tax,
            ]);
        });

        $transaction->refresh();

        if ($data['payment_method'] === 'tunai' && (int) ($data['bayar'] ?? 0) < $transaction->total) {
            return back()->withInput()->withErrors([
                'bayar' => 'Uang bayar kurang dari total belanja ('.Format::rupiah($transaction->total).'). Transaksi tidak disimpan.',
            ]);
        }

        $bayar = $data['payment_method'] === 'qris' ? $transaction->total : (int) $data['bayar'];
        $transaction->update([
            'status' => 'paid',
            'payment_method' => $data['payment_method'],
            'channel' => $data['channel'] ?? $transaction->channel,
            'customer_name' => $data['customer_name'] ?? $transaction->customer_name,
            'table_number' => $data['table_number'] ?? $transaction->table_number,
            'bayar' => $bayar,
            'kembalian' => $bayar - $transaction->total,
        ]);

        session()->forget(['cart', 'pending_id']);

        return redirect()->route('pos.struk', $transaction)
            ->with('sukses', 'Pesanan berhasil dibayar.');
    }

    /** Validasi input pembayaran bersama; mengembalikan array data atau redirect error voucher. */
    private function validasiBayar(Request $request): array|\Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'payment_method' => ['nullable', 'in:tunai,qris'],
            'bayar' => ['nullable', 'integer', 'min:0'],
            'customer_name' => ['nullable', 'string', 'max:100'],
            'table_number' => ['nullable', 'string', 'max:20'],
            'channel' => ['nullable', 'in:dine_in,takeaway,gofood,grabfood'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'voucher_code' => ['nullable', 'string', 'max:50'],
        ]);

        $data['payment_method'] = $data['payment_method'] ?? 'tunai';

        if ($errorVoucher = HitungTransaksi::validasiVoucher($data['voucher_code'] ?? null)) {
            return back()->withInput()->withErrors(['voucher_code' => $errorVoucher]);
        }

        return $data;
    }

    private function buatTransaksi($items, array $hitung, array $data, Request $request, string $status): Transaction
    {
        $metode = $data['payment_method'] ?? 'tunai';
        $bayar = 0;
        $kembalian = 0;

        if ($status === 'paid') {
            $bayar = $metode === 'qris' ? $hitung['total'] : (int) ($data['bayar'] ?? 0);
            $kembalian = $bayar - $hitung['total'];
        }

        $trx = Transaction::create([
            'user_id' => $request->user()->id,
            'payment_method' => $status === 'pending' ? null : $metode,
            'customer_name' => $data['customer_name'] ?? null,
            'table_number' => $data['table_number'] ?? null,
            'channel' => $data['channel'] ?? 'dine_in',
            'status' => $status,
            'queue_number' => Transaction::nomorAntreanBerikutnya(),
            'status_pesanan' => 'baru',
            'subtotal' => $hitung['subtotal'],
            'discount_amount' => $hitung['discount_amount'],
            'tax_amount' => $hitung['tax_amount'],
            'tax_percent_snapshot' => $hitung['tax_percent'],
            'voucher_code' => $hitung['voucher']?->kode,
            'discount_note' => $hitung['catatan_diskon'] ? implode('; ', $hitung['catatan_diskon']) : null,
            'total' => $hitung['total'],
            'bayar' => $bayar,
            'kembalian' => $kembalian,
        ]);

        $this->isiItemTransaksi($trx, $items);

        return $trx;
    }

    /** Tulis baris item transaksi dari daftar item keranjang yang sudah disusun. */
    private function isiItemTransaksi(Transaction $trx, $items): void
    {
        foreach ($items as $item) {
            $trx->items()->create([
                'menu_id' => $item['menu']->id,
                'nama_snapshot' => $item['nama'],
                'harga_snapshot' => $item['harga_satuan'],
                'harga_modal_snapshot' => $item['menu']->harga_modal,
                'qty' => $item['qty'],
                'size' => $item['size'] ?? 'Regular',
                'sugar_level' => $item['sugar_level'] ?? null,
                'ice_level' => $item['ice_level'] ?? null,
                'note' => $item['note'] ?? null,
                'addons_snapshot' => $item['addons_snapshot'] ?? null,
                'subtotal' => $item['subtotal'],
            ]);
        }
    }

    private function kurangiStokUntuk($items, Transaction $trx, string $jenis, $user): void
    {
        foreach ($this->agregatQtyMenu($items) as $menuId => $qty) {
            $menu = Menu::lockForUpdate()->findOrFail($menuId);

            if ($qty > $menu->stok) {
                throw new \RuntimeException('Stok '.$menu->nama.' tidak mencukupi.');
            }
            if (! $menu->bahanCukup($qty)) {
                throw new \RuntimeException('Stok bahan baku '.$menu->nama.' tidak mencukupi.');
            }

            StokService::ubahStokMenu($menu, -$qty, $jenis, $user, 'Transaksi #'.$trx->id);
            StokService::kurangiBahan($menu, $qty);
        }
    }

    /** Samakan stok dengan isi keranjang baru untuk pesanan pending (delta per menu). */
    private function sesuaikanStokPending(Transaction $transaction, $itemsBaru, $user): void
    {
        $lama = $transaction->items->groupBy('menu_id')->map(fn ($g) => (int) $g->sum('qty'));
        $baru = $this->agregatQtyMenu($itemsBaru);

        foreach (array_unique(array_merge($lama->keys()->all(), array_keys($baru))) as $menuId) {
            $delta = ($baru[$menuId] ?? 0) - ($lama[$menuId] ?? 0);
            if ($delta === 0) {
                continue;
            }
            $menu = Menu::lockForUpdate()->findOrFail($menuId);
            if ($delta > 0) {
                if ($delta > $menu->stok) {
                    throw new \RuntimeException('Stok '.$menu->nama.' tidak mencukupi.');
                }
                StokService::ubahStokMenu($menu, -$delta, 'pesanan', $user, 'Pesanan #'.$transaction->id);
                StokService::kurangiBahan($menu, $delta);
            } else {
                StokService::ubahStokMenu($menu, -$delta, 'pesanan', $user, 'Pesanan #'.$transaction->id);
                StokService::kembalikanBahan($menu, -$delta);
            }
        }
    }

    /** Kembalikan seluruh stok (menu + bahan) milik sebuah transaksi. */
    public function kembalikanStokTransaksi(Transaction $transaction, string $jenis, $user): void
    {
        foreach ($transaction->items->groupBy('menu_id') as $menuId => $grup) {
            $qty = (int) $grup->sum('qty');
            $menu = Menu::lockForUpdate()->find($menuId);
            if (! $menu) {
                continue;
            }
            StokService::ubahStokMenu($menu, $qty, $jenis, $user, 'Transaksi #'.$transaction->id);
            StokService::kembalikanBahan($menu, $qty);
        }
    }

    /** @return array<int,int> menu_id => total qty dari daftar item keranjang */
    private function agregatQtyMenu($items): array
    {
        $hasil = [];
        foreach ($items as $item) {
            $hasil[$item['menu']->id] = ($hasil[$item['menu']->id] ?? 0) + $item['qty'];
        }

        return $hasil;
    }

    private function qtyMenuDiKeranjang(array $cart, int $menuId): int
    {
        $total = 0;
        foreach ($cart as $row) {
            if (! empty($row['bundle_id'])) {
                $bundle = Bundle::with('items')->find($row['bundle_id']);
                foreach ($bundle?->items ?? [] as $komponen) {
                    if ((int) $komponen->menu_id === $menuId) {
                        $total += $komponen->qty * $row['qty'];
                    }
                }
            } elseif ((int) $row['menu_id'] === $menuId) {
                $total += $row['qty'];
            }
        }

        return $total;
    }

    private function kunciBaris(?int $menuId, ?string $size, ?string $sugar, ?string $ice, ?string $note, array $addonIds): string
    {
        $default = ($size === 'Regular' || $size === null) && ! $sugar && ! $ice && ! $note && empty($addonIds);
        if ($default) {
            return (string) $menuId;
        }

        return $menuId.'-'.substr(md5(json_encode([$size, $sugar, $ice, $note, $addonIds])), 0, 8);
    }

    /** Kelompokkan item keranjang menjadi baris tampilan (satu baris per kunci keranjang). */
    private function barisKeranjang($items): \Illuminate\Support\Collection
    {
        return $items->groupBy('key')->map(function ($grup, $key) {
            $pertama = $grup->first();

            return [
                'key' => $key,
                'judul' => $pertama['dari_bundel']
                    ? explode(' — ', $pertama['nama'])[0]
                    : $pertama['nama'],
                'komponen' => $pertama['dari_bundel'] ? $grup->pluck('nama')->all() : [],
                'qty' => $pertama['qty_baris'],
                'harga_satuan' => $pertama['harga_satuan'],
                'subtotal' => (int) $grup->sum('subtotal'),
                'menu_id' => $pertama['dari_bundel'] ? null : $pertama['menu']->id,
                'minuman' => $pertama['dari_bundel'] ? false : $pertama['menu']->isMinuman(),
                'harga_large' => $pertama['dari_bundel'] ? null : $pertama['menu']->harga_large,
                'size' => $pertama['size'] ?? 'Regular',
                'sugar_level' => $pertama['sugar_level'] ?? null,
                'ice_level' => $pertama['ice_level'] ?? null,
                'note' => $pertama['note'] ?? null,
                'addon_ids' => collect($pertama['addons_snapshot'] ?? [])->pluck('id')->map(fn ($i) => (int) $i)->all(),
                'dari_bundel' => $pertama['dari_bundel'],
                'ringkasan' => $pertama['dari_bundel'] ? '' : (new \App\Models\TransactionItem([
                    'size' => $pertama['size'], 'sugar_level' => $pertama['sugar_level'],
                    'ice_level' => $pertama['ice_level'], 'note' => $pertama['note'],
                    'addons_snapshot' => $pertama['addons_snapshot'],
                ]))->ringkasanCatatan(),
            ];
        })->values();
    }

    /**
     * Susun isi keranjang dari session.
     * Mengembalikan [daftar item tampilan, baris hitung untuk HitungTransaksi].
     * Item tampilan: satu baris per komponen (bundel dipecah menjadi komponennya,
     * dengan 'nama' berisi "Nama Bundel — Nama Menu" agar struk tetap jelas).
     */
    private function isiKeranjang(): array
    {
        $cart = session('cart', []);
        $items = collect();
        $lines = collect();

        foreach ($cart as $key => $row) {
            if (! empty($row['bundle_id'])) {
                $bundle = Bundle::with('items.menu.category')->find($row['bundle_id']);
                if (! $bundle) {
                    continue;
                }
                $diskonBundel = max(0, $bundle->hargaKomponen() - (int) $bundle->harga) * $row['qty'];
                $pertama = true;
                foreach ($bundle->items as $komponen) {
                    $menu = $komponen->menu;
                    $qty = $komponen->qty * $row['qty'];
                    $subtotal = $menu->harga * $qty;
                    $items->push([
                        'key' => $key, 'menu' => $menu, 'nama' => $bundle->nama.' — '.$menu->nama,
                        'qty' => $qty, 'size' => 'Regular', 'sugar_level' => null, 'ice_level' => null,
                        'note' => null, 'addons_snapshot' => null, 'qty_baris' => $row['qty'],
                        'harga_satuan' => (int) $menu->harga, 'subtotal' => $subtotal, 'dari_bundel' => true,
                    ]);
                    $lines->push([
                        'menu' => $menu, 'qty' => $qty, 'harga_satuan' => (int) $menu->harga,
                        'subtotal' => $subtotal, 'diskon_bundel' => $pertama ? $diskonBundel : 0,
                    ]);
                    $pertama = false;
                }

                continue;
            }

            $menu = Menu::with('category')->find($row['menu_id']);
            if (! $menu) {
                continue;
            }

            $hargaSatuan = (int) $menu->harga;
            if (($row['size'] ?? 'Regular') === 'Large' && $menu->harga_large !== null) {
                $hargaSatuan += (int) $menu->harga_large;
            }

            $addonsSnapshot = [];
            $hargaAddon = 0;
            foreach ($row['addon_ids'] ?? [] as $addonId) {
                $addon = Addon::find($addonId);
                if ($addon) {
                    $addonsSnapshot[] = ['id' => $addon->id, 'nama' => $addon->nama, 'harga' => (int) $addon->harga];
                    $hargaAddon += (int) $addon->harga;
                }
            }
            $hargaSatuan += $hargaAddon;
            $subtotal = $hargaSatuan * $row['qty'];

            $items->push([
                'key' => $key, 'menu' => $menu, 'nama' => $menu->nama,
                'qty' => $row['qty'], 'size' => $row['size'] ?? 'Regular',
                'sugar_level' => $row['sugar_level'] ?? null, 'ice_level' => $row['ice_level'] ?? null,
                'note' => $row['note'] ?? null, 'addons_snapshot' => $addonsSnapshot ?: null,
                'harga_satuan' => $hargaSatuan, 'subtotal' => $subtotal, 'dari_bundel' => false, 'qty_baris' => $row['qty'],
            ]);
            $lines->push([
                'menu' => $menu, 'qty' => $row['qty'], 'harga_satuan' => $hargaSatuan,
                'subtotal' => $subtotal, 'diskon_bundel' => 0,
            ]);
        }

        return [$items, $lines->all()];
    }
}
