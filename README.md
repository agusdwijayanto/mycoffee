# MyCoffee

Aplikasi kedai kopi berbasis **Laravel** yang menggabungkan tiga hal dalam satu sistem:
**company profile**, **menu live yang terhubung ke stok**, dan **POS kasir**.
Project ini dibuat sebagai portofolio. Nama brand "MyCoffee" bersifat universal —
kalau nanti dipakai untuk bisnis kopi beneran, cukup ganti nama brand dan data
di halaman Pengaturan, sistemnya tidak perlu diubah.

Versi 2 memperluas POS menjadi kasir yang lebih lengkap (catatan per item, QRIS,
diskon, voucher, bundel, pesanan pending per meja, shift, barista), menambah
manajemen bahan baku, pengeluaran, testimoni, reservasi, dua bahasa publik,
dan area pratinjau fitur masa depan.

## Fitur

**Halaman publik (tanpa login)**
- Beranda: hero foto toko, menu unggulan, gallery pilihan, banner promo terjadwal, testimoni yang disetujui admin, info alamat dan jam buka
- Beranda: hero foto besar dengan tombol ke Menu, cerita singkat, menu unggulan berfoto besar, strip gallery persegi ala Instagram, testimoni
- Menu: kartu berfoto besar berkelompok per kategori (filter dan pencarian tetap ada); menu yang habis **tetap tampil** dengan stiker **HABIS** dan foto redup; tiap menu punya tombol pesan via WhatsApp
- Gallery dan Tentang
- Kontak: tombol Petunjuk Arah (Google Maps), tombol WhatsApp, form reservasi meja, form testimoni
- Dua bahasa (Indonesia/English) untuk label navigasi dan label utama lewat tombol ID/EN di header; nama dan deskripsi menu tetap Bahasa Indonesia
- Menu musiman: menu dengan tanggal tampil di luar rentang **tidak tampil sama sekali** (pengecualian aturan HABIS)
- Meta Open Graph untuk pratinjau saat tautan dibagikan

**POS kasir (kasir dan admin)**
- Grid menu bersih untuk tablet: makanan dan dessert sekali ketuk langsung masuk keranjang; minuman membuka **pop-up opsi** (ukuran, level manis, level es, add-on, catatan, jumlah) yang melayang di atas grid dan bisa ditutup tanpa menambah item
- Baris keranjang punya tombol **Ubah** untuk membuka pop-up yang sama dengan nilai baris terisi; keranjang menempel di kanan pada tablet/desktop dan menjadi bar ringkas di bawah layar pada HP
- Sidebar panel (POS dan admin) menjadi **drawer** pada layar 992px ke bawah: tersembunyi, dibuka dari tombol hamburger di header, dengan overlay gelap dan tombol tutup
- Keranjang membedakan baris berdasarkan ukuran, level manis, level es, catatan, dan add-on
- Catatan per item untuk minuman: ukuran Regular/Large (harga Large per menu), level manis dan level es (No/Low/Half/Normal), add-on berbayar (mis. Extra Shot, Susu Oat, Gula Aren), dan catatan bebas — semua tampil di struk kasir, struk barista, dan detail transaksi
- Paket bundel dengan harga khusus (diskon bundel dihitung otomatis)
- Metode bayar tunai (uang bayar dan kembalian) atau QRIS (dianggap bayar pas)
- Nama pelanggan, nomor meja, dan kanal penjualan (dine-in, takeaway, GoFood, GrabFood) tercatat per transaksi
- Diskon persen per transaksi, kode voucher, promo Beli 2 Gratis 1 (diatur di Pengaturan), dan pajak persen dari Pengaturan — perhitungan terpusat dan rincian tampil di struk
- Nomor antrean per hari tampil besar di struk; struk bisa dicetak lebar standar atau 58mm
- Pesanan tahan (pending) per meja/nama: stok diamankan sejak dibuat, bisa dibuka lagi untuk ditambah item lalu dibayar, atau dibatalkan (stok kembali)
- Shift kasir opsional: buka shift dengan modal awal, tutup shift dengan hitung uang fisik dan selisih otomatis
- Layar Barista: antrean pesanan hari ini per status (baru/dibuat/selesai), memuat ulang otomatis
- Login cepat dengan PIN 4 angka di halaman Login PIN
- Tombol absen masuk/pulang untuk karyawan

**Panel admin (khusus admin)**
- Dashboard: omzet, jumlah transaksi, menu terlaris, daftar **Stok Menipis** (terpisah dari HABIS), menu habis, widget absensi
- Laporan dengan filter tanggal bebas: omzet per metode bayar, per kanal, per kasir; laba kotor (dari harga modal), pengeluaran, laba bersih; tombol Ekspor CSV dan tombol Kirim Rekap via WhatsApp ke pemilik
- Kelola kategori, menu (harga jual, harga modal, tambahan harga Large, stok, periode tampil musiman, resep bahan), gallery, add-on, bundel, bahan baku, voucher, pengeluaran
- Riwayat stok per menu (manual, jual, void, pesanan, batal)
- Void transaksi oleh admin dengan alasan wajib: stok menu dan bahan dikembalikan, transaksi keluar dari semua angka laporan
- Kelola testimoni (setujui/hapus), reservasi (ubah status), jadwal karyawan, dan PIN pengguna
- Pengaturan brand dan operasional: pajak, batas stok menipis, promo Beli 2 Gratis 1, banner promo, WhatsApp pemilik, jam buka hari libur, dan kode QR menu untuk meja
- Area **Pengembangan Berikutnya**: pratinjau static berlabel SEGERA HADIR untuk Multi Cabang, Aplikasi Pelanggan, Payment Gateway live, Integrasi Akuntansi, dan Ramalan Penjualan (belum berfungsi, tanpa data asli)

**Aturan HABIS:** menu dianggap habis bila stok = 0, dinonaktifkan manual oleh admin,
**atau** stok bahan baku resepnya tidak cukup untuk satu porsi.

## Akun Dummy

| Peran | Email | Password | PIN |
|---|---|---|---|
| Admin | admin@mycoffee.id | password | 1111 |
| Kasir | kasir@mycoffee.id | password | 1234 |

Password dan PIN di atas adalah dummy untuk portofolio dan **wajib diganti**
bila dipakai untuk bisnis beneran. PIN bisa diganti admin di menu Pengguna & PIN.

Kasir hanya bisa membuka POS, pesanan terbuka, layar barista, shift, dan riwayat
transaksi. Kelola data, laporan, dan pengaturan hanya untuk admin.

**Panduan lengkap** (pemasangan langkah demi langkah, panduan kasir dan admin,
daftar testing manual, pemecahan masalah, reset dan backup) ada di
`MANUAL_BOOK.md` di root project, dan di dalam aplikasi lewat menu
**Manual Book** di sidebar panel (membuka `public/manual-book.html`).

## Cara Pasang di Mac (Herd + DBngin)

1. Buat database kosong bernama `mycoffee` di **DBngin** dan pastikan servicenya jalan.
2. Clone atau ekstrak project ini ke folder yang dilayani **Herd** (misalnya `~/Herd/mycoffee`).
3. Salin file `.env.example` menjadi `.env`, lalu sesuaikan bagian database bila perlu.
   Asumsi bawaan DBngin: `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_DATABASE=mycoffee`,
   `DB_USERNAME=root`, `DB_PASSWORD=` (kosong). Ganti bila setting DBngin kamu berbeda.
4. Jalankan perintah berikut di Terminal dari folder project:

   ```bash
   composer install
   php artisan key:generate
   php artisan migrate --seed
   ```

5. Buka situsnya:
   - Lewat Herd: `http://mycoffee.test` (sesuaikan nama folder bila berbeda)
   - Atau tanpa Herd: `php artisan serve`, lalu buka `http://127.0.0.1:8000`

Catatan: project ini juga bisa jalan dengan SQLite untuk pengujian lokal —
cukup ubah `DB_CONNECTION=sqlite` di `.env` lalu jalankan `php artisan migrate --seed`.

## Data Demo dan Cara Reset Bersih

Seeder mengisi data demo (15 transaksi contoh tersebar 7 hari terakhir termasuk
hari ini, plus 4 pengeluaran contoh) **hanya bila** `DEMO_DATA=true` di `.env`.
File `.env.example` sudah berisi `DEMO_DATA=true` supaya dashboard dan laporan
langsung terisi saat pertama dipasang.

Untuk mulai dari data bersih (tanpa transaksi contoh):

```bash
# ubah DEMO_DATA=false di .env, lalu:
php artisan migrate:fresh --seed
```

Saat demo aktif, stok awal menu 50 lalu berkurang mengikuti transaksi contoh,
jadi stok akhir tetap realistis dan tidak negatif.

## Data Dummy

- 4 kategori: Kopi, Non-Kopi, Makanan, Dessert
- 13 menu dengan foto WebP dan deskripsi, harga Rp 18.000 sampai Rp 38.000
- 13 foto gallery, 3 add-on, 1 bundel contoh (Paket Santai), 2 voucher contoh (HEMAT10, NGOPI20)
- 4 bahan baku contoh (Susu Segar, Biji Kopi, Gula, Susu Kental) dengan resep untuk Espresso, Americano, Cappuccino, Latte, dan Mocha
- Alamat: Jl. Kopi No. 1, Jakarta — Telepon/WhatsApp: 0812-3456-7890 —
  Email: halo@mycoffee.id — Jam buka: 08.00 - 22.00 — Instagram: @mycoffee.id

## Catatan Kejujuran (yang masih dummy/static)

- **QRIS masih dummy**: kotak QR di POS hanya contoh tampilan berlabel jelas; belum terhubung ke payment gateway mana pun. Pembayaran dianggap pas.
- **Pratinjau Versi 4 static**: halaman di area Pengembangan Berikutnya hanya tampilan dengan data contoh berlabel DUMMY; belum ada logika di baliknya. Multi cabang khususnya tidak bisa sekadar ditambah belakangan karena menyangkut fondasi data.
- **Integrasi live belum ada**: GoFood/GrabFood hanya dicatat sebagai kanal penjualan; belum ada integrasi API, dan markup harga khusus ojol belum diterapkan (harga kanal sama dengan harga dine-in).
- **Membuka ulang pesanan pending** memuat komponen bundel sebagai baris menu biasa (pengelompokan bundel hilang), diskon lama dipertahankan sebatas subtotal baru.
- Riwayat perubahan stok bahan baku tidak dicatat terpisah (hanya stok menu yang punya riwayat); stok bahan terlihat di halaman Bahan Baku.

## Teknologi

- Laravel 13, PHP 8.3, Blade, CSS lokal (tanpa framework JS berat)
- Database: MySQL (target di Mac lewat DBngin) atau SQLite (pengujian lokal)
- Kode QR menu meja memakai package `simplesoftwareio/simple-qrcode` (format SVG)
