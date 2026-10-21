<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    public function index()
    {
        $vouchers = Voucher::orderBy('kode')->get();

        return view('admin.vouchers.index', compact('vouchers'));
    }

    public function store(Request $request)
    {
        Voucher::create($this->validasi($request));

        return back()->with('sukses', 'Voucher berhasil ditambahkan.');
    }

    public function edit(Voucher $voucher)
    {
        $vouchers = Voucher::orderBy('kode')->get();

        return view('admin.vouchers.index', compact('vouchers', 'voucher'));
    }

    public function update(Request $request, Voucher $voucher)
    {
        $voucher->update($this->validasi($request, $voucher));

        return redirect()->route('admin.vouchers.index')->with('sukses', 'Voucher berhasil diperbarui.');
    }

    public function destroy(Voucher $voucher)
    {
        $voucher->delete();

        return back()->with('sukses', 'Voucher berhasil dihapus.');
    }

    private function validasi(Request $request, ?Voucher $voucher = null): array
    {
        $data = $request->validate([
            'kode' => ['required', 'string', 'max:50', 'unique:vouchers,kode'.($voucher ? ','.$voucher->id : '')],
            'tipe' => ['required', 'in:persen,nominal'],
            'nilai' => ['required', 'integer', 'min:0'],
            'berlaku_sampai' => ['nullable', 'date'],
        ]);
        $data['kode'] = strtoupper($data['kode']);
        $data['aktif'] = $request->boolean('aktif');

        return $data;
    }
}
