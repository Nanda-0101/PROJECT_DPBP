-- =====================================================================
-- Patch database — Revisi: Keranjang Sewa, Rekomendasi, Metode Bayar Baru
-- Jalankan SEKALI di database yang sudah di-import dari db_dpbp.sql lama
-- (phpMyAdmin → pilih db_dpbp → tab SQL → tempel → Go).
-- Database baru cukup import db_dpbp.sql terbaru, patch ini tidak perlu.
--
-- Kolom payment_method sebelumnya ENUM('Cash','Transfer','QRIS') sehingga
-- BCA / BRI / BNI / SeaBank / DANA / ShopeePay / GoPay akan ditolak MySQL.
-- Diubah ke VARCHAR agar daftar metode cukup diatur dari config/payment.php.
-- Data lama (Cash, Transfer, QRIS) tetap aman.
-- =====================================================================

ALTER TABLE `bookings`
  MODIFY `payment_method` varchar(30) NOT NULL DEFAULT 'Cash';
