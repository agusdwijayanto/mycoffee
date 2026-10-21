<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Menu;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends Controller
{
    public function index()
    {
        $data = $this->ringkasan();

        // Kehadiran hari ini untuk widget absensi di dashboard
        $data['absensiHariIni'] = \App\Models\Attendance::where('user_id', auth()->id())
            ->whereDate('tanggal', today())->first();

        return view('admin.dashboard', $data);
    }

    public function laporan(Request $request)
    {
        $data = $this->ringkasan();
        $data = array_merge($data, $this->laporanRentang($request));

        // Omzet 7 hari terakhir (termasuk hari ini), hanya transaksi paid
        $perHari = collect();
        for ($i = 6; $i >= 0; $i--) {
            $tanggal = now()->subDays($i);
            $q = Transaction::paid()->whereDate('created_at', $tanggal->toDateString());
            $perHari->push([
                'tanggal' => $tanggal->format('Y-m-d'),
                'label' => $tanggal->translatedFormat('l, d M Y'),
                'omzet' => (int) (clone $q)->sum('total'),
                'transaksi' => (clone $q)->count(),
            ]);
        }
        $data['perHari'] = $perHari;

        return view('admin.laporan', $data);
    }

    /** Ekspor CSV transaksi paid pada rentang tanggal terpilih. */
    public function eksporCsv(Request $request): StreamedResponse
    {
        [$dari, $sampai] = $this->rentang($request);

        $namaFile = 'laporan-transaksi-'.$dari.'-sampai-'.$sampai.'.csv';

        return response()->streamDownload(function () use ($dari, $sampai) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID', 'Tanggal', 'Kasir', 'Pelanggan', 'Meja', 'Kanal', 'Metode Bayar', 'Subtotal', 'Diskon', 'Pajak', 'Total', 'Bayar', 'Kembalian', 'Status']);

            Transaction::with('user')
                ->whereBetween('created_at', [$dari.' 00:00:00', $sampai.' 23:59:59'])
                ->orderBy('created_at')
                ->chunk(100, function ($rows) use ($out) {
                    foreach ($rows as $t) {
                        fputcsv($out, [
                            $t->id, $t->created_at->format('Y-m-d H:i:s'), $t->user->name,
                            $t->customer_name, $t->table_number, $t->channel, $t->payment_method,
                            $t->subtotal, $t->discount_amount, $t->tax_amount, $t->total,
                            $t->bayar, $t->kembalian, $t->status,
                        ]);
                    }
                });

            fclose($out);
        }, $namaFile, ['Content-Type' => 'text/csv']);
    }

    /** Rentang tanggal laporan dari request (bawaan: 7 hari terakhir termasuk hari ini). */
    private function rentang(Request $request): array
    {
        $dari = $request->input('dari', now()->subDays(6)->toDateString());
        $sampai = $request->input('sampai', now()->toDateString());

        if (! strtotime($dari) || ! strtotime($sampai) || $dari > $sampai) {
            $dari = now()->subDays(6)->toDateString();
            $sampai = now()->toDateString();
        }

        return [$dari, $sampai];
    }

    private function laporanRentang(Request $request): array
    {
        [$dari, $sampai] = $this->rentang($request);
        $batas = [$dari.' 00:00:00', $sampai.' 23:59:59'];

        $base = fn () => Transaction::paid()->whereBetween('created_at', $batas);

        $omzetRentang = (int) $base()->sum('total');
        $jumlahRentang = $base()->count();

        $perMetode = [
            'tunai' => (int) $base()->where('payment_method', 'tunai')->sum('total'),
            'qris' => (int) $base()->where('payment_method', 'qris')->sum('total'),
        ];

        $perKanal = $base()->select('channel', DB::raw('SUM(total) as omzet'), DB::raw('COUNT(*) as jumlah'))
            ->groupBy('channel')->pluck('omzet', 'channel')->all();
        $perKanalJumlah = $base()->select('channel', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('channel')->pluck('jumlah', 'channel')->all();

        $perKasir = Transaction::paid()->whereBetween('transactions.created_at', $batas)
            ->join('users', 'users.id', '=', 'transactions.user_id')
            ->select('users.name as nama', DB::raw('SUM(transactions.total) as omzet'), DB::raw('COUNT(*) as jumlah'))
            ->groupBy('users.name')
            ->orderByDesc('omzet')
            ->get();

        $trxIds = $base()->pluck('id');
        $labaKotor = (int) TransactionItem::whereIn('transaction_id', $trxIds)
            ->sum(DB::raw('(harga_snapshot - harga_modal_snapshot) * qty'));

        $pengeluaran = (int) Expense::whereBetween('tanggal', [$dari.' 00:00:00', $sampai.' 23:59:59'])->sum('nominal');
        $labaBersih = $labaKotor - $pengeluaran;

        $menuTerlarisRentang = TransactionItem::whereIn('transaction_id', $trxIds)
            ->select('nama_snapshot', DB::raw('SUM(qty) as total_qty'), DB::raw('SUM(subtotal) as total_subtotal'))
            ->groupBy('nama_snapshot')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        return compact(
            'dari', 'sampai', 'omzetRentang', 'jumlahRentang', 'perMetode',
            'perKanal', 'perKanalJumlah', 'perKasir', 'labaKotor', 'pengeluaran',
            'labaBersih', 'menuTerlarisRentang'
        );
    }

    private function ringkasan(): array
    {
        $omzetHariIni = (int) Transaction::paid()->whereDate('created_at', today())->sum('total');
        $omzetBulanIni = (int) Transaction::paid()->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('total');
        $transaksiHariIni = Transaction::paid()->whereDate('created_at', today())->count();
        $transaksiBulanIni = Transaction::paid()->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        $menuTerlaris = TransactionItem::query()
            ->whereHas('transaction', fn ($q) => $q->where('status', 'paid'))
            ->select('nama_snapshot', DB::raw('SUM(qty) as total_qty'), DB::raw('SUM(subtotal) as total_subtotal'))
            ->groupBy('nama_snapshot')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        $menuHabis = Menu::with('category')
            ->where(fn ($q) => $q->where('stok', '<=', 0)->orWhere('is_available', false))
            ->orderBy('nama')
            ->get();

        // Menu yang bahan bakunya habis (tidak tertangkap query stok di atas)
        $habisBahan = Menu::with(['category', 'ingredients'])
            ->where('stok', '>', 0)->where('is_available', true)
            ->get()
            ->filter(fn ($m) => ! $m->bahanCukup(1));
        $menuHabis = $menuHabis->concat($habisBahan)->unique('id')->values();

        $batasMenipis = (int) (Setting::get('stok_minimum', '5') ?: 5);
        $stokMenipis = Menu::with('category')
            ->where('stok', '>', 0)
            ->where('stok', '<=', $batasMenipis)
            ->where('is_available', true)
            ->orderBy('stok')
            ->get();

        $transaksiTerbaru = Transaction::with('user')
            ->latest()
            ->take(5)
            ->get();

        return compact(
            'omzetHariIni', 'omzetBulanIni', 'transaksiHariIni', 'transaksiBulanIni',
            'menuTerlaris', 'menuHabis', 'stokMenipis', 'batasMenipis', 'transaksiTerbaru'
        );
    }
}
