<?php

namespace Tests\Feature;

use App\Models\Addon;
use App\Models\Bundle;
use App\Models\Category;
use App\Models\Expense;
use App\Models\Gallery;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test regresi paket perbaikan:
 * - Error "Undefined variable" di halaman admin voucher (dan audit kelas bug yang sama
 *   lewat pembukaan semua halaman GET admin/POS).
 * - Pop-up opsi POS menggantikan panel opsi lama; ubah baris keranjang.
 * - Drawer sidebar dan poles publik (strip gallery, grup kategori).
 */
class PerbaikanTest extends TestCase
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

    private function buatTransaksi(): Transaction
    {
        $espresso = $this->menu('Espresso');
        $this->actingAs($this->kasir)->post('/pos/tambah', ['menu_id' => $espresso->id])->assertRedirect();
        $respon = $this->actingAs($this->kasir)->post('/pos/checkout', ['bayar' => 50000]);
        $respon->assertRedirectContains('/pos/struk/');

        return Transaction::latest('id')->firstOrFail();
    }

    public function test_halaman_voucher_index_dan_edit_bisa_dibuka(): void
    {
        $voucher = Voucher::create(['kode' => 'UJI10', 'tipe' => 'persen', 'nilai' => 10, 'aktif' => true]);

        $this->actingAs($this->admin)->get('/admin/vouchers')->assertOk();
        $this->actingAs($this->admin)->get('/admin/vouchers/'.$voucher->id.'/edit')->assertOk();
    }

    public function test_semua_halaman_get_admin_dan_pos_bisa_dibuka_admin(): void
    {
        $trx = $this->buatTransaksi();
        $kategori = Category::firstOrFail();
        $menu = Menu::firstOrFail();
        $gallery = Gallery::firstOrFail();
        $addon = Addon::firstOrFail();
        $bundel = Bundle::firstOrFail();
        $bahan = Ingredient::firstOrFail();
        $pengeluaran = Expense::first() ?? Expense::create([
            'judul' => 'Uji', 'kategori' => 'Operasional', 'nominal' => 1000, 'tanggal' => today(),
        ]);
        $voucher = Voucher::first() ?? Voucher::create(['kode' => 'UJI10', 'tipe' => 'persen', 'nilai' => 10, 'aktif' => true]);

        $urls = [
            '/admin', '/admin/laporan', '/admin/laporan/csv',
            '/admin/categories', '/admin/categories/create', '/admin/categories/'.$kategori->id.'/edit',
            '/admin/menus', '/admin/menus/create', '/admin/menus/'.$menu->id.'/edit',
            '/admin/galleries', '/admin/galleries/'.$gallery->id.'/edit',
            '/admin/addons', '/admin/addons/'.$addon->id.'/edit',
            '/admin/bundles', '/admin/bundles/create', '/admin/bundles/'.$bundel->id.'/edit',
            '/admin/ingredients', '/admin/ingredients/'.$bahan->id.'/edit',
            '/admin/expenses', '/admin/expenses/create', '/admin/expenses/'.$pengeluaran->id.'/edit',
            '/admin/vouchers', '/admin/vouchers/'.$voucher->id.'/edit',
            '/admin/stok', '/admin/testimonials', '/admin/reservations', '/admin/schedules',
            '/admin/users', '/admin/settings',
            '/admin/transactions', '/admin/transactions/'.$trx->id,
            '/admin/pengembangan', '/admin/pengembangan/multi-cabang', '/admin/pengembangan/payment-gateway',
            '/pos', '/pos/pesanan', '/pos/struk/'.$trx->id, '/pos/struk/'.$trx->id.'/barista',
            '/shift', '/barista',
        ];

        foreach ($urls as $url) {
            $this->actingAs($this->admin)->get($url)->assertOk("Gagal membuka $url");
        }
    }

    public function test_layout_admin_memakai_drawer_dan_tautan_manual_book(): void
    {
        $this->actingAs($this->admin)->get('/admin')
            ->assertOk()
            ->assertSee('sidebar-overlay')
            ->assertSee('tombol-menu')
            ->assertSee('Manual Book')
            ->assertSee('manual-book.html');
    }

    public function test_pos_memakai_popup_opsi_dan_keranjang_bisa_diubah(): void
    {
        $this->actingAs($this->kasir)->get('/pos')
            ->assertOk()
            ->assertSee('modal-opsi')
            ->assertSee('data-opsi-tambah')
            ->assertDontSee('pi-opsi')
            ->assertDontSee('Tambah dgn Opsi');

        $latte = $this->menu('Latte');
        $this->actingAs($this->kasir)->post('/pos/tambah', [
            'menu_id' => $latte->id, 'sugar_level' => 'Low', 'ice_level' => 'Normal',
        ])->assertRedirect();

        $this->actingAs($this->kasir)->get('/pos')
            ->assertOk()
            ->assertSee('bar-keranjang')
            ->assertSee('data-opsi-ubah');

        $cart = session('cart');
        $this->assertCount(1, $cart);
        $key = (string) array_key_first($cart);

        $this->actingAs($this->kasir)->post('/pos/ubah/'.$key, [
            'size' => 'Regular', 'sugar_level' => 'Half', 'ice_level' => 'Half', 'qty' => 2,
        ])->assertRedirect();

        $cart = session('cart');
        $this->assertCount(1, $cart);
        $baris = array_values($cart)[0];
        $this->assertSame('Half', $baris['sugar_level']);
        $this->assertSame('Half', $baris['ice_level']);
        $this->assertSame(2, $baris['qty']);

        $respon = $this->actingAs($this->kasir)->post('/pos/checkout', ['bayar' => 50000]);
        $respon->assertRedirectContains('/pos/struk/');
        $trx = Transaction::latest('id')->firstOrFail();
        $this->assertSame(50000, $trx->total);
        $this->assertSame('Half', $trx->items->first()->sugar_level);
        $this->assertSame(2, $trx->items->first()->qty);
    }

    public function test_pos_tambah_dengan_jumlah_dari_popup(): void
    {
        $espresso = $this->menu('Espresso');
        $this->actingAs($this->kasir)->post('/pos/tambah', ['menu_id' => $espresso->id, 'qty' => 3])->assertRedirect();
        $cart = session('cart');
        $this->assertSame(3, array_values($cart)[0]['qty']);
    }

    public function test_publik_menu_grup_kategori_dan_beranda_strip_gallery(): void
    {
        $this->get('/menu')->assertOk()
            ->assertSee('menu-kat-judul')
            ->assertSee('card-menu')
            ->assertSee('besar');
        $this->get('/')->assertOk()
            ->assertSee('strip-gallery');
    }
}
