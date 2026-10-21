<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Models\Menu;
use Illuminate\Http\Request;

class BundleController extends Controller
{
    public function index()
    {
        $bundles = Bundle::with('items.menu')->orderBy('nama')->get();

        return view('admin.bundles.index', compact('bundles'));
    }

    public function create()
    {
        $menus = Menu::orderBy('nama')->get();

        return view('admin.bundles.form', ['bundle' => new Bundle, 'menus' => $menus]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);
        $bundle = Bundle::create($data['utama']);
        $this->simpanKomponen($bundle, $data['komponen']);

        return redirect()->route('admin.bundles.index')->with('sukses', 'Bundel berhasil ditambahkan.');
    }

    public function edit(Bundle $bundle)
    {
        $bundle->load('items');
        $menus = Menu::orderBy('nama')->get();

        return view('admin.bundles.form', compact('bundle', 'menus'));
    }

    public function update(Request $request, Bundle $bundle)
    {
        $data = $this->validasi($request);
        $bundle->update($data['utama']);
        $this->simpanKomponen($bundle, $data['komponen']);

        return redirect()->route('admin.bundles.index')->with('sukses', 'Bundel berhasil diperbarui.');
    }

    public function destroy(Bundle $bundle)
    {
        $bundle->delete();

        return back()->with('sukses', 'Bundel berhasil dihapus.');
    }

    private function simpanKomponen(Bundle $bundle, array $komponen): void
    {
        $bundle->items()->delete();
        foreach ($komponen as $menuId => $qty) {
            $bundle->items()->create(['menu_id' => $menuId, 'qty' => $qty]);
        }
    }

    private function validasi(Request $request): array
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'harga' => ['required', 'integer', 'min:0'],
            'komponen' => ['required', 'array', 'min:1'],
            'komponen.*.menu_id' => ['nullable', 'exists:menus,id'],
            'komponen.*.qty' => ['nullable', 'integer', 'min:1'],
        ], ['komponen.required' => 'Pilih minimal satu menu komponen bundel.']);

        $komponen = [];
        foreach ($data['komponen'] as $row) {
            if (! empty($row['menu_id'])) {
                $komponen[(int) $row['menu_id']] = (int) ($row['qty'] ?? 1);
            }
        }

        if ($komponen === []) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'komponen' => 'Pilih minimal satu menu komponen bundel.',
            ]);
        }

        return [
            'utama' => ['nama' => $data['nama'], 'harga' => $data['harga'], 'aktif' => $request->boolean('aktif')],
            'komponen' => $komponen,
        ];
    }
}
