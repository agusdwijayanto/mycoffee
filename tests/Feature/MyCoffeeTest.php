<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyCoffeeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function admin(): User
    {
        return User::where('email', 'admin@mycoffee.id')->firstOrFail();
    }

    private function kasir(): User
    {
        return User::where('email', 'kasir@mycoffee.id')->firstOrFail();
    }

    // Kriteria 1: halaman menu publik tampil, menu stok 0 berlabel HABIS
    public function test_halaman_menu_publik_menampilkan_stiker_habis_untuk_stok_nol(): void
    {
        $menu = Menu::where('nama', 'Espresso')->firstOrFail();
        $menu->update(['stok' => 0]);

        $response = $this->get('/menu');

        $response->assertOk();
        $response->assertSee('Espresso');
        $response->assertSee('HABIS');
        $this->assertTrue($menu->fresh()->habis);
    }

    // Kriteria 1b: menu dinonaktifkan manual juga habis walau stok masih ada
    public function test_menu_nonaktif_manual_juga_dianggap_habis(): void
    {
        $menu = Menu::where('nama', 'Latte')->firstOrFail();
        $menu->update(['is_available' => false]);

        $this->assertTrue($menu->fresh()->habis);
        $this->get('/menu')->assertOk()->assertSee('HABIS');
    }

    // Kriteria 2: kasir ditolak membuka kelola menu
    public function test_kasir_ditolak_membuka_kelola_menu(): void
    {
        $this->actingAs($this->kasir())
            ->get('/admin/menus')
            ->assertForbidden();

        $this->actingAs($this->kasir())
            ->get('/admin/categories')
            ->assertForbidden();

        $this->actingAs($this->kasir())
            ->get('/admin/galleries')
            ->assertForbidden();

        $this->actingAs($this->kasir())
            ->get('/admin/settings')
            ->assertForbidden();

        // Admin boleh membuka semuanya
        $this->actingAs($this->admin())->get('/admin/menus')->assertOk();
        $this->actingAs($this->admin())->get('/admin')->assertOk();
    }

    // Kriteria 2b: tamu tidak bisa membuka panel
    public function test_tamu_diarahkan_ke_login_saat_membuka_panel(): void
    {
        $this->get('/pos')->assertRedirect('/login');
        $this->get('/admin/menus')->assertRedirect('/login');
    }

    // Kriteria 3: checkout mengurangi stok; menu stok 1 terjual 1 menjadi habis
    public function test_checkout_mengurangi_stok_dan_menu_habis_setelah_terjual_habis(): void
    {
        $menu = Menu::where('nama', 'Espresso')->firstOrFail();
        $menu->update(['stok' => 1]);

        $this->actingAs($this->kasir());

        $this->post('/pos/tambah', ['menu_id' => $menu->id])->assertRedirect();
        $response = $this->post('/pos/checkout', ['bayar' => 18000]);
        $response->assertRedirectContains('/pos/struk/');

        $this->assertSame(0, $menu->fresh()->stok);
        $this->assertTrue($menu->fresh()->habis);
        $this->assertSame(1, Transaction::count());

        $trx = Transaction::first();
        $this->assertSame(18000, $trx->total);
        $this->assertSame(0, $trx->kembalian);
        $this->assertSame('Espresso', $trx->items->first()->nama_snapshot);
        $this->assertSame(18000, $trx->items->first()->harga_snapshot);
    }

    // Kriteria 3b: menu habis tidak bisa ditambahkan ke keranjang POS
    public function test_menu_habis_tidak_bisa_masuk_keranjang_pos(): void
    {
        $menu = Menu::where('nama', 'Mocha')->firstOrFail();
        $menu->update(['stok' => 0]);

        $this->actingAs($this->kasir());
        $this->post('/pos/tambah', ['menu_id' => $menu->id])
            ->assertSessionHasErrors('keranjang');

        $this->assertSame([], session('cart', []));
    }

    // Kriteria 3c: qty tidak boleh melebihi stok
    public function test_qty_keranjang_tidak_boleh_melebihi_stok(): void
    {
        $menu = Menu::where('nama', 'Pancake')->firstOrFail();
        $menu->update(['stok' => 1]);

        $this->actingAs($this->kasir());
        $this->post('/pos/tambah', ['menu_id' => $menu->id])->assertRedirect();
        $this->post('/pos/tambah', ['menu_id' => $menu->id])
            ->assertSessionHasErrors('keranjang');

        $cart = session('cart');
        $this->assertSame(1, $cart[$menu->id]['qty']);
    }

    // Kriteria 4: bayar kurang ditolak, transaksi tidak tersimpan, stok tidak berubah
    public function test_checkout_bayar_kurang_ditolak_dan_tidak_tersimpan(): void
    {
        $menu = Menu::where('nama', 'Nasi Goreng')->firstOrFail();
        $stokAwal = $menu->stok;

        $this->actingAs($this->kasir());
        $this->post('/pos/tambah', ['menu_id' => $menu->id])->assertRedirect();
        $this->post('/pos/checkout', ['bayar' => 10000])
            ->assertSessionHasErrors('bayar');

        $this->assertSame(0, Transaction::count());
        $this->assertSame($stokAwal, $menu->fresh()->stok);
    }

    // Kriteria 5: omzet dashboard sama dengan total transaksi
    public function test_omzet_dashboard_sama_dengan_total_transaksi(): void
    {
        $kasir = $this->kasir();
        Transaction::create(['user_id' => $kasir->id, 'total' => 50000, 'bayar' => 50000, 'kembalian' => 0]);
        Transaction::create(['user_id' => $kasir->id, 'total' => 30000, 'bayar' => 50000, 'kembalian' => 20000]);

        $response = $this->actingAs($this->admin())->get('/admin');
        $response->assertOk();
        $response->assertSee('Rp 80.000');

        $responseLaporan = $this->actingAs($this->admin())->get('/admin/laporan');
        $responseLaporan->assertOk();
        $responseLaporan->assertSee('Rp 80.000');
    }

    // Kriteria 6: ganti brand di settings mengubah header publik
    public function test_ganti_brand_di_pengaturan_mengubah_header_publik(): void
    {
        $this->actingAs($this->admin())
            ->put('/admin/settings', [
                'brand_name' => 'KopiBaru',
                'tagline' => 'Tagline Baru',
                'alamat' => 'Jl. Baru No. 2, Jakarta',
                'telepon' => '0812-0000-0000',
                'whatsapp' => '0812-0000-0000',
                'email' => 'halo@kopibaru.id',
                'jam_buka' => '09.00 - 21.00',
                'instagram' => '@kopibaru.id',
            ])
            ->assertRedirect();

        $this->get('/')->assertOk()->assertSee('KopiBaru');
        $this->get('/menu')->assertOk()->assertSee('KopiBaru');
    }

    // Halaman publik lain bisa dibuka
    public function test_halaman_publik_lain_bisa_dibuka(): void
    {
        $this->get('/')->assertOk()->assertSee('MyCoffee');
        $this->get('/tentang')->assertOk();
        $this->get('/gallery')->assertOk()->assertSee('Tampilan Depan MyCoffee');
        $this->get('/kontak')->assertOk()->assertSee('Jl. Kopi No. 1, Jakarta');
        $this->get('/login')->assertOk();
    }

    // Login dengan akun dummy bekerja dan mengarah sesuai peran
    public function test_login_kasir_dan_admin(): void
    {
        $this->post('/login', ['email' => 'kasir@mycoffee.id', 'password' => 'password'])
            ->assertRedirect(route('pos.index'));
        $this->post('/logout');

        $this->post('/login', ['email' => 'admin@mycoffee.id', 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));
        $this->post('/logout');

        $this->post('/login', ['email' => 'kasir@mycoffee.id', 'password' => 'salah'])
            ->assertSessionHasErrors('email');
    }

    // Snapshot harga: edit harga menu tidak mengubah transaksi lama
    public function test_snapshot_harga_transaksi_tidak_berubah_saat_harga_menu_diedit(): void
    {
        $menu = Menu::where('nama', 'Latte')->firstOrFail();

        $this->actingAs($this->kasir());
        $this->post('/pos/tambah', ['menu_id' => $menu->id])->assertRedirect();
        $this->post('/pos/checkout', ['bayar' => 25000])->assertRedirectContains('/pos/struk/');

        $menu->update(['harga' => 99000]);

        $item = Transaction::first()->items->first();
        $this->assertSame(25000, $item->harga_snapshot);
        $this->assertSame(25000, Transaction::first()->total);
    }
}
