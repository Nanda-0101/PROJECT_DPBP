# CamRent

CamRent adalah aplikasi marketplace untuk menyewa peralatan kreatif seperti kamera, lensa, lighting, audio, tripod, dan gimbal. README ini menjelaskan sistem dari sudut pandang **user interface (UI)** dan **user experience (UX)**: bagaimana pengguna menemukan alat, menyelesaikan sewa, mengelola akun, dan mendapatkan bantuan.

## Tujuan pengalaman pengguna

CamRent dirancang untuk membuat proses sewa terasa jelas dan ringkas:

- Pengguna dapat memulai dari katalog tanpa harus memahami proses yang rumit.
- Harga ditampilkan per hari dan stok tersedia ditunjukkan pada kartu produk.
- Keranjang memungkinkan beberapa alat dipilih dan disewa dalam satu proses checkout.
- Checkout membagi proses menjadi langkah-langkah yang mudah diikuti.
- Pesan sukses, kesalahan, dan status membantu pengguna memahami hasil tindakannya.
- Mitra memiliki jalur khusus untuk mendaftar, mengajukan barang, dan mengelola pesanan masuk.

## Peran pengguna

| Peran | Pengalaman dan akses utama |
| --- | --- |
| **Tamu** | Dapat menjelajahi katalog dan melihat informasi produk. Harus masuk atau mendaftar sebagai `user` untuk menambahkan barang ke keranjang dan menyewa. |
| **User** | Dapat menyimpan barang di keranjang, mengatur pilihan, melakukan checkout, melihat riwayat sewa, serta menghubungi CS. |
| **Mitra** | Dapat mengelola profil dan mengajukan produk untuk disewakan. Mitra tidak dapat menyewa barang menggunakan akun mitra. |
| **Admin** | Role akun dikelola melalui database; halaman pendaftaran dan marketplace tidak menyediakan pengelolaan admin. |

Pendaftaran akun biasa selalu menghasilkan role `user`. Pendaftaran dan pembaruan profil mitra dilakukan melalui satu halaman **Jadi Mitra** di `/mitra`.

## Alur utama

### Menjelajahi dan menyewa alat

1. **Beranda** memperkenalkan layanan, menampilkan alat trending dan kategori populer, serta menyediakan tombol menuju katalog atau keranjang.
2. **Catalog** menyediakan pencarian, filter kategori dan sumber produk, serta pengurutan berdasarkan tren, harga, atau rating.
3. Pengguna membuka **specsheet** dari foto atau nama produk untuk melihat detail sebelum memilih.
4. Tombol **Keranjang** menambahkan barang tanpa meninggalkan katalog. Tombol **Sewa** memilih barang tersebut dan memulai checkout.
5. **Keranjang** menampilkan pilihan produk, jumlah unit, stok, dan estimasi biaya per hari. Pengguna dapat mengubah jumlah, menghapus barang, atau memilih barang tertentu untuk checkout.
6. **Checkout** meminta tanggal sewa melalui kalender, data penyewa, jaminan, skema pembayaran, dan metode pembayaran. Ringkasan biaya diperbarui saat pilihan berubah.
7. Setelah pesanan dibuat, pengguna dapat melihat status dan riwayatnya di **Dashboard**.

Penyewa harus masuk dengan akun ber-role `user`. Jika tamu atau mitra mencoba melakukan aksi sewa, sistem menolaknya dan menampilkan pesan bahwa hanya role user yang dapat menyewa.

### Bergabung sebagai mitra dan menyewakan barang

1. Pengguna membuka halaman **Jadi Mitra** dari navigasi, beranda, footer, atau tautan di halaman masuk.
2. Formulir meminta data akun/usaha, pemilik, NIK, kota, kategori, rekening, dan foto alat.
3. Setelah mendaftar, profil menunggu verifikasi. Mitra yang sudah memiliki profil dapat menggunakan halaman yang sama untuk memperbarui data.
4. Role `mitra` dapat membuka **Sewakan Barang Saya** di Catalog untuk mengajukan barang beserta harga sewa, stok, deskripsi, dan foto.
5. Barang baru berstatus menunggu verifikasi. Pesanan masuk, produk mitra, dan status verifikasi dapat dilihat melalui Dashboard mitra.

Tombol pengajuan barang hanya tersedia untuk akun mitra. Tamu dan user melihat keterangan bahwa hanya role mitra yang dapat menyewakan barang; endpoint backend juga memeriksa role tersebut.

### Memantau akun dan meminta bantuan

- **Dashboard** menampilkan riwayat sewa. Dashboard mitra juga memiliki bagian toko untuk melihat pesanan masuk dan produk sendiri.
- **Bantuan (CS)** menyediakan form pengaduan, riwayat tiket, FAQ, serta chat berbasis respons otomatis sederhana.
- Pesan validasi dan notifikasi ditampilkan sebagai toast agar pengguna memperoleh umpan balik setelah mengirim form atau melakukan tindakan.

## Peta halaman

| Halaman | Tujuan UI/UX |
| --- | --- |
| `/` | Pengenalan CamRent, pintasan ke katalog dan keranjang, kategori, produk trending, cara kerja, dan ajakan bergabung sebagai mitra. |
| `/marketplace` | Menemukan dan membandingkan produk sewa melalui search, filter, kartu produk, specsheet, dan aksi keranjang. |
| `/keranjang` | Meninjau pilihan, stok, kuantitas, dan estimasi harga sebelum checkout. |
| `/booking` | Menyelesaikan checkout dengan kalender, data penyewa, jaminan, pembayaran, dan ringkasan. |
| `/dashboard` | Melihat riwayat/status sewa; untuk mitra, melihat pesanan masuk dan produk. |
| `/mitra` | Satu jalur untuk pendaftaran mitra dan pembaruan profil mitra. |
| `/login` | Masuk atau membuat akun user. Tautan daftar mitra mengarah ke `/mitra`. |
| `/cs` | Mengirim pengaduan, melihat tiket, membaca FAQ, dan mencoba chat otomatis. |

Navigasi utama menyediakan Beranda, Catalog, Jadi Mitra, Bantuan, keranjang, dan akses akun. Pada layar kecil, menu utama menggunakan tombol navigasi yang dapat dibuka dan ditutup.

## Prinsip UI dan UX

- **Alur bertahap:** checkout memisahkan pemilihan alat, tanggal, identitas, jaminan, dan pembayaran agar pengguna dapat fokus pada satu kelompok informasi.
- **Informasi sebelum tindakan:** kartu produk memperlihatkan kategori, penjual, harga harian, rating, dan stok. Specsheet menyediakan detail tambahan.
- **Umpan balik langsung:** aksi keranjang memakai AJAX dan memperbarui jumlah badge; perubahan kuantitas dan kegagalan ditampilkan melalui pesan.
- **Pencegahan kesalahan:** checkout memerlukan pilihan barang dan tanggal yang valid; stok diperiksa kembali oleh backend. Form menampilkan pesan validasi.
- **Ringkasan kontekstual:** estimasi biaya ditampilkan di keranjang dan ringkasan checkout, termasuk skema pembayaran.
- **Konsistensi navigasi:** navbar dan footer menyediakan akses ke katalog, keranjang, status pesanan, mitra, dan CS.
- **Responsif:** layout berubah mengikuti lebar layar; grid katalog, ringkasan checkout, form, dan navigasi menyesuaikan perangkat.
- **Aksesibilitas dasar:** input memiliki label, beberapa kontrol menyediakan `aria-label`, dan elemen yang menerima fokus keyboard memiliki indikator `focus-visible`.

## Catatan cakupan fitur

- Pilihan bank, e-wallet, Cash, dan QRIS digunakan pada alur checkout aplikasi. README ini tidak menyatakan adanya integrasi pemrosesan pembayaran eksternal.
- Chat CS di halaman bantuan memberikan respons otomatis sederhana di browser; untuk pengaduan, gunakan form tiket.
- Status verifikasi dan role ditampilkan untuk mendukung alur prototipe. Akun admin tidak didaftarkan melalui formulir publik.

## Teknologi

- Laravel 12 dan PHP 8.2+
- Blade untuk halaman dan komponen UI
- CSS dan JavaScript untuk layout responsif serta interaksi
- Vite untuk pengembangan dan build aset frontend
- MariaDB/MySQL melalui XAMPP atau SQLite untuk konfigurasi pengembangan

## Menjalankan aplikasi lokal

1. Siapkan PHP 8.2+, Composer, Node.js/npm, dan database yang dipilih.
2. Pasang dependency:

   ```bash
   composer install
   npm install
   ```

3. Salin `.env.example` menjadi `.env`, lalu atur koneksi database. Untuk MariaDB/MySQL lokal, isi `DB_CONNECTION=mysql`, `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` sesuai konfigurasi XAMPP.
4. Buat application key:

   ```bash
   php artisan key:generate
   ```

   Untuk skema utama aplikasi, buat database `db_dpbp` lalu impor `database/db_dpbp.sql` melalui phpMyAdmin. Migrasi yang tersedia hanya mencakup tabel dasar Laravel, bukan seluruh tabel CamRent. Jangan mengimpor dump ke database berisi data penting tanpa membuat backup terlebih dahulu.
5. Build aset frontend dan jalankan aplikasi:

   ```bash
   npm run build
   php artisan serve
   ```

   Buka URL yang ditampilkan Artisan, biasanya `http://127.0.0.1:8000`.

Untuk pengembangan frontend dengan hot reload, jalankan `npm run dev` di terminal terpisah dari `php artisan serve`.

## Pengujian

Jalankan seluruh pengujian Laravel dengan:

```bash
php artisan test
```

Tes fitur berada di `tests/Feature` dan mencakup alur katalog, keranjang, checkout, akun, mitra, serta akses berdasarkan role.
