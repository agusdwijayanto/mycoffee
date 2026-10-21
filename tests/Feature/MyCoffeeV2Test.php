<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyCoffeeV2Test extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $kasir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@mycoffee.id')->firstOrFail();
        $this->kasir = User::where('email', 'kasir@mycoffee.id')->firstOrFail();
    }

    private function menu(string $nama): Menu
    {
        return Menu::where('nama', $nama)->firstOrFail();
    }

    private function setSetting(string $key, ?string $value): void
    {
        Setting::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    private function jual(Menu $menu, array $opsi = [], int $bayar = 100000): Transaction
    {
        $this->actingAs($this->kasir)->post('/pos/tambah', array_merge(['menu_id' => $menu->id], $opsi))->assertRedirect();
        $respon = $this->actingAs($this->kasir)->post('/pos/checkout', ['bayar' => $bayar]);
        $respon->assertRedirectContains('/pos/struk/');

        return Transaction::latest('id')->firstOrFail();
    }

    // --- Paket A ---

    public function test_catatan_item_tersimpan_dan_tampil_di_struk(): void
    {
        $latte = $this->menu('Latte');
        $tx = $this->jual($latte, ['sugar_level' => 'Low', 'ice_level' => 'Half', 'note' => 'jangan terlalu panas'], 25000);

        $item = $tx->items->first();
        $this->assertSame('Low', $item->sugar_level);
        $this->assertSame('Half', $item->ice_level);
        $this->assertSame('jangan terlalu panas', $item->note);
        $this->assertSame('Regular', $item->size);
        $this->assertNotNull($tx->queue_number);

        $this->actingAs($this->kasir)->get('/pos/struk/'.$tx->id)
            ->assertOk()
            ->assertSee('jangan terlalu panas')
            ->assertSee('Manis: Low')
            ->assertSee('Es: Half')
            ->assertSee('Antrean');

        $this->actingAs($this->admin)->get('/admin/transactions/'.$tx->id)
            ->assertOk()
            ->assertSee('jangan terlalu panas');
    }

    public function test_checkout_qris_bayar_pas(): void
    {
        $espresso = $this->menu('Espresso');
        $this->actingAs($this->kasir)->post('/pos/tambah', ['menu_id' => $espresso->id])->assertRedirect();
        $this->actingAs($this->kasir)->post('/pos/checkout', ['payment_method' => 'qris'])
            ->assertRedirectContains('/pos/struk/');

        $tx = Transaction::latest('id')->firstOrFail();
        $this->assertSame('qris', $tx->payment_method);
        $this->assertSame($tx->total, (int) $tx->bayar);
        $this->assertSame(0, (int) $tx->kembalian);
    }

    public function test_diskon_persen_dan_pajak_menghitung_benar(): void
    {
        $this->setSetting('tax_percent', '10');
        $cappuccino = $this->menu('Cappuccino'); // 25000

        $this->actingAs($this->kasir)->post('/pos/tambah', ['menu_id' => $cappuccino->id])->assertRedirect();
        $this->actingAs($this->kasir)->post('/pos/checkout', ['bayar' => 25000, 'discount_percent' => 10])
            ->assertRedirectContains('/pos/struk/');

        $tx = Transaction::latest('id')->firstOrFail();
        $this->assertSame(25000, (int) $tx->subtotal);
        $this->assertSame(2500, (int) $tx->discount_amount);      // 10% dari 25000
        $this->assertSame(2250, (int) $tx->tax_amount);           // 10% dari (25000-2500)
        $this->assertSame(24750, (int) $tx->total);
        $this->assertSame(250, (int) $tx->kembalian);
    }

    public function test_ekspor_csv_berisi_transaksi(): void
    {
        $this->jual($this->menu('Espresso'), [], 18000);

        $respon = $this->actingAs($this->admin)->get('/admin/laporan/csv');
        $respon->assertOk();
        $respon->assertHeader('content-type', 'text/csv; charset=utf-8');
        $isi = $respon->streamedContent();
        $this->assertStringContainsString('Tanggal', $isi);
        $this->assertStringContainsString('Metode Bayar', $isi);
        $this->assertStringContainsString('tunai', $isi);
        $this->assertStringContainsString('18000', $isi);
        $this->assertStringContainsString('paid', $isi);
    }

    public function test_stok_menipis_terpisah_dari_habis_di_dashboard(): void
    {
        $menu = $this->menu('Espresso');
        $menu->update(['stok' => 3]);
        $this->menu('Mocha')->update(['stok' => 0]);

        $this->actingAs($this->admin)->get('/admin')
            ->assertOk()
            ->assertSee('Stok Menipis')
            ->assertSee('MENIPIS')
            ->assertSee('Menu Habis');
    }

    // --- Paket B ---

    public function test_voucher_persen_dan_kedaluwarsa(): void
    {
        $cappuccino = $this->menu('Cappuccino');
        $this->actingAs($this->kasir)->post('/pos/tambah', ['menu_id' => $cappuccino->id])->assertRedirect();
        $this->actingAs($this->kasir)->post('/pos/checkout', ['bayar' => 22500, 'voucher_code' => 'HEMAT10'])
            ->assertRedirectContains('/pos/struk/');

        $tx = Transaction::latest('id')->firstOrFail();
        $this->assertSame(2500, (int) $tx->discount_amount);
        $this->assertSame('HEMAT10', $tx->voucher_code);
        $this->assertSame(22500, (int) $tx->total);

        // Kode tidak dikenal ditolak
        $this->actingAs($this->kasir)->post('/pos/tambah', ['menu_id' => $cappuccino->id])->assertRedirect();
        $this->actingAs($this->kasir)->post('/pos/checkout', ['bayar' => 25000, 'voucher_code' => 'NGACO'])
            ->assertSessionHasErrors('voucher_code');
    }

    public function test_beli_2_gratis_1(): void
    {
        $espresso = $this->menu('Espresso');
        $this->setSetting('promo_b2g1_menu_id', (string) $espresso->id);

        for ($i = 0; $i < 3; $i++) {
            $this->actingAs($this->kasir)->post('/pos/tambah', ['menu_id' => $espresso->id])->assertRedirect();
        }
        $this->actingAs($this->kasir)->post('/pos/checkout', ['bayar' => 36000])
            ->assertRedirectContains('/pos/struk/');

        $tx = Transaction::latest('id')->firstOrFail();
        $this->assertSame(54000, (int) $tx->subtotal);
        $this->assertSame(18000, (int) $tx->discount_amount);
        $this->assertSame(36000, (int) $tx->total);
        $this->assertStringContainsString('Beli 2 Gratis 1', $tx->discount_note);
    }

    public function test_void_mengembalikan_stok_dan_keluar_dari_omzet(): void
    {
        $espresso = $this->menu('Espresso');
        $stokAwal = $espresso->stok;
        $tx = $this->jual($espresso, [], 18000);
        $this->assertSame($stokAwal - 1, $espresso->fresh()->stok);

        // Void tanpa alasan ditolak
        $this->actingAs($this->admin)->post('/admin/transactions/'.$tx->id.'/void', [])
            ->assertSessionHasErrors('void_reason');

        $this->actingAs($this->admin)->post('/admin/transactions/'.$tx->id.'/void', ['void_reason' => 'salah input'])
            ->assertRedirect();
        $this->assertSame('void', $tx->fresh()->status);
        $this->assertSame($stokAwal, $espresso->fresh()->stok);
        $this->assertDatabaseHas('stock_logs', ['menu_id' => $espresso->id, 'jenis' => 'void', 'perubahan' => 1]);

        // Omzet hari ini kembali nol karena satu-satunya transaksi sudah void
        $this->actingAs($this->admin)->get('/admin')->assertOk()->assertSee('Rp 0');
    }

    public function test_pesanan_pending_siklus_penuh(): void
    {
        $espresso = $this->menu('Espresso');
        $latte = $this->menu('Latte');
        $stokEspresso = $espresso->stok;

        // Pending wajib meja atau nama
        $this->actingAs($this->kasir)->post('/pos/tambah', ['menu_id' => $espresso->id])->assertRedirect();
        $this->actingAs($this->kasir)->post('/pos/simpan-pending', [])
            ->assertSessionHasErrors('table_number');
        $this->actingAs($this->kasir)->post('/pos/checkout', ['bayar' => 1])->assertSessionHasErrors(); // keranjang utuh? tidak: pending gagal, keranjang masih ada

        // Buat pending dengan meja
        $this->actingAs($this->kasir)->post('/pos/simpan-pending', ['table_number' => '5'])
            ->assertRedirect(route('pos.pesanan'));
        $pending = Transaction::where('status', 'pending')->latest('id')->firstOrFail();
        $this->assertSame($stokEspresso - 1, $espresso->fresh()->stok);
        $this->assertDatabaseHas('stock_logs', ['menu_id' => $espresso->id, 'jenis' => 'pesanan']);

        // Buka, tambah item, simpan perubahan
        $this->actingAs($this->kasir)->post('/pos/pesanan/'.$pending->id.'/buka')->assertRedirect('/pos');
        $this->actingAs($this->kasir)->post('/pos/tambah', ['menu_id' => $latte->id])->assertRedirect();
        $this->actingAs($this->kasir)->post('/pos/pesanan/'.$pending->id.'/perbarui')->assertRedirect(route('pos.pesanan'));
        $this->assertSame(2, $pending->fresh()->items()->count());
        $this->assertSame(43000, (int) $pending->fresh()->total); // 18000 + 25000

        // Buka lagi lalu bayar
        $this->actingAs($this->kasir)->post('/pos/pesanan/'.$pending->id.'/buka')->assertRedirect('/pos');
        $this->actingAs($this->kasir)->post('/pos/checkout', ['bayar' => 50000])
            ->assertRedirectContains('/pos/struk/');
        $this->assertSame('paid', $pending->fresh()->status);
        $this->assertSame(7000, (int) $pending->fresh()->kembalian);

        // Pending kedua dibatalkan: stok kembali
        $stokSebelum = $espresso->fresh()->stok;
        $this->actingAs($this->kasir)->post('/pos/tambah', ['menu_id' => $espresso->id])->assertRedirect();
        $this->actingAs($this->kasir)->post('/pos/simpan-pending', ['customer_name' => 'Budi'])->assertRedirect();
        $pending2 = Transaction::where('status', 'pending')->latest('id')->firstOrFail();
        $this->actingAs($this->kasir)->post('/pos/pesanan/'.$pending2->id.'/batal')->assertRedirect();
        $this->assertSame('void', $pending2->fresh()->status);
        $this->assertSame($stokSebelum, $espresso->fresh()->stok);
    }

    public function test_shift_buka_tutup_dan_selisih(): void
    {
        $this->actingAs($this->kasir)->post('/shift/buka', ['modal_awal' => 100000])->assertRedirect();
        $this->jual($this->menu('Espresso'), [], 50000); // tunai 18000

        $shift = \App\Models\Shift::where('user_id', $this->kasir->id)->firstOrFail();
        $ringkasan = $shift->ringkasanPenjualan();
        $this->assertSame(18000, $ringkasan['tunai']);
        $this->assertSame(0, $ringkasan['qris']);
        $this->assertSame(1, $ringkasan['jumlah']);

        $this->actingAs($this->kasir)->post('/shift/'.$shift->id.'/tutup', ['uang_fisik' => 118000])
            ->assertRedirect();
        $shift->refresh();
        $this->assertNotNull($shift->closed_at);
        $this->assertSame(100000, (int) $shift->selisih); // fisik 118000 - tunai sistem 18000
    }

    public function test_log_stok_manual_dan_penjualan(): void
    {
        $menu = $this->menu('Espresso');
        $this->actingAs($this->admin)->put('/admin/menus/'.$menu->id, [
            'nama' => 'Espresso', 'category_id' => $menu->category_id, 'deskripsi' => $menu->deskripsi,
            'harga' => 18000, 'stok' => 15, 'is_available' => 1,
        ])->assertRedirect(route('admin.menus.index'));
        $this->assertDatabaseHas('stock_logs', ['menu_id' => $menu->id, 'jenis' => 'manual', 'perubahan' => -5, 'stok_sesudah' => 15]);

        $this->jual($menu->fresh(), [], 18000);
        $this->assertDatabaseHas('stock_logs', ['menu_id' => $menu->id, 'jenis' => 'jual', 'perubahan' => -1, 'stok_sesudah' => 14]);
    }

    public function test_laba_kotor_dan_bersih_di_laporan(): void
    {
        // Latte: harga 25000, modal 13750 (55%) -> laba 11250 per porsi
        $this->jual($this->menu('Latte'), [], 25000);
        \App\Models\Expense::create([
            'judul' => 'Beli susu', 'kategori' => 'Bahan Baku', 'nominal' => 5000,
            'tanggal' => now()->toDateString(),
        ]);

        $this->actingAs($this->admin)->get('/admin/laporan')
            ->assertOk()
            ->assertSee('Laba Kotor')
            ->assertSee('Rp 11.250')
            ->assertSee('Rp 6.250'); // laba bersih 11250 - 5000
    }

    public function test_tombol_whatsapp_di_halaman_menu(): void
    {
        $this->get('/menu')->assertOk()->assertSee('wa.me/6281234567890');
    }

    public function test_pengaturan_menampilkan_qr_atau_url_menu(): void
    {
        $this->actingAs($this->admin)->get('/admin/settings')
            ->assertOk()
            ->assertSee('QR Menu untuk Meja')
            ->assertSee(url('/menu'));
    }

    public function test_struk_barista_tanpa_harga(): void
    {
        $tx = $this->jual($this->menu('Latte'), ['note' => 'extra hot'], 25000);
        $this->actingAs($this->kasir)->get('/pos/struk/'.$tx->id.'/barista')
            ->assertOk()
            ->assertSee('BARISTA')
            ->assertSee('extra hot')
            ->assertDontSee('25.000');
    }

    // --- Paket C ---

    public function test_ukuran_large_menambah_harga(): void
    {
        $latte = $this->menu('Latte'); // harga_large 6000
        $tx = $this->jual($latte, ['size' => 'Large'], 31000);
        $this->assertSame(31000, (int) $tx->items->first()->harga_snapshot);
        $this->assertSame('Large', $tx->items->first()->size);
        $this->assertSame(31000, (int) $tx->total);
    }

    public function test_addon_menambah_subtotal_dan_snapshot(): void
    {
        $latte = $this->menu('Latte');
        $extraShot = \App\Models\Addon::where('nama', 'Extra Shot')->firstOrFail();
        $tx = $this->jual($latte, ['addons' => [$extraShot->id]], 30000);

        $item = $tx->items->first();
        $this->assertSame(30000, (int) $item->subtotal);
        $this->assertSame('Extra Shot', $item->addons_snapshot[0]['nama']);
        $this->assertSame(5000, $item->addons_snapshot[0]['harga']);
    }

    public function test_bundel_total_dan_diskon(): void
    {
        $bundel = \App\Models\Bundle::where('nama', 'Paket Santai')->firstOrFail();
        $this->actingAs($this->kasir)->post('/pos/tambah-bundel', ['bundle_id' => $bundel->id])->assertRedirect();
        $this->actingAs($this->kasir)->post('/pos/checkout', ['bayar' => 48000])
            ->assertRedirectContains('/pos/struk/');

        $tx = Transaction::latest('id')->firstOrFail();
        $this->assertSame(53000, (int) $tx->subtotal);
        $this->assertSame(5000, (int) $tx->discount_amount);
        $this->assertStringContainsString('Bundel', $tx->discount_note);
        $this->assertSame(48000, (int) $tx->total);
        $this->assertSame(2, $tx->items()->count());
    }

    public function test_bahan_habis_menu_tidak_bisa_dijual(): void
    {
        $susu = \App\Models\Ingredient::where('nama', 'Susu Segar')->firstOrFail();
        $susu->update(['stok' => 100]); // Latte butuh 150 ml per porsi

        $latte = $this->menu('Latte');
        $this->assertTrue($latte->fresh()->habis);

        $this->actingAs($this->kasir)->post('/pos/tambah', ['menu_id' => $latte->id])
            ->assertSessionHasErrors();
        $this->assertSame(0, Transaction::count());
    }

    public function test_bahan_berkurang_saat_penjualan_dan_kembali_saat_void(): void
    {
        $susu = \App\Models\Ingredient::where('nama', 'Susu Segar')->firstOrFail();
        $stokAwal = (float) $susu->stok;
        $tx = $this->jual($this->menu('Latte'), [], 25000);
        $this->assertSame($stokAwal - 150, (float) $susu->fresh()->stok);

        $this->actingAs($this->admin)->post('/admin/transactions/'.$tx->id.'/void', ['void_reason' => 'tes'])
            ->assertRedirect();
        $this->assertSame($stokAwal, (float) $susu->fresh()->stok);
    }

    public function test_login_pin_benar_dan_salah(): void
    {
        auth()->logout();
        $this->post('/login-pin', ['user_id' => $this->kasir->id, 'pin' => '9999'])
            ->assertSessionHasErrors('pin');
        $this->assertGuest();

        $this->post('/login-pin', ['user_id' => $this->kasir->id, 'pin' => '1234'])
            ->assertRedirect('/pos');
        $this->assertAuthenticatedAs($this->kasir);
    }

    public function test_absensi_masuk_lalu_pulang(): void
    {
        $this->actingAs($this->kasir)->post('/absensi/toggle')->assertRedirect();
        $absen = \App\Models\Attendance::where('user_id', $this->kasir->id)->firstOrFail();
        $this->assertNotNull($absen->jam_masuk_at);
        $this->assertNull($absen->jam_keluar_at);

        $this->actingAs($this->kasir)->post('/absensi/toggle')->assertRedirect();
        $this->assertNotNull($absen->fresh()->jam_keluar_at);
    }

    public function test_barista_ubah_status(): void
    {
        $tx = $this->jual($this->menu('Espresso'), [], 18000);
        $this->assertSame('baru', $tx->status_pesanan);

        $this->actingAs($this->kasir)->get('/barista')->assertOk()->assertSee('Espresso');
        $this->actingAs($this->kasir)->post('/barista/'.$tx->id.'/status', ['status_pesanan' => 'dibuat'])
            ->assertRedirect();
        $this->assertSame('dibuat', $tx->fresh()->status_pesanan);
    }

    public function test_menu_musiman_di_luar_rentang_tidak_tampil(): void
    {
        $menu = $this->menu('Pancake');
        $menu->update([
            'tampil_dari' => now()->addDays(2)->toDateString(),
            'tampil_sampai' => now()->addDays(9)->toDateString(),
        ]);

        $this->get('/menu')->assertOk()->assertDontSee('Pancake');
        // Di POS, kartu menu Pancake tidak tampil (nama "Pancake" hanya boleh
        // muncul sebagai teks komponen bundel, bukan kartu menu yang bisa dijual)
        $this->actingAs($this->kasir)->get('/pos')->assertOk()->assertDontSee('pancake.webp');
    }

    public function test_banner_promo_tampil_dalam_rentang(): void
    {
        $this->setSetting('promo_banner_text', 'Promo Spesial Minggu Ini');
        $this->setSetting('promo_banner_dari', now()->subDay()->toDateString());
        $this->setSetting('promo_banner_sampai', now()->addDay()->toDateString());
        $this->get('/')->assertOk()->assertSee('Promo Spesial Minggu Ini');

        $this->setSetting('promo_banner_sampai', now()->subDays(2)->toDateString());
        $this->get('/')->assertOk()->assertDontSee('Promo Spesial Minggu Ini');
    }

    public function test_testimoni_harus_disetujui_dulu(): void
    {
        $this->post('/testimoni', ['nama' => 'Rina', 'isi' => 'Kopinya enak sekali', 'rating' => 5])
            ->assertRedirect();
        $t = \App\Models\Testimonial::where('nama', 'Rina')->firstOrFail();
        $this->assertFalse((bool) $t->disetujui);

        $this->get('/')->assertOk()->assertDontSee('Kopinya enak sekali');

        $this->actingAs($this->admin)->post('/admin/testimonials/'.$t->id.'/setujui')->assertRedirect();
        $this->get('/')->assertOk()->assertSee('Kopinya enak sekali');
    }

    public function test_reservasi_terkirim_dan_terdaftar(): void
    {
        $this->post('/reservasi', [
            'nama' => 'Dedi', 'whatsapp' => '081298765432',
            'tanggal' => now()->addDay()->toDateString(), 'jam' => '19:00', 'jumlah_orang' => 4,
        ])->assertRedirect();

        $this->assertDatabaseHas('reservations', ['nama' => 'Dedi', 'status' => 'menunggu']);
        $this->actingAs($this->admin)->get('/admin/reservations')->assertOk()->assertSee('Dedi');
    }

    public function test_tombol_bahasa_menyimpan_locale_di_session(): void
    {
        $this->get('/')->assertOk()->assertSee('Beranda');
        $this->get('/bahasa/en')->assertRedirect('/')->assertSessionHas('locale', 'en');
        $this->get('/bahasa/id')->assertSessionHas('locale', 'id');
    }

    public function test_label_inggris_dari_session_locale(): void
    {
        // withSession dipakai sebelum request lain: meniru pengunjung yang
        // session-nya sudah berisi locale en (alur browser asli terverifikasi via curl)
        $this->withSession(['locale' => 'en'])->get('/')
            ->assertOk()->assertSee('>Home<', false)->assertSee('View Menu');

        // Label HABIS menjadi SOLD OUT pada locale en
        $this->menu('Mocha')->update(['stok' => 0]);
        $this->withSession(['locale' => 'en'])->get('/menu')->assertOk()->assertSee('SOLD OUT');
    }

    public function test_kanal_tercatat_di_laporan(): void
    {
        $menu = $this->menu('Espresso');
        $this->actingAs($this->kasir)->post('/pos/tambah', ['menu_id' => $menu->id])->assertRedirect();
        $this->actingAs($this->kasir)->post('/pos/checkout', ['bayar' => 18000, 'channel' => 'gofood'])
            ->assertRedirectContains('/pos/struk/');

        $tx = Transaction::latest('id')->firstOrFail();
        $this->assertSame('gofood', $tx->channel);

        $this->actingAs($this->admin)->get('/admin/laporan')
            ->assertOk()
            ->assertSee('GoFood')
            ->assertSee('Rp 18.000');
    }

    public function test_area_pengembangan_static_khusus_admin(): void
    {
        $this->actingAs($this->kasir)->get('/admin/pengembangan')->assertForbidden();
        $this->actingAs($this->admin)->get('/admin/pengembangan')
            ->assertOk()
            ->assertSee('SEGERA HADIR')
            ->assertSee('Multi Cabang');
        $this->actingAs($this->admin)->get('/admin/pengembangan/multi-cabang')
            ->assertOk()
            ->assertSee('SEGERA HADIR')
            ->assertSee('DUMMY');
    }
}
