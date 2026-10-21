<?php

use App\Http\Controllers\Admin\AddonController;
use App\Http\Controllers\Admin\BundleController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\IngredientController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\PengembanganController;
use App\Http\Controllers\Admin\ReservationController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StockLogController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BaristaController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\ShiftController;
use Illuminate\Support\Facades\Route;

// Halaman publik
Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/tentang', [PublicController::class, 'tentang'])->name('tentang');
Route::get('/menu', [PublicController::class, 'menu'])->name('menu');
Route::get('/gallery', [PublicController::class, 'gallery'])->name('gallery');
Route::get('/kontak', [PublicController::class, 'kontak'])->name('kontak');
Route::get('/bahasa/{locale}', [PublicController::class, 'setBahasa'])->name('bahasa');
Route::post('/testimoni', [PublicController::class, 'simpanTestimoni'])->name('testimoni.simpan');
Route::post('/reservasi', [PublicController::class, 'simpanReservasi'])->name('reservasi.simpan');

// Autentikasi
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.proses');
Route::get('/login-pin', [AuthController::class, 'showLoginPin'])->name('login.pin');
Route::post('/login-pin', [AuthController::class, 'loginPin'])->name('login.pin.proses');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Panel (wajib login)
Route::middleware('auth')->group(function () {
    // Absensi: admin dan kasir
    Route::post('/absensi/toggle', [AttendanceController::class, 'toggle'])
        ->middleware('role:admin,kasir')->name('absensi.toggle');

    // Layar barista: admin dan kasir
    Route::middleware('role:admin,kasir')->group(function () {
        Route::get('/barista', [BaristaController::class, 'index'])->name('barista.index');
        Route::post('/barista/{transaction}/status', [BaristaController::class, 'ubahStatus'])->name('barista.status');
    });

    // Shift kasir: admin dan kasir
    Route::middleware('role:admin,kasir')->prefix('shift')->name('shift.')->group(function () {
        Route::get('/', [ShiftController::class, 'index'])->name('index');
        Route::post('/buka', [ShiftController::class, 'buka'])->name('buka');
        Route::post('/{shift}/tutup', [ShiftController::class, 'tutup'])->name('tutup');
    });

    // POS kasir: admin dan kasir
    Route::middleware('role:admin,kasir')->prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [PosController::class, 'index'])->name('index');
        Route::post('/tambah', [PosController::class, 'add'])->name('tambah');
        Route::post('/tambah-bundel', [PosController::class, 'addBundle'])->name('tambahBundel');
        Route::post('/qty/{key}', [PosController::class, 'updateQty'])->name('qty');
        Route::post('/ubah/{key}', [PosController::class, 'updateLine'])->name('ubah');
        Route::post('/hapus/{key}', [PosController::class, 'remove'])->name('hapus');
        Route::post('/kosongkan', [PosController::class, 'clear'])->name('kosongkan');
        Route::post('/checkout', [PosController::class, 'checkout'])->name('checkout');
        Route::post('/simpan-pending', [PosController::class, 'simpanPending'])->name('simpanPending');
        Route::get('/pesanan', [PosController::class, 'pesanan'])->name('pesanan');
        Route::post('/pesanan/{transaction}/buka', [PosController::class, 'bukaPending'])->name('pesanan.buka');
        Route::post('/pesanan/{transaction}/perbarui', [PosController::class, 'perbaruiPending'])->name('pesanan.perbarui');
        Route::post('/pesanan/{transaction}/batal', [PosController::class, 'batalPending'])->name('pesanan.batal');
        Route::get('/struk/{transaction}', [PosController::class, 'struk'])->name('struk');
        Route::get('/struk/{transaction}/barista', [PosController::class, 'strukBarista'])->name('struk.barista');
    });

    // Khusus admin
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/laporan', [DashboardController::class, 'laporan'])->name('laporan');
        Route::get('/laporan/csv', [DashboardController::class, 'eksporCsv'])->name('laporan.csv');
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::resource('menus', MenuController::class)->except(['show']);
        Route::post('menus/{menu}/toggle', [MenuController::class, 'toggle'])->name('menus.toggle');
        Route::resource('galleries', GalleryController::class)->except(['show', 'create']);
        Route::resource('addons', AddonController::class)->except(['show', 'create']);
        Route::resource('bundles', BundleController::class)->except(['show']);
        Route::resource('ingredients', IngredientController::class)->except(['show', 'create']);
        Route::resource('expenses', ExpenseController::class)->except(['show']);
        Route::resource('vouchers', VoucherController::class)->except(['show', 'create']);
        Route::get('stok', [StockLogController::class, 'index'])->name('stok.index');
        Route::get('testimonials', [TestimonialController::class, 'index'])->name('testimonials.index');
        Route::post('testimonials/{testimonial}/setujui', [TestimonialController::class, 'setujui'])->name('testimonials.setujui');
        Route::delete('testimonials/{testimonial}', [TestimonialController::class, 'destroy'])->name('testimonials.destroy');
        Route::get('reservations', [ReservationController::class, 'index'])->name('reservations.index');
        Route::post('reservations/{reservation}/status', [ReservationController::class, 'ubahStatus'])->name('reservations.status');
        Route::delete('reservations/{reservation}', [ReservationController::class, 'destroy'])->name('reservations.destroy');
        Route::get('schedules', [ScheduleController::class, 'index'])->name('schedules.index');
        Route::post('schedules', [ScheduleController::class, 'store'])->name('schedules.store');
        Route::delete('schedules/{schedule}', [ScheduleController::class, 'destroy'])->name('schedules.destroy');
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::post('users/{user}/pin', [UserController::class, 'updatePin'])->name('users.pin');
        Route::post('transactions/{transaction}/void', [TransactionController::class, 'void'])->name('transactions.void');
        Route::get('pengembangan', [PengembanganController::class, 'index'])->name('pengembangan.index');
        Route::get('pengembangan/{slug}', [PengembanganController::class, 'show'])->name('pengembangan.show');
        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
    });

    // Riwayat transaksi: admin dan kasir
    Route::middleware('role:admin,kasir')->prefix('admin')->name('admin.')->group(function () {
        Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');
        Route::get('transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    });
});
