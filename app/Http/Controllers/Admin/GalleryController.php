<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GalleryController extends Controller
{
    public function index()
    {
        $galleries = Gallery::orderBy('judul')->get();
        $fileTersedia = $this->daftarFile();

        return view('admin.galleries.index', compact('galleries', 'fileTersedia'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'judul' => ['required', 'string', 'max:150'],
            'foto_file' => ['nullable', 'string'],
            'foto_upload' => ['nullable', 'image', 'max:4096'],
        ], [
            'judul.required' => 'Judul wajib diisi.',
        ]);

        if ($request->hasFile('foto_upload')) {
            $file = $request->file('foto_upload');
            $nama = time().'-'.Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                .'.'.$file->getClientOriginalExtension();
            $file->move(public_path('images/gallery'), $nama);
            $foto = $nama;
        } elseif (! empty($data['foto_file'])) {
            $foto = $data['foto_file'];
        } else {
            return back()->withInput()->withErrors(['foto_file' => 'Pilih file foto yang ada atau unggah foto baru.']);
        }

        Gallery::create(['judul' => $data['judul'], 'foto' => $foto]);

        return redirect()->route('admin.galleries.index')->with('sukses', 'Foto gallery berhasil ditambahkan.');
    }

    public function edit(Gallery $gallery)
    {
        $fileTersedia = $this->daftarFile();

        return view('admin.galleries.edit', compact('gallery', 'fileTersedia'));
    }

    public function update(Request $request, Gallery $gallery)
    {
        $data = $request->validate([
            'judul' => ['required', 'string', 'max:150'],
            'foto_file' => ['nullable', 'string'],
            'foto_upload' => ['nullable', 'image', 'max:4096'],
        ]);

        if ($request->hasFile('foto_upload')) {
            $file = $request->file('foto_upload');
            $nama = time().'-'.Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                .'.'.$file->getClientOriginalExtension();
            $file->move(public_path('images/gallery'), $nama);
            $gallery->foto = $nama;
        } elseif (! empty($data['foto_file'])) {
            $gallery->foto = $data['foto_file'];
        }

        $gallery->judul = $data['judul'];
        $gallery->save();

        return redirect()->route('admin.galleries.index')->with('sukses', 'Foto gallery berhasil diperbarui.');
    }

    public function destroy(Gallery $gallery)
    {
        $gallery->delete();

        return redirect()->route('admin.galleries.index')->with('sukses', 'Foto gallery berhasil dihapus.');
    }

    private function daftarFile(): array
    {
        $dir = public_path('images/gallery');
        if (! is_dir($dir)) {
            return [];
        }

        return collect(scandir($dir))
            ->filter(fn ($f) => preg_match('/\.(png|jpe?g|webp)$/i', $f))
            ->values()
            ->all();
    }
}
