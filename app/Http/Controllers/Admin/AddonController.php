<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use Illuminate\Http\Request;

class AddonController extends Controller
{
    public function index()
    {
        $addons = Addon::orderBy('nama')->get();

        return view('admin.addons.index', compact('addons'));
    }

    public function store(Request $request)
    {
        Addon::create($this->validasi($request));

        return back()->with('sukses', 'Add-on berhasil ditambahkan.');
    }

    public function edit(Addon $addon)
    {
        $addons = Addon::orderBy('nama')->get();

        return view('admin.addons.index', compact('addons', 'addon'));
    }

    public function update(Request $request, Addon $addon)
    {
        $addon->update($this->validasi($request));

        return redirect()->route('admin.addons.index')->with('sukses', 'Add-on berhasil diperbarui.');
    }

    public function destroy(Addon $addon)
    {
        $addon->delete();

        return back()->with('sukses', 'Add-on berhasil dihapus.');
    }

    private function validasi(Request $request): array
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'harga' => ['required', 'integer', 'min:0'],
        ]);
        $data['aktif'] = $request->boolean('aktif');

        return $data;
    }
}
