<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('pin')->nullable()->after('password');
        });

        Schema::table('menus', function (Blueprint $table) {
            $table->unsignedBigInteger('harga_modal')->default(0)->after('harga');
            $table->unsignedBigInteger('harga_large')->nullable()->after('harga_modal');
            $table->date('tampil_dari')->nullable()->after('is_available');
            $table->date('tampil_sampai')->nullable()->after('tampil_dari');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->default('tunai')->after('user_id');
            $table->string('customer_name')->nullable()->after('payment_method');
            $table->string('table_number')->nullable()->after('customer_name');
            $table->string('channel')->default('dine_in')->after('table_number');
            $table->string('status')->default('paid')->after('channel');
            $table->text('void_reason')->nullable()->after('status');
            $table->unsignedInteger('queue_number')->nullable()->after('void_reason');
            $table->string('status_pesanan')->default('baru')->after('queue_number');
            $table->unsignedBigInteger('subtotal')->default(0)->after('status_pesanan');
            $table->unsignedBigInteger('discount_amount')->default(0)->after('subtotal');
            $table->unsignedBigInteger('tax_amount')->default(0)->after('discount_amount');
            $table->decimal('tax_percent_snapshot', 5, 2)->default(0)->after('tax_amount');
            $table->string('voucher_code')->nullable()->after('tax_percent_snapshot');
            $table->text('discount_note')->nullable()->after('voucher_code');
        });

        Schema::table('transaction_items', function (Blueprint $table) {
            $table->unsignedBigInteger('harga_modal_snapshot')->default(0)->after('harga_snapshot');
            $table->string('size')->default('Regular')->after('qty');
            $table->string('sugar_level')->nullable()->after('size');
            $table->string('ice_level')->nullable()->after('sugar_level');
            $table->text('note')->nullable()->after('ice_level');
            $table->json('addons_snapshot')->nullable()->after('note');
        });

        Schema::create('addons', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->unsignedBigInteger('harga');
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('bundles', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->unsignedBigInteger('harga');
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('bundle_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bundle_id')->constrained('bundles')->cascadeOnDelete();
            $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete();
            $table->unsignedInteger('qty')->default(1);
            $table->timestamps();
        });

        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('satuan');
            $table->decimal('stok', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('menu_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained('ingredients')->cascadeOnDelete();
            $table->decimal('qty_per_porsi', 10, 2);
            $table->timestamps();
        });

        Schema::create('stock_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('perubahan');
            $table->integer('stok_sebelum');
            $table->integer('stok_sesudah');
            $table->string('jenis'); // manual, jual, void, pesanan, batal
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->unsignedBigInteger('modal_awal')->default(0);
            $table->unsignedBigInteger('uang_fisik')->nullable();
            $table->bigInteger('selisih')->nullable();
            $table->string('catatan')->nullable();
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('kategori');
            $table->unsignedBigInteger('nominal');
            $table->date('tanggal');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('tipe'); // persen, nominal
            $table->unsignedBigInteger('nilai');
            $table->boolean('aktif')->default(true);
            $table->date('berlaku_sampai')->nullable();
            $table->timestamps();
        });

        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->text('isi');
            $table->unsignedTinyInteger('rating')->default(5);
            $table->boolean('disetujui')->default(false);
            $table->timestamps();
        });

        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('whatsapp');
            $table->date('tanggal');
            $table->time('jam');
            $table->unsignedInteger('jumlah_orang');
            $table->text('catatan')->nullable();
            $table->string('status')->default('menunggu'); // menunggu, dikonfirmasi, selesai, dibatalkan
            $table->timestamps();
        });

        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('hari'); // 0=Minggu .. 6=Sabtu
            $table->time('jam_masuk');
            $table->time('jam_selesai');
            $table->timestamps();
        });

        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('tanggal');
            $table->timestamp('jam_masuk_at');
            $table->timestamp('jam_keluar_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('schedules');
        Schema::dropIfExists('reservations');
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('vouchers');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('shifts');
        Schema::dropIfExists('stock_logs');
        Schema::dropIfExists('menu_ingredients');
        Schema::dropIfExists('ingredients');
        Schema::dropIfExists('bundle_items');
        Schema::dropIfExists('bundles');
        Schema::dropIfExists('addons');

        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropColumn(['harga_modal_snapshot', 'size', 'sugar_level', 'ice_level', 'note', 'addons_snapshot']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'payment_method', 'customer_name', 'table_number', 'channel', 'status', 'void_reason',
                'queue_number', 'status_pesanan', 'subtotal', 'discount_amount', 'tax_amount',
                'tax_percent_snapshot', 'voucher_code', 'discount_note',
            ]);
        });

        Schema::table('menus', function (Blueprint $table) {
            $table->dropColumn(['harga_modal', 'harga_large', 'tampil_dari', 'tampil_sampai']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('pin');
        });
    }
};
