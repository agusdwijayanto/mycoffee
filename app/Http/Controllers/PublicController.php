<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Gallery;
use App\Models\Menu;
use App\Models\Reservation;
use App\Models\Setting;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function __construct()
    {
        // Bahasa publik dari session (bawaan: Indonesia)
        app()->setLocale(session('locale', 'id'));
    }

    public function setBahasa(string $locale)
    {
        if (in_array($locale, ['id', 'en'], true)) {
            session(['locale' => $locale]);
        }

        return back();
    }

    public function home()
    {
        $hero = Gallery::where('foto', 'tampilan-depan-mycoffee.webp')->first()
            ?? Gallery::first();

        $unggulan = Menu::with(['category', 'ingredients'])
            ->tampilSekarang()
            ->orderByDesc('stok')
            ->take(4)
            ->get();

        $galleryPilihan = Gallery::take(6)->get();

        $testimoni = Testimonial::where('disetujui', true)->latest()->take(6)->get();

        $banner = $this->bannerPromo();

        return view('public.home', compact('hero', 'unggulan', 'galleryPilihan', 'testimoni', 'banner'));
    }

    public function tentang()
    {
        $gallery = Gallery::take(4)->get();

        return view('public.tentang', compact('gallery'));
    }

    public function menu(Request $request)
    {
        $categories = Category::withCount(['menus' => fn ($q) => $q->tampilSekarang()])->get();

        $menus = Menu::with(['category', 'ingredients'])
            ->tampilSekarang()
            ->when($request->filled('kategori'), function ($q) use ($request) {
                $q->whereHas('category', fn ($c) => $c->where('slug', $request->kategori));
            })
            ->when($request->filled('q'), function ($q) use ($request) {
                $q->where('nama', 'like', '%'.$request->q.'%');
            })
            ->orderBy('nama')
            ->get();

        return view('public.menu', compact('categories', 'menus'));
    }

    public function gallery()
    {
        $galleries = Gallery::orderBy('judul')->get();

        return view('public.gallery', compact('galleries'));
    }

    public function kontak()
    {
        $hero = Gallery::where('foto', 'tampilan-depan-mycoffee.webp')->first()
            ?? Gallery::first();

        return view('public.kontak', compact('hero'));
    }

    public function simpanTestimoni(Request $request)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'isi' => ['required', 'string', 'max:1000'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        Testimonial::create($data + ['disetujui' => false]);

        return back()->with('sukses_testimoni', 'Terima kasih! Testimoni kamu tersimpan dan menunggu persetujuan admin sebelum tampil.');
    }

    public function simpanReservasi(Request $request)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'whatsapp' => ['required', 'string', 'max:30'],
            'tanggal' => ['required', 'date', 'after_or_equal:today'],
            'jam' => ['required', 'date_format:H:i'],
            'jumlah_orang' => ['required', 'integer', 'min:1', 'max:100'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ], [
            'tanggal.after_or_equal' => 'Tanggal reservasi tidak boleh di masa lalu.',
        ]);

        Reservation::create($data + ['status' => 'menunggu']);

        return back()->with('sukses_reservasi', 'Reservasi terkirim! Kami akan menghubungimu lewat WhatsApp untuk konfirmasi.');
    }

    private function bannerPromo(): ?string
    {
        $teks = Setting::get('promo_banner_text');
        if (! $teks) {
            return null;
        }

        $dari = Setting::get('promo_banner_dari');
        $sampai = Setting::get('promo_banner_sampai');
        $hariIni = today()->toDateString();

        if ($dari && $hariIni < $dari) {
            return null;
        }
        if ($sampai && $hariIni > $sampai) {
            return null;
        }

        return $teks;
    }
}
