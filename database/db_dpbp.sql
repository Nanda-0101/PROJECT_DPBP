-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 26, 2026 at 06:02 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_dpbp`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(20) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('Sewa','Pembelian') NOT NULL DEFAULT 'Sewa',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `duration_label` varchar(30) DEFAULT NULL,
  `payment_type` enum('DP','Bayar Penuh') NOT NULL DEFAULT 'DP',
  `payment_method` varchar(30) NOT NULL DEFAULT 'Cash',
  `guarantee` varchar(50) DEFAULT NULL,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `paid_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('Menunggu Konfirmasi','Dikonfirmasi','Selesai','Ditolak','Batal') NOT NULL DEFAULT 'Menunggu Konfirmasi',
  `note` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `code`, `user_id`, `type`, `start_date`, `end_date`, `duration_label`, `payment_type`, `payment_method`, `guarantee`, `total_amount`, `paid_amount`, `status`, `note`, `address`, `created_at`, `updated_at`) VALUES
(1, 'BKG-1001', 1, 'Sewa', '2026-09-05', '2026-09-06', '1 Hari', 'DP', 'QRIS', 'KTP', 480000.00, 144000.00, 'Selesai', NULL, NULL, '2026-08-31 17:00:00', '2026-09-18 06:41:36'),
(2, 'BKG-1002', 1, 'Sewa', '2026-09-14', '2026-09-14', '12 Jam', 'Bayar Penuh', 'Transfer', 'SIM', 190000.00, 190000.00, 'Dikonfirmasi', NULL, NULL, '2026-09-09 17:00:00', '2026-09-18 06:41:36'),
(3, 'BKG-1003', 3, 'Sewa', '2026-09-20', '2026-09-22', '2 Hari', 'DP', 'Cash', 'KTP', 500000.00, 150000.00, 'Menunggu Konfirmasi', NULL, NULL, '2026-09-10 17:00:00', '2026-09-18 06:41:36'),
(4, 'BKG-84001073', 5, 'Sewa', '2026-10-20', '2026-10-20', '12 Jam', 'Bayar Penuh', 'QRIS', 'KTP', 140000.00, 140000.00, 'Menunggu Konfirmasi', NULL, 'KS', '2026-10-11 06:36:33', '2026-10-11 06:36:33'),
(5, 'BKG-83268260', 6, 'Sewa', '2026-10-18', '2026-10-18', '1 Hari', 'DP', 'Cash', 'KTP', 480000.00, 144000.00, 'Menunggu Konfirmasi', NULL, NULL, '2026-10-11 06:37:21', '2026-10-11 06:37:21');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `icon` varchar(50) NOT NULL DEFAULT 'bi-box',
  `sort_order` tinyint(3) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `icon`, `sort_order`) VALUES
(1, 'Kamera', 'bi-camera', 1),
(2, 'Lensa', 'bi-camera2', 2),
(3, 'Lighting', 'bi-lightbulb', 3),
(4, 'Audio', 'bi-mic', 4),
(5, 'Gimbal', 'bi-camera-reels', 5),
(6, 'Tripod', 'bi-camera-reels', 6),
(7, 'Video', 'bi-play-btn', 7);

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mitra_documents`
--

CREATE TABLE `mitra_documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `mitra_id` bigint(20) UNSIGNED NOT NULL,
  `photo_url` varchar(500) NOT NULL,
  `sort_order` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mitra_profiles`
--

CREATE TABLE `mitra_profiles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `owner_name` varchar(120) NOT NULL,
  `nik` char(16) NOT NULL,
  `category` varchar(50) NOT NULL,
  `city` varchar(80) NOT NULL,
  `bank_account` varchar(120) NOT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mitra_profiles`
--

INSERT INTO `mitra_profiles` (`id`, `user_id`, `owner_name`, `nik`, `category`, `city`, `bank_account`, `verified_at`, `created_at`, `updated_at`) VALUES
(1, 2, 'Andi Wijaya', '3201234567890001', 'Campuran', 'Jakarta Selatan', 'BCA 1234567890 a.n. Andi Wijaya', '2026-09-18 06:41:36', '2026-09-18 06:41:36', '2026-09-18 06:41:36');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `booking_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `product_name_snap` varchar(150) NOT NULL,
  `price_snap` decimal(12,2) NOT NULL,
  `qty` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `subtotal` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `booking_id`, `product_id`, `product_name_snap`, `price_snap`, `qty`, `subtotal`) VALUES
(1, 1, 1, 'Sony A7III', 480000.00, 1, 480000.00),
(2, 2, 6, 'Aputure 120D II', 190000.00, 1, 190000.00),
(3, 3, 10, 'DJI RS3 Gimbal', 250000.00, 2, 500000.00),
(4, 4, 10, 'DJI RS3 Gimbal', 250000.00, 1, 140000.00),
(5, 5, 1, 'Sony A7III', 480000.00, 1, 480000.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `seller_id` bigint(20) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `brand` varchar(80) DEFAULT NULL,
  `icon` varchar(50) DEFAULT 'bi-box-seam',
  `image` varchar(500) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price_per_half_day` decimal(12,2) NOT NULL DEFAULT 0.00,
  `price_per_day` decimal(12,2) NOT NULL DEFAULT 0.00,
  `price_sale` decimal(12,2) NOT NULL DEFAULT 0.00,
  `stock` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `rating` decimal(2,1) NOT NULL DEFAULT 0.0,
  `reviews_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_trending` tinyint(1) NOT NULL DEFAULT 0,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('Aktif','Menunggu Verifikasi','Ditolak','Habis') NOT NULL DEFAULT 'Aktif',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `seller_id`, `category_id`, `name`, `brand`, `icon`, `image`, `description`, `price_per_half_day`, `price_per_day`, `price_sale`, `stock`, `rating`, `reviews_count`, `is_trending`, `is_verified`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 'Sony A7III', 'Sony', 'bi-camera', 'products/sony-a7iii.jpg', 'Mirrorless full-frame andalan untuk foto & video.', 280000.00, 480000.00, 18500000.00, 4, 4.9, 212, 1, 1, 'Aktif', '2026-09-18 06:41:36', '2026-09-17 23:53:16'),
(2, 2, 1, 'Canon EOS R6', 'Canon', 'bi-camera', 'products/canon-eos-r6.jpg', 'Mirrorless dengan stabilizer 5-axis.', 300000.00, 520000.00, 24900000.00, 3, 4.8, 154, 1, 1, 'Aktif', '2026-09-18 06:41:36', '2026-09-17 23:53:16'),
(3, 2, 1, 'Fujifilm X-T5', 'Fujifilm', 'bi-camera', 'products/fujifilm-x-t5.jpg', 'APS-C dengan warna khas film simulation.', 230000.00, 400000.00, 0.00, 2, 4.7, 98, 0, 1, 'Aktif', '2026-09-18 06:41:36', '2026-09-17 23:53:16'),
(4, 2, 2, 'Sigma 24-70mm f/2.8', 'Sigma', 'bi-camera2', 'products/sigma-24-70mm.jpg', 'Lensa zoom serbaguna aperture konstan.', 150000.00, 260000.00, 9800000.00, 5, 4.6, 76, 1, 1, 'Aktif', '2026-09-18 06:41:36', '2026-09-17 23:53:16'),
(5, 2, 2, 'Tamron 70-200mm', 'Tamron', 'bi-camera2', 'products/tamron-70-200mm.jpg', 'Telephoto ringan, tajam untuk potret & olahraga.', 180000.00, 320000.00, 0.00, 2, 4.5, 41, 0, 1, 'Aktif', '2026-09-18 06:41:36', '2026-09-17 23:53:16'),
(6, 2, 3, 'Aputure 120D II', 'Aputure', 'bi-lightbulb', 'products/aputure-120d-ii.jpg', 'LED daylight tenaga besar, standar industri.', 190000.00, 340000.00, 6500000.00, 6, 4.8, 132, 1, 1, 'Aktif', '2026-09-18 06:41:36', '2026-09-17 23:53:16'),
(7, 2, 3, 'Godox SL60W', 'Godox', 'bi-lightbulb', 'products/godox-sl60w.jpg', 'LED continuous ekonomis untuk studio kecil.', 90000.00, 160000.00, 1850000.00, 8, 4.4, 59, 0, 1, 'Aktif', '2026-09-18 06:41:36', '2026-09-17 23:53:16'),
(8, 2, 4, 'RØDE NTG5', 'RØDE', 'bi-mic', 'products/rode-ntg5.jpg', 'Shotgun mic broadcast-grade, minim noise.', 120000.00, 210000.00, 4200000.00, 3, 4.7, 64, 0, 1, 'Aktif', '2026-09-18 06:41:36', '2026-09-17 23:53:16'),
(9, 2, 4, 'Shure SM7B', 'Shure', 'bi-mic', 'products/shure-sm7b.jpg', 'Mic dinamis studio favorit podcaster & musisi.', 130000.00, 230000.00, 4900000.00, 2, 4.9, 88, 1, 1, 'Aktif', '2026-09-18 06:41:36', '2026-09-17 23:53:17'),
(10, 2, 5, 'DJI RS3 Gimbal', 'DJI', 'bi-camera-reels', 'products/dji-rs3-gimbal.jpg', 'Stabilizer 3-axis payload 3kg.', 140000.00, 250000.00, 6900000.00, 4, 4.8, 120, 1, 1, 'Aktif', '2026-09-18 06:41:36', '2026-09-17 23:53:17'),
(11, 2, 6, 'Manfrotto Tripod 190', 'Manfrotto', 'bi-camera-reels', 'products/manfrotto-tripod-190.jpg', 'Tripod aluminium kokoh dengan ballhead presisi.', 60000.00, 100000.00, 1650000.00, 10, 4.5, 47, 0, 1, 'Aktif', '2026-09-18 06:41:36', '2026-09-17 23:53:17'),
(12, 2, 7, 'GoPro Hero 12', 'GoPro', 'bi-play-btn', 'products/gopro-hero-12.jpg', 'Action cam tahan air, cocok konten outdoor.', 110000.00, 190000.00, 5600000.00, 5, 4.6, 93, 1, 1, 'Aktif', '2026-09-18 06:41:36', '2026-09-17 23:53:17');

-- --------------------------------------------------------

--
-- Table structure for table `product_specs`
--

CREATE TABLE `product_specs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `spec_text` varchar(180) NOT NULL,
  `sort_order` tinyint(3) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_specs`
--

INSERT INTO `product_specs` (`id`, `product_id`, `spec_text`, `sort_order`) VALUES
(1, 1, 'Tipe: Kamera mirrorless full-frame', 1),
(2, 1, 'Sensor: 24,2 MP full-frame Exmor R BSI CMOS', 2),
(3, 1, 'Prosesor: BIONZ X', 3),
(4, 1, 'ISO: 100–51.200 (diperluas 50–204.800)', 4),
(5, 1, 'Autofokus: 693 titik deteksi fase + 425 titik kontras, Eye AF', 5),
(6, 1, 'Burst: Hingga 10 fps (mekanik & senyap)', 6),
(7, 1, 'Stabilisasi: IBIS 5-axis (hingga 5 stop)', 7),
(8, 1, 'Video: 4K 30p (full pixel readout), Full HD 120p', 8),
(9, 1, 'Viewfinder: OLED EVF 2,36 juta titik', 9),
(10, 1, 'Layar: 3" tilt touchscreen, 922 ribu titik', 10),
(11, 1, 'Slot kartu: Dual SD (slot 1 UHS-II)', 11),
(12, 1, 'Baterai: NP-FZ100, ±710 foto (LCD)', 12),
(13, 1, 'Mount: Sony E', 13),
(14, 1, 'Berat: ±650 g (dengan baterai & kartu)', 14),
(15, 2, 'Tipe: Kamera mirrorless full-frame', 1),
(16, 2, 'Sensor: 20,1 MP full-frame CMOS', 2),
(17, 2, 'Prosesor: DIGIC X', 3),
(18, 2, 'ISO: 100–102.400 (diperluas 204.800)', 4),
(19, 2, 'Autofokus: Dual Pixel CMOS AF II, 1.053 zona, deteksi mata/hewan', 5),
(20, 2, 'Burst: 12 fps mekanik, 20 fps elektronik', 6),
(21, 2, 'Stabilisasi: IBIS 5-axis (hingga 8 stop dengan lensa RF tertentu)', 7),
(22, 2, 'Video: 4K 60p, Full HD 120p, 10-bit (Canon Log / HDR PQ)', 8),
(23, 2, 'Viewfinder: OLED EVF 3,69 juta titik', 9),
(24, 2, 'Layar: 3" vari-angle touchscreen, 1,62 juta titik', 10),
(25, 2, 'Slot kartu: Dual SD UHS-II', 11),
(26, 2, 'Baterai: LP-E6NH, ±360 foto (LCD)', 12),
(27, 2, 'Mount: Canon RF', 13),
(28, 2, 'Berat: ±680 g (dengan baterai & kartu)', 14),
(29, 3, 'Tipe: Kamera mirrorless APS-C', 1),
(30, 3, 'Sensor: 40,2 MP APS-C X-Trans CMOS 5 HR', 2),
(31, 3, 'Prosesor: X-Processor 5', 3),
(32, 3, 'ISO: 125–12.800 (diperluas 64–51.200)', 4),
(33, 3, 'Autofokus: 425 area, deteksi subjek berbasis AI', 5),
(34, 3, 'Burst: 15 fps mekanik, hingga 20 fps elektronik (crop 1,29×)', 6),
(35, 3, 'Stabilisasi: IBIS 5-axis (hingga 7 stop)', 7),
(36, 3, 'Video: 6,2K 30p, 4K 60p, 10-bit F-Log / F-Log2', 8),
(37, 3, 'Viewfinder: OLED EVF 3,69 juta titik', 9),
(38, 3, 'Layar: 3,0" tilt 3 arah touchscreen, 1,84 juta titik', 10),
(39, 3, 'Slot kartu: Dual SD UHS-II', 11),
(40, 3, 'Baterai: NP-W235, ±580 foto', 12),
(41, 3, 'Mount: Fujifilm X', 13),
(42, 3, 'Berat: ±557 g (dengan baterai & kartu)', 14),
(43, 3, 'Film Simulation: 19 mode (termasuk Nostalgic Neg. & Reala Ace)', 15),
(44, 4, 'Tipe: Lensa zoom standar full-frame mirrorless', 1),
(45, 4, 'Model: Sigma 24-70mm F2.8 DG DN | Art', 2),
(46, 4, 'Mount: Sony E / L-Mount', 3),
(47, 4, 'Panjang fokus: 24–70 mm', 4),
(48, 4, 'Bukaan: f/2.8 konstan (minimum f/22)', 5),
(49, 4, 'Konstruksi: 19 elemen dalam 15 grup', 6),
(50, 4, 'Bilah diafragma: 11 (membulat)', 7),
(51, 4, 'Diameter filter: 82 mm', 8),
(52, 4, 'Fokus terdekat: 18 cm (24 mm) – 38 cm (70 mm)', 9),
(53, 4, 'Stabilisasi: Tidak ada OIS (mengandalkan IBIS di bodi kamera)', 10),
(54, 4, 'Autofokus: Motor stepping, internal focusing', 11),
(55, 4, 'Ketahanan: Tahan debu & percikan air', 12),
(56, 4, 'Dimensi: Ø 87,8 × 124,9 mm', 13),
(57, 4, 'Berat: ±830 g', 14),
(58, 5, 'Tipe: Lensa zoom tele full-frame', 1),
(59, 5, 'Panjang fokus: 70–200 mm', 2),
(60, 5, 'Bukaan: f/2.8 konstan', 3),
(61, 6, 'Tipe: Lampu LED COB daylight', 1),
(62, 6, 'Daya: 135 W', 2),
(63, 6, 'Suhu warna: 5500K (daylight)', 3),
(64, 6, 'CRI: 96+', 4),
(65, 6, 'Mount: Bowens', 5),
(66, 6, 'Dimmer: 0–100%', 6),
(67, 6, 'Kontrol: Aplikasi Sidus Link (Bluetooth) & DMX', 7),
(68, 7, 'Tipe: Lampu LED studio daylight', 1),
(69, 7, 'Daya: 60 W', 2),
(70, 7, 'Suhu warna: 5600K ±300K', 3),
(71, 7, 'Mount: Bowens', 4),
(72, 7, 'Kontrol: Remote 2,4 GHz (termasuk) & knob dimmer', 5),
(73, 7, 'Pendingin: Kipas internal', 6),
(74, 8, 'Tipe: Mikrofon shotgun kondenser', 1),
(75, 8, 'Pola tangkap: Supercardioid', 2),
(76, 8, 'Respons frekuensi: 20 Hz – 20 kHz', 3),
(77, 8, 'Daya: Phantom power 48V', 4),
(78, 8, 'Konektor: XLR 3-pin', 5),
(79, 8, 'Ketahanan: Tahan kelembapan', 6),
(80, 9, 'Tipe: Mikrofon dinamis', 1),
(81, 9, 'Pola tangkap: Cardioid', 2),
(82, 9, 'Respons frekuensi: 50 Hz – 20 kHz', 3),
(83, 9, 'Impedansi output: 150 Ω', 4),
(84, 9, 'Sensitivitas: −59 dBV/Pa (1,12 mV/Pa)', 5),
(85, 9, 'Konektor: XLR 3-pin', 6),
(86, 9, 'Kontrol: Switch bass roll-off & presence boost', 7),
(87, 9, 'Peredam: Isolasi guncangan internal + pop filter bawaan', 8),
(88, 9, 'Berat: ±764 g', 9),
(89, 10, 'Tipe: Gimbal genggam 3-axis', 1),
(90, 10, 'Beban maksimum: 3 kg', 2),
(91, 10, 'Layar: OLED 1,8" touchscreen', 3),
(92, 10, 'Baterai: Hingga ±12 jam', 4),
(93, 10, 'Konektivitas: Bluetooth 5.0, USB-C', 5),
(94, 10, 'Fitur: Axis lock otomatis, mode vertikal (quick flip)', 6),
(95, 11, 'Tipe: Tripod aluminium', 1),
(96, 11, 'Tinggi maksimum: 150 cm', 2),
(97, 11, 'Beban maksimum: 6 kg', 3),
(98, 12, 'Tipe: Action camera', 1),
(99, 12, 'Video: 5,3K 60 fps, 4K 120 fps, 2,7K 240 fps', 2),
(100, 12, 'Foto: 27 MP', 3),
(101, 12, 'Sensor: 1/1,9"', 4),
(102, 12, 'Stabilisasi: HyperSmooth 6.0', 5),
(103, 12, 'Warna: 10-bit, HDR video', 6),
(104, 12, 'Tahan air: 10 m tanpa casing', 7),
(105, 12, 'Layar: Touchscreen depan & belakang', 8),
(106, 12, 'Baterai: Enduro 1.720 mAh', 9),
(107, 12, 'Berat: ±154 g', 10);

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(20) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `booking_id` bigint(20) UNSIGNED DEFAULT NULL,
  `category` varchar(80) NOT NULL,
  `name` varchar(120) NOT NULL,
  `contact` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `status` enum('Diproses','Selesai','Ditolak') NOT NULL DEFAULT 'Diproses',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('user','mitra','admin') NOT NULL DEFAULT 'user',
  `status` enum('Aktif','Menunggu Verifikasi','Terverifikasi','Ditolak') NOT NULL DEFAULT 'Aktif',
  `avatar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `role`, `status`, `avatar`, `created_at`, `updated_at`) VALUES
(1, 'Andi', 'andi@gmail.com', '$2y$12$tPhQFqIvMse5ukTVWVRyo.Rivb1WEB2zJqg7jV6pfi9OB9wtM1dTq', '081234567890', 'user', 'Aktif', NULL, '2026-09-18 06:41:36', '2026-10-11 06:15:31'),
(2, 'Budi', 'budi@gmail.com', '$2y$12$gJBnSpS1rbbMOeg7K/iWiuuksp6kjTRQMXHG0brx5wA32NFPTodUW', '081298765432', 'mitra', 'Terverifikasi', NULL, '2026-09-18 06:41:36', '2026-10-25 20:55:23'),
(3, 'Caca', 'caca@gmail.com', '123456', '081311122233', 'user', 'Aktif', NULL, '2026-09-18 06:41:36', '2026-10-11 13:46:50'),
(4, 'dimas', 'dimas@gmail.com', '$2y$12$KK6a..MVgfTlHq9KGhDZqevTlMyOrNSI.beGE8BT/F/mlzhy9svQi', '081232211122', 'user', 'Aktif', NULL, '2026-10-11 06:30:48', '2026-10-11 06:30:48'),
(5, 'SD', 'guest+d603cf98-6ddb-4403-939b-2c84b6f1b06d@camrent.invalid', '$2y$12$JkTAHTLDp9Xh7a/WTPNuK.8AERS8vzHWpOvFk0ZB2NqH4u.rld97e', '009090909', 'user', 'Aktif', NULL, '2026-10-11 06:36:33', '2026-10-11 06:36:33'),
(6, 'ww', 'guest+4833c55e-5b13-4a4d-bfe5-eb2685805c18@camrent.invalid', '$2y$12$ku2zT3GUzGIVsXSuGNSJ6ebkUzzPVx0YtJx3hQVqm/isxGX.9.bOO', '232323', 'user', 'Aktif', NULL, '2026-10-11 06:37:21', '2026-10-11 06:37:21');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_bookings_code` (`code`),
  ADD KEY `idx_bookings_user` (`user_id`),
  ADD KEY `idx_bookings_status` (`status`),
  ADD KEY `idx_bookings_type` (`type`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_categories_name` (`name`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mitra_documents`
--
ALTER TABLE `mitra_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mitra_docs_mitra` (`mitra_id`);

--
-- Indexes for table `mitra_profiles`
--
ALTER TABLE `mitra_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_mitra_user` (`user_id`),
  ADD UNIQUE KEY `uk_mitra_nik` (`nik`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_booking` (`booking_id`),
  ADD KEY `idx_order_product` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_products_seller` (`seller_id`),
  ADD KEY `idx_products_category` (`category_id`),
  ADD KEY `idx_products_status` (`status`),
  ADD KEY `idx_products_trending` (`is_trending`);

--
-- Indexes for table `product_specs`
--
ALTER TABLE `product_specs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_specs_product` (`product_id`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_tickets_code` (`code`),
  ADD KEY `idx_tickets_user` (`user_id`),
  ADD KEY `idx_tickets_booking` (`booking_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_users_email` (`email`),
  ADD KEY `idx_users_role` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mitra_documents`
--
ALTER TABLE `mitra_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mitra_profiles`
--
ALTER TABLE `mitra_profiles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `product_specs`
--
ALTER TABLE `product_specs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_bookings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `mitra_documents`
--
ALTER TABLE `mitra_documents`
  ADD CONSTRAINT `fk_mitra_docs_mitra` FOREIGN KEY (`mitra_id`) REFERENCES `mitra_profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `mitra_profiles`
--
ALTER TABLE `mitra_profiles`
  ADD CONSTRAINT `fk_mitra_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_order_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_products_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `product_specs`
--
ALTER TABLE `product_specs`
  ADD CONSTRAINT `fk_specs_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `fk_tickets_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tickets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
--
-- Table structure for table `product_images` (foto sudut tambahan untuk specsheet)
--
CREATE TABLE `product_images` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `path` varchar(500) NOT NULL,
  `label` varchar(60) DEFAULT NULL,
  `sort_order` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_images_product` (`product_id`),
  CONSTRAINT `fk_images_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
