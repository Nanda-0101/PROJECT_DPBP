-- Kolom payment_method sebelumnya ENUM('Cash','Transfer','QRIS') sehingga
-- BCA / BRI / BNI / SeaBank / DANA / ShopeePay / GoPay akan ditolak MySQL.
-- Diubah ke VARCHAR agar daftar metode cukup diatur dari config/payment.php.
-- Data lama (Cash, Transfer, QRIS) tetap aman.
-- =====================================================================

ALTER TABLE `bookings`
  MODIFY `payment_method` varchar(30) NOT NULL DEFAULT 'Cash';
