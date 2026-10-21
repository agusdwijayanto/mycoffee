# Manual Book MyCoffee

Panduan resmi pemakaian aplikasi **MyCoffee** — gabungan company profile kedai kopi,
menu live yang terhubung ke stok, dan POS kasir, dibangun dengan Laravel.
Dokumen ini juga tersedia di dalam aplikasi: menu **Manual Book** di sidebar panel
(membuka `manual-book.html` di tab baru).

---

## 1. Cara Pasang di Mac (Herd + DBngin)

Ikuti persis urutan ini. Jangan loncat langkah: `php artisan serve` adalah langkah
terakhir, bukan pertama.

### Langkah 1 — Nyalakan MySQL dan buat database

1. Buka aplikasi **DBngin**, start service MySQL sampai statusnya jalan.
2. Buat database kosong bernama `mycoffee`. Bisa dari aplikasi DBngin, atau dari
   Terminal di VSCode:

   ```bash
   mysql -h 127.0.0.1 -u root -e "CREATE DATABASE IF NOT EXISTS mycoffee; SHOW DATABASES LIKE 'mycoffee';"
   ```

   Kriteria lolos: muncul baris `mycoffee`. Tambahan `-h 127.0.0.1` penting agar
   koneksi lewat jaringan (TCP), bukan socket.

   Bila keluar `command not found: mysql`, pakai PHP bawaan Herd (hasilnya sama):

   ```bash
   php -r '$p=new PDO("mysql:host=127.0.0.1;port=3306","root",""); $p->exec("CREATE DATABASE IF NOT EXISTS mycoffee"); echo "mycoffee OK\n";'
   ```

3. Jangan buat tabel apa pun secara manual — semua tabel dibuat oleh migrate.

### Langkah 2 — Ekstrak dan pasang project

1. Ekstrak file zip project. Isi folder project harus langsung terlihat: file
   `artisan`, `composer.json`, `README.md`, `MANUAL_BOOK.md`, dan folder `app`.
   Letakkan di folder yang dilayani Herd (misalnya `~/Herd/mycoffee`) agar bisa
   dibuka lewat alamat `mycoffee.test`.
2. Buka Terminal di folder project, lalu jalankan:

   ```bash
   composer install
   cp .env.example .env
   php artisan key:generate
   php artisan migrate --seed
   ```

   - `composer install` selesai tanpa error dan folder `vendor` terbentuk.
   - `key:generate` menampilkan *Application key set successfully*.
   - `migrate --seed` semua tabel berstatus DONE dan berakhir dengan
     *Database seeding completed successfully*.
   - Sesuaikan isi `.env` bila setting DBngin kamu berbeda. Bawaan DBngin:
     `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_DATABASE=mycoffee`,
     `DB_USERNAME=root`, `DB_PASSWORD=` (kosong).

### Langkah 3 — Pilihan data

- **Data demo (bawaan, `DEMO_DATA=true` di `.env`):** terisi 15 transaksi contoh
  tersebar 7 hari terakhir dan 4 pengeluaran contoh, sehingga dashboard dan
  laporan langsung ada isinya. Cocok untuk portofolio dan presentasi.
- **Data bersih:** ubah baris di `.env` menjadi `DEMO_DATA=false` SEBELUM
  menjalankan `migrate --seed`. Hasilnya hanya data pokok: kategori, menu,
  gallery, pengaturan, dan akun — tanpa transaksi contoh.

### Langkah 4 — Buka aplikasinya

```bash
php artisan serve
```

Lalu buka `http://127.0.0.1:8000`, atau `http://mycoffee.test` bila project
diletakkan di folder Herd. Terminal yang menjalankan `serve` jangan ditutup.

---

## 2. Akun dan PIN Bawaan

| Peran  | Email              | Password | PIN  |
|--------|--------------------|----------|------|
| Admin  | admin@mycoffee.id  | password | 1111 |
| Kasir  | kasir@mycoffee.id  | password | 1234 |

> **PERINGATAN:** semua kredensial di atas adalah contoh untuk latihan dan
> portofolio. Bila aplikasi dipakai untuk bisnis beneran, WAJIB ganti password
> (lewat database/pengaturan pengguna) dan PIN (menu **Pengguna & PIN**) sebelum
> dipakai kasir sungguhan. Jangan pernah memakai password dan PIN bawaan di
> tempat usaha yang beroperasi.

---

## 3. Panduan Kasir

### 3.1 Masuk (login)

- **Login email:** buka `/login`, isi email dan password. Kasir langsung diarahkan
  ke halaman POS.
- **Login PIN:** buka `/login-pin`, pilih nama pengguna, masukkan PIN 4 angka.
  Cocok untuk ganti shift dengan cepat.

### 3.2 Melayani transaksi di POS

1. Di halaman **POS Kasir**, pilih kategori atau cari menu di kolom pencarian.
2. **Makanan dan Dessert:** ketuk kartunya sekali — langsung masuk keranjang.
3. **Minuman (Kopi dan Non-Kopi):** ketuk kartunya — muncul **pop-up opsi**:
   pilih **Ukuran** (Regular/Large), **Level Manis** (No/Low/Half/Normal),
   **Level Es** (No/Low/Half/Normal), centang **Add-on** (extra shot, susu oat,
   gula aren), isi **Catatan** bebas, atur **Jumlah**, lalu tekan **Tambahkan**.
   Pop-up bisa ditutup dengan tombol **Batal** atau klik di luar kotak tanpa
   menambah item.
4. Item yang sama persis kombinasinya akan menyatu dalam satu baris; kombinasi
   berbeda menjadi baris terpisah.
5. Di keranjang: tombol **− / +** mengubah jumlah, **×** menghapus, dan tombol
   **Ubah** membuka lagi pop-up dengan nilai baris itu terisi — simpan untuk
   mengubah barisnya (bukan menambah baris baru).
6. Isi bila perlu: **Nama Pelanggan**, **Nomor Meja**, **Kanal Penjualan**
   (Makan di Tempat / Bawa Pulang / GoFood / GrabFood), **Diskon %**, dan
   **Kode Voucher**.
7. Pilih **Metode Bayar**:
   - **Tunai:** isi uang bayar. Bila kurang dari total, transaksi ditolak dan
     tidak tersimpan. Kembalian dihitung otomatis di struk.
   - **QRIS:** tampil kotak QR bertuliskan *QRIS DUMMY - contoh*. Ini BUKAN kode
     pembayaran asli (lihat Catatan Jujur). Pembayaran dianggap pas.
8. Tekan **Bayar & Simpan Transaksi**. Stok menu dan stok bahan baku berkurang
   otomatis. Halaman berpindah ke **struk** berisi nomor antrean besar, rincian
   item beserta catatannya, total, bayar, dan kembalian. Dari struk tersedia
   tombol cetak biasa, **Cetak 58mm** (kertas thermal), dan **Struk Barista**.

Di HP, keranjang tampil sebagai bar tetap di bawah layar; ketuk bar itu untuk
membuka panel keranjang penuh. Di tablet dan desktop, keranjang menempel di sisi
kanan layar.

### 3.3 Pesanan pending (tahan per meja)

1. Susun pesanan di keranjang seperti biasa.
2. Isi **Nomor Meja** atau **Nama Pelanggan** di form bayar (wajib salah satu).
3. Tekan **Tahan sebagai Pesanan (bayar belakangan)**. Pesanan tersimpan
   berstatus pending dan stoknya sudah diamankan (berkurang).
4. Di menu **Pesanan Terbuka**, pesanan bisa: **dibuka kembali** ke keranjang
   untuk ditambah itemnya lalu disimpan lagi (**Simpan Perubahan Pesanan**),
   **dibayar** lewat form bayar biasa, atau **dibatalkan** — pembatalan
   mengembalikan stok.

### 3.4 Shift kasir

1. Di panel atas POS, isi **modal awal** lalu tekan **Buka Shift** (opsional —
   transaksi tetap bisa jalan tanpa shift).
2. Di akhir jam kerja buka menu **Shift Saya**, tekan tutup shift, isi **uang
   fisik** yang ada di laci.
3. Sistem menghitung total tunai dan QRIS selama shift itu dan menampilkan
   **selisih** antara uang fisik dan total tunai sistem.

### 3.5 Layar barista

Menu **Layar Barista** menampilkan pesanan yang sudah dibayar hari ini dalam
tiga kolom: **Baru**, **Dibuat**, dan **Selesai**. Tekan tombol status pada kartu
pesanan untuk memindahkannya. Halaman ini hanya berisi item dan catatannya
(tanpa harga), dan juga tersedia sebagai **Struk Barista** per transaksi.

### 3.6 Absensi

Di bagian bawah sidebar ada tombol **Absen Masuk** dan **Absen Pulang**.
Satu kali ketuk mencatat jam masuk, ketuk berikutnya mencatat jam pulang hari itu.

---

## 4. Panduan Admin

Admin mempunyai semua akses kasir, ditambah menu-menu berikut.

### 4.1 Dashboard dan Laporan

- **Dashboard:** omzet hari ini dan bulan ini, jumlah transaksi, menu terlaris,
  daftar **Stok Menipis** (di bawah batas *stok_minimum*, bawaan 5) dan daftar
  menu **HABIS**.
- **Laporan:** filter rentang tanggal bebas; menampilkan omzet, laba kotor
  (dari harga modal), total pengeluaran, laba bersih, omzet per kanal, per
  metode bayar, dan per kasir. Tombol **Ekspor CSV** mengunduh transaksi pada
  rentang itu. Tombol **Kirim Rekap via WhatsApp** membuka WhatsApp ke nomor
  pemilik dengan teks rekap hari ini terisi.

### 4.2 Kelola menu, stok, dan bahan

- **Kelola Menu:** tambah/ubah/hapus menu (nama, kategori, deskripsi, harga,
  tambahan harga Large, harga modal, foto, stok, status aktif). Perubahan stok
  di sini tercatat di **Riwayat Stok** beserta siapa yang mengubah.
- **Status HABIS** berlaku otomatis bila salah satu terpenuhi: stok menu 0,
  menu dinonaktifkan manual, atau **stok bahan baku resepnya tidak cukup** untuk
  1 porsi. Menu habis tetap tampil di website dan POS dengan stiker HABIS,
  tidak hilang, dan tidak bisa dijual.
- **Bahan Baku:** kelola bahan (nama, satuan, stok) dan resep per menu
  (bahan + takaran per porsi) dari halaman ubah menu. Setiap penjualan mengurangi
  stok bahan sesuai takaran; void dan pembatalan mengembalikannya.
- **Menu musiman:** isi *tampil dari* / *tampil sampai* pada menu agar menu hanya
  muncul pada rentang tanggal itu (di luar rentang menu tidak tampil sama sekali).
- **Bundel & Add-on:** kelola paket bundel (komponen + satu harga paket; selisih
  harga komponen menjadi diskon otomatis di kasir) dan daftar add-on berbayar.

### 4.3 Voucher dan promo

- **Voucher:** buat kode di menu **Voucher** (tipe persen atau nominal, nilai,
  tanggal berlaku, status aktif). Contoh bawaan: `HEMAT10` (diskon 10%) dan
  `NGOPI20` (potongan Rp 20.000). Kasir memasukkan kode di form bayar; kode
  tidak aktif/kedaluwarsa/salah ditolak.
- **Promo Beli 2 Gratis 1:** pilih menu sasaran di **Pengaturan**
  (*promo Beli 2 Gratis 1*). Bila jumlah menu itu dalam satu transaksi mencapai
  3, diskon bertambah sebesar harga 1 menu tersebut.
- Voucher, promo, dan diskon persen kasir digabung dalam satu perhitungan dan
  rincian diskonnya tercatat di transaksi serta tampil di struk.

### 4.4 Void transaksi

Di **Riwayat Transaksi → Detail**, admin dapat men-**void** transaksi yang sudah
dibayar dengan **alasan wajib**. Akibatnya: stok menu dan bahan kembali,
transaksi dikeluarkan dari semua angka laporan dan dashboard, dan riwayatnya
tetap tersimpan berstatus void beserta alasannya.

### 4.5 Pengaturan brand

Menu **Pengaturan** mengubah nama brand, tagline, alamat, telepon, WhatsApp,
WhatsApp pemilik, email, jam buka, jam buka hari libur, Instagram, persen pajak,
batas stok minimum, dan promo. Nama brand yang diganti langsung tampil di
header/footer website publik dan judul panel. Di halaman ini juga tampil
**kode QR menu** (SVG) yang bisa dicetak dan ditempel di meja — mengarah ke
halaman Menu publik yang status HABIS-nya selalu sinkron dengan kasir.

### 4.6 Pelanggan: testimoni, reservasi, jadwal, pengguna

- **Testimoni:** kiriman dari website masuk berstatus menunggu; setujui agar
  tampil di Beranda, atau hapus.
- **Reservasi:** kiriman dari halaman Kontak; ubah statusnya
  (menunggu/dikonfirmasi/selesai/dibatalkan) atau hapus.
- **Jadwal Karyawan:** atur jadwal mingguan tiap pengguna.
- **Pengguna & PIN:** daftar pengguna dan ganti PIN login masing-masing.

### 4.7 Pengembangan Berikutnya (pratinjau)

Area berisi 5 kartu pratinjau fitur versi berikutnya: Multi Cabang, Aplikasi
Pelanggan, Payment Gateway Live, Integrasi Akuntansi, dan Ramalan Penjualan.
Halaman pratinjau berlabel besar **SEGERA HADIR** dengan data contoh berlabel
DUMMY. Ini hanya tampilan konsep — belum berfungsi dan hanya bisa dibuka admin.

---

## 5. Daftar Testing Manual

Centang satu per satu setelah aplikasi terpasang. Hasil yang diharapkan tertulis
di tiap item.

### Publik

- [ ] Beranda, Tentang, Menu, Gallery, Kontak terbuka; semua foto tampil.
- [ ] Halaman Menu menampilkan menu berkelompok per kategori dengan foto besar.
- [ ] Ganti bahasa ID → EN di header: label navigasi berubah, stiker HABIS
      menjadi **SOLD OUT**.
- [ ] Beranda menampilkan strip foto gallery persegi; klik strip membuka Gallery.
- [ ] Tombol **Pesan via WhatsApp** pada kartu menu membuka WhatsApp dengan teks
      nama menu terisi.

### Login

- [ ] Login email kasir benar → masuk ke POS.
- [ ] Login PIN kasir `1234` benar → masuk ke POS; PIN salah → ditolak.
- [ ] Kasir membuka `/admin/vouchers` atau Pengaturan → ditolak (403).

### Transaksi kasir

- [ ] Makanan (mis. Nasi Goreng) sekali ketuk langsung masuk keranjang.
- [ ] Minuman (mis. Latte) membuka pop-up opsi; grid menu tetap terlihat utuh di
      belakang pop-up (pop-up tidak menghalangi daftar menu secara permanen).
- [ ] Di tablet/HP, sidebar panel tampil sebagai **drawer**: sembunyi secara
      bawaan, terbuka dari tombol hamburger dengan latar gelap, tertutup dari
      tombol × atau klik latar.
- [ ] Transaksi tunai: beli 1 Espresso (Rp 18.000) bayar Rp 50.000 → struk
      tampil, kembalian **Rp 32.000**, stok Espresso berkurang 1.
- [ ] Opsi lengkap: Latte ukuran **Large**, manis **Low**, es **Half**, centang
      **Extra Shot**, catatan bebas terisi → di **struk kasir** DAN **struk
      barista** tercetak persis opsi itu; harga satuan sudah termasuk tambahan
      Large dan add-on.
- [ ] Tombol **Ubah** pada baris keranjang membuka pop-up dengan nilai lama
      terisi; ubah level manisnya → ringkasan baris berubah, total ikut berubah.
- [ ] Bayar kurang dari total → ditolak, transaksi tidak tersimpan, stok tidak
      berubah.
- [ ] Metode **QRIS** → kotak QRIS DUMMY tampil, transaksi tersimpan,
      kembalian 0.
- [ ] Voucher `HEMAT10` → total terpotong 10%. Kode sembarangan → ditolak.
- [ ] Struk mode **58mm** tampil sempit siap cetak thermal.

### Pesanan pending

- [ ] Buat pesanan pending dengan nomor meja → muncul di Pesanan Terbuka, stok
      berkurang.
- [ ] Buka kembali pesanan, tambah 1 item, simpan perubahan → isi pesanan
      bertambah.
- [ ] Bayar pesanan pending → status menjadi lunas dan struk keluar.
- [ ] Buat pesanan pending lain lalu batalkan → stok kembali seperti semula.

### Stok dan HABIS

- [ ] Atur stok satu menu menjadi 1, jual 1 → menu tampil berstiker **HABIS**
      di website dan tidak bisa dimasukkan keranjang POS.
- [ ] Turunkan stok **Susu Segar** hingga tidak cukup untuk 1 porsi Latte →
      Latte menjadi HABIS walaupun stok menunya masih ada.
- [ ] Menu dengan stok di bawah/sama dengan 5 muncul di daftar **Stok Menipis**
      dashboard (bukan di daftar HABIS).

### Admin dan laporan

- [ ] **Void** satu transaksi dengan alasan → omzet laporan berkurang persis
      sebesar transaksi itu dan stoknya kembali.
- [ ] Filter laporan dengan rentang tanggal lain → angka berubah mengikuti
      rentang; **Ekspor CSV** terunduh dan isinya sesuai rentang.
- [ ] Laporan menampilkan rincian per metode bayar, per kanal, dan per kasir.
- [ ] **Shift:** buka shift, lakukan 1 transaksi tunai, tutup shift dengan uang
      fisik berbeda → selisih terhitung benar.
- [ ] **Absensi:** tombol Absen Masuk lalu Absen Pulang mencatat jam hari ini.
- [ ] **Reservasi** yang dikirim dari halaman Kontak muncul di panel admin.
- [ ] **Testimoni** yang dikirim dari website tidak langsung tampil; setelah
      disetujui admin baru tampil di Beranda.
- [ ] Area **Pengembangan Berikutnya**: 5 pratinjau terbuka berlabel
      **SEGERA HADIR**; dibuka sebagai kasir → ditolak (403).
- [ ] Menu **Manual Book** di sidebar membuka buku ini di tab baru.

---

## 6. Pemecahan Masalah

### `Failed opening required .../vendor/autoload.php`

Penyebab: `composer install` belum dijalankan (folder `vendor` belum ada).
Atasi: jalankan `composer install` sampai selesai di folder project. Jangan
menjalankan `php artisan serve` sebelum langkah ini beres.

### `ERROR 2002 (HY000): Can't connect to local MySQL server through socket`

Penyebab: server MySQL belum berjalan (DBngin belum di-start) atau client
mencari socket yang salah.
Atasi: start service MySQL di DBngin, lalu hubungkan lewat jaringan:

```bash
mysql -h 127.0.0.1 -u root
```

### `bootstrap/cache directory must be present and writable`

Penyebab: folder `bootstrap/cache` (dan sebagian folder `storage`) tidak ikut
terbawa saat ekstrak.
Atasi dari folder project:

```bash
mkdir -p bootstrap/cache storage/framework/cache storage/framework/sessions storage/framework/views storage/logs
php artisan key:generate
```

Lalu ulangi perintah yang gagal (`migrate --seed` atau `serve`).

### `Access denied for user 'root'@'localhost'`

Penyebab: password root MySQL di DBngin berbeda dari yang ada di `.env`.
Atasi: samakan `DB_USERNAME`/`DB_PASSWORD` di `.env` dengan setting DBngin kamu,
lalu jalankan `php artisan config:clear`.

### Halaman tidak berubah setelah mengubah file `.env`

Jalankan `php artisan config:clear`, lalu muat ulang halaman.

---

## 7. Reset dan Backup

### Reset data ke kondisi awal seed

```bash
php artisan migrate:fresh --seed
```

Perintah ini MENGHAPUS semua tabel dan mengisinya kembali dari seeder (mengikuti
pengaturan `DEMO_DATA` di `.env`). Transaksi, stok berubah, dan data kelola akan
hilang — gunakan hanya bila memang mau mulai bersih.

### Backup database

```bash
mysqldump -h 127.0.0.1 -u root mycoffee > backup-mycoffee-$(date +%F).sql
```

Simpan file hasilnya di tempat aman di luar folder project. Untuk memulihkan:

```bash
mysql -h 127.0.0.1 -u root mycoffee < backup-mycoffee-2026-10-21.sql
```

Backup juga folder `public/images` dan file `.env` agar pemulihan lengkap.

---

## 8. Catatan Jujur (Keterbatasan Versi Ini)

- **QRIS masih dummy.** Kotak QR di POS hanya contoh tampilan berlabel jelas,
  bukan kode pembayaran asli; tidak ada integrasi payment gateway (mode live
  menunggu akun bisnis). Pembayaran QRIS di aplikasi ini dianggap pas.
- **Pratinjau versi 4 bersifat static.** Multi Cabang, Aplikasi Pelanggan,
  Payment Gateway Live, Integrasi Akuntansi, dan Ramalan Penjualan baru berupa
  tampilan "Segera Hadir" tanpa fungsi di belakangnya.
- **Markup harga ojol belum ada.** Penjualan GoFood/GrabFood dicatat sebagai
  kanal terpisah di laporan, tetapi harganya masih sama dengan harga dine-in.
- **Data demo.** Kontak, alamat, harga, akun, dan 15 transaksi contoh adalah
  data dummy untuk portofolio. Matikan dengan `DEMO_DATA=false` sebelum
  `migrate --seed` untuk data bersih, dan ganti semua kredensial bawaan sebelum
  dipakai bisnis beneran.
- **Satu toko, satu server lokal.** Aplikasi berjalan di satu komputer
  (Mac + Herd); perangkat lain di jaringan lokal yang sama dapat membukanya
  lewat alamat IP komputer itu.
