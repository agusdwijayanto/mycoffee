<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use Illuminate\Http\Request;

class IngredientController extends Controller
{
    public function index()
    {
        $ingredients = Ingredient::orderBy('nama')->get();

        return view('admin.ingredients.index', compact('ingredients'));
    }

    public function store(Request $request)
    {
        Ingredient::create($this->validasi($request));

        return back()->with('sukses', 'Bahan baku berhasil ditambahkan.');
    }

    public function edit(Ingredient $ingredient)
    {
        $ingredients = Ingredient::orderBy('nama')->get();

        return view('admin.ingredients.index', compact('ingredients', 'ingredient'));
    }

    public function update(Request $request, Ingredient $ingredient)
    {
        $ingredient->update($this->validasi($request));

        return redirect()->route('admin.ingredients.index')->with('sukses', 'Bahan baku berhasil diperbarui.');
    }

    public function destroy(Ingredient $ingredient)
    {
        $ingredient->delete();

        return back()->with('sukses', 'Bahan baku berhasil dihapus.');
    }

    private function validasi(Request $request): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'satuan' => ['required', 'string', 'max:20'],
            'stok' => ['required', 'numeric', 'min:0'],
        ]);
    }
}
