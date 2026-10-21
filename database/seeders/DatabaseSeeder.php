<?php

namespace Database\Seeders;

use App\Models\Addon;
use App\Models\Bundle;
use App\Models\Category;
use App\Models\Expense;
use App\Models\Gallery;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $demo = filter_var(env('DEMO_DATA', false), FILTER_VALIDATE_BOOLEAN);
        $stokAwal = $demo ? 50 : 20;

        // Akun dummy (PIN dummy: admin 1111, kasir 1234 — wajib diganti untuk pemakaian nyata)
        $admin = User::updateOrCreate(
            ['email' => 'admin@mycoffee.id'],
            ['name' => 'Admin MyCoffee', 'password' => Hash::make('password'), 'role' => 'admin', 'pin' => Hash::make('1111')]
        );
        $kasir = User::updateOrCreate(
            ['email' => 'kasir@mycoffee.id'],
            ['name' => 'Kasir MyCoffee', 'password' => Hash::make('password'), 'role' => 'kasir', 'pin' => Hash::make('1234')]
        );

        // Kategori
        $kategori = [];
        foreach (['Kopi', 'Non-Kopi', 'Makanan', 'Dessert'] as $nama) {
            $kategori[$nama] = Category::updateOrCreate(
                ['slug' => Str::slug($nama)],
                ['nama' => $nama]
            );
        }

        // Menu: [kategori, nama, harga, harga_large (tambahan Large, minuman saja), foto, deskripsi]
        $menus = [
            ['Kopi', 'Espresso', 18000, 5000, 'espresso.webp', 'Satu shot pekat dari biji arabika pilihan yang digiling segar, rasa intens dengan crema keemasan dan aroma cokelat panggang.'],
            ['Kopi', 'Americano', 20000, 5000, 'americano.webp', 'Espresso yang dipadukan air panas, menghasilkan kopi hitam yang ringan, bersih, dan menonjolkan rasa asli bijinya tanpa ampas.'],
            ['Kopi', 'Cappuccino', 25000, 6000, 'cappuccino.webp', 'Perpaduan seimbang espresso, susu kukus, dan busa susu tebal yang lembut, ditaburi bubuk kakao di atasnya.'],
            ['Kopi', 'Latte', 25000, 6000, 'latte.webp', 'Espresso dengan susu kukus yang creamy dan sedikit busa, rasa kopinya halus dengan latte art cantik di permukaan.'],
            ['Kopi', 'Mocha', 28000, 6000, 'mocha.webp', 'Latte yang diperkaya cokelat Belgia dan susu segar, manis gurih dengan rasa kopi yang tetap terasa di akhir.'],
            ['Non-Kopi', 'Matcha Latte', 27000, 6000, 'matcha-latte.webp', 'Bubuk matcha premium Jepang yang dikocok hingga berbusa lalu dipadukan susu segar, rasanya earthy, lembut, dan tidak pahit.'],
            ['Non-Kopi', 'Chocolate', 25000, 6000, 'chocolate.webp', 'Cokelat kental dari kakao asli yang dilelehkan dengan susu, disajikan hangat dengan rasa manis yang pas dan menghangatkan.'],
            ['Non-Kopi', 'Vanilla Milkshake', 28000, 6000, 'vanilla-milkshake.webp', 'Es krim vanila diblender dengan susu segar sampai halus dan dingin, teksturnya creamy dengan aroma vanila yang wangi.'],
            ['Makanan', 'Nasi Goreng', 35000, null, 'nasi-goreng.webp', 'Nasi goreng spesial bumbu rumahan dengan telur mata sapi, potongan ayam, udang, sambal terasi, dan kerupuk renyah.'],
            ['Makanan', 'Pasta', 38000, null, 'pasta.webp', 'Pasta al dente dengan saus krim bawang putih, jamur, dan taburan keju parmesan, gurih dan mengenyangkan.'],
            ['Makanan', 'Sandwich', 30000, null, 'sandwich.webp', 'Roti panggang isi ayam panggang, telur, selada, tomat, dan saus spesial, ringan tapi tetap bikin kenyang.'],
            ['Makanan', 'Pancake', 28000, null, 'pancake.webp', 'Pancake empuk dua lapis dengan sirup maple, mentega, dan potongan buah segar, teman paling pas untuk secangkir kopi.'],
            ['Dessert', 'Ice Cream', 20000, null, 'ice-cream.webp', 'Dua scoop es krim lembut pilihan rasa vanila, cokelat, atau stroberi, penutup manis yang selalu menyegarkan.'],
        ];

        $menuByNama = [];
        foreach ($menus as [$kat, $nama, $harga, $hargaLarge, $foto, $deskripsi]) {
            $menuByNama[$nama] = Menu::updateOrCreate(
                ['nama' => $nama],
                [
                    'category_id' => $kategori[$kat]->id,
                    'deskripsi' => $deskripsi,
                    'harga' => $harga,
                    'harga_modal' => (int) round($harga * 0.55),
                    'harga_large' => $hargaLarge,
                    'foto' => $foto,
                    'stok' => $stokAwal,
                    'is_available' => true,
                ]
            );
        }

        // Gallery: [judul, foto]
        $galleries = [
            ['Tampilan Depan MyCoffee', 'tampilan-depan-mycoffee.webp'],
            ['Tampilan Ruang Utama — Wide Shot', 'tampilan-ruang-utama-wide-shot.webp'],
            ['Area Tempat Duduk Cozy', 'area-tempat-duduk-cozy.webp'],
            ['Meja Kasir & Display', 'meja-kasir-display.webp'],
            ['Mesin Espresso', 'mesin-espresso.webp'],
            ['Latte Art — Heart', 'latte-art-heart.webp'],
            ['Latte Art — Rosetta', 'latte-art-rosetta.webp'],
            ['Gelas Kopi & Uap', 'gelas-kopi-uap.webp'],
            ['Close-up Biji Kopi', 'close-up-biji-kopi.webp'],
            ['Rak Buku & Tanaman', 'rak-buku-tanaman.webp'],
            ['Rainy Day Coffee Shop', 'rainy-day-coffee-shop.webp'],
            ['Golden Hour di Coffee Shop', 'golden-hour-di-coffee-shop.webp'],
            ['Malam Hari di Coffee Shop', 'malam-hari-di-coffee-shop.webp'],
        ];

        foreach ($galleries as [$judul, $foto]) {
            Gallery::updateOrCreate(['foto' => $foto], ['judul' => $judul]);
        }

        // Add-on berbayar
        foreach ([['Extra Shot', 5000], ['Susu Oat', 6000], ['Gula Aren', 3000]] as [$nama, $harga]) {
            Addon::updateOrCreate(['nama' => $nama], ['harga' => $harga, 'aktif' => true]);
        }

        // Paket bundel contoh: Latte + Pancake lebih hemat
        $bundle = Bundle::updateOrCreate(['nama' => 'Paket Santai'], ['harga' => 48000, 'aktif' => true]);
        $bundle->items()->delete();
        $bundle->items()->create(['menu_id' => $menuByNama['Latte']->id, 'qty' => 1]);
        $bundle->items()->create(['menu_id' => $menuByNama['Pancake']->id, 'qty' => 1]);

        // Voucher contoh
        Voucher::updateOrCreate(['kode' => 'HEMAT10'], ['tipe' => 'persen', 'nilai' => 10, 'aktif' => true, 'berlaku_sampai' => null]);
        Voucher::updateOrCreate(['kode' => 'NGOPI20'], ['tipe' => 'nominal', 'nilai' => 20000, 'aktif' => true, 'berlaku_sampai' => null]);

        // Bahan baku + resep contoh
        $susuSegar = Ingredient::updateOrCreate(['nama' => 'Susu Segar'], ['satuan' => 'ml', 'stok' => 20000]);
        $bijiKopi = Ingredient::updateOrCreate(['nama' => 'Biji Kopi'], ['satuan' => 'gr', 'stok' => 5000]);
        $gula = Ingredient::updateOrCreate(['nama' => 'Gula'], ['satuan' => 'gr', 'stok' => 5000]);
        $susuKental = Ingredient::updateOrCreate(['nama' => 'Susu Kental'], ['satuan' => 'ml', 'stok' => 5000]);

        $resep = [
            'Espresso' => [$bijiKopi->id => 18],
            'Americano' => [$bijiKopi->id => 18],
            'Cappuccino' => [$bijiKopi->id => 18, $susuSegar->id => 120, $gula->id => 10],
            'Latte' => [$bijiKopi->id => 18, $susuSegar->id => 150, $gula->id => 10],
            'Mocha' => [$bijiKopi->id => 18, $susuSegar->id => 130, $susuKental->id => 20, $gula->id => 10],
        ];
        foreach ($resep as $namaMenu => $bahan) {
            $menuByNama[$namaMenu]->ingredients()->sync(
                collect($bahan)->map(fn ($qty) => ['qty_per_porsi' => $qty])->all()
            );
        }

        // Pengaturan brand & operasional (data dummy)
        $settings = [
            'brand_name' => 'MyCoffee',
            'tagline' => 'Kedai Kopi Hangat di Tengah Jakarta',
            'alamat' => 'Jl. Kopi No. 1, Jakarta',
            'telepon' => '0812-3456-7890',
            'whatsapp' => '0812-3456-7890',
            'whatsapp_pemilik' => '0812-3456-7890',
            'email' => 'halo@mycoffee.id',
            'jam_buka' => '08.00 - 22.00',
            'jam_buka_libur' => '',
            'instagram' => '@mycoffee.id',
            'tax_percent' => '0',
            'stok_minimum' => '5',
            'promo_b2g1_menu_id' => '',
            'promo_banner_text' => '',
            'promo_banner_dari' => '',
            'promo_banner_sampai' => '',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        if ($demo) {
            $this->seedDemo($menuByNama, $kasir, $admin);
        }
    }

    /** Data contoh: transaksi tersebar 7 hari terakhir + pengeluaran, mengurangi stok secara normal. */
    private function seedDemo(array $menuByNama, User $kasir, User $admin): void
    {
        if (Transaction::count() > 0) {
            return;
        }

        // [hari ke belakang, user, metode, kanal, [nama menu => qty]]
        $daftar = [
            [6, $kasir, 'tunai', 'dine_in', ['Latte' => 2, 'Pancake' => 1]],
            [6, $kasir, 'qris', 'takeaway', ['Espresso' => 1, 'Sandwich' => 1]],
            [5, $kasir, 'tunai', 'dine_in', ['Cappuccino' => 2, 'Americano' => 1]],
            [5, $admin, 'qris', 'gofood', ['Nasi Goreng' => 2, 'Chocolate' => 2]],
            [4, $kasir, 'tunai', 'takeaway', ['Vanilla Milkshake' => 2, 'Ice Cream' => 1]],
            [4, $kasir, 'tunai', 'dine_in', ['Mocha' => 1, 'Pasta' => 1]],
            [3, $kasir, 'qris', 'dine_in', ['Latte' => 3, 'Sandwich' => 2]],
            [3, $kasir, 'tunai', 'grabfood', ['Matcha Latte' => 2, 'Pancake' => 2]],
            [2, $kasir, 'tunai', 'dine_in', ['Americano' => 2, 'Espresso' => 2]],
            [2, $admin, 'qris', 'dine_in', ['Cappuccino' => 1, 'Ice Cream' => 2]],
            [1, $kasir, 'tunai', 'takeaway', ['Mocha' => 2, 'Nasi Goreng' => 1]],
            [1, $kasir, 'qris', 'dine_in', ['Latte' => 2, 'Chocolate' => 1, 'Pancake' => 1]],
            [0, $kasir, 'tunai', 'dine_in', ['Espresso' => 2, 'Americano' => 1]],
            [0, $kasir, 'qris', 'dine_in', ['Cappuccino' => 2, 'Sandwich' => 1]],
            [0, $kasir, 'tunai', 'takeaway', ['Matcha Latte' => 1, 'Ice Cream' => 1]],
        ];

        $antreanPerHari = [];
        foreach ($daftar as [$hariLalu, $user, $metode, $kanal, $items]) {
            $tanggal = now()->subDays($hariLalu)->setTime(9 + ($antreanPerHari[$hariLalu] ?? 0) * 2, 15);
            $antreanPerHari[$hariLalu] = ($antreanPerHari[$hariLalu] ?? 0) + 1;

            $subtotal = 0;
            $baris = [];
            foreach ($items as $nama => $qty) {
                $menu = $menuByNama[$nama];
                $subtotal += $menu->harga * $qty;
                $baris[] = [$menu, $qty];
            }

            $trx = Transaction::create([
                'user_id' => $user->id,
                'payment_method' => $metode,
                'channel' => $kanal,
                'status' => 'paid',
                'queue_number' => $antreanPerHari[$hariLalu],
                'subtotal' => $subtotal,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total' => $subtotal,
                'bayar' => $metode === 'qris' ? $subtotal : (int) (ceil($subtotal / 50000) * 50000),
                'kembalian' => $metode === 'qris' ? 0 : (int) (ceil($subtotal / 50000) * 50000) - $subtotal,
            ]);
            $trx->forceFill(['created_at' => $tanggal, 'updated_at' => $tanggal])->save();

            foreach ($baris as [$menu, $qty]) {
                $trx->items()->create([
                    'menu_id' => $menu->id,
                    'nama_snapshot' => $menu->nama,
                    'harga_snapshot' => $menu->harga,
                    'harga_modal_snapshot' => $menu->harga_modal,
                    'qty' => $qty,
                    'size' => 'Regular',
                    'subtotal' => $menu->harga * $qty,
                ]);
                $menu->decrement('stok', $qty);
                foreach ($menu->ingredients as $bahan) {
                    $bahan->decrement('stok', (float) $bahan->pivot->qty_per_porsi * $qty);
                }
            }
        }

        foreach ([
            ['Beli susu segar 10 liter', 'Bahan Baku', 180000, 5],
            ['Beli biji kopi arabika 2 kg', 'Bahan Baku', 420000, 4],
            ['Token listrik kedai', 'Operasional', 200000, 3],
            ['Gas elpiji 3 kg', 'Operasional', 22000, 1],
        ] as [$judul, $kat, $nominal, $hariLalu]) {
            Expense::create([
                'judul' => $judul,
                'kategori' => $kat,
                'nominal' => $nominal,
                'tanggal' => now()->subDays($hariLalu)->toDateString(),
                'catatan' => 'Data pengeluaran contoh (demo).',
            ]);
        }
    }
}
