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
| **Mitra** | Dapat mengelola profil, menayangkan barang, dan memantau semua pesanan. Mitra hanya dapat mengubah status pesanan produk miliknya; mitra tetap dapat menyewa barang pihak lain, tetapi tidak barang sendiri. |
| **Admin** | Dapat masuk, menambahkan produk CamRent ke Catalog, memantau semua pesanan, dan menggunakan alur sewa. Akun admin tidak dibuat melalui pendaftaran publik. |

Pendaftaran akun biasa selalu menghasilkan role `user`. Pendaftaran dan pembaruan profil mitra dilakukan melalui satu halaman **Jadi Mitra** di `/mitra`.

## Alur utama

### Menjelajahi dan menyewa alat

1. **Beranda** memperkenalkan layanan, menampilkan alat trending dan kategori populer, serta menyediakan tombol menuju katalog atau keranjang.
2. **Catalog** menyediakan pencarian, filter kategori dan sumber produk, serta pengurutan berdasarkan tren, harga, atau rating.
3. Pengguna membuka **specsheet** dari foto atau nama produk untuk melihat detail sebelum memilih.
4. Tombol **Keranjang** menambahkan barang tanpa meninggalkan katalog. Tombol **Sewa** memilih barang tersebut dan memulai checkout. Produk yang ditambahkan admin memiliki penjual **CamRent**.
5. **Keranjang** menampilkan pilihan produk, jumlah unit, stok, dan estimasi biaya per hari. Pengguna dapat mengubah jumlah, menghapus barang, atau memilih barang tertentu untuk checkout.
6. **Checkout** meminta tanggal sewa melalui kalender, data penyewa, jaminan, skema pembayaran, dan metode pembayaran. Ringkasan biaya diperbarui saat pilihan berubah.
7. Setelah pesanan dibuat, pengguna dapat melihat status dan riwayatnya di **Dashboard**.

Aksi sewa memerlukan akun yang sudah masuk dengan role `user`, `mitra`, atau `admin`. Mitra tidak dapat menyewa barang miliknya sendiri. Tamu dapat menjelajahi produk, tetapi harus masuk atau mendaftar sebelum menyewa.

### Bergabung sebagai mitra dan menyewakan barang

1. Pengguna membuka halaman **Jadi Mitra** dari navigasi, beranda, footer, atau tautan di halaman masuk.
2. Formulir meminta data akun/usaha, pemilik, NIK, kota, kategori, rekening, dan foto alat.
3. Setelah mendaftar, akun langsung aktif dengan role `mitra` tanpa menunggu verifikasi admin. Mitra yang sudah memiliki profil dapat menggunakan halaman yang sama untuk memperbarui data.
4. Role `mitra` dapat membuka **Sewakan Barang Saya** di Catalog untuk mengajukan barang beserta harga sewa, stok, deskripsi, dan foto.
5. Barang baru langsung aktif dan tampil di marketplace tanpa menunggu verifikasi. Pesanan masuk dan produk mitra dapat dilihat melalui Dashboard mitra.

Tombol **Sewakan Barang Saya** tersedia untuk mitra, sedangkan admin mendapat tombol **Tambah Produk CamRent**. Produk mitra dicatat atas nama mitra; produk admin dicatat sebagai produk CamRent.

### Memantau akun dan meminta bantuan

- **Dashboard** menampilkan riwayat sewa. Admin dan mitra memiliki tab pemantauan semua pesanan user; mitra juga memiliki tab produk sendiri dan hanya dapat mengubah status pesanan produk miliknya.
- **Bantuan (CS)** menyediakan form pengaduan, riwayat tiket, FAQ, serta chat AI yang dapat digunakan tamu maupun pengguna.
- Pesan validasi dan notifikasi ditampilkan sebagai toast agar pengguna memperoleh umpan balik setelah mengirim form atau melakukan tindakan.

## Peta halaman

| Halaman | Tujuan UI/UX |
| --- | --- |
| `/` | Pengenalan CamRent, pintasan ke katalog dan keranjang, kategori, produk trending, cara kerja, dan ajakan bergabung sebagai mitra. |
| `/marketplace` | Menemukan dan membandingkan produk sewa melalui pencarian, filter, kartu produk, specsheet, dan aksi keranjang; mitra/admin dapat menambahkan produk sesuai perannya. |
| Specsheet pada `/marketplace` | Melihat foto, spesifikasi, harga, stok, dan penjual produk sebelum menyewa. Detail dibuka sebagai modal agar pengguna tetap berada di hasil katalog. |
| `/keranjang` | Meninjau pilihan, stok, kuantitas, dan estimasi harga sebelum checkout. |
| `/booking` | Menyelesaikan checkout secara bertahap dengan kalender, data penyewa, jaminan, pembayaran, dan ringkasan. |
| `/dashboard` | Tamu melihat aktivitas sesi browser; akun melihat riwayat sewa dan profil. Admin/mitra dapat memantau semua pesanan user; mitra juga melihat produk miliknya. |
| `/mitra` | Mendaftar sebagai mitra atau memperbarui profil mitra pada satu halaman. |
| `/login` | Masuk atau membuat akun user. Pendaftaran publik membuat role `user`; tautan mitra mengarah ke `/mitra`. |
| `/pengaturan-akun` | Memperbarui nama, email, nomor HP, foto profil, dan kata sandi. |
| `/cs` | Mengirim pengaduan, melihat riwayat tiket, membaca FAQ, dan memakai chat bantuan otomatis. |

Navigasi utama menyediakan Beranda, Catalog, Jadi Mitra, Bantuan, keranjang, dan akses akun. Pada layar kecil, menu utama menggunakan tombol navigasi yang dapat dibuka dan ditutup.

## UCD per halaman

Uraian ini menjelaskan bagaimana kebutuhan pengguna diterjemahkan ke alur dan antarmuka yang tersedia. Ini adalah dokumentasi rancangan UCD aplikasi, bukan klaim bahwa wawancara atau pengujian usability formal sudah dilakukan.

| Halaman / pengguna | Kebutuhan pengguna | Penerapan UCD pada antarmuka |
| --- | --- | --- |
| **Beranda** (`/`) — calon penyewa dan mitra | Memahami layanan dan menemukan langkah awal tanpa harus membaca panduan panjang. | Headline menjelaskan manfaat utama; tombol **Sewa Kamera** dan **Lihat Keranjang** memberi jalur tindakan yang jelas. Kategori, produk trending, langkah sewa, dan ajakan bergabung sebagai mitra disusun sebagai pintasan sesuai tujuan pengguna. |
| **Catalog** (`/marketplace`) — penyewa, mitra, admin | Menemukan alat yang cocok dan membandingkan harga, stok, serta penyedia. | Pencarian, filter kategori/penyedia, dan pengurutan mengurangi beban mencari. Kartu menampilkan informasi penting untuk membandingkan alat; tombol detail membuka specsheet tanpa meninggalkan daftar. Admin melihat **Tambah Produk CamRent**, sedangkan mitra melihat **Sewakan Barang Saya**. |
| **Specsheet produk** — calon penyewa | Memeriksa kecocokan alat sebelum memasukkannya ke keranjang. | Modal mempertahankan konteks hasil pencarian, menyediakan foto/galeri serta rincian produk, dan memberi akses ke tindakan sewa. Informasi harga, stok, kategori, dan penjual membantu keputusan sebelum melanjutkan. |
| **Keranjang** (`/keranjang`) — penyewa | Memastikan barang dan jumlah unit sudah benar sebelum checkout. | Pengguna dapat memilih sebagian/semua barang, mengubah jumlah dengan kontrol tambah/kurang, atau menghapus barang. Ringkasan memperlihatkan jumlah alat, unit, dan estimasi biaya harian; batas jumlah mengikuti stok. Keadaan kosong menyediakan tautan kembali ke Catalog. |
| **Checkout** (`/booking`) — penyewa | Menyelesaikan sewa dengan memahami tanggal, identitas, jaminan, dan biaya. | Form dibagi menjadi langkah bernomor: alat, tanggal, data penyewa, jaminan, dan pembayaran. Kalender memperlihatkan pilihan tanggal; ringkasan biaya dan metode pembayaran membantu pengguna memeriksa pesanan sebelum konfirmasi. Validasi server mencegah tanggal, stok, dan data wajib yang tidak valid. |
| **Masuk/Daftar** (`/login`) — tamu dan pengguna | Masuk atau membuat akun dengan proses yang mudah dikenali. | Tab **Masuk** dan **Daftar Akun** memisahkan dua tugas. Label dan tipe input sesuai data yang diminta. Pendaftaran publik selalu membuat akun user; jalur menjadi mitra dipisahkan ke halaman **Jadi Mitra**. Admin masuk melalui form yang sama, bukan melalui pilihan role publik. |
| **Dashboard** (`/dashboard`) — tamu, user, mitra, admin | Melihat riwayat sendiri atau memantau pesanan pelanggan sesuai peran. | Riwayat menampilkan kode, tanggal, item, biaya, dan status. Admin dan mitra mendapat tab **Pantau Pesanan** untuk melihat semua pesanan; hanya mitra yang memiliki produk terkait mendapat aksi perubahan status. Mitra juga memiliki tab produk sendiri. Tamu diberi penjelasan bahwa aktivitasnya berasal dari browser saat ini dan ajakan masuk untuk menyimpan riwayat ke akun. |
| **Jadi Mitra** (`/mitra`) — calon mitra dan mitra aktif | Mendaftar atau memperbarui data usaha tanpa mengulang proses pada halaman berbeda. | Satu halaman mengelompokkan data usaha, akun, dan data mitra. Nilai profil yang sudah ada ditampilkan kembali saat pembaruan; foto wajib saat pendaftaran dan opsional saat update. Langkah dan manfaat kemitraan dijelaskan di samping form, sementara validasi menandai data yang harus diperbaiki. |
| **Pengaturan Akun** (`/pengaturan-akun`) — pengguna terdaftar | Memperbarui data pribadi dan keamanan akun. | Form memisahkan profil dari bagian ubah kata sandi. Foto profil memiliki batas format/ukuran; kata sandi saat ini dan konfirmasi kata sandi baru membantu menghindari perubahan yang tidak disengaja. Tombol **Kembali ke Dashboard** menyediakan jalan keluar yang jelas. |
| **Pusat Bantuan** (`/cs`) — tamu dan pengguna | Mendapat jawaban atau menyampaikan masalah dengan konteks pesanan. | Kategori pengaduan dan pilihan pesanan membantu pengguna memberi konteks. Riwayat tiket menampilkan kode dan status; FAQ menangani pertanyaan umum, sementara chat AI dapat digunakan sebagai jalur bantuan cepat. Pengiriman tiket memerlukan login dan tiket dikaitkan dengan akun. |

### Pola UCD lintas halaman

- **Bahasa dan tindakan yang konsisten:** label tombol menjelaskan hasil tindakan, seperti **Sewa**, **Checkout**, **Simpan perubahan**, dan **Kirim Pengaduan**.
- **Visibilitas status:** toast sukses/kesalahan, badge keranjang, stok, dan status pesanan memberi umpan balik setelah tindakan.
- **Pencegahan kesalahan:** validasi form, batas stok, konfirmasi atau pemilihan item, dan pengecekan ulang data di server membantu mencegah pesanan yang tidak valid.
- **Navigasi dan pemulihan:** navbar, footer, breadcrumb, tautan kembali, dan empty state membantu pengguna berpindah atau melanjutkan saat belum ada data.
- **Responsif dan aksesibilitas dasar:** layout menyesuaikan ukuran layar; input memakai label dan kontrol interaktif tertentu menyediakan `aria-label` serta indikator fokus keyboard.

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
- Chat CS memakai layanan AI Ollama dan menyimpan riwayat percakapan; tamu dapat menggunakan chat. Pengiriman tiket pengaduan memerlukan akun yang sudah masuk.
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

   Untuk skema utama aplikasi, buat database `db_dpbp` lalu impor `database/db_dpbp.sql` melalui phpMyAdmin. Untuk database lama, buat backup lalu jalankan `database/patch_admin_camrent_catalog.sql` melalui tab SQL phpMyAdmin. Migrasi yang tersedia hanya mencakup tabel dasar Laravel, bukan seluruh tabel CamRent.

   Akun admin demo: `admin@camrent.id` / `admin123`. Admin dapat menambah produk CamRent dari halaman Catalog.
   Untuk menambahkan akun ke database yang sudah ada tanpa menjalankan SQL manual, jalankan:

   ```bash
   php artisan db:seed --class=AdminSeeder
   ```
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
