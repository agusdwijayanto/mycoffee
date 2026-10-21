<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Support\StokService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $menus = Menu::with(['category', 'ingredients'])
            ->when($request->filled('q'), fn ($q) => $q->where('nama', 'like', '%'.$request->q.'%'))
            ->orderBy('nama')
            ->paginate(15)
            ->withQueryString();

        return view('admin.menus.index', compact('menus'));
    }

    public function create()
    {
        $categories = Category::orderBy('nama')->get();
        $ingredients = Ingredient::orderBy('nama')->get();

        return view('admin.menus.form', ['menu' => new Menu, 'categories' => $categories, 'ingredients' => $ingredients]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);

        if ($request->hasFile('foto')) {
            $data['foto'] = $this->simpanFoto($request);
        }

        $menu = Menu::create($data['utama']);
        $this->simpanResep($menu, $data['resep']);

        return redirect()->route('admin.menus.index')->with('sukses', 'Menu berhasil ditambahkan.');
    }

    public function edit(Menu $menu)
    {
        $categories = Category::orderBy('nama')->get();
        $ingredients = Ingredient::orderBy('nama')->get();
        $menu->load('ingredients');

        return view('admin.menus.form', compact('menu', 'categories', 'ingredients'));
    }

    public function update(Request $request, Menu $menu)
    {
        $data = $this->validasi($request);
        $stokLama = $menu->stok;

        if ($request->hasFile('foto')) {
            $data['utama']['foto'] = $this->simpanFoto($request);
        }

        $menu->update($data['utama']);
        $this->simpanResep($menu, $data['resep']);

        // Perubahan stok manual wajib tercatat di riwayat stok
        if ((int) $data['utama']['stok'] !== $stokLama) {
            \App\Models\StockLog::create([
                'menu_id' => $menu->id,
                'user_id' => $request->user()->id,
                'perubahan' => (int) $data['utama']['stok'] - $stokLama,
                'stok_sebelum' => $stokLama,
                'stok_sesudah' => (int) $data['utama']['stok'],
                'jenis' => 'manual',
                'keterangan' => 'Perubahan stok lewat Kelola Menu',
            ]);
        }

        return redirect()->route('admin.menus.index')->with('sukses', 'Menu berhasil diperbarui.');
    }

    public function destroy(Menu $menu)
    {
        $menu->delete();

        return redirect()->route('admin.menus.index')->with('sukses', 'Menu berhasil dihapus.');
    }

    public function toggle(Menu $menu)
    {
        $menu->update(['is_available' => ! $menu->is_available]);

        return back()->with('sukses', 'Status menu '.$menu->nama.' berhasil diubah.');
    }

    private function simpanResep(Menu $menu, array $resep): void
    {
        $sync = [];
        foreach ($resep as $ingredientId => $qty) {
            if ($qty !== null && (float) $qty > 0) {
                $sync[(int) $ingredientId] = ['qty_per_porsi' => (float) $qty];
            }
        }
        $menu->ingredients()->sync($sync);
    }

    private function validasi(Request $request): array
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'category_id' => ['required', 'exists:categories,id'],
            'deskripsi' => ['nullable', 'string'],
            'harga' => ['required', 'integer', 'min:0'],
            'harga_modal' => ['nullable', 'integer', 'min:0'],
            'harga_large' => ['nullable', 'integer', 'min:0'],
            'stok' => ['required', 'integer', 'min:0'],
            'tampil_dari' => ['nullable', 'date'],
            'tampil_sampai' => ['nullable', 'date', 'after_or_equal:tampil_dari'],
            'foto' => ['nullable', 'image', 'max:4096'],
            'resep' => ['nullable', 'array'],
            'resep.*' => ['nullable', 'numeric', 'min:0'],
        ], [
            'nama.required' => 'Nama menu wajib diisi.',
            'harga.required' => 'Harga wajib diisi.',
            'stok.required' => 'Stok wajib diisi.',
        ]);

        $utama = [
            'nama' => $data['nama'],
            'category_id' => $data['category_id'],
            'deskripsi' => $data['deskripsi'] ?? null,
            'harga' => $data['harga'],
            'harga_modal' => (int) ($data['harga_modal'] ?? 0),
            'harga_large' => $data['harga_large'] ?? null,
            'stok' => $data['stok'],
            'tampil_dari' => $data['tampil_dari'] ?? null,
            'tampil_sampai' => $data['tampil_sampai'] ?? null,
            'is_available' => $request->boolean('is_available'),
        ];

        return ['utama' => $utama, 'resep' => $data['resep'] ?? []];
    }

    private function simpanFoto(Request $request): string
    {
        $file = $request->file('foto');
        $nama = time().'-'.Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
            .'.'.$file->getClientOriginalExtension();
        $file->move(public_path('images/menu'), $nama);

        return $nama;
    }
}
