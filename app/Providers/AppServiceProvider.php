<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Bagikan pengaturan brand ke semua view (header, footer, dll)
        View::composer('*', function ($view) {
            $defaults = [
                'brand_name' => 'MyCoffee',
                'tagline' => 'Kedai Kopi Hangat di Tengah Jakarta',
                'alamat' => 'Jl. Kopi No. 1, Jakarta',
                'telepon' => '0812-3456-7890',
                'whatsapp' => '0812-3456-7890',
                'email' => 'halo@mycoffee.id',
                'jam_buka' => '08.00 - 22.00',
                'instagram' => '@mycoffee.id',
                'whatsapp_pemilik' => '0812-3456-7890',
                'jam_buka_libur' => '',
                'tax_percent' => '0',
                'stok_minimum' => '5',
                'promo_b2g1_menu_id' => '',
                'promo_banner_text' => '',
                'promo_banner_dari' => '',
                'promo_banner_sampai' => '',
            ];

            $view->with('settings', array_merge($defaults, Setting::all_map()));
        });
    }
}
