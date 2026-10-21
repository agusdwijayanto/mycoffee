<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index(Request $request)
    {
        $shifts = Shift::with('user')
            ->when(! $request->user()->isAdmin(), fn ($q) => $q->where('user_id', $request->user()->id))
            ->latest('opened_at')
            ->paginate(15);

        $terbuka = Shift::where('user_id', $request->user()->id)
            ->whereNull('closed_at')->latest('opened_at')->first();

        $ringkasanTerbuka = $terbuka?->ringkasanPenjualan();

        return view('shift.index', compact('shifts', 'terbuka', 'ringkasanTerbuka'));
    }

    public function buka(Request $request)
    {
        $data = $request->validate([
            'modal_awal' => ['required', 'integer', 'min:0'],
        ], ['modal_awal.required' => 'Modal awal wajib diisi.']);

        $sudahAda = Shift::where('user_id', $request->user()->id)
            ->whereNull('closed_at')->exists();

        if ($sudahAda) {
            return back()->withErrors(['shift' => 'Masih ada shift terbuka. Tutup dulu sebelum membuka shift baru.']);
        }

        Shift::create([
            'user_id' => $request->user()->id,
            'opened_at' => now(),
            'modal_awal' => $data['modal_awal'],
        ]);

        return back()->with('sukses', 'Shift dibuka. Selamat bekerja!');
    }

    public function tutup(Request $request, Shift $shift)
    {
        if (! $request->user()->isAdmin() && $shift->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($shift->closed_at) {
            return back()->withErrors(['shift' => 'Shift ini sudah ditutup.']);
        }

        $data = $request->validate([
            'uang_fisik' => ['required', 'integer', 'min:0'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ], ['uang_fisik.required' => 'Uang fisik di laci wajib diisi untuk tutup shift.']);

        $shift->closed_at = now();
        $ringkasan = $shift->ringkasanPenjualan();

        $shift->uang_fisik = $data['uang_fisik'];
        $shift->selisih = (int) $data['uang_fisik'] - $ringkasan['tunai'];
        $shift->catatan = $data['catatan'] ?? null;
        $shift->save();

        return back()->with('sukses', 'Shift ditutup. Selisih tunai: '.\App\Support\Format::rupiah($shift->selisih).'.');
    }
}
