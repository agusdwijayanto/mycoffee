<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /** Kunci pengaturan versi 2 (semuanya opsional di form). */
    private array $kunciV2 = [
        'whatsapp_pemilik', 'jam_buka_libur', 'tax_percent', 'stok_minimum',
        'promo_b2g1_menu_id', 'promo_banner_text', 'promo_banner_dari', 'promo_banner_sampai',
    ];

    public function edit()
    {
        $menus = Menu::orderBy('nama')->get();
        $urlMenu = url('/menu');

        $qrSvg = null;
        if (class_exists(\SimpleSoftwareIO\QrCode\Facades\QrCode::class)) {
            try {
                $qrSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                    ->size(220)->margin(1)->generate($urlMenu);
            } catch (\Throwable $e) {
                $qrSvg = null;
            }
        }

        return view('admin.settings.edit', compact('menus', 'urlMenu', 'qrSvg'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'brand_name' => ['required', 'string', 'max:100'],
            'tagline' => ['nullable', 'string', 'max:200'],
            'alamat' => ['nullable', 'string', 'max:255'],
            'telepon' => ['nullable', 'string', 'max:50'],
            'whatsapp' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:100'],
            'jam_buka' => ['nullable', 'string', 'max:100'],
            'instagram' => ['nullable', 'string', 'max:100'],
        ], [
            'brand_name.required' => 'Nama brand wajib diisi.',
        ]);

        foreach ($data as $key => $value) {
            Setting::put($key, $value);
        }

        foreach ($this->kunciV2 as $key) {
            if ($request->exists($key)) {
                Setting::put($key, $request->input($key));
            }
        }

        return back()->with('sukses', 'Pengaturan berhasil disimpan.');
    }
}
