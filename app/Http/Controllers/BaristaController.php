<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class BaristaController extends Controller
{
    public function index()
    {
        $pesanan = Transaction::with(['user', 'items'])
            ->where('status', 'paid')
            ->whereDate('created_at', today())
            ->oldest()
            ->get()
            ->groupBy('status_pesanan');

        return view('barista.index', compact('pesanan'));
    }

    public function ubahStatus(Request $request, Transaction $transaction)
    {
        $data = $request->validate([
            'status_pesanan' => ['required', 'in:baru,dibuat,selesai'],
        ]);

        $transaction->update(['status_pesanan' => $data['status_pesanan']]);

        return back()->with('sukses', 'Status pesanan #'.$transaction->id.' diubah menjadi '.$data['status_pesanan'].'.');
    }
}
