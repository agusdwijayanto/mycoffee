<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PosController;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::with('user')
            ->latest()
            ->paginate(15);

        return view('admin.transactions.index', compact('transactions'));
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['user', 'items']);

        return view('admin.transactions.show', compact('transaction'));
    }

    /** Void transaksi lunas: stok dan bahan dikembalikan, transaksi keluar dari laporan. */
    public function void(Request $request, Transaction $transaction)
    {
        $data = $request->validate([
            'void_reason' => ['required', 'string', 'max:255'],
        ], ['void_reason.required' => 'Alasan void wajib diisi.']);

        if ($transaction->status !== 'paid') {
            return back()->withErrors(['void' => 'Hanya transaksi berstatus paid yang bisa di-void.']);
        }

        DB::transaction(function () use ($transaction, $data, $request) {
            app(PosController::class)->kembalikanStokTransaksi($transaction, 'void', $request->user());
            $transaction->update([
                'status' => 'void',
                'void_reason' => $data['void_reason'],
            ]);
        });

        return back()->with('sukses', 'Transaksi #'.$transaction->id.' berhasil di-void. Stok sudah dikembalikan.');
    }
}
