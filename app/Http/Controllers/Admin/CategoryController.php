<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('menus')->orderBy('nama')->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.form', ['category' => new Category]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
        ]);

        $data['slug'] = Str::slug($data['nama']);
        if (Category::where('slug', $data['slug'])->exists()) {
            return back()->withInput()->withErrors(['nama' => 'Nama kategori ini sudah ada.']);
        }
        Category::create($data);

        return redirect()->route('admin.categories.index')->with('sukses', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.form', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
        ]);

        $slug = Str::slug($data['nama']);
        $duplikat = Category::where('slug', $slug)->where('id', '!=', $category->id)->exists();
        if ($duplikat) {
            return back()->withInput()->withErrors(['nama' => 'Nama kategori ini sudah dipakai kategori lain.']);
        }

        $data['slug'] = $slug;
        $category->update($data);

        return redirect()->route('admin.categories.index')->with('sukses', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('admin.categories.index')->with('sukses', 'Kategori berhasil dihapus.');
    }
}
