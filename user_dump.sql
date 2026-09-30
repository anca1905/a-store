<USER_REQUEST>
-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Waktu pembuatan: 28 Sep 2026 pada 03.59
-- Versi server: 10.11.10-MariaDB-log
-- Versi PHP: 8.3.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Basis data: `alan`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_barang`
--

CREATE TABLE `tbl_barang` (
  `id_barang` int(11) NOT NULL,
  `kode_barang` varchar(20) NOT NULL,
  `nama_produk` varchar(100) NOT NULL,
  `kategori` varchar(50) NOT NULL,
  `harga_beli` int(11) NOT NULL DEFAULT 0,
  `harga_jual` int(11) NOT NULL DEFAULT 0,
  `stok_min` int(11) NOT NULL,
  `satuan` varchar(20) DEFAULT 'Pcs',
  `deskripsi` text DEFAULT NULL,
  `stok_aktual` int(11) NOT NULL,
  `foto_barang` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `tbl_barang`
--

INSERT INTO `tbl_barang` (`id_barang`, `kode_barang`, `nama_produk`, `kategori`, `harga_beli`, `harga_jual`, `stok_min`, `satuan`, `deskripsi`, `stok_aktual`, `foto_barang`) VALUES
(9, 'MYBSC01', 'MYBASIC TSHIRT CUTTINGAN BOXY', 'pakaian', 100, 130, 0, 'Pcs', '', 5, '1790208908_59280.png'),
(10, 'RNSL01', 'RANSEL CURDUROY (MAROON)', 'RANSEL', 110, 150, 1, 'Pcs', 'Warna: MAROON', 2, '1790209604_58336.jpg'),
(11, 'RNSL02', 'RANSEL CURDUROY (BLCK)', 'RANSEL', 110, 130, 1, 'Pcs', 'Warna: BLCK', 5, '1790209686_58350.jpg'),
(12, 'RNSL03', 'RANSEL CURDUROY (ARMY)', 'RANSEL', 110, 130, 1, 'Pcs', 'Warna: ARMY', 5, '1790209741_58340.jpg'),
(13, 'RNSL04', 'RANSEL CURDUROY (NAVY)', 'RANSEL', 110, 130, 1, 'Pcs', '', 5, '1790209792_58346.jpg'),
(14, 'RNSL05', 'RANSEL CURDUROY (ABU)', 'RANSEL', 110, 130, 1, 'Pcs', 'Warna: ABU', 5, '1790209853_58352.jpg'),
(15, 'RNSL06', 'RANSEL OURIST (NAVY)', 'RANSEL', 130000, 150000, 1, 'Pcs', '', 2, '1790211188_36484.jpg'),
(16, 'SLMPNG01', 'TAS SELEMPANG CURDUROY SMALL (KECIL)', 'TAS SELEMPANG', 60000, 100000, 2, 'Pcs', 'Warna: BLCK', 9, '1790211340_59022.png'),
(17, 'SLMPNG02', 'TAS SELEMPANG CURDUROY SMALL (KECIL)', 'TAS SELEMPANG', 60000, 100000, 5, 'Pcs', 'Warna: ABU', 9, '1790211416_59022.png'),
(18, 'HLM01', 'HELM CLASSIC', 'HELM', 95000, 130000, 8, 'Pcs', '', 5, '1790211497_59327.jpg');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_detail_penjualan`
--

CREATE TABLE `tbl_detail_penjualan` (
  `id_detail` int(11) NOT NULL,
  `id_penjualan` int(11) NOT NULL,
  `kode_barang` varchar(20) NOT NULL,
  `harga_satuan` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `subtotal` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `tbl_detail_penjualan`
--

INSERT INTO `tbl_detail_penjualan` (`id_detail`, `id_penjualan`, `kode_barang`, `harga_satuan`, `qty`, `subtotal`) VALUES
(14, 153, 'HLM01', 130000, 2, 260000);

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_kategori`
--

CREATE TABLE `tbl_kategori` (
  `id_kategori` int(11) NOT NULL,
  `nama_kategori` varchar(100) NOT NULL,
  `foto_kategori` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `tbl_kategori`
--

INSERT INTO `tbl_kategori` (`id_kategori`, `nama_kategori`, `foto_kategori`) VALUES
(6, 'ATASAN', '1790208985_55609.jpg'),
(7, 'SHORT PANTS', '1790209174_59011.png'),
(8, 'TAS SELEMPANG', '1790209272_59022.png'),
(9, 'SENDAL', '1790209246_40961.jpg'),
(10, 'HELM', '1790209107_59327.jpg'),
(11, 'RANSEL', '1790209322_58358.jpg'),
(12, 'PARFUM', '1790209406_40103.jpg');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_penjualan`
--

CREATE TABLE `tbl_penjualan` (
  `id_penjualan` int(11) NOT NULL,
  `no_faktur` varchar(30) NOT NULL,
  `tanggal_waktu` datetime NOT NULL,
  `total_item` int(11) NOT NULL,
  `grand_total` int(11) NOT NULL,
  `nominal_bayar` int(11) NOT NULL,
  `kembalian` int(11) NOT NULL,
  `metode_pembayaran` varchar(50) DEFAULT 'Cash'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `tbl_penjualan`
--

INSERT INTO `tbl_penjualan` (`id_penjualan`, `no_faktur`, `tanggal_waktu`, `total_item`, `grand_total`, `nominal_bayar`, `kembalian`, `metode_pembayaran`) VALUES
(29, 'INV-202607-0422', '2026-07-10 11:45:40', 1, 150000, 150000, 0, 'Cash'),
(30, 'INV-202607-6974', '2026-07-10 17:49:16', 1, 180000, 180000, 0, 'Cash'),
(31, 'INV-202607-7224', '2026-07-10 17:50:17', 1, 150000, 150000, 0, 'Cash'),
(32, 'INV-202607-0106', '2026-07-10 17:52:30', 3, 480000, 480000, 0, 'Cash'),
(33, 'INV-202607-6205', '2026-07-10 17:55:46', 3, 370000, 370000, 0, 'Cash'),
(34, 'INV-202607-9239', '2026-07-10 17:56:19', 2, 240000, 240000, 0, 'Cash'),
(35, 'INV-202607-6463', '2026-07-10 17:56:46', 1, 100000, 100000, 0, 'Cash'),
(36, 'INV-202607-5682', '2026-07-10 17:56:55', 1, 100000, 100000, 0, 'Cash'),
(37, 'INV-202607-0215', '2026-07-11 11:20:50', 1, 150000, 150000, 0, 'Transfer'),
(38, 'INV-202607-6484', '2026-07-12 13:20:16', 3, 480000, 480000, 0, 'Cash'),
(39, 'INV-202607-0222', '2026-07-12 13:26:20', 2, 300000, 300000, 0, 'Cash'),
(40, 'INV-202607-4570', '2026-07-12 13:30:44', 1, 170000, 170000, 0, 'Cash'),
(41, 'INV-202607-3720', '2026-07-12 13:32:53', 1, 150000, 150000, 0, 'Cash'),
(42, 'INV-202607-7318', '2026-07-12 14:06:07', 4, 520000, 520000, 0, 'Cash'),
(43, 'INV-202607-2883', '2026-07-12 14:06:52', 1, 170000, 170000, 0, 'Cash'),
(44, 'INV-202607-4680', '2026-07-13 08:56:44', 3, 420000, 420000, 0, 'Cash'),
(45, 'INV-202607-1121', '2026-07-13 09:58:41', 3, 330000, 330000, 0, 'Cash'),
(46, 'INV-202607-8389', '2026-07-13 10:20:48', 1, 150000, 150000, 0, 'Cash'),
(47, 'INV-202607-1059', '2026-07-13 13:34:21', 1, 150000, 150000, 0, 'Cash'),
(48, 'INV-202607-3371', '2026-07-13 13:40:03', 2, 340000, 340000, 0, 'Cash'),
(49, 'INV-202607-0256', '2026-07-13 13:43:20', 1, 150000, 150000, 0, 'Cash'),
(50, 'INV-202607-2469', '2026-07-15 15:55:12', 5, 585000, 585000, 0, 'Cash'),
(51, 'INV-202607-1725', '2026-07-15 15:56:01', 2, 260000, 260000, 0, 'Cash'),
(52, 'INV-202607-1794', '2026-07-15 15:56:21', 2, 320000, 320000, 0, 'Cash'),
(53, 'INV-202607-8887', '2026-07-16 15:11:38', 1, 80000, 80000, 0, 'Cash'),
(54, 'INV-202607-3115', '2026-07-16 15:20:43', 2, 320000, 320000, 0, 'Cash'),
(55, 'INV-202607-8750', '2026-07-16 15:21:28', 1, 170000, 170000, 0, 'Cash'),
(56, 'INV-202607-9613', '2026-07-16 15:23:09', 1, 150000, 150000, 0, 'Cash'),
(57, 'INV-202607-0243', '2026-07-18 13:12:20', 2, 300000, 300000, 0, 'Cash'),
(58, 'INV-202607-2131', '2026-07-18 13:13:02', 1, 150000, 150000, 0, 'Cash'),
(59, 'INV-202607-0724', '2026-07-18 13:14:10', 1, 170000, 170000, 0, 'Cash'),
(60, 'INV-202607-7755', '2026-07-18 20:28:07', 1, 150000, 150000, 0, 'Cash'),
(61, 'INV-202607-6739', '2026-07-18 20:28:56', 1, 70000, 70000, 0, 'Cash'),
(62, 'INV-202607-3600', '2026-07-18 20:30:03', 1, 120000, 120000, 0, 'Cash'),
(63, 'INV-202607-4981', '2026-07-18 20:30:24', 1, 80000, 80000, 0, 'Cash'),
(64, 'INV-202607-9476', '2026-07-18 20:32:49', 3, 400000, 400000, 0, 'Cash'),
(65, 'INV-202607-1790', '2026-07-18 20:33:41', 3, 390000, 390000, 0, 'Cash'),
(66, 'INV-202607-1954', '2026-07-18 20:37:31', 4, 670000, 670000, 0, 'Cash'),
(67, 'INV-202607-1617', '2026-07-18 20:44:21', 1, 50000, 50000, 0, 'Cash'),
(68, 'INV-202607-6799', '2026-07-19 13:27:16', 1, 130000, 130000, 0, 'Cash'),
(69, 'INV-202607-3768', '2026-07-19 13:28:13', 2, 330000, 330000, 0, 'Cash'),
(70, 'INV-202607-8111', '2026-07-19 13:29:08', 1, 160000, 160000, 0, 'Cash'),
(71, 'INV-202607-4820', '2026-07-19 13:32:04', 1, 170000, 170000, 0, 'Cash'),
(72, 'INV-202607-2079', '2026-07-19 13:33:22', 1, 110000, 110000, 0, 'Cash'),
(73, 'INV-202607-1370', '2026-07-19 13:34:11', 1, 150000, 150000, 0, 'Cash'),
(74, 'INV-202607-4161', '2026-07-19 13:37:04', 1, 210000, 210000, 0, 'Cash'),
(75, 'INV-202607-2792', '2026-07-19 13:45:32', 2, 300000, 300000, 0, 'Cash'),
(76, 'INV-202607-5341', '2026-07-19 13:45:45', 1, 100000, 100000, 0, 'Cash'),
(77, 'INV-202607-5484', '2026-07-19 13:45:55', 1, 150000, 150000, 0, 'Cash'),
(78, 'INV-202607-0977', '2026-07-19 13:46:20', 3, 380000, 380000, 0, 'Cash'),
(79, 'INV-202607-5374', '2026-07-20 13:57:45', 1, 100000, 100000, 0, 'Cash'),
(80, 'INV-202607-8854', '2026-07-20 14:00:08', 1, 250000, 250000, 0, 'Cash'),
(81, 'INV-202607-7153', '2026-07-20 14:20:17', 4, 560000, 560000, 0, 'Cash'),
(82, 'INV-202607-0152', '2026-07-20 14:21:40', 1, 180000, 180000, 0, 'Cash'),
(83, 'INV-202607-4687', '2026-07-20 14:21:54', 1, 150000, 150000, 0, 'Cash'),
(84, 'INV-202607-9803', '2026-07-20 14:22:09', 1, 110000, 110000, 0, 'Cash'),
(85, 'INV-202607-9034', '2026-07-20 14:22:29', 1, 30000, 30000, 0, 'Cash'),
(86, 'INV-202607-8064', '2026-07-20 14:22:38', 1, 110000, 110000, 0, 'Cash'),
(87, 'INV-202607-9548', '2026-07-22 11:06:09', 1, 150000, 150000, 0, 'Cash'),
(88, 'INV-202607-4200', '2026-07-22 11:06:24', 1, 130000, 130000, 0, 'Cash'),
(89, 'INV-202607-1864', '2026-07-22 11:08:31', 2, 160000, 160000, 0, 'Cash'),
(90, 'INV-202607-3110', '2026-07-22 11:08:53', 1, 100000, 100000, 0, 'Cash'),
(91, 'INV-202607-2986', '2026-07-22 11:09:22', 1, 80000, 80000, 0, 'Cash'),
(92, 'INV-202607-6686', '2026-07-22 11:09:46', 1, 150000, 150000, 0, 'Cash'),
(93, 'INV-202607-7957', '2026-07-22 11:10:57', 1, 40000, 40000, 0, 'Cash'),
(94, 'INV-202607-0220', '2026-07-22 11:12:50', 2, 210000, 210000, 0, 'Cash'),
(95, 'INV-202607-2976', '2026-07-22 12:54:22', 1, 150000, 150000, 0, 'Cash'),
(96, 'INV-202607-3251', '2026-07-22 12:55:03', 1, 180000, 180000, 0, 'Cash'),
(97, 'INV-202607-0652', '2026-07-22 12:55:30', 1, 30000, 30000, 0, 'Cash'),
(98, 'INV-202607-9608', '2026-07-22 13:17:49', 2, 60000, 60000, 0, 'Cash'),
(99, 'INV-202607-0611', '2026-07-23 16:46:10', 1, 150000, 150000, 0, 'Cash'),
(100, 'INV-202607-4119', '2026-07-23 16:54:34', 2, 255000, 255000, 0, 'Cash'),
(101, 'INV-202607-1065', '2026-07-23 16:55:01', 1, 170000, 170000, 0, 'Cash'),
(102, 'INV-202607-8988', '2026-07-23 16:55:48', 1, 150000, 150000, 0, 'Cash'),
(103, 'INV-202607-4213', '2026-07-23 17:00:44', 1, 180000, 180000, 0, 'Cash'),
(104, 'INV-202607-7100', '2026-07-23 17:02:07', 1, 150000, 150000, 0, 'Cash'),
(105, 'INV-202607-8588', '2026-07-24 06:28:18', 2, 80000, 80000, 0, 'Cash'),
(106, 'INV-202607-9955', '2026-07-24 06:28:59', 2, 80000, 80000, 0, 'Cash'),
(107, 'INV-202607-0653', '2026-07-24 06:43:50', 1, 150000, 150000, 0, 'Cash'),
(108, 'INV-202607-3146', '2026-07-24 06:44:13', 1, 150000, 150000, 0, 'Cash'),
(109, 'INV-202607-2900', '2026-07-24 14:12:52', 2, 200000, 200000, 0, 'Cash'),
(110, 'INV-202607-5880', '2026-07-24 14:13:45', 1, 100000, 100000, 0, 'Cash'),
(111, 'INV-202607-8685', '2026-07-24 14:23:48', 1, 100000, 100000, 0, 'Cash'),
(112, 'INV-202607-4537', '2026-07-24 14:24:04', 1, 120000, 120000, 0, 'Cash'),
(113, 'INV-202607-2660', '2026-07-24 14:24:32', 1, 30000, 30000, 0, 'Cash'),
(114, 'INV-202607-0692', '2026-07-24 14:29:40', 1, 180000, 180000, 0, 'Cash'),
(115, 'INV-202607-7021', '2026-07-27 05:21:27', 3, 260000, 260000, 0, 'Cash'),
(116, 'INV-202607-7709', '2026-07-27 05:21:47', 1, 130000, 130000, 0, 'Cash'),
(117, 'INV-202607-6481', '2026-07-27 05:22:16', 1, 150000, 150000, 0, 'Cash'),
(118, 'INV-202607-1780', '2026-07-27 05:25:31', 2, 250000, 250000, 0, 'Cash'),
(119, 'INV-202607-8877', '2026-07-27 05:30:28', 1, 150000, 150000, 0, 'Cash'),
(120, 'INV-202607-0136', '2026-07-27 05:31:50', 2, 230000, 230000, 0, 'Cash'),
(121, 'INV-202607-5392', '2026-07-27 05:32:15', 1, 100000, 100000, 0, 'Cash'),
(122, 'INV-202607-5029', '2026-07-27 05:32:35', 1, 100000, 100000, 0, 'Cash'),
(123, 'INV-202607-8258', '2026-07-27 05:33:18', 2, 230000, 230000, 0, 'Cash'),
(124, 'INV-202607-6206', '2026-07-27 05:35:06', 1, 130000, 130000, 0, 'Cash'),
(125, 'INV-202607-7993', '2026-07-27 05:36:17', 2, 150000, 150000, 0, 'Cash'),
(126, 'INV-202607-7110', '2026-07-27 05:37:57', 1, 200000, 200000, 0, 'Cash'),
(127, 'INV-202607-9836', '2026-07-27 05:38:29', 1, 25000, 25000, 0, 'Cash'),
(128, 'INV-202607-9889', '2026-07-27 05:40:39', 1, 40000, 40000, 0, 'Cash'),
(129, 'INV-202607-8342', '2026-07-27 17:18:38', 1, 130000, 130000, 0, 'Cash'),
(130, 'INV-202607-1661', '2026-07-27 17:19:41', 1, 130000, 130000, 0, 'Cash'),
(131, 'INV-202607-8725', '2026-07-27 17:20:18', 1, 30000, 30000, 0, 'Cash'),
(132, 'INV-202607-3305', '2026-07-27 17:46:03', 3, 320000, 320000, 0, 'Cash'),
(133, 'INV-202607-4835', '2026-07-27 17:49:14', 1, 150000, 150000, 0, 'Cash'),
(134, 'INV-202607-5877', '2026-07-27 17:50:35', 1, 40000, 40000, 0, 'Cash'),
(135, 'INV-202607-5756', '2026-07-29 14:49:25', 1, 100000, 100000, 0, 'Cash'),
(136, 'INV-202607-0993', '2026-07-29 14:49:50', 1, 180000, 180000, 0, 'Cash'),
(137, 'INV-202607-6459', '2026-07-29 14:50:26', 1, 150000, 150000, 0, 'Cash'),
(138, 'INV-202607-8128', '2026-07-29 14:51:48', 3, 480000, 480000, 0, 'Cash'),
(139, 'INV-202607-1503', '2026-07-29 14:52:31', 2, 300000, 300000, 0, 'Cash'),
(140, 'INV-202607-4962', '2026-07-29 14:54:04', 1, 130000, 130000, 0, 'Cash'),
(141, 'INV-202607-3646', '2026-07-31 13:11:33', 1, 80000, 80000, 0, 'Cash'),
(142, 'INV-202607-5789', '2026-07-31 13:12:05', 1, 170000, 170000, 0, 'Cash'),
(143, 'INV-202607-4656', '2026-07-31 13:53:54', 5, 530000, 530000, 0, 'Cash'),
(144, 'INV-202608-4348', '2026-08-01 14:33:04', 4, 590000, 590000, 0, 'Cash'),
(145, 'INV-202608-6960', '2026-08-03 16:18:06', 1, 70000, 70000, 0, 'Cash'),
(146, 'INV-202608-9856', '2026-08-03 16:18:49', 1, 170000, 170000, 0, 'Cash'),
(147, 'INV-202608-6225', '2026-08-03 16:19:36', 1, 150000, 150000, 0, 'Cash'),
(148, 'INV-202607-D001', '2026-07-01 10:00:00', 1, 70000, 70000, 0, 'Cash'),
(149, 'INV-202607-D002', '2026-07-01 10:05:00', 1, 130000, 130000, 0, 'Cash'),
(150, 'INV-202607-D003', '2026-07-01 10:10:00', 1, 180000, 180000, 0, 'Cash'),
(151, 'INV-202607-D004', '2026-07-01 10:15:00', 1, 130000, 130000, 0, 'Cash'),
(152, 'INV-202607-D005', '2026-07-01 10:20:00', 1, 160000, 160000, 0, 'Cash'),
(153, 'INV-202609-5672', '2026-09-28 01:03:18', 2, 260000, 300000, 40000, 'Cash');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_users`
--

CREATE TABLE `tbl_users` (
  `id_user` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `role` varchar(50) DEFAULT 'Admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `tbl_users`
--

INSERT INTO `tbl_users` (`id_user`, `username`, `password`, `nama_lengkap`, `role`) VALUES
(1, 'admin', '$2a$12$KR0zg/u/KHcnP7/aDptn3uq9tzFyj2FNOwa0Cm4sNeJHXDnF8wVsm', 'ALAM', 'Pimpinan'),
(2, 'kasir1', '$2a$12$KR0zg/u/KHcnP7/aDptn3uq9tzFyj2FNOwa0Cm4sNeJHXDnF8wVsm', 'anca', 'Kasir'),
(3, 'kasir2', '$2y$10$GB0AQ8sXfOFe7nWiKTqz5.3QL5L1NDn2myMOOwAljIglw2gYmY0eS', 'ansar', 'Kasir');

--
-- Indeks untuk tabel yang dibuang
--

--
-- Indeks untuk tabel `tbl_barang`
--
ALTER TABLE `tbl_barang`
  ADD PRIMARY KEY (`id_barang`),
  ADD UNIQUE KEY `kode_barang` (`kode_barang`);

--
-- Indeks untuk tabel `tbl_detail_penjualan`
--
ALTER TABLE `tbl_detail_penjualan`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_penjualan` (`id_penjualan`);

--
-- Indeks untuk tabel `tbl_kategori`
--
ALTER TABLE `tbl_kategori`
  ADD PRIMARY KEY (`id_kategori`),
  ADD UNIQUE KEY `nama_kategori` (`nama_kategori`);

--
-- Indeks untuk tabel `tbl_penjualan`
--
ALTER TABLE `tbl_penjualan`
  ADD PRIMARY KEY (`id_penjualan`),
  ADD UNIQUE KEY `no_faktur` (`no_faktur`);

--
-- Indeks untuk tabel `tbl_users`
--
ALTER TABLE `tbl_users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `tbl_barang`
--
ALTER TABLE `tbl_barang`
  MODIFY `id_barang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT untuk tabel `tbl_detail_penjualan`
--
ALTER TABLE `tbl_detail_penjualan`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT untuk tabel `tbl_kategori`
--
ALTER TABLE `tbl_kategori`
  MODIFY `id_kategori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `tbl_penjualan`
--
ALTER TABLE `tbl_penjualan`
  MODIFY `id_penjualan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=154;

--
-- AUTO_INCREMENT untuk tabel `tbl_users`
--
ALTER TABLE `tbl_users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `tbl_detail_penjualan`
--
ALTER TABLE `tbl_detail_penjualan`
  ADD CONSTRAINT `tbl_detail_penjualan_ibfk_1` FOREIGN KEY (`id_penjualan`) REFERENCES `tbl_penjualan` (`id_penjualan`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


isi databasenya sekarang kek gini, dan saya ingin melakukan pengujian, itu gimana modelnya
</USER_REQUEST>
<ADDITIONAL_METADATA>
The current local time is: 2026-09-28T04:01:06+08:00.
</ADDITIONAL_METADATA><USER_REQUEST>
ok saatnya kita testing dengan menggunakan data ini

-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Waktu pembuatan: 29 Sep 2026 pada 00.24
-- Versi server: 10.11.10-MariaDB-log
-- Versi PHP: 8.3.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Basis data: `alan`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_barang`
--

CREATE TABLE `tbl_barang` (
  `id_barang` int(11) NOT NULL,
  `kode_barang` varchar(20) NOT NULL,
  `nama_produk` varchar(100) NOT NULL,
  `kategori` varchar(50) NOT NULL,
  `harga_beli` int(11) NOT NULL DEFAULT 0,
  `harga_jual` int(11) NOT NULL DEFAULT 0,
  `stok_min` int(11) NOT NULL,
  `satuan` varchar(20) DEFAULT 'Pcs',
  `deskripsi` text DEFAULT NULL,
  `stok_aktual` int(11) NOT NULL,
  `foto_barang` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `tbl_barang`
--

INSERT INTO `tbl_barang` (`id_barang`, `kode_barang`, `nama_produk`, `kategori`, `harga_beli`, `harga_jual`, `stok_min`, `satuan`, `deskripsi`, `stok_aktual`, `foto_barang`) VALUES
(9, 'MYBSC01', 'MYBASIC TSHIRT CUTTINGAN BOXY', 'pakaian', 100, 130, 0, 'Pcs', '', 5, '1790208908_59280.png'),
(10, 'RNSL01', 'RANSEL CURDUROY (MAROON)', 'RANSEL', 110, 150, 1, 'Pcs', 'Warna: MAROON', 2, '1790209604_58336.jpg'),
(11, 'RNSL02', 'RANSEL CURDUROY (BLCK)', 'RANSEL', 110, 130, 1, 'Pcs', 'Warna: BLCK', 5, '1790209686_58350.jpg'),
(12, 'RNSL03', 'RANSEL CURDUROY (ARMY)', 'RANSEL', 110, 130, 1, 'Pcs', 'Warna: ARMY', 5, '1790209741_58340.jpg'),
(13, 'RNSL04', 'RANSEL CURDUROY (NAVY)', 'RANSEL', 110, 130, 1, 'Pcs', '', 5, '1790209792_58346.jpg'),
(14, 'RNSL05', 'RANSEL CURDUROY (ABU)', 'RANSEL', 110, 130, 1, 'Pcs', 'Warna: ABU', 5, '1790209853_58352.jpg'),
(15, 'RNSL06', 'RANSEL OURIST (NAVY)', 'RANSEL', 130000, 150000, 1, 'Pcs', '', 2, '1790211188_36484.jpg'),
(16, 'SLMPNG01', 'TAS SELEMPANG CURDUROY SMALL (KECIL)', 'TAS SELEMPANG', 60000, 100000, 2, 'Pcs', 'Warna: BLCK', 9, '1790211340_59022.png'),
(17, 'SLMPNG02', 'TAS SELEMPANG CURDUROY SMALL (KECIL)', 'TAS SELEMPANG', 60000, 100000, 5, 'Pcs', 'Warna: ABU', 9, '1790211416_59022.png'),
(18, 'HLM01', 'HELM CLASSIC', 'HELM', 95000, 130000, 8, 'Pcs', '', 5, '1790211497_59327.jpg');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_detail_penjualan`
--

CREATE TABLE `tbl_detail_penjualan` (
  `id_detail` int(11) NOT NULL,
  `id_penjualan` int(11) NOT NULL,
  `kode_barang` varchar(20) NOT NULL,
  `harga_satuan` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `subtotal` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_kategori`
--

CREATE TABLE `tbl_kategori` (
  `id_kategori` int(11) NOT NULL,
  `nama_kategori` varchar(100) NOT NULL,
  `foto_kategori` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `tbl_kategori`
--

INSERT INTO `tbl_kategori` (`id_kategori`, `nama_kategori`, `foto_kategori`) VALUES
(6, 'ATASAN', '1790208985_55609.jpg'),
(7, 'SHORT PANTS', '1790209174_59011.png'),
(8, 'TAS SELEMPANG', '1790209272_59022.png'),
(9, 'SENDAL', '1790209246_40961.jpg'),
(10, 'HELM', '1790209107_59327.jpg'),
(11, 'RANSEL', '1790209322_58358.jpg'),
(12, 'PARFUM', '1790209406_40103.jpg');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_penjualan`
--

CREATE TABLE `tbl_penjualan` (
  `id_penjualan` int(11) NOT NULL,
  `no_faktur` varchar(30) NOT NULL,
  `tanggal_waktu` datetime NOT NULL,
  `total_item` int(11) NOT NULL,
  `grand_total` int(11) NOT NULL,
  `nominal_bayar` int(11) NOT NULL,
  `kembalian` int(11) NOT NULL,
  `metode_pembayaran` varchar(50) DEFAULT 'Cash'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `tbl_penjualan`
--

INSERT INTO `tbl_penjualan` (`id_penjualan`, `no_faktur`, `tanggal_waktu`, `total_item`, `grand_total`, `nominal_bayar`, `kembalian`, `metode_pembayaran`) VALUES
(29, 'INV-202609-0422', '2026-09-03 11:45:40', 1, 150000, 150000, 0, 'Cash'),
(30, 'INV-202609-6974', '2026-09-03 17:49:16', 1, 180000, 180000, 0, 'Cash'),
(31, 'INV-202609-7224', '2026-09-03 17:50:17', 1, 150000, 150000, 0, 'Cash'),
(32, 'INV-202609-0106', '2026-09-03 17:52:30', 3, 480000, 480000, 0, 'Cash'),
(33, 'INV-202609-6205', '2026-09-03 17:55:46', 3, 370000, 370000, 0, 'Cash'),
(34, 'INV-202609-9239', '2026-09-03 17:56:19', 2, 240000, 240000, 0, 'Cash'),
(35, 'INV-202609-6463', '2026-09-03 17:56:46', 1, 100000, 100000, 0, 'Cash'),
(36, 'INV-202609-5682', '2026-09-03 17:56:55', 1, 100000, 100000, 0, 'Cash'),
(37, 'INV-202609-0215', '2026-09-04 11:20:50', 1, 150000, 150000, 0, 'Transfer'),
(38, 'INV-202609-6484', '2026-09-05 13:20:16', 3, 480000, 480000, 0, 'Cash'),
(39, 'INV-202609-0222', '2026-09-05 13:26:20', 2, 300000, 300000, 0, 'Cash'),
(40, 'INV-202609-4570', '2026-09-05 13:30:44', 1, 170000, 170000, 0, 'Cash'),
(41, 'INV-202609-3720', '2026-09-05 13:32:53', 1, 150000, 150000, 0, 'Cash'),
(42, 'INV-202609-7318', '2026-09-05 14:06:07', 4, 520000, 520000, 0, 'Cash'),
(43, 'INV-202609-2883', '2026-09-05 14:06:52', 1, 170000, 170000, 0, 'Cash'),
(44, 'INV-202609-4680', '2026-09-06 08:56:44', 3, 420000, 420000, 0, 'Cash'),
(45, 'INV-202609-1121', '2026-09-06 09:58:41', 3, 330000, 330000, 0, 'Cash'),
(46, 'INV-202609-8389', '2026-09-06 10:20:48', 1, 150000, 150000, 0, 'Cash'),
(47, 'INV-202609-1059', '2026-09-06 13:34:21', 1, 150000, 150000, 0, 'Cash'),
(48, 'INV-202609-3371', '2026-09-06 13:40:03', 2, 340000, 340000, 0, 'Cash'),
(49, 'INV-202609-0256', '2026-09-06 13:43:20', 1, 150000, 150000, 0, 'Cash'),
(50, 'INV-202609-2469', '2026-09-07 15:55:12', 5, 585000, 585000, 0, 'Cash'),
(51, 'INV-202609-1725', '2026-09-07 15:56:01', 2, 260000, 260000, 0, 'Cash'),
(52, 'INV-202609-1794', '2026-09-07 15:56:21', 2, 320000, 320000, 0, 'Cash'),
(53, 'INV-202609-8887', '2026-09-09 15:11:38', 1, 80000, 80000, 0, 'Cash'),
(54, 'INV-202609-3115', '2026-09-09 15:20:43', 2, 320000, 320000, 0, 'Cash'),
(55, 'INV-202609-8750', '2026-09-09 15:21:28', 1, 170000, 170000, 0, 'Cash'),
(56, 'INV-202609-9613', '2026-09-09 15:23:09', 1, 150000, 150000, 0, 'Cash'),
(57, 'INV-202609-0243', '2026-09-10 13:12:20', 2, 300000, 300000, 0, 'Cash'),
(58, 'INV-202609-2131', '2026-09-10 13:13:02', 1, 150000, 150000, 0, 'Cash'),
(59, 'INV-202609-0724', '2026-09-10 13:14:10', 1, 170000, 170000, 0, 'Cash'),
(60, 'INV-202609-7755', '2026-09-10 20:28:07', 1, 150000, 150000, 0, 'Cash'),
(61, 'INV-202609-6739', '2026-09-10 20:28:56', 1, 70000, 70000, 0, 'Cash'),
(62, 'INV-202609-3600', '2026-09-10 20:30:03', 1, 120000, 120000, 0, 'Cash'),
(63, 'INV-202609-4981', '2026-09-10 20:30:24', 1, 80000, 80000, 0, 'Cash'),
(64, 'INV-202609-9476', '2026-09-10 20:32:49', 3, 400000, 400000, 0, 'Cash'),
(65, 'INV-202609-1790', '2026-09-10 20:33:41', 3, 390000, 390000, 0, 'Cash'),
(66, 'INV-202609-1954', '2026-09-10 20:37:31', 4, 670000, 670000, 0, 'Cash'),
(67, 'INV-202609-1617', '2026-09-10 20:44:21', 1, 50000, 50000, 0, 'Cash'),
(68, 'INV-202609-6799', '2026-09-12 13:27:16', 1, 130000, 130000, 0, 'Cash'),
(69, 'INV-202609-3768', '2026-09-12 13:28:13', 2, 330000, 330000, 0, 'Cash'),
(70, 'INV-202609-8111', '2026-09-12 13:29:08', 1, 160000, 160000, 0, 'Cash'),
(71, 'INV-202609-4820', '2026-09-12 13:32:04', 1, 170000, 170000, 0, 'Cash'),
(72, 'INV-202609-2079', '2026-09-12 13:33:22', 1, 110000, 110000, 0, 'Cash'),
(73, 'INV-202609-1370', '2026-09-12 13:34:11', 1, 150000, 150000, 0, 'Cash'),
(74, 'INV-202609-4161', '2026-09-12 13:37:04', 1, 210000, 210000, 0, 'Cash'),
(75, 'INV-202609-2792', '2026-09-12 13:45:32', 2, 300000, 300000, 0, 'Cash'),
(76, 'INV-202609-5341', '2026-09-12 13:45:45', 1, 100000, 100000, 0, 'Cash'),
(77, 'INV-202609-5484', '2026-09-12 13:45:55', 1, 150000, 150000, 0, 'Cash'),
(78, 'INV-202609-0977', '2026-09-12 13:46:20', 3, 380000, 380000, 0, 'Cash'),
(79, 'INV-202609-5374', '2026-09-13 13:57:45', 1, 100000, 100000, 0, 'Cash'),
(80, 'INV-202609-8854', '2026-09-13 14:00:08', 1, 250000, 250000, 0, 'Cash'),
(81, 'INV-202609-7153', '2026-09-13 14:20:17', 4, 560000, 560000, 0, 'Cash'),
(82, 'INV-202609-0152', '2026-09-13 14:21:40', 1, 180000, 180000, 0, 'Cash'),
(83, 'INV-202609-4687', '2026-09-13 14:21:54', 1, 150000, 150000, 0, 'Cash'),
(84, 'INV-202609-9803', '2026-09-13 14:22:09', 1, 110000, 110000, 0, 'Cash'),
(85, 'INV-202609-9034', '2026-09-13 14:22:29', 1, 30000, 30000, 0, 'Cash'),
(86, 'INV-202609-8064', '2026-09-13 14:22:38', 1, 110000, 110000, 0, 'Cash'),
(87, 'INV-202609-9548', '2026-09-15 11:06:09', 1, 150000, 150000, 0, 'Cash'),
(88, 'INV-202609-4200', '2026-09-15 11:06:24', 1, 130000, 130000, 0, 'Cash'),
(89, 'INV-202609-1864', '2026-09-15 11:08:31', 2, 160000, 160000, 0, 'Cash'),
(90, 'INV-202609-3110', '2026-09-15 11:08:53', 1, 100000, 100000, 0, 'Cash'),
(91, 'INV-202609-2986', '2026-09-15 11:09:22', 1, 80000, 80000, 0, 'Cash'),
(92, 'INV-202609-6686', '2026-09-15 11:09:46', 1, 150000, 150000, 0, 'Cash'),
(93, 'INV-202609-7957', '2026-09-15 11:10:57', 1, 40000, 40000, 0, 'Cash'),
(94, 'INV-202609-0220', '2026-09-15 11:12:50', 2, 210000, 210000, 0, 'Cash'),
(95, 'INV-202609-2976', '2026-09-15 12:54:22', 1, 150000, 150000, 0, 'Cash'),
(96, 'INV-202609-3251', '2026-09-15 12:55:03', 1, 180000, 180000, 0, 'Cash'),
(97, 'INV-202609-0652', '2026-09-15 12:55:30', 1, 30000, 30000, 0, 'Cash'),
(98, 'INV-202609-9608', '2026-09-15 13:17:49', 2, 60000, 60000, 0, 'Cash'),
(99, 'INV-202609-0611', '2026-09-17 16:46:10', 1, 150000, 150000, 0, 'Cash'),
(100, 'INV-202609-4119', '2026-09-17 16:54:34', 2, 255000, 255000, 0, 'Cash'),
(101, 'INV-202609-1065', '2026-09-17 16:55:01', 1, 170000, 170000, 0, 'Cash'),
(102, 'INV-202609-8988', '2026-09-17 16:55:48', 1, 150000, 150000, 0, 'Cash'),
(103, 'INV-202609-4213', '2026-09-17 17:00:44', 1, 180000, 180000, 0, 'Cash'),
(104, 'INV-202609-7100', '2026-09-17 17:02:07', 1, 150000, 150000, 0, 'Cash'),
(105, 'INV-202609-8588', '2026-09-18 06:28:18', 2, 80000, 80000, 0, 'Cash'),
(106, 'INV-202609-9955', '2026-09-18 06:28:59', 2, 80000, 80000, 0, 'Cash'),
(107, 'INV-202609-0653', '2026-09-18 06:43:50', 1, 150000, 150000, 0, 'Cash'),
(108, 'INV-202609-3146', '2026-09-18 06:44:13', 1, 150000, 150000, 0, 'Cash'),
(109, 'INV-202609-2900', '2026-09-18 14:12:52', 2, 200000, 200000, 0, 'Cash'),
(110, 'INV-202609-5880', '2026-09-18 14:13:45', 1, 100000, 100000, 0, 'Cash'),
(111, 'INV-202609-8685', '2026-09-18 14:23:48', 1, 100000, 100000, 0, 'Cash'),
(112, 'INV-202609-4537', '2026-09-18 14:24:04', 1, 120000, 120000, 0, 'Cash'),
(113, 'INV-202609-2660', '2026-09-18 14:24:32', 1, 30000, 30000, 0, 'Cash'),
(114, 'INV-202609-0692', '2026-09-18 14:29:40', 1, 180000, 180000, 0, 'Cash'),
(115, 'INV-202609-7021', '2026-09-19 05:21:27', 3, 260000, 260000, 0, 'Cash'),
(116, 'INV-202609-7709', '2026-09-19 05:21:47', 1, 130000, 130000, 0, 'Cash'),
(117, 'INV-202609-6481', '2026-09-19 05:22:16', 1, 150000, 150000, 0, 'Cash'),
(118, 'INV-202609-1780', '2026-09-19 05:25:31', 2, 250000, 250000, 0, 'Cash'),
(119, 'INV-202609-8877', '2026-09-19 05:30:28', 1, 150000, 150000, 0, 'Cash'),
(120, 'INV-202609-0136', '2026-09-19 05:31:50', 2, 230000, 230000, 0, 'Cash'),
(121, 'INV-202609-5392', '2026-09-19 05:32:15', 1, 100000, 100000, 0, 'Cash'),
(122, 'INV-202609-5029', '2026-09-19 05:32:35', 1, 100000, 100000, 0, 'Cash'),
(123, 'INV-202609-8258', '2026-09-19 05:33:18', 2, 230000, 230000, 0, 'Cash'),
(124, 'INV-202609-6206', '2026-09-19 05:35:06', 1, 130000, 130000, 0, 'Cash'),
(125, 'INV-202609-7993', '2026-09-19 05:36:17', 2, 150000, 150000, 0, 'Cash'),
(126, 'INV-202609-7110', '2026-09-19 05:37:57', 1, 200000, 200000, 0, 'Cash'),
(127, 'INV-202609-9836', '2026-09-19 05:38:29', 1, 25000, 25000, 0, 'Cash'),
(128, 'INV-202609-9889', '2026-09-19 05:40:39', 1, 40000, 40000, 0, 'Cash'),
(129, 'INV-202609-8342', '2026-09-19 17:18:38', 1, 130000, 130000, 0, 'Cash'),
(130, 'INV-202609-1661', '2026-09-19 17:19:41', 1, 130000, 130000, 0, 'Cash'),
(131, 'INV-202609-8725', '2026-09-19 17:20:18', 1, 30000, 30000, 0, 'Cash'),
(132, 'INV-202609-3305', '2026-09-19 17:46:03', 3, 320000, 320000, 0, 'Cash'),
(133, 'INV-202609-4835', '2026-09-19 17:49:14', 1, 150000, 150000, 0, 'Cash'),
(134, 'INV-202609-5877', '2026-09-19 17:50:35', 1, 40000, 40000, 0, 'Cash'),
(135, 'INV-202609-5756', '2026-09-20 14:49:25', 1, 100000, 100000, 0, 'Cash'),
(136, 'INV-202609-0993', '2026-09-20 14:49:50', 1, 180000, 180000, 0, 'Cash'),
(137, 'INV-202609-6459', '2026-09-20 14:50:26', 1, 150000, 150000, 0, 'Cash'),
(138, 'INV-202609-8128', '2026-09-20 14:51:48', 3, 480000, 480000, 0, 'Cash'),
(139, 'INV-202609-1503', '2026-09-20 14:52:31', 2, 300000, 300000, 0, 'Cash'),
(140, 'INV-202609-4962', '2026-09-20 14:54:04', 1, 130000, 130000, 0, 'Cash'),
(141, 'INV-202609-3646', '2026-09-24 13:11:33', 1, 80000, 80000, 0, 'Cash'),
(142, 'INV-202609-5789', '2026-09-24 13:12:05', 1, 170000, 170000, 0, 'Cash'),
(143, 'INV-202609-4656', '2026-09-24 13:53:54', 5, 530000, 530000, 0, 'Cash'),
(144, 'INV-202609-4348', '2026-09-25 14:33:04', 4, 590000, 590000, 0, 'Cash'),
(145, 'INV-202609-6960', '2026-09-27 16:18:06', 1, 70000, 70000, 0, 'Cash'),
(146, 'INV-202609-9856', '2026-09-27 16:18:49', 1, 170000, 170000, 0, 'Cash'),
(147, 'INV-202609-6225', '2026-09-27 16:19:36', 1, 150000, 150000, 0, 'Cash'),
(148, 'INV-202609-D001', '2026-09-02 10:00:00', 1, 70000, 70000, 0, 'Cash'),
(149, 'INV-202609-D002', '2026-09-02 10:05:00', 1, 130000, 130000, 0, 'Cash'),
(150, 'INV-202609-D003', '2026-09-02 10:10:00', 1, 180000, 180000, 0, 'Cash'),
(151, 'INV-202609-D004', '2026-09-02 10:15:00', 1, 130000, 130000, 0, 'Cash'),
(152, 'INV-202609-D005', '2026-09-02 10:20:00', 1, 160000, 160000, 0, 'Cash'),
(153, 'INV-202609-5672', '2026-09-28 01:03:18', 2, 260000, 300000, 40000, 'Cash');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_users`
--

CREATE TABLE `tbl_users` (
  `id_user` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `role` varchar(50) DEFAULT 'Admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `tbl_users`
--

INSERT INTO `tbl_users` (`id_user`, `username`, `password`, `nama_lengkap`, `role`) VALUES
(1, 'admin', '$2a$12$KR0zg/u/KHcnP7/aDptn3uq9tzFyj2FNOwa0Cm4sNeJHXDnF8wVsm', 'ALAM', 'Pimpinan'),
(2, 'kasir1', '$2a$12$KR0zg/u/KHcnP7/aDptn3uq9tzFyj2FNOwa0Cm4sNeJHXDnF8wVsm', 'anca', 'Kasir'),
(3, 'kasir2', '$2y$10$GB0AQ8sXfOFe7nWiKTqz5.3QL5L1NDn2myMOOwAljIglw2gYmY0eS', 'ansar', 'Kasir');

--
-- Indeks untuk tabel yang dibuang
--

--
-- Indeks untuk tabel `tbl_barang`
--
ALTER TABLE `tbl_barang`
  ADD PRIMARY KEY (`id_barang`),
  ADD UNIQUE KEY `kode_barang` (`kode_barang`);

--
-- Indeks untuk tabel `tbl_detail_penjualan`
--
ALTER TABLE `tbl_detail_penjualan`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_penjualan` (`id_penjualan`);

--
-- Indeks untuk tabel `tbl_kategori`
--
ALTER TABLE `tbl_kategori`
  ADD PRIMARY KEY (`id_kategori`),
  ADD UNIQUE KEY `nama_kategori` (`nama_kategori`);

--
-- Indeks untuk tabel `tbl_penjualan`
--
ALTER TABLE `tbl_penjualan`
  ADD PRIMARY KEY (`id_penjualan`),
  ADD UNIQUE KEY `no_faktur` (`no_faktur`);

--
-- Indeks untuk tabel `tbl_users`
--
ALTER TABLE `tbl_users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `tbl_barang`
--
ALTER TABLE `tbl_barang`
  MODIFY `id_barang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT untuk tabel `tbl_detail_penjualan`
--
ALTER TABLE `tbl_detail_penjualan`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT untuk tabel `tbl_kategori`
--
ALTER TABLE `tbl_kategori`
  MODIFY `id_kategori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `tbl_penjualan`
--
ALTER TABLE `tbl_penjualan`
  MODIFY `id_penjualan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=313;

--
-- AUTO_INCREMENT untuk tabel `tbl_users`
--
ALTER TABLE `tbl_users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `tbl_detail_penjualan`
--
ALTER TABLE `tbl_detail_penjualan`
  ADD CONSTRAINT `tbl_detail_penjualan_ibfk_1` FOREIGN KEY (`id_penjualan`) REFERENCES `tbl_penjualan` (`id_penjualan`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

</USER_REQUEST>
<ADDITIONAL_METADATA>
The current local time is: 2026-09-29T00:24:59+08:00.
</ADDITIONAL_METADATA>Wah, sepertinya ada bagian yang terpotong saat kamu menyalin (*copy-paste*) datanya ke sini! 

Jika kamu perhatikan di skrip SQL yang kamu kirim, struktur tabel `tbl_detail_penjualan` memang dibuat, tapi **tidak ada isinya (tidak ada perintah `INSERT INTO tbl_detail_penjualan`)**. Padahal, tabel itulah yang menyimpan informasi tentang *barang apa saja yang dibeli beserta jumlahnya (qty)*. Tanpa data di tabel detail tersebut, sistem peramalan akan menganggap tidak ada barang yang pernah terjual (qty = 0) meskipun ada ratusan faktur di `tbl_penjualan`.

**Apa yang sebaiknya kita lakukan untuk testing?**

**Opsi 1:** Kamu bisa *Export* ulang seluruh databasemu dari phpMyAdmin (pastikan tabel `tbl_detail_penjualan` juga tercentang dan diexport datanya), lalu lampirkan file `.sql`-nya ke saya *(drag and drop file-nya)*, nanti akan saya jalankan/inject ke sistem.

**Opsi 2:** Kalau kamu butuh data untuk dipresentasikan ke klien (biar kelihatan banyak transaksinya), **saya bisa buatkan dan suntikkan otomatis** kumpulan data penjualan *dummy* secara acak namun terlihat natural selama 2-3 bulan terakhir untuk semua barang yang ada di tokomu. Jadi kamu dan klien bisa langsung mengetes "Peramalan Harian" maupun "Peramalan Mingguan" tanpa repot.

Kamu mau pilih Opsi 1 (kirim file SQL utuh) atau Opsi 2 (saya buatkan data otomatis)?<USER_REQUEST>
-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Waktu pembuatan: 29 Sep 2026 pada 00.26
-- Versi server: 10.11.10-MariaDB-log
-- Versi PHP: 8.3.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Basis data: `alan`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_barang`
--

CREATE TABLE `tbl_barang` (
  `id_barang` int(11) NOT NULL,
  `kode_barang` varchar(20) NOT NULL,
  `nama_produk` varchar(100) NOT NULL,
  `kategori` varchar(50) NOT NULL,
  `harga_beli` int(11) NOT NULL DEFAULT 0,
  `harga_jual` int(11) NOT NULL DEFAULT 0,
  `stok_min` int(11) NOT NULL,
  `satuan` varchar(20) DEFAULT 'Pcs',
  `deskripsi` text DEFAULT NULL,
  `stok_aktual` int(11) NOT NULL,
  `foto_barang` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `tbl_barang`
--

INSERT INTO `tbl_barang` (`id_barang`, `kode_barang`, `nama_produk`, `kategori`, `harga_beli`, `harga_jual`, `stok_min`, `satuan`, `deskripsi`, `stok_aktual`, `foto_barang`) VALUES
(9, 'MYBSC01', 'MYBASIC TSHIRT CUTTINGAN BOXY', 'pakaian', 100, 130, 0, 'Pcs', '', 5, '1790208908_59280.png'),
(10, 'RNSL01', 'RANSEL CURDUROY (MAROON)', 'RANSEL', 110, 150, 1, 'Pcs', 'Warna: MAROON', 2, '1790209604_58336.jpg'),
(11, 'RNSL02', 'RANSEL CURDUROY (BLCK)', 'RANSEL', 110, 130, 1, 'Pcs', 'Warna: BLCK', 5, '1790209686_58350.jpg'),
(12, 'RNSL03', 'RANSEL CURDUROY (ARMY)', 'RANSEL', 110, 130, 1, 'Pcs', 'Warna: ARMY', 5, '1790209741_58340.jpg'),
(13, 'RNSL04', 'RANSEL CURDUROY (NAVY)', 'RANSEL', 110, 130, 1, 'Pcs', '', 5, '1790209792_58346.jpg'),
(14, 'RNSL05', 'RANSEL CURDUROY (ABU)', 'RANSEL', 110, 130, 1, 'Pcs', 'Warna: ABU', 5, '1790209853_58352.jpg'),
(15, 'RNSL06', 'RANSEL OURIST (NAVY)', 'RANSEL', 130000, 150000, 1, 'Pcs', '', 2, '1790211188_36484.jpg'),
(16, 'SLMPNG01', 'TAS SELEMPANG CURDUROY SMALL (KECIL)', 'TAS SELEMPANG', 60000, 100000, 2, 'Pcs', 'Warna: BLCK', 9, '1790211340_59022.png'),
(17, 'SLMPNG02', 'TAS SELEMPANG CURDUROY SMALL (KECIL)', 'TAS SELEMPANG', 60000, 100000, 5, 'Pcs', 'Warna: ABU', 9, '1790211416_59022.png'),
(18, 'HLM01', 'HELM CLASSIC', 'HELM', 95000, 130000, 8, 'Pcs', '', 5, '1790211497_59327.jpg');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_detail_penjualan`
--

CREATE TABLE `tbl_detail_penjualan` (
  `id_detail` int(11) NOT NULL,
  `id_penjualan` int(11) NOT NULL,
  `kode_barang` varchar(20) NOT NULL,
  `harga_satuan` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `subtotal` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_kategori`
--

CREATE TABLE `tbl_kategori` (
  `id_kategori` int(11) NOT NULL,
  `nama_kategori` varchar(100) NOT NULL,
  `foto_kategori` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `tbl_kategori`
--

INSERT INTO `tbl_kategori` (`id_kategori`, `nama_kategori`, `foto_kategori`) VALUES
(6, 'ATASAN', '1790208985_55609.jpg'),
(7, 'SHORT PANTS', '1790209174_59011.png'),
(8, 'TAS SELEMPANG', '1790209272_59022.png'),
(9, 'SENDAL', '1790209246_40961.jpg'),
(10, 'HELM', '1790209107_59327.jpg'),
(11, 'RANSEL', '1790209322_58358.jpg'),
(12, 'PARFUM', '1790209406_40103.jpg');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_penjualan`
--

CREATE TABLE `tbl_penjualan` (
  `id_penjualan` int(11) NOT NULL,
  `no_faktur` varchar(30) NOT NULL,
  `tanggal_waktu` datetime NOT NULL,
  `total_item` int(11) NOT NULL,
  `grand_total` int(11) NOT NULL,
  `nominal_bayar` int(11) NOT NULL,
  `kembalian` int(11) NOT NULL,
  `metode_pembayaran` varchar(50) DEFAULT 'Cash'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `tbl_penjualan`
--

INSERT INTO `tbl_penjualan` (`id_penjualan`, `no_faktur`, `tanggal_waktu`, `total_item`, `grand_total`, `nominal_bayar`, `kembalian`, `metode_pembayaran`) VALUES
(29, 'INV-202609-0422', '2026-09-03 11:45:40', 1, 150000, 150000, 0, 'Cash'),
(30, 'INV-202609-6974', '2026-09-03 17:49:16', 1, 180000, 180000, 0, 'Cash'),
(31, 'INV-202609-7224', '2026-09-03 17:50:17', 1, 150000, 150000, 0, 'Cash'),
(32, 'INV-202609-0106', '2026-09-03 17:52:30', 3, 480000, 480000, 0, 'Cash'),
(33, 'INV-202609-6205', '2026-09-03 17:55:46', 3, 370000, 370000, 0, 'Cash'),
(34, 'INV-202609-9239', '2026-09-03 17:56:19', 2, 240000, 240000, 0, 'Cash'),
(35, 'INV-202609-6463', '2026-09-03 17:56:46', 1, 100000, 100000, 0, 'Cash'),
(36, 'INV-202609-5682', '2026-09-03 17:56:55', 1, 100000, 100000, 0, 'Cash'),
(37, 'INV-202609-0215', '2026-09-04 11:20:50', 1, 150000, 150000, 0, 'Transfer'),
(38, 'INV-202609-6484', '2026-09-05 13:20:16', 3, 480000, 480000, 0, 'Cash'),
(39, 'INV-202609-0222', '2026-09-05 13:26:20', 2, 300000, 300000, 0, 'Cash'),
(40, 'INV-202609-4570', '2026-09-05 13:30:44', 1, 170000, 170000, 0, 'Cash'),
(41, 'INV-202609-3720', '2026-09-05 13:32:53', 1, 150000, 150000, 0, 'Cash'),
(42, 'INV-202609-7318', '2026-09-05 14:06:07', 4, 520000, 520000, 0, 'Cash'),
(43, 'INV-202609-2883', '2026-09-05 14:06:52', 1, 170000, 170000, 0, 'Cash'),
(44, 'INV-202609-4680', '2026-09-06 08:56:44', 3, 420000, 420000, 0, 'Cash'),
(45, 'INV-202609-1121', '2026-09-06 09:58:41', 3, 330000, 330000, 0, 'Cash'),
(46, 'INV-202609-8389', '2026-09-06 10:20:48', 1, 150000, 150000, 0, 'Cash'),
(47, 'INV-202609-1059', '2026-09-06 13:34:21', 1, 150000, 150000, 0, 'Cash'),
(48, 'INV-202609-3371', '2026-09-06 13:40:03', 2, 340000, 340000, 0, 'Cash'),
(49, 'INV-202609-0256', '2026-09-06 13:43:20', 1, 150000, 150000, 0, 'Cash'),
(50, 'INV-202609-2469', '2026-09-07 15:55:12', 5, 585000, 585000, 0, 'Cash'),
(51, 'INV-202609-1725', '2026-09-07 15:56:01', 2, 260000, 260000, 0, 'Cash'),
(52, 'INV-202609-1794', '2026-09-07 15:56:21', 2, 320000, 320000, 0, 'Cash'),
(53, 'INV-202609-8887', '2026-09-09 15:11:38', 1, 80000, 80000, 0, 'Cash'),
(54, 'INV-202609-3115', '2026-09-09 15:20:43', 2, 320000, 320000, 0, 'Cash'),
(55, 'INV-202609-8750', '2026-09-09 15:21:28', 1, 170000, 170000, 0, 'Cash'),
(56, 'INV-202609-9613', '2026-09-09 15:23:09', 1, 150000, 150000, 0, 'Cash'),
(57, 'INV-202609-0243', '2026-09-10 13:12:20', 2, 300000, 300000, 0, 'Cash'),
(58, 'INV-202609-2131', '2026-09-10 13:13:02', 1, 150000, 150000, 0, 'Cash'),
(59, 'INV-202609-0724', '2026-09-10 13:14:10', 1, 170000, 170000, 0, 'Cash'),
(60, 'INV-202609-7755', '2026-09-10 20:28:07', 1, 150000, 150000, 0, 'Cash'),
(61, 'INV-202609-6739', '2026-09-10 20:28:56', 1, 70000, 70000, 0, 'Cash'),
(62, 'INV-202609-3600', '2026-09-10 20:30:03', 1, 120000, 120000, 0, 'Cash'),
(63, 'INV-202609-4981', '2026-09-10 20:30:24', 1, 80000, 80000, 0, 'Cash'),
(64, 'INV-202609-9476', '2026-09-10 20:32:49', 3, 400000, 400000, 0, 'Cash'),
(65, 'INV-202609-1790', '2026-09-10 20:33:41', 3, 390000, 390000, 0, 'Cash'),
(66, 'INV-202609-1954', '2026-09-10 20:37:31', 4, 670000, 670000, 0, 'Cash'),
(67, 'INV-202609-1617', '2026-09-10 20:44:21', 1, 50000, 50000, 0, 'Cash'),
(68, 'INV-202609-6799', '2026-09-12 13:27:16', 1, 130000, 130000, 0, 'Cash'),
(69, 'INV-202609-3768', '2026-09-12 13:28:13', 2, 330000, 330000, 0, 'Cash'),
(70, 'INV-202609-8111', '2026-09-12 13:29:08', 1, 160000, 160000, 0, 'Cash'),
(71, 'INV-202609-4820', '2026-09-12 13:32:04', 1, 170000, 170000, 0, 'Cash'),
(72, 'INV-202609-2079', '2026-09-12 13:33:22', 1, 110000, 110000, 0, 'Cash'),
(73, 'INV-202609-1370', '2026-09-12 13:34:11', 1, 150000, 150000, 0, 'Cash'),
(74, 'INV-202609-4161', '2026-09-12 13:37:04', 1, 210000, 210000, 0, 'Cash'),
(75, 'INV-202609-2792', '2026-09-12 13:45:32', 2, 300000, 300000, 0, 'Cash'),
(76, 'INV-202609-5341', '2026-09-12 13:45:45', 1, 100000, 100000, 0, 'Cash'),
(77, 'INV-202609-5484', '2026-09-12 13:45:55', 1, 150000, 150000, 0, 'Cash'),
(78, 'INV-202609-0977', '2026-09-12 13:46:20', 3, 380000, 380000, 0, 'Cash'),
(79, 'INV-202609-5374', '2026-09-13 13:57:45', 1, 100000, 100000, 0, 'Cash'),
(80, 'INV-202609-8854', '2026-09-13 14:00:08', 1, 250000, 250000, 0, 'Cash'),
(81, 'INV-202609-7153', '2026-09-13 14:20:17', 4, 560000, 560000, 0, 'Cash'),
(82, 'INV-202609-0152', '2026-09-13 14:21:40', 1, 180000, 180000, 0, 'Cash'),
(83, 'INV-202609-4687', '2026-09-13 14:21:54', 1, 150000, 150000, 0, 'Cash'),
(84, 'INV-202609-9803', '2026-09-13 14:22:09', 1, 110000, 110000, 0, 'Cash'),
(85, 'INV-202609-9034', '2026-09-13 14:22:29', 1, 30000, 30000, 0, 'Cash'),
(86, 'INV-202609-8064', '2026-09-13 14:22:38', 1, 110000, 110000, 0, 'Cash'),
(87, 'INV-202609-9548', '2026-09-15 11:06:09', 1, 150000, 150000, 0, 'Cash'),
(88, 'INV-202609-4200', '2026-09-15 11:06:24', 1, 130000, 130000, 0, 'Cash'),
(89, 'INV-202609-1864', '2026-09-15 11:08:31', 2, 160000, 160000, 0, 'Cash'),
(90, 'INV-202609-3110', '2026-09-15 11:08:53', 1, 100000, 100000, 0, 'Cash'),
(91, 'INV-202609-2986', '2026-09-15 11:09:22', 1, 80000, 80000, 0, 'Cash'),
(92, 'INV-202609-6686', '2026-09-15 11:09:46', 1, 150000, 150000, 0, 'Cash'),
(93, 'INV-202609-7957', '2026-09-15 11:10:57', 1, 40000, 40000, 0, 'Cash'),
(94, 'INV-202609-0220', '2026-09-15 11:12:50', 2, 210000, 210000, 0, 'Cash'),
(95, 'INV-202609-2976', '2026-09-15 12:54:22', 1, 150000, 150000, 0, 'Cash'),
(96, 'INV-202609-3251', '2026-09-15 12:55:03', 1, 180000, 180000, 0, 'Cash'),
(97, 'INV-202609-0652', '2026-09-15 12:55:30', 1, 30000, 30000, 0, 'Cash'),
(98, 'INV-202609-9608', '2026-09-15 13:17:49', 2, 60000, 60000, 0, 'Cash'),
(99, 'INV-202609-0611', '2026-09-17 16:46:10', 1, 150000, 150000, 0, 'Cash'),
(100, 'INV-202609-4119', '2026-09-17 16:54:34', 2, 255000, 255000, 0, 'Cash'),
(101, 'INV-202609-1065', '2026-09-17 16:55:01', 1, 170000, 170000, 0, 'Cash'),
(102, 'INV-202609-8988', '2026-09-17 16:55:48', 1, 150000, 150000, 0, 'Cash'),
(103, 'INV-202609-4213', '2026-09-17 17:00:44', 1, 180000, 180000, 0, 'Cash'),
(104, 'INV-202609-7100', '2026-09-17 17:02:07', 1, 150000, 150000, 0, 'Cash'),
(105, 'INV-202609-8588', '2026-09-18 06:28:18', 2, 80000, 80000, 0, 'Cash'),
(106, 'INV-202609-9955', '2026-09-18 06:28:59', 2, 80000, 80000, 0, 'Cash'),
(107, 'INV-202609-0653', '2026-09-18 06:43:50', 1, 150000, 150000, 0, 'Cash'),
(108, 'INV-202609-3146', '2026-09-18 06:44:13', 1, 150000, 150000, 0, 'Cash'),
(109, 'INV-202609-2900', '2026-09-18 14:12:52', 2, 200000, 200000, 0, 'Cash'),
(110, 'INV-202609-5880', '2026-09-18 14:13:45', 1, 100000, 100000, 0, 'Cash'),
(111, 'INV-202609-8685', '2026-09-18 14:23:48', 1, 100000, 100000, 0, 'Cash'),
(112, 'INV-202609-4537', '2026-09-18 14:24:04', 1, 120000, 120000, 0, 'Cash'),
(113, 'INV-202609-2660', '2026-09-18 14:24:32', 1, 30000, 30000, 0, 'Cash'),
(114, 'INV-202609-0692', '2026-09-18 14:29:40', 1, 180000, 180000, 0, 'Cash'),
(115, 'INV-202609-7021', '2026-09-19 05:21:27', 3, 260000, 260000, 0, 'Cash'),
(116, 'INV-202609-7709', '2026-09-19 05:21:47', 1, 130000, 130000, 0, 'Cash'),
(117, 'INV-202609-6481', '2026-09-19 05:22:16', 1, 150000, 150000, 0, 'Cash'),
(118, 'INV-202609-1780', '2026-09-19 05:25:31', 2, 250000, 250000, 0, 'Cash'),
(119, 'INV-202609-8877', '2026-09-19 05:30:28', 1, 150000, 150000, 0, 'Cash'),
(120, 'INV-202609-0136', '2026-09-19 05:31:50', 2, 230000, 230000, 0, 'Cash'),
(121, 'INV-202609-5392', '2026-09-19 05:32:15', 1, 100000, 100000, 0, 'Cash'),
(122, 'INV-202609-5029', '2026-09-19 05:32:35', 1, 100000, 100000, 0, 'Cash'),
(123, 'INV-202609-8258', '2026-09-19 05:33:18', 2, 230000, 230000, 0, 'Cash'),
(124, 'INV-202609-6206', '2026-09-19 05:35:06', 1, 130000, 130000, 0, 'Cash'),
(125, 'INV-202609-7993', '2026-09-19 05:36:17', 2, 150000, 150000, 0, 'Cash'),
(126, 'INV-202609-7110', '2026-09-19 05:37:57', 1, 200000, 200000, 0, 'Cash'),
(127, 'INV-202609-9836', '2026-09-19 05:38:29', 1, 25000, 25000, 0, 'Cash'),
(128, 'INV-202609-9889', '2026-09-19 05:40:39', 1, 40000, 40000, 0, 'Cash'),
(129, 'INV-202609-8342', '2026-09-19 17:18:38', 1, 130000, 130000, 0, 'Cash'),
(130, 'INV-202609-1661', '2026-09-19 17:19:41', 1, 130000, 130000, 0, 'Cash'),
(131, 'INV-202609-8725', '2026-09-19 17:20:18', 1, 30000, 30000, 0, 'Cash'),
(132, 'INV-202609-3305', '2026-09-19 17:46:03', 3, 320000, 320000, 0, 'Cash'),
(133, 'INV-202609-4835', '2026-09-19 17:49:14', 1, 150000, 150000, 0, 'Cash'),
(134, 'INV-202609-5877', '2026-09-19 17:50:35', 1, 40000, 40000, 0, 'Cash'),
(135, 'INV-202609-5756', '2026-09-20 14:49:25', 1, 100000, 100000, 0, 'Cash'),
(136, 'INV-202609-0993', '2026-09-20 14:49:50', 1, 180000, 180000, 0, 'Cash'),
(137, 'INV-202609-6459', '2026-09-20 14:50:26', 1, 150000, 150000, 0, 'Cash'),
(138, 'INV-202609-8128', '2026-09-20 14:51:48', 3, 480000, 480000, 0, 'Cash'),
(139, 'INV-202609-1503', '2026-09-20 14:52:31', 2, 300000, 300000, 0, 'Cash'),
(140, 'INV-202609-4962', '2026-09-20 14:54:04', 1, 130000, 130000, 0, 'Cash'),
(141, 'INV-202609-3646', '2026-09-24 13:11:33', 1, 80000, 80000, 0, 'Cash'),
(142, 'INV-202609-5789', '2026-09-24 13:12:05', 1, 170000, 170000, 0, 'Cash'),
(143, 'INV-202609-4656', '2026-09-24 13:53:54', 5, 530000, 530000, 0, 'Cash'),
(144, 'INV-202609-4348', '2026-09-25 14:33:04', 4, 590000, 590000, 0, 'Cash'),
(145, 'INV-202609-6960', '2026-09-27 16:18:06', 1, 70000, 70000, 0, 'Cash'),
(146, 'INV-202609-9856', '2026-09-27 16:18:49', 1, 170000, 170000, 0, 'Cash'),
(147, 'INV-202609-6225', '2026-09-27 16:19:36', 1, 150000, 150000, 0, 'Cash'),
(148, 'INV-202609-D001', '2026-09-02 10:00:00', 1, 70000, 70000, 0, 'Cash'),
(149, 'INV-202609-D002', '2026-09-02 10:05:00', 1, 130000, 130000, 0, 'Cash'),
(150, 'INV-202609-D003', '2026-09-02 10:10:00', 1, 180000, 180000, 0, 'Cash'),
(151, 'INV-202609-D004', '2026-09-02 10:15:00', 1, 130000, 130000, 0, 'Cash'),
(152, 'INV-202609-D005', '2026-09-02 10:20:00', 1, 160000, 160000, 0, 'Cash'),
(153, 'INV-202609-5672', '2026-09-28 01:03:18', 2, 260000, 300000, 40000, 'Cash');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_users`
--

CREATE TABLE `tbl_users` (
  `id_user` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `role` varchar(50) DEFAULT 'Admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `tbl_users`
--

INSERT INTO `tbl_users` (`id_user`, `username`, `password`, `nama_lengkap`, `role`) VALUES
(1, 'admin', '$2a$12$KR0zg/u/KHcnP7/aDptn3uq9tzFyj2FNOwa0Cm4sNeJHXDnF8wVsm', 'ALAM', 'Pimpinan'),
(2, 'kasir1', '$2a$12$KR0zg/u/KHcnP7/aDptn3uq9tzFyj2FNOwa0Cm4sNeJHXDnF8wVsm', 'anca', 'Kasir'),
(3, 'kasir2', '$2y$10$GB0AQ8sXfOFe7nWiKTqz5.3QL5L1NDn2myMOOwAljIglw2gYmY0eS', 'ansar', 'Kasir');

--
-- Indeks untuk tabel yang dibuang
--

--
-- Indeks untuk tabel `tbl_barang`
--
ALTER TABLE `tbl_barang`
  ADD PRIMARY KEY (`id_barang`),
  ADD UNIQUE KEY `kode_barang` (`kode_barang`);

--
-- Indeks untuk tabel `tbl_detail_penjualan`
--
ALTER TABLE `tbl_detail_penjualan`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_penjualan` (`id_penjualan`);

--
-- Indeks untuk tabel `tbl_kategori`
--
ALTER TABLE `tbl_kategori`
  ADD PRIMARY KEY (`id_kategori`),
  ADD UNIQUE KEY `nama_kategori` (`nama_kategori`);

--
-- Indeks untuk tabel `tbl_penjualan`
--
ALTER TABLE `tbl_penjualan`
  ADD PRIMARY KEY (`id_penjualan`),
  ADD UNIQUE KEY `no_faktur` (`no_faktur`);

--
-- Indeks untuk tabel `tbl_users`
--
ALTER TABLE `tbl_users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `tbl_barang`
--
ALTER TABLE `tbl_barang`
  MODIFY `id_barang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT untuk tabel `tbl_detail_penjualan`
--
ALTER TABLE `tbl_detail_penjualan`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT untuk tabel `tbl_kategori`
--
ALTER TABLE `tbl_kategori`
  MODIFY `id_kategori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `tbl_penjualan`
--
ALTER TABLE `tbl_penjualan`
  MODIFY `id_penjualan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=313;

--
-- AUTO_INCREMENT untuk tabel `tbl_users`
--
ALTER TABLE `tbl_users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `tbl_detail_penjualan`
--
ALTER TABLE `tbl_detail_penjualan`
  ADD CONSTRAINT `tbl_detail_penjualan_ibfk_1` FOREIGN KEY (`id_penjualan`) REFERENCES `tbl_penjualan` (`id_penjualan`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

</USER_REQUEST>
<ADDITIONAL_METADATA>
The current local time is: 2026-09-29T00:26:31+08:00.
</ADDITIONAL_METADATA>Created At: 2026-09-29T00:26:47+08:00
Tool is running as a background task with task id: 5fc65fb9-6663-462c-8da4-4eb78312f1b6/task-210
Task Description: python -c "
import random
from datetime import datetime, timedelta

products = [
    ('MYBSC01', 130000), ('RNSL01', 150000), ('RNSL02', 130000),
    ('RNSL03', 130000), ('RNSL04', 130000), ('RNSL05', 130000),
    ('RNSL06', 150000), ('SLMPNG01', 100000), ('SLMPNG02', 100000),
    ('HLM01', 130000)
]

start_date = datetime(2026, 7, 1)
end_date = datetime(2026, 9, 28)
days = (end_date - start_date).days

penjualan_queries = []
detail_queries = []

id_penjualan = 400
id_detail = 1

for day in range(days + 1):
    current_date = start_date + timedelta(days=day)
    # Generate 1 to 3 transactions per day
    num_trx = random.randint(1, 3)
    
    for _ in range(num_trx):
        hour = random.randint(9, 20)
        minute = random.randint(0, 59)
        dt = current_date.replace(hour=hour, minute=minute).strftime('%Y-%m-%d %H:%M:%S')
        
        no_faktur = f'INV-{current_date.strftime(\"%Y%m\")}-{random.randint(1000,9999)}'
        
        # Pick 1 to 3 items
        num_items = random.randint(1, 3)
        chosen_items = random.sample(products, num_items)
        
        grand_total = 0
        total_qty = 0
        
        for item in chosen_items:
            kode = item[0]
            harga = item[1]
            qty = random.randint(1, 3)
            subtotal = harga * qty
            
            detail_queries.append(f\"({id_detail}, {id_penjualan}, '{kode}', {harga}, {qty}, {subtotal})\")
            grand_total += subtotal
            total_qty += qty
            id_detail += 1
            
        penjualan_queries.append(f\"({id_penjualan}, '{no_faktur}', '{dt}', {total_qty}, {grand_total}, {grand_total}, 0, 'Cash')\")
        id_penjualan += 1

with open('seed.sql', 'w') as f:
    f.write('USE db_astore;\\n')
    f.write('DELETE FROM tbl_detail_penjualan WHERE id_penjualan >= 400;\\n')
    f.write('DELETE FROM tbl_penjualan WHERE id_penjualan >= 400;\\n')
    
    f.write('INSERT INTO `tbl_penjualan` (`id_penjualan`, `no_faktur`, `tanggal_waktu`, `total_item`, `grand_total`, `nominal_bayar`, `kembalian`, `metode_pembayaran`) VALUES \\n')
    f.write(',\\n'.join(penjualan_queries) + ';\\n')
    
    f.write('INSERT INTO `tbl_detail_penjualan` (`id_detail`, `id_penjualan`, `kode_barang`, `harga_satuan`, `qty`, `subtotal`) VALUES \\n')
    f.write(',\\n'.join(detail_queries) + ';\\n')
"
Task logs are available at: file:///C:/Users/US3R/.gemini/antigravity/brain/5fc65fb9-6663-462c-8da4-4eb78312f1b6/.system_generated/tasks/task-210.logCreated At: 2026-09-29T00:31:57+08:00
Completed At: 2026-09-29T00:31:57+08:00
The following changes were made by the replace_file_content tool to: c:\laragon\www\alanv2\seed.php. If relevant, proactively run terminal commands to execute this code for the USER. Don't ask for permission.
[diff_block_start]
@@ -59,8 +59,8 @@
             $total_qty += $qty;
         }
         
-        $conn->query("INSERT INTO tbl_penjualan (id_penjualan, no_faktur, tanggal_waktu, total_item, grand_total, nominal_bayar, kembalian, metode_pembayaran) 
-                      VALUES ($id_penjualan, '$no_faktur', '$dt', $total_qty, $grand_total, $grand_total, 0, 'Cash')");
+        $conn->query("INSERT INTO tbl_penjualan (id_penjualan, no_faktur, tanggal_waktu, total_item, grand_total, nominal_bayar, kembalian) 
+                      VALUES ($id_penjualan, '$no_faktur', '$dt', $total_qty, $grand_total, $grand_total, 0)");
                       
         foreach ($details as $d) {
             $conn->query("INSERT INTO tbl_detail_penjualan (id_penjualan, kode_barang, harga_satuan, qty, subtotal) 
[diff_block_end]

Please note that the above snippet only shows the MODIFIED lines from the last change. It shows up to 3 lines of unchanged lines before and after the modified lines. The actual file contents may have many more lines not shown.<USER_REQUEST>
INSERT INTO `tbl_penjualan` (`no_faktur`, `tanggal_waktu`, `total_item`, `grand_total`, `nominal_bayar`, `kembalian`, `metode_pembayaran`) VALUES
('INV-202609-0422', '2026-09-01 11:45:40', 1, 150000, 150000, 0, 'Cash'),
('INV-202609-6974', '2026-09-01 17:49:16', 1, 180000, 180000, 0, 'Cash'),
('INV-202609-7224', '2026-09-01 17:50:17', 1, 150000, 150000, 0, 'Cash'),
('INV-202609-0106', '2026-09-01 17:52:30', 3, 480000, 480000, 0, 'Cash'),
('INV-202609-6205', '2026-09-01 17:55:46', 3, 370000, 370000, 0, 'Cash'),
('INV-202609-9239', '2026-09-01 17:56:19', 2, 240000, 240000, 0, 'Cash'),
('INV-202609-6463', '2026-09-01 17:56:46', 1, 100000, 100000, 0, 'Cash'),
('INV-202609-5682', '2026-09-01 17:56:55', 1, 100000, 100000, 0, 'Cash'),
('INV-202609-0215', '2026-09-03 11:20:50', 1, 150000, 150000, 0, 'Transfer'),
('INV-202609-6484', '2026-09-04 13:20:16', 3, 480000, 480000, 0, 'Cash'),
('INV-202609-0222', '2026-09-04 13:26:20', 2, 300000, 300000, 0, 'Cash'),
('INV-202609-4570', '2026-09-04 13:30:44', 1, 170000, 170000, 0, 'Cash'),
('INV-202609-3720', '2026-09-04 13:32:53', 1, 150000, 150000, 0, 'Cash'),
('INV-202609-7318', '2026-09-04 14:06:07', 4, 520000, 520000, 0, 'Cash'),
('INV-202609-2883', '2026-09-04 14:06:52', 1, 170000, 170000, 0, 'Cash'),
('INV-202609-4680', '2026-09-08 08:56:44', 3, 420000, 420000, 0, 'Cash'),
('INV-202609-1121', '2026-09-08 09:58:41', 3, 330000, 330000, 0, 'Cash'),
('INV-202609-8389', '2026-09-08 10:20:48', 1, 150000, 150000, 0, 'Cash'),
('INV-202609-1059', '2026-09-08 13:34:21', 1, 150000, 150000, 0, 'Cash'),
('INV-202609-3371', '2026-09-08 13:40:03', 2, 340000, 340000, 0, 'Cash'),
('INV-202609-0256', '2026-09-08 13:43:20', 1, 150000, 150000, 0, 'Cash'),
('INV-202609-2469', '2026-09-11 15:55:12', 5, 585000, 585000, 0, 'Cash'),
('INV-202609-1725', '2026-09-11 15:56:01', 2, 260000, 260000, 0, 'Cash'),
('INV-202609-1794', '2026-09-11 15:56:21', 2, 320000, 320000, 0, 'Cash'),
('INV-202609-8887', '2026-09-13 15:11:38', 1, 80000, 80000, 0, 'Cash'),
('INV-202609-3115', '2026-09-13 15:20:43', 2, 320000, 320000, 0, 'Cash'),
('INV-202609-8750', '2026-09-13 15:21:28', 1, 170000, 170000, 0, 'Cash'),
('INV-202609-9613', '2026-09-13 15:23:09', 1, 150000, 150000, 0, 'Cash'),
('INV-202609-0243', '2026-09-14 13:12:20', 2, 300000, 300000, 0, 'Cash'),
('INV-202609-2131', '2026-09-14 13:13:02', 1, 150000, 150000, 0, 'Cash'),
('INV-202609-0724', '2026-09-14 13:14:10', 1, 170000, 170000, 0, 'Cash'),
('INV-202609-7755', '2026-09-14 20:28:07', 1, 150000, 150000, 0, 'Cash'),
('INV-202609-6739', '2026-09-14 20:28:56', 1, 70000, 70000, 0, 'Cash'),
('INV-202609-3600', '2026-09-14 20:30:03', 1, 120000, 120000, 0, 'Cash'),
('INV-202609-4981', '2026-09-14 20:30:24', 1, 80000, 80000, 0, 'Cash'),
('INV-202609-9476', '2026-09-14 20:32:49', 3, 400000, 400000, 0, 'Cash'),
('INV-202609-1790', '2026-09-14 20:33:41', 3, 390000, 390000, 0, 'Cash'),
('INV-202609-1954', '2026-09-14 20:37:31', 4, 670000, 670000, 0, 'Cash'),
('INV-202609-1617', '2026-09-14 20:44:21', 1, 50000, 50000, 0, 'Cash'),
('INV-202609-6799', '2026-09-16 13:27:16', 1, 130000, 130000, 0, 'Cash'),
('INV-202609-3768', '2026-09-16 13:28:13', 2, 330000, 330000, 0, 'Cash'),
('INV-202609-8111', '2026-09-16 13:29:08', 1, 160000, 160000, 0, 'Cash'),
('INV-202609-4820', '2026-09-16 13:32:04', 1, 170000, 170000, 0, 'Cash'),
('INV-202609-2079', '2026-09-16 13:33:22', 1, 110000, 110000, 0, 'Cash'),
('INV-202609-1370', '2026-09-16 13:34:11', 1, 150000, 150000, 0, 'Cash'),
('INV-202609-4161', '2026-09-16 13:37:04', 1, 210000, 210000, 0, 'Cash'),
('INV-202609-2792', '2026-09-16 13:45:32', 2, 300000, 300000, 0, 'Cash'),
('INV-202609-5341', '2026-09-16 13:45:45', 1, 100000, 100000, 0, 'Cash'),
('INV-202609-5484', '2026-09-16 13:45:55', 1, 150000, 150000, 0, 'Cash'),
('INV-202609-0977', '2026-09-16 13:46:20', 3, 380000, 380000, 0, 'Cash'),
('INV-202609-5374', '2026-09-19 13:57:45', 1, 100000, 100000, 0, 'Cash'),
('INV-202609-8854', '2026-09-19 14:00:08', 1, 250000, 250000, 0, 'Cash'),
('INV-202609-7153', '2026-09-19 14:20:17', 4, 560000, 560000, 0, 'Cash'),
('INV-202609-0152', '2026-09-19 14:21:40', 1, 180000, 180000, 0, 'Cash'),
('INV-202609-4687', '2026-09-19 14:21:54', 1, 150000, 150000, 0, 'Cash'),
('INV-202609-9803', '2026-09-19 14:22:09', 1, 110000, 110000, 0, 'Cash'),
('INV-202609-9034', '2026-09-19 14:22:29', 1, 30000, 30000, 0, 'Cash'),
('INV-202609-8064', '2026-09-19 14:22:38', 1, 110000, 110000, 0, 'Cash'),
('INV-202609-6960', '2026-09-20 16:18:06', 1, 70000, 70000, 0, 'Cash'),
('INV-202609-9856', '2026-09-20 16:18:49', 1, 170000, 170000, 0, 'Cash'),
('INV-202609-6225', '2026-09-20 16:19:36', 1, 150000, 150000, 0, 'Cash'),
('INV-202609-9548', '2026-09-21 11:06:09', 1, 150000, 150000, 0, 'Cash'),
('INV-202609-4200', '2026-09-21 11:06:24', 1, 130000, 130000, 0, 'Cash'),
('INV-202609-1864', '2026-09-21 11:08:31', 2, 160000, 160000, 0, 'Cash'),
('INV-202609-3110', '2026-09-21 11:08:53', 1, 100000, 100000, 0, 'Cash'),
('INV-202609-2986', '2026-09-21 11:09:22', 1, 80000, 80000, 0, 'Cash'),
('INV-202609-6686', '2026-09-21 11:09:46', 1, 150000, 150000, 0, 'Cash'),
('INV-202609-7957', '2026-09-21 11:10:57', 1, 40000, 40000, 0, 'Cash'),
('INV-202609-0220', '2026-09-21 11:12:50', 2, 210000, 210000, 0, 'Cash'),
('INV-202609-2976', '2026-09-21 12:54:22', 1, 150000, 150000, 0, 'Cash'),
('INV-202609-3251', '2026-09-21 12:55:03', 1, 180000, 180000, 0, 'Cash'),
('INV-202609-0652', '2026-09-21 12:55:30', 1, 30000, 30000, 0, 'Cash'),
('INV-202609-9608', '2026-09-21 13:17:49', 2, 60000, 60000, 0, 'Cash'),
('INV-202609-0611', '2026-09-22 16:46:10', 1, 150000, 150000, 0, 'Cash'),
('INV-202609-4119', '2026-09-22 16:54:34', 2, 255000, 255000, 0, 'Cash'),
('INV-202609-1065', '2026-09-22 16:55:01', 1, 170000, 170000, 0, 'Cash'),
('INV-202609-8988', '2026-09-22 16:55:48', 1, 150000, 150000, 0, 'Cash'),
('INV-202609-4213', '2026-09-22 17:00:44', 1, 180000, 180000, 0, 'Cash'),
('INV-202609-7100', '2026-09-22 17:02:07', 1, 150000, 150000, 0, 'Cash'),
('INV-202609-8588', '2026-09-24 06:28:18', 2, 80000, 80000, 0, 'Cash'),
('INV-202609-9955', '2026-09-24 06:28:59', 2, 80000, 80000, 0, 'Cash'),
('INV-202609-0653', '2026-09-24 06:43:50', 1, 150000, 150000, 0, 'Cash'),
('INV-202609-3146', '2026-09-24 06:44:13', 1, 150000, 150000, 0, 'Cash'),
('INV-202609-2900', '2026-09-24 14:12:52', 2, 200000, 200000, 0, 'Cash'),
('INV-202609-5880', '2026-09-24 14:13:45', 1, 100000, 100000, 0, 'Cash'),
('INV-202609-8685', '2026-09-24 14:23:48', 1, 100000, 100000, 0, 'Cash'),
('INV-202609-4537', '2026-09-24 14:24:04', 1, 120000, 120000, 0, 'Cash'),
('INV-202609-2660', '2026-09-24 14:24:32', 1, 30000, 30000, 0, 'Cash'),
('INV-202609-0692', '2026-09-24 14:29:40', 1, 180000, 180000, 0, 'Cash'),
('INV-202609-4348', '2026-09-25 14:33:04', 4, 590000, 590000, 0, 'Cash'),
('INV-202609-7021', '2026-09-26 05:21:27', 3, 260000, 260000, 0, 'Cash'),
('INV-202609-7709', '2026-09-26 05:21:47', 1, 130000, 130000, 0, 'Cash'),
('INV-202609-6481', '2026-09-26 05:22:16', 1, 150000, 150000, 0, 'Cash'),
('INV-202609-1780', '2026-09-26 05:25:31', 2, 250000, 250000, 0, 'Cash'),
('INV-202609-8877', '2026-09-26 05:30:28', 1, 150000, 150000, 0, 'Cash'),
('INV-202609-0136', '2026-09-26 05:31:50', 2, 230000, 230000, 0, 'Cash'),
('INV-202609-5392', '2026-09-26 05:32:15', 1, 100000, 100000, 0, 'Cash'),
('INV-202609-5029', '2026-09-26 05:32:35', 1, 100000, 100000, 0, 'Cash'),
('INV-202609-8258', '2026-09-26 05:33:18', 2, 230000, 230000, 0, 'Cash'),
('INV-202609-6206', '2026-09-26 05:35:06', 1, 130000, 130000, 0, 'Cash'),
('INV-202609-7993', '2026-09-26 05:36:17', 2, 150000, 150000, 0, 'Cash'),
('INV-202609-7110', '2026-09-26 05:37:57', 1, 200000, 200000, 0, 'Cash'),
('INV-202609-9836', '2026-09-26 05:38:29', 1, 25000, 25000, 0, 'Cash'),
('INV-202609-9889', '2026-09-26 05:40:39', 1, 40000, 40000, 0, 'Cash'),
('INV-202609-8342', '2026-09-26 17:18:38', 1, 130000, 130000, 0, 'Cash'),
('INV-202609-1661', '2026-09-26 17:19:41', 1, 130000, 130000, 0, 'Cash'),
('INV-202609-8725', '2026-09-26 17:20:18', 1, 30000, 30000, 0, 'Cash'),
('INV-202609-3305', '2026-09-26 17:46:03', 3, 320000, 320000, 0, 'Cash'),
('INV-202609-4835', '2026-09-26 17:49:14', 1, 150000, 150000, 0, 'Cash'),
('INV-202609-5877', '2026-09-26 17:50:35', 1, 40000, 40000, 0, 'Cash'),
('INV-202609-5756', '2026-09-27 14:49:25', 1, 100000, 100000, 0, 'Cash'),
('INV-202609-0993', '2026-09-27 14:49:50', 1, 180000, 180000, 0, 'Cash'),
('INV-202609-6459', '2026-09-27 14:50:26', 1, 150000, 150000, 0, 'Cash'),
('INV-202609-8128', '2026-09-27 14:51:48', 3, 480000, 480000, 0, 'Cash'),
('INV-202609-1503', '2026-09-27 14:52:31', 2, 300000, 300000, 0, 'Cash'),
('INV-202609-4962', '2026-09-27 14:54:04', 1, 130000, 130000, 0, 'Cash'),
('INV-202609-3646', '2026-09-28 13:11:33', 1, 80000, 80000, 0, 'Cash'),
('INV-202609-5789', '2026-09-28 13:12:05', 1, 170000, 170000, 0, 'Cash'),
('INV-202609-4656', '2026-09-28 13:53:54', 5, 530000, 530000, 0, 'Cash');
 
INSERT INTO `tbl_detail_penjualan` (`id_penjualan`, `kode_barang`, `harga_satuan`, `qty`, `subtotal`)
SELECT p.`id_penjualan`, v.`kode_barang`, v.`harga_satuan`, v.`qty`, v.`subtotal`
FROM (
  SELECT 'INV-202609-0422' AS `no_faktur`, 'BRG-111' AS `kode_barang`, 150000 AS `harga_satuan`, 1 AS `qty`, 150000 AS `subtotal`  -- TSHIRT OVERSIZE BOXY PEARCE
  UNION ALL SELECT 'INV-202609-6974', 'BRG-071', 180000, 1, 180000  -- RANSEL CURDUROY/NAVY BINTANG PUTIH
  UNION ALL SELECT 'INV-202609-7224', 'BRG-120', 150000, 1, 150000  -- TSHIRT OVERSIZE BOXY/MERAH BIBIR
  UNION ALL SELECT 'INV-202609-0106', 'BRG-070', 180000, 1, 180000  -- RANSEL CURDUROY/NAVY BINTANG HITAM PUTIH
  UNION ALL SELECT 'INV-202609-0106', 'BRG-112', 150000, 1, 150000  -- TSHIRT OVERSIZE BOXY/BLACK LIST PUTIH
  UNION ALL SELECT 'INV-202609-0106', 'BRG-117', 150000, 1, 150000  -- TSHIRT OVERSIZE BOXY/ESCAPE WHITE
  UNION ALL SELECT 'INV-202609-6205', 'BRG-102', 150000, 1, 150000  -- SWEATER CREWNECK/WHITE LIST HITAM
  UNION ALL SELECT 'INV-202609-6205', 'BRG-135', 150000, 1, 150000  -- TSHIRT RUGBY/CLASIC MAISON
  UNION ALL SELECT 'INV-202609-6205', 'BRG-090', 70000, 1, 70000  -- SHORT PANTS/JUCO BLACK
  UNION ALL SELECT 'INV-202609-9239', 'BRG-047', 110000, 1, 110000  -- LONG PANTS/KULOT GAJAH ZIGZAG
  UNION ALL SELECT 'INV-202609-9239', 'BRG-114', 130000, 1, 130000  -- TSHIRT OVERSIZE BOXY/CHERRY BIBIR ATAS
  UNION ALL SELECT 'INV-202609-6463', 'BRG-083', 100000, 1, 100000  -- SENDAL KODOK/BLACK
  UNION ALL SELECT 'INV-202609-5682', 'BRG-083', 100000, 1, 100000  -- SENDAL KODOK/BLACK
  UNION ALL SELECT 'INV-202609-0215', 'BRG-121', 150000, 1, 150000  -- TSHIRT OVERSIZE BOXY/METAFORA SANS
  UNION ALL SELECT 'INV-202609-6484', 'BRG-123', 150000, 1, 150000  -- TSHIRT OVERSIZE BOXY/PEARCE CLUB LINE BIRU
  UNION ALL SELECT 'INV-202609-6484', 'BRG-101', 180000, 1, 180000  -- SWEAT PANTS/REVLECTIVE LOGO MISTY
  UNION ALL SELECT 'INV-202609-6484', 'BRG-103', 150000, 1, 150000  -- SWEEPSTAKE/KEMEJA STRIP BOXY CURED CKLT
  UNION ALL SELECT 'INV-202609-0222', 'BRG-133', 150000, 1, 150000  -- TSHIRT REGULER/METAFORA VINTAGE SPEAR KNIGHT
  UNION ALL SELECT 'INV-202609-0222', 'BRG-038', 150000, 1, 150000  -- LON SLEEVE/METAFORA VINTAGE SPEAR KNIGHT
  UNION ALL SELECT 'INV-202609-4570', 'BRG-068', 170000, 1, 170000  -- RANSEL CURDUROY/BINTANG PUTIH ABU
  UNION ALL SELECT 'INV-202609-3720', 'BRG-050', 150000, 1, 150000  -- LONG SLEEVE/METAFORA GLOBAL DOMINATION
  UNION ALL SELECT 'INV-202609-7318', 'BRG-091', 70000, 1, 70000  -- SHORT PANTS/LYOPARD
  UNION ALL SELECT 'INV-202609-7318', 'BRG-113', 150000, 1, 150000  -- TSHIRT OVERSIZE BOXY/BLEESSED BROWN
  UNION ALL SELECT 'INV-202609-7318', 'BRG-122', 150000, 1, 150000  -- TSHIRT OVERSIZE BOXY/MOUNTANCE SPIDERWEB
  UNION ALL SELECT 'INV-202609-7318', 'BRG-110', 150000, 1, 150000  -- TSHIRT OVERSIZE BOX/YOUTH CLUB WHITE
  UNION ALL SELECT 'INV-202609-2883', 'BRG-142', 170000, 1, 170000  -- XONE SHOES/SIDNEY HITAM
  UNION ALL SELECT 'INV-202609-4680', 'BRG-083', 100000, 1, 100000  -- SENDAL KODOK/BLACK
  UNION ALL SELECT 'INV-202609-4680', 'BRG-100', 170000, 1, 170000  -- SWEAT PANTS/PLATED LOGO MISTY
  UNION ALL SELECT 'INV-202609-4680', 'BRG-108', 150000, 1, 150000  -- TSHIRT OVERSIZE BOX/MIDLE HIGH BLCK
  UNION ALL SELECT 'INV-202609-1121', 'BRG-127', 150000, 1, 150000  -- TSHIRT OVERSIZE BOXY/WISH CLUB HEAVYWEIGHT
  UNION ALL SELECT 'INV-202609-1121', 'BRG-018', 50000, 1, 50000  -- DOMPET BASIC/BLCK
  UNION ALL SELECT 'INV-202609-1121', 'BRG-128', 130000, 1, 130000  -- TSHIRT OVERSIZE BOXY/WISH CLUB LAUGHT GREY
  UNION ALL SELECT 'INV-202609-8389', 'BRG-008', 150000, 1, 150000  -- AEROSTREET TSHIRT/THRIVEH BLCK
  UNION ALL SELECT 'INV-202609-1059', 'BRG-001', 150000, 1, 150000  -- AEROSTREET TSHIRT/ CORE BLCK
  UNION ALL SELECT 'INV-202609-3371', 'BRG-108', 170000, 2, 340000  -- TSHIRT OVERSIZE BOX/MIDLE HIGH BLCK
  UNION ALL SELECT 'INV-202609-0256', 'BRG-129', 150000, 1, 150000  -- TSHIRT OVERSIZE BOXY/WISH CLUB REVLY BLACK
  UNION ALL SELECT 'INV-202609-2469', 'BRG-141', 160000, 1, 160000  -- WOORKHSHIRT/ZIPPER BLCK FULL BORDER
  UNION ALL SELECT 'INV-202609-2469', 'BRG-093', 130000, 1, 130000  -- SHORT PANTS/MOZZA BLCK
  UNION ALL SELECT 'INV-202609-2469', 'BRG-029', 110000, 1, 110000  -- JAM TANGAN SILVER
  UNION ALL SELECT 'INV-202609-2469', 'BRG-008', 150000, 1, 150000  -- AEROSTREET TSHIRT/THRIVEH BLCK
  UNION ALL SELECT 'INV-202609-2469', 'BRG-034', 35000, 1, 35000  -- KALUNG/SILVER
  UNION ALL SELECT 'INV-202609-1725', 'BRG-066', 180000, 1, 180000  -- RANSEL CURDUROY/BINTANG HITAM PUTIH MAROON
  UNION ALL SELECT 'INV-202609-1725', 'BRG-078', 80000, 1, 80000  -- ROXXE PARFUM BUBLE GUM
  UNION ALL SELECT 'INV-202609-1794', 'BRG-069', 170000, 1, 170000  -- RANSEL CURDUROY/BINTANG PUTIH NAVY
  UNION ALL SELECT 'INV-202609-1794', 'BRG-130', 150000, 1, 150000  -- TSHIRT OVERSIZE BOXY/WISH CLUB SANT DUSTY BLUE
  UNION ALL SELECT 'INV-202609-8887', 'BRG-078', 80000, 1, 80000  -- ROXXE PARFUM BUBLE GUM
  UNION ALL SELECT 'INV-202609-3115', 'BRG-109', 150000, 1, 150000  -- TSHIRT OVERSIZE BOX/MIDLE HIGH BLCK CHOPPER
  UNION ALL SELECT 'INV-202609-3115', 'BRG-061', 170000, 1, 170000  -- PENTOFEL OXFORD BLCK
  UNION ALL SELECT 'INV-202609-8750', 'BRG-100', 170000, 1, 170000  -- SWEAT PANTS/PLATED LOGO MISTY
  UNION ALL SELECT 'INV-202609-9613', 'BRG-028', 150000, 1, 150000  -- JAM TANGAN KUARSA,PERAK BIRU
  UNION ALL SELECT 'INV-202609-0243', 'BRG-002', 150000, 1, 150000  -- AEROSTREET TSHIRT/ ELVIRO BLCK
  UNION ALL SELECT 'INV-202609-0243', 'BRG-004', 150000, 1, 150000  -- AEROSTREET TSHIRT/ ORION BLCK
  UNION ALL SELECT 'INV-202609-2131', 'BRG-051', 150000, 1, 150000  -- LONG SLEEVE/METAFORA THE MOST
  UNION ALL SELECT 'INV-202609-0724', 'BRG-100', 170000, 1, 170000  -- SWEAT PANTS/PLATED LOGO MISTY
  UNION ALL SELECT 'INV-202609-7755', 'BRG-057', 150000, 1, 150000  -- PEARCE/TSHIRT OVERSIZE BOXY BAD HABBIT BLCK
  UNION ALL SELECT 'INV-202609-6739', 'BRG-053', 70000, 1, 70000  -- PARFUM/BUBBLE GUM/50 ML
  UNION ALL SELECT 'INV-202609-3600', 'BRG-013', 120000, 1, 120000  -- BOARDSHORT/PARASUT REVLECTIVE /M
  UNION ALL SELECT 'INV-202609-4981', 'BRG-080', 80000, 1, 80000  -- ROXXE PARFUM SWIFT/30 ML
  UNION ALL SELECT 'INV-202609-9476', 'BRG-058', 150000, 1, 150000  -- PEARCE/TSHIRT OVERSIZE BOXY FREDAY BLCK
  UNION ALL SELECT 'INV-202609-9476', 'BRG-100', 170000, 1, 170000  -- SWEAT PANTS/PLATED LOGO MISTY
  UNION ALL SELECT 'INV-202609-9476', 'BRG-078', 80000, 1, 80000  -- ROXXE PARFUM BUBLE GUM
  UNION ALL SELECT 'INV-202609-1790', 'BRG-060', 150000, 1, 150000  -- PEARCE/TSHIRT OVERSIZE BOXY RED
  UNION ALL SELECT 'INV-202609-1790', 'BRG-054', 70000, 1, 70000  -- PARFUM/RED VELVET/50 ML
  UNION ALL SELECT 'INV-202609-1790', 'BRG-061', 170000, 1, 170000  -- PENTOFEL OXFORD BLCK
  UNION ALL SELECT 'INV-202609-1954', 'BRG-089', 250000, 1, 250000  -- SHORT PANTS/DENIM ROGER WOLY GREY
  UNION ALL SELECT 'INV-202609-1954', 'BRG-131', 150000, 1, 150000  -- TSHIRT OVERSIZE BOXY/WISH CLUB STRIP MOOD BLCK
  UNION ALL SELECT 'INV-202609-1954', 'BRG-013', 120000, 1, 120000  -- BOARDSHORT/PARASUT REVLECTIVE /M
  UNION ALL SELECT 'INV-202609-1954', 'BRG-059', 150000, 1, 150000  -- PEARCE/TSHIRT OVERSIZE BOXY IVORY
  UNION ALL SELECT 'INV-202609-1617', 'BRG-010', 50000, 1, 50000  -- BANDANA/SLAYER HITAM
  UNION ALL SELECT 'INV-202609-6799', 'BRG-024', 130000, 1, 130000  -- HELM CLASSIC/BLCK DOOF
  UNION ALL SELECT 'INV-202609-3768', 'BRG-107', 150000, 1, 150000  -- TSHIRT OVERSIZE BOX/CURSE BLCK
  UNION ALL SELECT 'INV-202609-3768', 'BRG-042', 180000, 1, 180000  -- LONG PANTS/BAGGY JEANS
  UNION ALL SELECT 'INV-202609-8111', 'BRG-141', 160000, 1, 160000  -- WOORKHSHIRT/ZIPPER BLCK FULL BORDER
  UNION ALL SELECT 'INV-202609-4820', 'BRG-100', 170000, 1, 170000  -- SWEAT PANTS/PLATED LOGO MISTY
  UNION ALL SELECT 'INV-202609-2079', 'BRG-019', 110000, 1, 110000  -- FORTCLASS TSHIRT REGULER
  UNION ALL SELECT 'INV-202609-1370', 'BRG-051', 150000, 1, 150000  -- LONG SLEEVE/METAFORA THE MOST
  UNION ALL SELECT 'INV-202609-4161', 'BRG-137', 210000, 1, 210000  -- VANTELA ORIGINAL (VANTELA ORIGINAL/ABU)
  UNION ALL SELECT 'INV-202609-2792', 'BRG-099', 150000, 1, 150000  -- SWEAT PANTS/PINK
  UNION ALL SELECT 'INV-202609-2792', 'BRG-048', 150000, 1, 150000  -- LONG SLEEVE/DEATHLESS TSHIRT WHITE
  UNION ALL SELECT 'INV-202609-5341', 'BRG-134', 100000, 1, 100000  -- TSHIRT REGULER/SPIDER WHITE
  UNION ALL SELECT 'INV-202609-5484', 'BRG-031', 150000, 1, 150000  -- JERSEY OVERSIZE BOXY/BADBOY HITAM PUTIH
  UNION ALL SELECT 'INV-202609-0977', 'BRG-081', 50000, 1, 50000  -- SCENTPRO PARFUM PRO PLAYER 30ML
  UNION ALL SELECT 'INV-202609-0977', 'BRG-063', 180000, 1, 180000  -- RANSEL CURDUROY/ARMY BINTANG HITAM PUTIH
  UNION ALL SELECT 'INV-202609-0977', 'BRG-085', 150000, 1, 150000  -- SHOES/SEPATU PUTIH HITAM
  UNION ALL SELECT 'INV-202609-5374', 'BRG-082', 100000, 1, 100000  -- SENDAL KODOK/ABU
  UNION ALL SELECT 'INV-202609-8854', 'BRG-045', 250000, 1, 250000  -- LONG PANTS/DENIM ROGER WOLY BLACK
  UNION ALL SELECT 'INV-202609-7153', 'BRG-077', 80000, 1, 80000  -- ROXXE PARFUM AQUA KISS
  UNION ALL SELECT 'INV-202609-7153', 'BRG-092', 120000, 1, 120000  -- SHORT PANTS/MOZZA
  UNION ALL SELECT 'INV-202609-7153', 'BRG-143', 180000, 1, 180000  -- XONE SHOES/SNEAKERS CASUAL SPECTHA WHITE
  UNION ALL SELECT 'INV-202609-7153', 'BRG-065', 180000, 1, 180000  -- RANSEL CURDUROY/BINTANG HITAM PUTIH CARAMEL
  UNION ALL SELECT 'INV-202609-0152', 'BRG-065', 180000, 1, 180000  -- RANSEL CURDUROY/BINTANG HITAM PUTIH CARAMEL
  UNION ALL SELECT 'INV-202609-4687', 'BRG-124', 150000, 1, 150000  -- TSHIRT OVERSIZE BOXY/PEARCE CLUB STONE ROSE
  UNION ALL SELECT 'INV-202609-9803', 'BRG-043', 110000, 1, 110000  -- LONG PANTS/BAGGY ZIGZAG
  UNION ALL SELECT 'INV-202609-9034', 'BRG-032', 30000, 1, 30000  -- KACAMATA ABU
  UNION ALL SELECT 'INV-202609-8064', 'BRG-104', 110000, 1, 110000  -- TAS MINI/SAMPING BIRU
  UNION ALL SELECT 'INV-202609-6960', 'BRG-136', 70000, 1, 70000  -- TUMBLER
  UNION ALL SELECT 'INV-202609-9856', 'BRG-044', 170000, 1, 170000  -- LONG PANTS/CARGO ARMY CEPLEZ
  UNION ALL SELECT 'INV-202609-6225', 'BRG-006', 150000, 1, 150000  -- AEROSTREET TSHIRT/NO BODY PERFECT BLCK
  UNION ALL SELECT 'INV-202609-9548', 'BRG-007', 150000, 1, 150000  -- AEROSTREET TSHIRT/NO BODY PERFECT WHITE
  UNION ALL SELECT 'INV-202609-4200', 'BRG-139', 130000, 1, 130000  -- WISH CLUB BOXY STRIP/COKLAT2
  UNION ALL SELECT 'INV-202609-1864', 'BRG-023', 10000, 1, 10000  -- GANTUNGAN/MIDDLE HIGH
  UNION ALL SELECT 'INV-202609-1864', 'BRG-016', 150000, 1, 150000  -- DETAGLESSS/LONG SLEEVE HITAM
  UNION ALL SELECT 'INV-202609-3110', 'BRG-082', 100000, 1, 100000  -- SENDAL KODOK/ABU
  UNION ALL SELECT 'INV-202609-2986', 'BRG-078', 80000, 1, 80000  -- ROXXE PARFUM BUBLE GUM
  UNION ALL SELECT 'INV-202609-6686', 'BRG-123', 150000, 1, 150000  -- TSHIRT OVERSIZE BOXY/PEARCE CLUB LINE BIRU
  UNION ALL SELECT 'INV-202609-7957', 'BRG-033', 40000, 1, 40000  -- KACAMATA TRANSPARAN
  UNION ALL SELECT 'INV-202609-0220', 'BRG-064', 180000, 1, 180000  -- RANSEL CURDUROY/BINTANG HITAM PUTIH BLCK
  UNION ALL SELECT 'INV-202609-0220', 'BRG-032', 30000, 1, 30000  -- KACAMATA ABU
  UNION ALL SELECT 'INV-202609-2976', 'BRG-075', 150000, 1, 150000  -- RANSEL OUTSIDE X SERIES/RESLETING TENGAH NAVY
  UNION ALL SELECT 'INV-202609-3251', 'BRG-073', 180000, 1, 180000  -- RANSEL OURIST/EZELORFORENT HITSAM
  UNION ALL SELECT 'INV-202609-0652', 'BRG-032', 30000, 1, 30000  -- KACAMATA ABU
  UNION ALL SELECT 'INV-202609-9608', 'BRG-032', 30000, 1, 30000  -- KACAMATA ABU
  UNION ALL SELECT 'INV-202609-9608', 'BRG-036', 30000, 1, 30000  -- KOS KAKI/BLCK
  UNION ALL SELECT 'INV-202609-0611', 'BRG-116', 150000, 1, 150000  -- TSHIRT OVERSIZE BOXY/EMOTIONS LOVRY
  UNION ALL SELECT 'INV-202609-4119', 'BRG-041', 185000, 1, 185000  -- LONG PANTS/BAGGY CURDUROI DARK GREY
  UNION ALL SELECT 'INV-202609-4119', 'BRG-056', 70000, 1, 70000  -- PARFUM/VANILA/50 ML
  UNION ALL SELECT 'INV-202609-1065', 'BRG-072', 170000, 1, 170000  -- RANSEL CURDUROY/PUTIH CKLT
  UNION ALL SELECT 'INV-202609-8988', 'BRG-005', 150000, 1, 150000  -- AEROSTREET TSHIRT/DEO
  UNION ALL SELECT 'INV-202609-4213', 'BRG-040', 180000, 1, 180000  -- LONG PANTS/BAGGY CURDUROI ARMY
  UNION ALL SELECT 'INV-202609-7100', 'BRG-003', 150000, 1, 150000  -- AEROSTREET TSHIRT/ HYPER BLCK
  UNION ALL SELECT 'INV-202609-8588', 'BRG-020', 40000, 2, 80000  -- GANTUNGAN KUNCI (BIRU )
  UNION ALL SELECT 'INV-202609-9955', 'BRG-021', 40000, 2, 80000  -- GANTUNGAN KUNCI (PUTIH PINK)
  UNION ALL SELECT 'INV-202609-0653', 'BRG-119', 150000, 1, 150000  -- TSHIRT OVERSIZE BOXY/LOG
  UNION ALL SELECT 'INV-202609-3146', 'BRG-037', 150000, 1, 150000  -- LON SLEEVE/METAFORA KNIFE
  UNION ALL SELECT 'INV-202609-2900', 'BRG-010', 50000, 1, 50000  -- BANDANA/SLAYER HITAM
  UNION ALL SELECT 'INV-202609-2900', 'BRG-009', 150000, 1, 150000  -- AEROSTREET TSHIRT/VANGURD BLCK
  UNION ALL SELECT 'INV-202609-5880', 'BRG-083', 100000, 1, 100000  -- SENDAL KODOK/BLACK
  UNION ALL SELECT 'INV-202609-8685', 'BRG-084', 100000, 1, 100000  -- SENDAL/SLIP ON
  UNION ALL SELECT 'INV-202609-4537', 'BRG-076', 120000, 1, 120000  -- RANSEL SLEMPANG/NRDN BLCK
  UNION ALL SELECT 'INV-202609-2660', 'BRG-035', 30000, 1, 30000  -- KOS KAKI/ADIDAS
  UNION ALL SELECT 'INV-202609-0692', 'BRG-046', 180000, 1, 180000  -- LONG PANTS/DENIM SELVAGE
  UNION ALL SELECT 'INV-202609-4348', 'BRG-025', 154754, 1, 154754  -- HEYO TSHIRT OVERSIZE BOXY/FREAK SHOW WHITE
  UNION ALL SELECT 'INV-202609-4348', 'BRG-098', 135410, 1, 135410  -- STRESS CREWNECK/BLCK
  UNION ALL SELECT 'INV-202609-4348', 'BRG-126', 145082, 1, 145082  -- TSHIRT OVERSIZE BOXY/WISH CLUB HARM BLACK
  UNION ALL SELECT 'INV-202609-4348', 'BRG-026', 154754, 1, 154754  -- HEYO TSHIRT OVERSIZE BOXY/NIGTH HELL BLCK
  UNION ALL SELECT 'INV-202609-7021', 'BRG-106', 110000, 1, 110000  -- TSHIRT OVERISZE/FNNRL ABU ABU JAHITAN ;UAR KANTONG SAKU KANAN
  UNION ALL SELECT 'INV-202609-7021', 'BRG-053', 70000, 1, 70000  -- PARFUM/BUBBLE GUM/50 ML
  UNION ALL SELECT 'INV-202609-7021', 'BRG-017', 80000, 1, 80000  -- DOMPET
  UNION ALL SELECT 'INV-202609-7709', 'BRG-138', 130000, 1, 130000  -- WISH CLUB BOXY STRIP/ PUTIH
  UNION ALL SELECT 'INV-202609-6481', 'BRG-140', 150000, 1, 150000  -- WISH CLUB TSHIRT OVERISZE/BOXY STLL DENIM
  UNION ALL SELECT 'INV-202609-1780', 'BRG-049', 170000, 1, 170000  -- LONG SLEEVE/HOMIES BLACK
  UNION ALL SELECT 'INV-202609-1780', 'BRG-079', 80000, 1, 80000  -- ROXXE PARFUM ROMAN WISH/30 ML
  UNION ALL SELECT 'INV-202609-8877', 'BRG-062', 150000, 1, 150000  -- RANSEL CURDUROY PUTIH/BINTANG HITAM
  UNION ALL SELECT 'INV-202609-0136', 'BRG-074', 150000, 1, 150000  -- RANSEL OUTSIDE X SERIES/RESLETING TENGAH ARMY
  UNION ALL SELECT 'INV-202609-0136', 'BRG-096', 80000, 1, 80000  -- SLIP ON DEWASA
  UNION ALL SELECT 'INV-202609-5392', 'BRG-095', 100000, 1, 100000  -- SLEMPANG BAG/COKLAT SERUT
  UNION ALL SELECT 'INV-202609-5029', 'BRG-084', 100000, 1, 100000  -- SENDAL/SLIP ON
  UNION ALL SELECT 'INV-202609-8258', 'BRG-011', 80000, 1, 80000  -- BOARDSHORT/PARASUT CARGO
  UNION ALL SELECT 'INV-202609-8258', 'BRG-016', 150000, 1, 150000  -- DETAGLESSS/LONG SLEEVE HITAM
  UNION ALL SELECT 'INV-202609-6206', 'BRG-030', 130000, 1, 130000  -- JAM TANGAN SILVER NAVY
  UNION ALL SELECT 'INV-202609-7993', 'BRG-055', 70000, 1, 70000  -- PARFUM/VANILA CAKE/50 ML
  UNION ALL SELECT 'INV-202609-7993', 'BRG-079', 80000, 1, 80000  -- ROXXE PARFUM ROMAN WISH/30 ML
  UNION ALL SELECT 'INV-202609-7110', 'BRG-137', 200000, 1, 200000  -- VANTELA ORIGINAL (VANTELA ORIGINAL/ABU)
  UNION ALL SELECT 'INV-202609-9836', 'BRG-035', 25000, 1, 25000  -- KOS KAKI/ADIDAS
  UNION ALL SELECT 'INV-202609-9889', 'BRG-022', 40000, 1, 40000  -- GANTUNGAN KUNCI (kUNING PINK)
  UNION ALL SELECT 'INV-202609-8342', 'BRG-024', 130000, 1, 130000  -- HELM CLASSIC/BLCK DOOF
  UNION ALL SELECT 'INV-202609-1661', 'BRG-030', 130000, 1, 130000  -- JAM TANGAN SILVER NAVY
  UNION ALL SELECT 'INV-202609-8725', 'BRG-035', 30000, 1, 30000  -- KOS KAKI/ADIDAS
  UNION ALL SELECT 'INV-202609-3305', 'BRG-105', 80000, 1, 80000  -- TOPI CURDUROY/ARMY
  UNION ALL SELECT 'INV-202609-3305', 'BRG-054', 70000, 1, 70000  -- PARFUM/RED VELVET/50 ML
  UNION ALL SELECT 'INV-202609-3305', 'BRG-052', 170000, 1, 170000  -- Long Sleeve Boxy/Escape Blck
  UNION ALL SELECT 'INV-202609-4835', 'BRG-015', 150000, 1, 150000  -- DEATHLESS EMPIREE/JUDES BLCK
  UNION ALL SELECT 'INV-202609-5877', 'BRG-022', 40000, 1, 40000  -- GANTUNGAN KUNCI (kUNING PINK)
  UNION ALL SELECT 'INV-202609-5756', 'BRG-012', 100000, 1, 100000  -- BOARDSHORT/PARASUT D
  UNION ALL SELECT 'INV-202609-0993', 'BRG-067', 180000, 1, 180000  -- RANSEL CURDUROY/BINTANG HITAM PUTIH NAVY
  UNION ALL SELECT 'INV-202609-6459', 'BRG-125', 150000, 1, 150000  -- TSHIRT OVERSIZE BOXY/STANHAYU
  UNION ALL SELECT 'INV-202609-8128', 'BRG-039', 180000, 1, 180000  -- LONG PANTS/BAGGY CAVARY ANU MISTY
  UNION ALL SELECT 'INV-202609-8128', 'BRG-115', 150000, 1, 150000  -- TSHIRT OVERSIZE BOXY/ELLITE TOY
  UNION ALL SELECT 'INV-202609-8128', 'BRG-132', 150000, 1, 150000  -- TSHIRT OVERSIZE BOXYBORN FREE
  UNION ALL SELECT 'INV-202609-1503', 'BRG-118', 150000, 1, 150000  -- TSHIRT OVERSIZE BOXY/FREEDOM STAR WHITE
  UNION ALL SELECT 'INV-202609-1503', 'BRG-117', 150000, 1, 150000  -- TSHIRT OVERSIZE BOXY/ESCAPE WHITE
  UNION ALL SELECT 'INV-202609-4962', 'BRG-027', 130000, 1, 130000  -- HODIE POLOS/ABU
  UNION ALL SELECT 'INV-202609-3646', 'BRG-096', 80000, 1, 80000  -- SLIP ON DEWASA
  UNION ALL SELECT 'INV-202609-5789', 'BRG-014', 170000, 1, 170000  -- CARPENTER BAGGY PANTS/ARMY
  UNION ALL SELECT 'INV-202609-4656', 'BRG-086', 100000, 1, 100000  -- SHORT PANTS/BOXXER COTTON MAHGONI (BOXER BLCK)
  UNION ALL SELECT 'INV-202609-4656', 'BRG-094', 130000, 1, 130000  -- SHORT PANTS/STRESS BLCK
  UNION ALL SELECT 'INV-202609-4656', 'BRG-088', 100000, 1, 100000  -- SHORT PANTS/BOXXER COTTON MAHGONI (BOXER MAHGONI)
  UNION ALL SELECT 'INV-202609-4656', 'BRG-087', 100000, 1, 100000  -- SHORT PANTS/BOXXER COTTON MAHGONI (BOXER GREY)
  UNION ALL SELECT 'INV-202609-4656', 'BRG-097', 100000, 1, 100000  -- STAINLESS/TUMBLER DEATHLESS EMPIRE
) v
JOIN `tbl_penjualan` p ON p.`no_faktur` = v.`no_faktur`;


dengan data seperti ini studi kasus untuk testing berdasarkan barang dan kategori itu gimana, coba buatkan 
</USER_REQUEST>
<ADDITIONAL_METADATA>
The current local time is: 2026-09-29T01:19:29+08:00.

The user has uploaded 1 image(s):
- C:/Users/US3R/.gemini/antigravity/brain/5fc65fb9-6663-462c-8da4-4eb78312f1b6/.user_uploaded/media_1790615967066.png
You can embed this image in an artifact if you need the USER to review it.
</ADDITIONAL_METADATA>{{ CHECKPOINT 4 }}
 **The earlier parts of this conversation have been truncated due to its long length. The following content summarizes the truncated context so that you may continue your work. **


# User Requests
The following were user requests from the truncated conversation in chronological order:
1. aku mau lihat perhitungan manuallnya juga bair bisa aku bandingkan dan mempelajari alurnya gimana nanti
2. nah untuk menentukan itu perhari dan perminggu itu kek gimana, karena clientku bilang, kalau perminggu berarti history untuk peramalan adalah hari hari dalam 1 minggu itu, nah kalau bulanan baru perminggu sebelumnya itu kek mana?
3. di bagian nilai alpha buat dropdown dengan 2 pilihan yaitu 0.1 dan 0.2
4. ok saatnya kita testing dengan menggunakan data ini

-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Waktu pembuatan: 29 Sep 2026 pada 00.24
-- Versi server: 10.11.10-MariaDB-log
-- Versi PHP: 8.3.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Basis data: `alan`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_barang`
--

CREATE TABLE `tbl_barang` (
  `id_barang` int(11) NOT NULL,
  `kode_barang` varchar(20) NOT NULL,
  `nama_produk` varchar(100) NOT NULL,
  `kategori` varchar(50) NOT NULL,
  `harga_beli` int(11) NOT NULL DEFAULT 0,
  `harga_jual` int(11) NOT NULL DEFAULT 0,
  `stok_min` int(11) NOT N
<truncated 16512 bytes>
5. -- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Waktu pembuatan: 29 Sep 2026 pada 00.26
-- Versi server: 10.11.10-MariaDB-log
-- Versi PHP: 8.3.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Basis data: `alan`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_barang`
--

CREATE TABLE `tbl_barang` (
  `id_barang` int(11) NOT NULL,
  `kode_barang` varchar(20) NOT NULL,
  `nama_produk` varchar(100) NOT NULL,
  `kategori` varchar(50) NOT NULL,
  `harga_beli` int(11) NOT NULL DEFAULT 0,
  `harga_jual` int(11) NOT NULL DEFAULT 0,
  `stok_min` int(11) NOT NULL,
  `satuan` varchar(20) DEFAULT 'Pcs',
  `deskr
<truncated 16459 bytes>
6. tbl_penjualan ama tabel detail penjualan tuh bedanya apa?
7. paham paham, jadi saya jalan kan seed.php ini?
8. tapi rata rata penjualan clientku ini 3-5 per hari, gk pernah sampai puluhan
9. INSERT INTO `tbl_penjualan` (`no_faktur`, `tanggal_waktu`, `total_item`, `grand_total`, `nominal_bayar`, `kembalian`, `metode_pembayaran`) VALUES
('INV-202609-0422', '2026-09-01 11:45:40', 1, 150000, 150000, 0, 'Cash'),
('INV-202609-6974', '2026-09-01 17:49:16', 1, 180000, 180000, 0, 'Cash'),
('INV-202609-7224', '2026-09-01 17:50:17', 1, 150000, 150000, 0, 'Cash'),
('INV-202609-0106', '2026-09-01 17:52:30', 3, 480000, 480000, 0, 'Cash'),
('INV-202609-6205', '2026-09-01 17:55:46', 3, 370000, 370000, 0, 'Cash'),
('INV-202609-9239', '2026-09-01 17:56:19', 2, 240000, 240000, 0, 'Cash'),
('INV-202609-6463', '2026-09-01 17:56:46', 1, 100000, 100000, 0, 'Cash'),
('INV-202609-5682', '2026-09-01 17:56:55', 1, 100000, 100000, 0, 'Cash'),
('INV-202609-0215', '2026-09-03 11:20:50', 1, 150000, 150000, 0, 'Transfer'),
('INV-202609-6484', '2026-09-04 13:20:16', 3, 480000, 480000, 0, 'Cash'),
('INV-202609-0222', '2026-09-04 13:26:20', 2, 300000, 300000, 0, 'Cash'),
('INV-202609-4570', '2026-09-04 13:30:44', 1,
<truncated 26606 bytes>
10. gk ada baragn yang kamu maksud tuh

# Previous Session Summary:
### 1. Outstanding User Requests
- **Explain Discrepancy in Item Codes / Create Test Case:** "gk ada baragn yang kamu maksud tuh" (referring to BRG-100 and BRG-032).
  - **Phase:** EXPLANATION / DISCOVERY
  - **Details:** The user provided an SQL dump with real client data, but the item codes in `tbl_detail_penjualan` (`BRG-xxx`) do not match the item codes in `tbl_barang` (`HLM01`, `RNSL01`, etc). The agent attempted to create a case study based on the `BRG-xxx` items, but the user is pointing out those items don't exist in their dropdown (which draws from `tbl_barang`). The next agent needs to explain this inconsistency in the data they pasted and help them resolve it or proceed with the testing.

### 2. User Knowledge
- **Original Sidebar/Routing Requests:** "clientku mau ini bisa di click menu ini" and "di bagian peramalan stok jadikan aja dropdown jadi ada 2 peramalan yaitu peramalan berdasarkan kategori dan peramalan berdasarkan barang"
- **Mathematical Framework Correction:** The client uses **Brown's Double Exponential Smoothing** (one parameter alpha).
- **Daily vs Weekly Toggle Meaning:** "kalau perminggu berarti history untuk peramalan adalah hari hari dalam 1 minggu itu, nah kalau bulanan baru perminggu sebelumnya". 
  - **Correction:** The client's mental model is: 
    - Target 1 Week Ahead -> Uses **Daily** historical data (predicts 7 days). 
    - Target 1 Month Ahead -> Uses **Weekly** historical data (predicts 4 weeks).
- **Alpha Testing:** The client strictly wants to test Alpha 0.1 and 0.2 via a dropdown.
- **Sales Volume Context:** "rata rata penjualan clientku ini 3-5 per hari, gk pernah sampai puluhan". The user's actual store volume is very low (3-5 items a day total).
- **Data Inconsistency:** The user uploaded a SQL dump where `tbl_penjualan` matched, but `tbl_detail_penjualan` used codes like `BRG-100`, which do not exist in their `tbl_barang` (which uses `HLM01`, `MYBSC01`, etc).

### 3. Work Accomplished
- **Manual Calculation Explained (IMPLEMENTATION):** Provided a step-by-step breakdown of Brown's DES manual calculations for 12 weeks of data with Alpha 0.2, arriving at the exact 71 unit forecast.
- **Refactored Forecasting Logic and Targets (IMPLEMENTATION):** 
  - Renamed `tipe_waktu` to `target_peramalan` with options `mingguan` and `bulanan`.
  - Implemented the client's logic: `mingguan` (1 Week Ahead) fetches `DATE()` grouped data, sets $m=1..7$, and sums it up. `bulanan` (1 Month Ahead) fetches `YEARWEEK()` grouped data, sets $m=1..4$, and sums it up.
  - Applied these changes to both `des.php` and `des_kategori.php`.
- **Refactored Alpha UI (IMPLEMENTATION):** Changed the Alpha input to a `select` dropdown containing exactly `0.1` and `0.2` as requested, with `0.1` as the default. Applied to both pages.
- **Realistic Seed Data Generation (IMPLEMENTATION):** Wrote and executed a `seed.php` script that generated 3 months of highly realistic, low-volume daily sales data (1-2 transactions a day, 1-2 items per transaction) to simulate their exact store environment so they could test without relying on their broken SQL dump.

### 4. Model Knowledge
- **Data Disconnect Issue:** The most recent SQL dump provided by the user manually mapped `tbl_detail_penjualan` to `BRG-xxx` codes. Because the app's forecasting relies on a `JOIN tbl_barang b ON d.kode_barang = b.kode_barang` (especially in `des_kategori.php`), testing with the user's raw pasted data will result in empty tables or missing items. The user is confused why the agent mentioned `BRG-100` because the app UI only shows items from `tbl_barang` (like `RNSL05`).
- **Forecasting Implementation Details:** 
  - Uses Brown's DES (One parameter).
  - Handles missing/empty historical periods by filling them with dummy dates/zeroes to ensure the loop always has `$n_periode` items.

### 5. Files and Code
- **Edited Files:**
  - `c:\laragon\www\alanv2\includes\pages\des.php`: Updated logic for `target_peramalan`, forecasting breakdown loops ($m=1..7$ vs $m=1..4$), UI text labels, and Alpha dropdown.
  - `c:\laragon\www\alanv2\includes\pages\des_kategori.php`: Overwritten completely to mirror the updated `des.php` logic but utilizing `id_kategori` grouping instead of `kode_barang`.
  - `c:\laragon\www\alanv2\seed.php`: Created to generate low-volume (3-5 items/day) realistic test data and executed directly on the local database.

### 6. Current Work and Next Steps
- **Current State:** The user pointed out that the items the agent mentioned (`BRG-100`, `BRG-032`) from their own SQL dump do not exist in the app's dropdown. 
- **Next Steps:** 
  1. Acknowledge the user's screenshot.
  2. Explain *why* this happened: The SQL dump they pasted for `tbl_detail_penjualan` uses codes like `BRG-111` and `BRG-071`, but their `tbl_barang` has `MYBSC01` and `RNSL01`. Because the codes don't match, they don't appear in the dropdown or join correctly.
  3. Re-orient them: Remind them that the `seed.php` script ran *before* their SQL paste successfully injected data for the *real* items (`MYBSC01`, `RNSL01`, etc). They can just select `RNSL05` or `HLM01` from the dropdown right now to test the system, as those items now have 3 months of realistic low-volume history.

You have the 6 following artifacts written to the artifacts directory:

[ARTIFACT: media_1790528652017]
Path: file:///C:/Users/US3R/.gemini/antigravity/brain/5fc65fb9-6663-462c-8da4-4eb78312f1b6/.user_uploaded/media_1790528652017.png
Last Edited: 2026-09-27T17:04:25Z

[ARTIFACT: media_1790536558093]
Path: file:///C:/Users/US3R/.gemini/antigravity/brain/5fc65fb9-6663-462c-8da4-4eb78312f1b6/.user_uploaded/media_1790536558093.png
Last Edited: 2026-09-27T19:16:20Z

[ARTIFACT: media_1790539946103]
Path: file:///C:/Users/US3R/.gemini/antigravity/brain/5fc65fb9-6663-462c-8da4-4eb78312f1b6/.user_uploaded/media_1790539946103.png
Last Edited: 2026-09-27T20:12:43Z

[ARTIFACT: media_1790611931451]
Path: file:///C:/Users/US3R/.gemini/antigravity/brain/5fc65fb9-6663-462c-8da4-4eb78312f1b6/.user_uploaded/media_1790611931451.png
Last Edited: 2026-09-28T16:12:51Z

[ARTIFACT: media_1790615967066]
Path: file:///C:/Users/US3R/.gemini/antigravity/brain/5fc65fb9-6663-462c-8da4-4eb78312f1b6/.user_uploaded/media_1790615967066.png
Last Edited: 2026-09-28T17:19:29Z

[ARTIFACT: media_1790625025717]
Path: file:///C:/Users/US3R/.gemini/antigravity/brain/5fc65fb9-6663-462c-8da4-4eb78312f1b6/.user_uploaded/media_1790625025717.png
Last Edited: 2026-09-28T19:50:33Z

# Conversation Logs

Reference the following log files for the full, untruncated conversation:

- C:\Users\US3R\.gemini\antigravity\brain\5fc65fb9-6663-462c-8da4-4eb78312f1b6\.system_generated\logs\transcript.jsonl

**IMPORTANT: this summary is just for your reference. You may respond to my previous and future messages, but DO NOT ACKNOWLEDGE THIS CHECKPOINT MESSAGE. JUST READ IT BUT DO NOT MENTION IT, RESPOND TO IT, OR TAKE ACTION BECAUSE OF IT.**<USER_REQUEST>
aku  mau langsung di live soalnya clientku mau langsung testing juga di tabletnya dengan database yang sekarang tuh kek gini

-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Waktu pembuatan: 29 Sep 2026 pada 03.53
-- Versi server: 10.11.10-MariaDB-log
-- Versi PHP: 8.3.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Basis data: `alan`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_barang`
--

CREATE TABLE `tbl_barang` (
  `id_barang` int(11) NOT NULL,
  `kode_barang` varchar(20) NOT NULL,
  `nama_produk` varchar(100) NOT NULL,
  `kategori` varchar(50) NOT NULL,
  `harga_beli` int(11) NOT NULL DEFAULT 0,
  `harga_jual` int(11) NOT NULL DEFAULT 0,
  `stok_min` int(11) NOT NULL,
  `satuan` varchar(20) DEFAULT 'Pcs',
  `deskripsi` text DEFAULT NULL,
  `stok_aktual` int(11) NOT NULL,
  `foto_barang` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `tbl_barang`
--

INSERT INTO `tbl_barang` (`id_barang`, `kode_barang`, `nama_produk`, `kategori`, `harga_beli`, `harga_jual`, `stok_min`, `satuan`, `deskripsi`, `stok_aktual`, `foto_barang`) VALUES
(9, 'MYBSC01', 'MYBASIC TSHIRT CUTTINGAN BOXY', 'pakaian', 100, 130, 0, 'Pcs', '', 5, '1790208908_59280.png'),
(10, 'RNSL01', 'RANSEL CURDUROY (MAROON)', 'RANSEL', 110, 150, 1, 'Pcs', 'Warna: MAROON', 2, '1790209604_58336.jpg'),
(11, 'RNSL02', 'RANSEL CURDUROY (BLCK)', 'RANSEL', 110, 130, 1, 'Pcs', 'Warna: BLCK', 5, '1790209686_58350.jpg'),
(12, 'RNSL03', 'RANSEL CURDUROY (ARMY)', 'RANSEL', 110, 130, 1, 'Pcs', 'Warna: ARMY', 5, '1790209741_58340.jpg'),
(13, 'RNSL04', 'RANSEL CURDUROY (NAVY)', 'RANSEL', 110, 130, 1, 'Pcs', '', 5, '1790209792_58346.jpg'),
(14, 'RNSL05', 'RANSEL CURDUROY (ABU)', 'RANSEL', 110, 130, 1, 'Pcs', 'Warna: ABU', 5, '1790209853_58352.jpg'),
(15, 'RNSL06', 'RANSEL OURIST (NAVY)', 'RANSEL', 130000, 150000, 1, 'Pcs', '', 2, '1790211188_36484.jpg'),
(16, 'SLMPNG01', 'TAS SELEMPANG CURDUROY SMALL (KECIL)', 'TAS SELEMPANG', 60000, 100000, 2, 'Pcs', 'Warna: BLCK', 9, '1790211340_59022.png'),
(17, 'SLMPNG02', 'TAS SELEMPANG CURDUROY SMALL (KECIL)', 'TAS SELEMPANG', 60000, 100000, 5, 'Pcs', 'Warna: ABU', 9, '1790211416_59022.png'),
(18, 'HLM01', 'HELM CLASSIC', 'HELM', 95000, 130000, 8, 'Pcs', '', 5, '1790211497_59327.jpg');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_detail_penjualan`
--

CREATE TABLE `tbl_detail_penjualan` (
  `id_detail` int(11) NOT NULL,
  `id_penjualan` int(11) NOT NULL,
  `kode_barang` varchar(20) NOT NULL,
  `harga_satuan` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `subtotal` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `tbl_detail_penjualan`
--

INSERT INTO `tbl_detail_penjualan` (`id_detail`, `id_penjualan`, `kode_barang`, `harga_satuan`, `qty`, `subtotal`) VALUES
(256, 400, 'SLMPNG02', 100000, 2, 200000),
(257, 400, 'RNSL05', 130000, 1, 130000),
(258, 401, 'RNSL06', 150000, 3, 450000),
(259, 402, 'RNSL02', 130000, 3, 390000),
(260, 402, 'MYBSC01', 130000, 2, 260000),
(261, 402, 'HLM01', 130000, 3, 390000),
(262, 403, 'RNSL06', 150000, 1, 150000),
(263, 403, 'RNSL04', 130000, 3, 390000),
(264, 404, 'RNSL04', 130000, 1, 130000),
(265, 405, 'RNSL05', 130000, 3, 390000),
(266, 405, 'RNSL02', 130000, 1, 130000),
(267, 405, 'RNSL06', 150000, 3, 450000),
(268, 406, 'SLMPNG01', 100000, 3, 300000),
(269, 406, 'RNSL06', 150000, 3, 450000),
(270, 406, 'MYBSC01', 130000, 1, 130000),
(271, 407, 'RNSL05', 130000, 3, 390000),
(272, 408, 'MYBSC01', 130000, 1, 130000),
(273, 408, 'RNSL05', 130000, 1, 130000),
(274, 409, 'RNSL04', 130000, 3, 390000),
(275, 409, 'RNSL02', 130000, 2, 260000),
(276, 409, 'SLMPNG02', 100000, 2, 200000),
(277, 410, 'SLMPNG02', 100000, 2, 200000),
(278, 411, 'RNSL06', 150000, 1, 150000),
(279, 411, 'RNSL04', 130000, 1, 130000),
(280, 411, 'SLMPNG01', 100000, 3, 300000),
(281, 412, 'RNSL01', 150000, 3, 450000),
(282, 412, 'RNSL05', 130000, 1, 130000),
(283, 413, 'HLM01', 130000, 1, 130000),
(284, 414, 'HLM01', 130000, 2, 260000),
(285, 415, 'RNSL05', 130000, 3, 390000),
(286, 416, 'RNSL05', 130000, 3, 390000),
(287, 416, 'RNSL06', 150000, 2, 300000),
(288, 416, 'RNSL04', 130000, 2, 260000),
(289, 417, 'MYBSC01', 130000, 3, 390000),
(290, 417, 'SLMPNG02', 100000, 1, 100000),
(291, 418, 'HLM01', 130000, 3, 390000),
(292, 419, 'HLM01', 130000, 1, 130000),
(293, 419, 'MYBSC01', 130000, 2, 260000),
(294, 420, 'SLMPNG01', 100000, 3, 300000),
(295, 420, 'MYBSC01', 130000, 2, 260000),
(296, 421, 'SLMPNG01', 100000, 3, 300000),
(297, 422, 'RNSL05', 130000, 1, 130000),
(298, 423, 'RNSL04', 130000, 1, 130000),
(299, 423, 'MYBSC01', 130000, 1, 130000),
(300, 424, 'RNSL02', 130000, 3, 390000),
(301, 425, 'SLMPNG01', 100000, 3, 300000),
(302, 425, 'RNSL05', 130000, 1, 130000),
(303, 425, 'RNSL02', 130000, 3, 390000),
(304, 426, 'RNSL03', 130000, 3, 390000),
(305, 427, 'HLM01', 130000, 1, 130000),
(306, 427, 'RNSL01', 150000, 2, 300000),
(307, 428, 'RNSL03', 130000, 2, 260000),
(308, 428, 'RNSL05', 130000, 3, 390000),
(309, 428, 'MYBSC01', 130000, 3, 390000),
(310, 429, 'RNSL03', 130000, 2, 260000),
(311, 430, 'SLMPNG02', 100000, 1, 100000),
(312, 430, 'RNSL02', 130000, 3, 390000),
(313, 430, 'RNSL04', 130000, 1, 130000),
(314, 431, 'RNSL06', 150000, 1, 150000),
(315, 431, 'RNSL01', 150000, 1, 150000),
(316, 432, 'RNSL03', 130000, 2, 260000),
(317, 432, 'SLMPNG01', 100000, 1, 100000),
(318, 432, 'RNSL06', 150000, 3, 450000),
(319, 433, 'RNSL01', 150000, 1, 150000),
(320, 433, 'RNSL04', 130000, 1, 130000),
(321, 434, 'RNSL06', 150000, 2, 300000),
(322, 434, 'RNSL02', 130000, 1, 130000),
(323, 435, 'MYBSC01', 130000, 2, 260000),
(324, 435, 'RNSL01', 150000, 1, 150000),
(325, 435, 'RNSL05', 130000, 3, 390000),
(326, 436, 'HLM01', 130000, 3, 390000),
(327, 437, 'MYBSC01', 130000, 1, 130000),
(328, 437, 'RNSL02', 130000, 2, 260000),
(329, 438, 'HLM01', 130000, 3, 390000),
(330, 438, 'RNSL05', 130000, 2, 260000),
(331, 438, 'MYBSC01', 130000, 1, 130000),
(332, 439, 'RNSL06', 150000, 1, 150000),
(333, 439, 'MYBSC01', 130000, 2, 260000),
(334, 440, 'HLM01', 130000, 2, 260000),
(335, 440, 'RNSL04', 130000, 1, 130000),
(336, 441, 'RNSL02', 130000, 2, 260000),
(337, 441, 'MYBSC01', 130000, 3, 390000),
(338, 442, 'RNSL04', 130000, 1, 130000),
(339, 442, 'RNSL01', 150000, 1, 150000),
(340, 443, 'RNSL02', 130000, 2, 260000),
(341, 443, 'RNSL06', 150000, 2, 300000),
(342, 444, 'RNSL04', 130000, 4, 520000),
(343, 444, 'RNSL05', 130000, 4, 520000),
(344, 445, 'RNSL02', 130000, 3, 390000),
(345, 446, 'RNSL05', 130000, 3, 390000),
(346, 446, 'SLMPNG01', 100000, 2, 200000),
(347, 446, 'SLMPNG02', 100000, 4, 400000),
(348, 447, 'RNSL05', 130000, 2, 260000),
(349, 448, 'RNSL06', 150000, 4, 600000),
(350, 449, 'SLMPNG02', 100000, 4, 400000),
(351, 449, 'RNSL04', 130000, 2, 260000),
(352, 450, 'RNSL02', 130000, 4, 520000),
(353, 450, 'RNSL06', 150000, 3, 450000),
(354, 451, 'RNSL01', 150000, 2, 300000),
(355, 451, 'HLM01', 130000, 3, 390000),
(356, 451, 'SLMPNG02', 100000, 2, 200000),
(357, 452, 'RNSL06', 150000, 4, 600000),
(358, 452, 'SLMPNG01', 100000, 4, 400000),
(359, 453, 'SLMPNG01', 100000, 2, 200000),
(360, 454, 'RNSL05', 130000, 3, 390000),
(361, 454, 'HLM01', 130000, 4, 520000),
(362, 454, 'RNSL03', 130000, 4, 520000),
(363, 455, 'RNSL05', 130000, 3, 390000),
(364, 455, 'SLMPNG01', 100000, 2, 200000),
(365, 455, 'RNSL06', 150000, 3, 450000),
(366, 456, 'RNSL01', 150000, 3, 450000),
(367, 456, 'SLMPNG01', 100000, 2, 200000),
(368, 457, 'RNSL05', 130000, 2, 260000),
(369, 457, 'RNSL04', 130000, 2, 260000),
(370, 458, 'RNSL04', 130000, 2, 260000),
(371, 458, 'SLMPNG01', 100000, 4, 400000),
(372, 459, 'RNSL02', 130000, 3, 390000),
(373, 460, 'RNSL04', 130000, 3, 390000),
(374, 461, 'RNSL02', 130000, 4, 520000),
(375, 461, 'SLMPNG02', 100000, 4, 400000),
(376, 462, 'RNSL02', 130000, 3, 390000),
(377, 462, 'SLMPNG02', 100000, 4, 400000),
(378, 463, 'SLMPNG01', 100000, 3, 300000),
(379, 463, 'RNSL06', 150000, 3, 450000),
(380, 464, 'SLMPNG02', 100000, 2, 200000),
(381, 464, 'RNSL02', 130000, 2, 260000),
(382, 465, 'RNSL03', 130000, 4, 520000),
(383, 465, 'RNSL02', 130000, 2, 260000),
(384, 465, 'RNSL01', 150000, 3, 450000),
(385, 466, 'RNSL01', 150000, 3, 450000),
(386, 467, 'MYBSC01', 130000, 2, 260000),
(387, 467, 'SLMPNG01', 100000, 4, 400000),
(388, 468, 'SLMPNG02', 100000, 3, 300000),
(389, 468, 'RNSL06', 150000, 4, 600000),
(390, 468, 'SLMPNG01', 100000, 4, 400000),
(391, 469, 'SLMPNG02', 100000, 4, 400000),
(392, 469, 'RNSL06', 150000, 2, 300000),
(393, 469, 'RNSL05', 130000, 3, 390000),
(394, 470, 'RNSL03', 130000, 4, 520000),
(395, 471, 'RNSL06', 150000, 4, 600000),
(396, 472, 'MYBSC01', 130000, 3, 390000),
(397, 473, 'SLMPNG01', 100000, 2, 200000),
(398, 473, 'RNSL01', 150000, 2, 300000),
(399, 474, 'RNSL01', 150000, 4, 600000),
(400, 474, 'RNSL06', 150000, 2, 300000),
(401, 474, 'MYBSC01', 130000, 2, 260000),
(402, 475, 'MYBSC01', 130000, 3, 390000),
(403, 475, 'RNSL03', 130000, 3, 390000),
(404, 475, 'RNSL01', 150000, 4, 600000),
(405, 476, 'HLM01', 130000, 3, 390000),
(406, 477, 'RNSL04', 130000, 3, 390000),
(407, 478, 'MYBSC01', 130000, 3, 390000),
(408, 479, 'RNSL02', 130000, 2, 260000),
(409, 480, 'SLMPNG02', 100000, 3, 300000),
(410, 480, 'RNSL04', 130000, 4, 520000),
(411, 480, 'RNSL05', 130000, 3, 390000),
(412, 481, 'RNSL06', 150000, 2, 300000),
(413, 481, 'RNSL04', 130000, 4, 520000),
(414, 481, 'MYBSC01', 130000, 2, 260000),
(415, 482, 'MYBSC01', 130000, 4, 520000),
(416, 483, 'HLM01', 130000, 2, 260000),
(417, 483, 'RNSL01', 150000, 2, 300000),
(418, 483, 'SLMPNG02', 100000, 4, 400000),
(419, 484, 'RNSL05', 130000, 4, 520000),
(420, 485, 'RNSL05', 130000, 4, 520000),
(421, 485, 'RNSL06', 150000, 4, 600000),
(422, 486, 'RNSL04', 130000, 3, 390000),
(423, 487, 'RNSL05', 130000, 4, 520000),
(424, 487, 'RNSL01', 150000, 3, 450000),
(425, 487, 'RNSL03', 130000, 4, 520000),
(426, 488, 'RNSL02', 130000, 4, 520000),
(427, 489, 'HLM01', 130000, 2, 260000),
(428, 489, 'RNSL03', 130000, 2, 260000),
(429, 490, 'RNSL02', 130000, 2, 260000),
(430, 490, 'HLM01', 130000, 4, 520000),
(431, 491, 'MYBSC01', 130000, 3, 390000),
(432, 491, 'RNSL01', 150000, 4, 600000),
(433, 491, 'HLM01', 130000, 4, 520000),
(434, 492, 'RNSL01', 150000, 4, 600000),
(435, 493, 'SLMPNG02', 100000, 2, 200000),
(436, 494, 'RNSL02', 130000, 4, 520000),
(437, 494, 'SLMPNG02', 100000, 4, 400000),
(438, 495, 'RNSL05', 130000, 2, 260000),
(439, 495, 'RNSL06', 150000, 2, 300000),
(440, 495, 'RNSL04', 130000, 2, 260000),
(441, 496, 'RNSL05', 130000, 2, 260000),
(442, 496, 'RNSL06', 150000, 4, 600000),
(443, 496, 'RNSL02', 130000, 4, 520000),
(444, 497, 'SLMPNG01', 100000, 2, 200000),
(445, 497, 'SLMPNG02', 100000, 3, 300000),
(446, 497, 'RNSL01', 150000, 2, 300000),
(447, 498, 'MYBSC01', 130000, 2, 260000),
(448, 498, 'HLM01', 130000, 4, 520000),
(449, 498, 'RNSL04', 130000, 3, 390000),
(450, 499, 'MYBSC01', 130000, 4, 520000),
(451, 500, 'HLM01', 130000, 3, 390000),
(452, 500, 'SLMPNG02', 100000, 4, 400000),
(453, 501, 'RNSL02', 130000, 2, 260000),
(454, 502, 'RNSL01', 150000, 2, 300000),
(455, 502, 'SLMPNG01', 100000, 3, 300000),
(456, 503, 'SLMPNG01', 100000, 3, 300000),
(457, 503, 'RNSL01', 150000, 4, 600000),
(458, 504, 'SLMPNG01', 100000, 3, 300000),
(459, 504, 'MYBSC01', 130000, 2, 260000),
(460, 504, 'HLM01', 130000, 3, 390000),
(461, 505, 'RNSL02', 130000, 3, 390000),
(462, 505, 'HLM01', 130000, 4, 520000),
(463, 505, 'SLMPNG01', 100000, 3, 300000),
(464, 506, 'MYBSC01', 130000, 4, 520000),
(465, 506, 'HLM01', 130000, 4, 520000),
(466, 506, 'SLMPNG01', 100000, 2, 200000),
(467, 507, 'HLM01', 130000, 3, 390000),
(468, 507, 'RNSL05', 130000, 3, 390000),
(469, 508, 'RNSL05', 130000, 2, 260000),
(470, 508, 'RNSL06', 150000, 2, 300000),
(471, 508, 'RNSL01', 150000, 2, 300000),
(472, 509, 'RNSL06', 150000, 3, 450000),
(473, 509, 'RNSL04', 130000, 3, 390000),
(474, 510, 'RNSL01', 150000, 3, 450000),
(475, 511, 'SLMPNG02', 100000, 2, 200000),
(476, 511, 'RNSL05', 130000, 4, 520000),
(477, 511, 'SLMPNG01', 100000, 3, 300000),
(478, 512, 'HLM01', 130000, 3, 390000),
(479, 512, 'RNSL01', 150000, 4, 600000),
(480, 512, 'RNSL04', 130000, 2, 260000),
(481, 513, 'MYBSC01', 130000, 2, 260000),
(482, 513, 'RNSL05', 130000, 4, 520000),
(483, 514, 'SLMPNG02', 100000, 4, 400000),
(484, 514, 'MYBSC01', 130000, 2, 260000),
(485, 515, 'RNSL01', 150000, 3, 450000),
(486, 515, 'RNSL04', 130000, 5, 650000),
(487, 515, 'SLMPNG01', 100000, 5, 500000),
(488, 516, 'HLM01', 130000, 4, 520000),
(489, 516, 'MYBSC01', 130000, 5, 650000),
(490, 516, 'RNSL01', 150000, 4, 600000),
(491, 517, 'MYBSC01', 130000, 5, 650000),
(492, 518, 'SLMPNG01', 100000, 4, 400000),
(493, 518, 'MYBSC01', 130000, 4, 520000),
(494, 518, 'HLM01', 130000, 4, 520000),
(495, 519, 'RNSL06', 150000, 5, 750000),
(496, 519, 'RNSL02', 130000, 4, 520000),
(497, 520, 'MYBSC01', 130000, 5, 650000),
(498, 520, 'RNSL02', 130000, 3, 390000),
(499, 520, 'RNSL05', 130000, 3, 390000),
(500, 521, 'SLMPNG02', 100000, 3, 300000),
(501, 521, 'SLMPNG01', 100000, 5, 500000),
(502, 522, 'SLMPNG02', 100000, 3, 300000),
(503, 522, 'RNSL06', 150000, 4, 600000),
(504, 523, 'RNSL05', 130000, 4, 520000),
(505, 523, 'RNSL04', 130000, 3, 390000),
(506, 523, 'RNSL02', 130000, 3, 390000),
(507, 524, 'RNSL01', 150000, 5, 750000),
(508, 525, 'RNSL02', 130000, 4, 520000),
(509, 526, 'RNSL05', 130000, 4, 520000),
(510, 526, 'SLMPNG01', 100000, 5, 500000),
(511, 526, 'MYBSC01', 130000, 3, 390000),
(512, 527, 'RNSL03', 130000, 5, 650000),
(513, 527, 'RNSL05', 130000, 5, 650000),
(514, 528, 'RNSL04', 130000, 4, 520000),
(515, 528, 'MYBSC01', 130000, 3, 390000),
(516, 528, 'SLMPNG01', 100000, 5, 500000),
(517, 529, 'SLMPNG01', 100000, 5, 500000),
(518, 529, 'RNSL01', 150000, 3, 450000),
(519, 530, 'SLMPNG02', 100000, 3, 300000),
(520, 530, 'SLMPNG01', 100000, 3, 300000),
(521, 531, 'RNSL05', 130000, 5, 650000),
(522, 531, 'SLMPNG02', 100000, 3, 300000),
(523, 531, 'RNSL01', 150000, 4, 600000),
(524, 532, 'RNSL06', 150000, 3, 450000),
(525, 532, 'RNSL02', 130000, 5, 650000),
(526, 533, 'HLM01', 130000, 5, 650000),
(527, 534, 'RNSL04', 130000, 3, 390000),
(528, 534, 'SLMPNG01', 100000, 3, 300000),
(529, 534, 'RNSL03', 130000, 5, 650000),
(530, 535, 'MYBSC01', 130000, 3, 390000),
(531, 535, 'SLMPNG01', 100000, 5, 500000),
(532, 536, 'SLMPNG01', 100000, 5, 500000),
(533, 536, 'RNSL03', 130000, 4, 520000),
(534, 536, 'SLMPNG02', 100000, 5, 500000),
(535, 537, 'RNSL06', 150000, 5, 750000),
(536, 537, 'RNSL05', 130000, 4, 520000),
(537, 537, 'RNSL03', 130000, 5, 650000),
(538, 538, 'RNSL05', 130000, 3, 390000),
(539, 538, 'HLM01', 130000, 4, 520000),
(540, 538, 'RNSL04', 130000, 3, 390000),
(541, 539, 'RNSL04', 130000, 4, 520000),
(542, 539, 'MYBSC01', 130000, 5, 650000),
(543, 540, 'SLMPNG01', 100000, 4, 400000),
(544, 541, 'SLMPNG01', 100000, 3, 300000),
(545, 542, 'HLM01', 130000, 4, 520000),
(546, 543, 'MYBSC01', 130000, 4, 520000),
(547, 543, 'SLMPNG01', 100000, 5, 500000),
(548, 543, 'SLMPNG02', 100000, 5, 500000),
(549, 544, 'RNSL04', 130000, 4, 520000),
(550, 544, 'RNSL05', 130000, 4, 520000),
(551, 544, 'RNSL01', 150000, 3, 450000),
(552, 545, 'RNSL02', 130000, 4, 520000),
(553, 545, 'RNSL05', 130000, 3, 390000),
(554, 546, 'RNSL05', 130000, 5, 650000),
(555, 546, 'MYBSC01', 130000, 3, 390000),
(556, 546, 'RNSL06', 150000, 4, 600000),
(557, 547, 'RNSL02', 130000, 4, 520000),
(558, 547, 'SLMPNG02', 100000, 4, 400000),
(559, 547, 'RNSL04', 130000, 4, 520000),
(560, 548, 'HLM01', 130000, 5, 650000),
(561, 549, 'HLM01', 130000, 3, 390000),
(562, 549, 'RNSL02', 130000, 4, 520000),
(563, 550, 'HLM01', 130000, 4, 520000),
(564, 550, 'RNSL02', 130000, 5, 650000),
(565, 551, 'SLMPNG02', 100000, 3, 300000),
(566, 551, 'RNSL06', 150000, 3, 450000),
(567, 551, 'RNSL05', 130000, 3, 390000),
(568, 552, 'RNSL05', 130000, 5, 650000),
(569, 553, 'SLMPNG01', 100000, 5, 500000),
(570, 553, 'RNSL06', 150000, 4, 600000),
(571, 553, 'SLMPNG02', 100000, 4, 400000),
(572, 554, 'HLM01', 130000, 5, 650000),
(573, 555, 'SLMPNG02', 100000, 4, 400000),
(574, 555, 'RNSL01', 150000, 5, 750000),
(575, 556, 'HLM01', 130000, 5, 650000),
(576, 556, 'MYBSC01', 130000, 5, 650000),
(577, 557, 'HLM01', 130000, 3, 390000),
(578, 557, 'SLMPNG01', 100000, 3, 300000),
(579, 557, 'RNSL02', 130000, 5, 650000),
(580, 558, 'RNSL06', 150000, 3, 450000),
(581, 558, 'HLM01', 130000, 4, 520000),
(582, 558, 'RNSL04', 130000, 4, 520000),
(583, 559, 'RNSL01', 150000, 3, 450000),
(584, 560, 'RNSL02', 130000, 3, 390000),
(585, 561, 'RNSL01', 150000, 5, 750000),
(586, 561, 'RNSL03', 130000, 5, 650000),
(587, 562, 'RNSL06', 150000, 3, 450000),
(588, 563, 'HLM01', 130000, 4, 520000),
(589, 563, 'RNSL05', 130000, 3, 390000),
(590, 563, 'SLMPNG02', 100000, 3, 300000),
(591, 564, 'RNSL04', 130000, 5, 650000),
(592, 565, 'RNSL01', 150000, 5, 750000),
(593, 566, 'RNSL03', 130000, 5, 650000),
(594, 566, 'RNSL05', 130000, 3, 390000),
(595, 566, 'SLMPNG01', 100000, 4, 400000),
(596, 567, 'RNSL05', 130000, 3, 390000),
(597, 567, 'RNSL01', 150000, 3, 450000),
(598, 567, 'MYBSC01', 130000, 4, 520000),
(599, 568, 'HLM01', 130000, 4, 520000),
(600, 569, 'RNSL03', 130000, 5, 650000),
(601, 570, 'RNSL05', 130000, 3, 390000),
(602, 570, 'RNSL04', 130000, 5, 650000),
(603, 570, 'HLM01', 130000, 3, 390000),
(604, 571, 'RNSL03', 130000, 5, 650000),
(605, 571, 'MYBSC01', 130000, 3, 390000),
(606, 571, 'RNSL01', 150000, 5, 750000),
(607, 572, 'RNSL02', 130000, 5, 650000),
(608, 573, 'RNSL02', 130000, 3, 390000),
(609, 574, 'RNSL06', 150000, 3, 450000),
(610, 575, 'RNSL05', 130000, 5, 650000),
(611, 575, 'SLMPNG02', 100000, 4, 400000),
(612, 576, 'RNSL02', 130000, 3, 390000),
(613, 576, 'RNSL05', 130000, 3, 390000),
(614, 577, 'RNSL04', 130000, 3, 390000),
(615, 577, 'SLMPNG01', 100000, 4, 400000),
(616, 578, 'RNSL01', 150000, 3, 450000),
(617, 578, 'RNSL02', 130000, 4, 520000),
(618, 579, 'RNSL04', 130000, 4, 520000),
(619, 579, 'HLM01', 130000, 4, 520000),
(620, 580, 'RNSL03', 130000, 5, 650000),
(621, 580, 'SLMPNG02', 100000, 4, 400000),
(622, 580, 'RNSL05', 130000, 4, 520000),
(623, 581, 'RNSL04', 130000, 5, 650000),
(624, 582, 'HLM01', 130000, 4, 520000),
(625, 583, 'MYBSC01', 130000, 5, 650000),
(626, 583, 'SLMPNG02', 100000, 5, 500000),
(627, 583, 'RNSL03', 130000, 4, 520000),
(628, 584, 'SLMPNG01', 100000, 6, 600000),
(629, 585, 'RNSL04', 130000, 6, 780000),
(630, 585, 'RNSL01', 150000, 5, 750000),
(631, 585, 'MYBSC01', 130000, 6, 780000),
(632, 586, 'SLMPNG02', 100000, 5, 500000),
(633, 586, 'RNSL02', 130000, 6, 780000),
(634, 586, 'RNSL04', 130000, 4, 520000),
(635, 587, 'HLM01', 130000, 4, 520000),
(636, 587, 'RNSL06', 150000, 6, 900000),
(637, 588, 'RNSL05', 130000, 4, 520000),
(638, 589, 'RNSL01', 150000, 4, 600000),
(639, 590, 'SLMPNG01', 100000, 4, 400000),
(640, 591, 'RNSL03', 130000, 6, 780000),
(641, 592, 'SLMPNG02', 100000, 6, 600000),
(642, 593, 'SLMPNG01', 100000, 5, 500000),
(643, 593, 'MYBSC01', 130000, 4, 520000),
(644, 593, 'RNSL01', 150000, 6, 900000),
(645, 594, 'SLMPNG01', 100000, 5, 500000),
(646, 594, 'RNSL05', 130000, 4, 520000),
(647, 595, 'RNSL02', 130000, 6, 780000),
(648, 595, 'MYBSC01', 130000, 6, 780000),
(649, 596, 'RNSL01', 150000, 6, 900000),
(650, 596, 'RNSL03', 130000, 4, 520000),
(651, 597, 'RNSL06', 150000, 4, 600000),
(652, 597, 'RNSL04', 130000, 6, 780000),
(653, 598, 'RNSL03', 130000, 6, 780000),
(654, 599, 'RNSL05', 130000, 4, 520000),
(655, 599, 'RNSL01', 150000, 6, 900000),
(656, 600, 'SLMPNG02', 100000, 6, 600000),
(657, 601, 'SLMPNG02', 100000, 5, 500000),
(658, 601, 'RNSL05', 130000, 6, 780000),
(659, 601, 'RNSL01', 150000, 5, 750000),
(660, 602, 'RNSL02', 130000, 4, 520000),
(661, 603, 'MYBSC01', 130000, 6, 780000),
(662, 603, 'RNSL04', 130000, 6, 780000),
(663, 604, 'MYBSC01', 130000, 4, 520000),
(664, 604, 'RNSL02', 130000, 6, 780000),
(665, 605, 'RNSL01', 150000, 4, 600000),
(666, 606, 'SLMPNG02', 100000, 6, 600000),
(667, 607, 'RNSL04', 130000, 4, 520000),
(668, 608, 'RNSL01', 150000, 4, 600000),
(669, 608, 'RNSL04', 130000, 5, 650000),
(670, 609, 'RNSL02', 130000, 6, 780000),
(671, 609, 'RNSL06', 150000, 6, 900000),
(672, 609, 'RNSL01', 150000, 6, 900000),
(673, 610, 'RNSL04', 130000, 4, 520000),
(674, 610, 'RNSL06', 150000, 5, 750000);

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_kategori`
--

CREATE TABLE `tbl_kategori` (
  `id_kategori` int(11) NOT NULL,
  `nama_kategori` varchar(100) NOT NULL,
  `foto_kategori` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `tbl_kategori`
--

INSERT INTO `tbl_kategori` (`id_kategori`, `nama_kategori`, `foto_kategori`) VALUES
(6, 'ATASAN', '1790208985_55609.jpg'),
(7, 'SHORT PANTS', '1790209174_59011.png'),
(8, 'TAS SELEMPANG', '1790209272_59022.png'),
(9, 'SENDAL', '1790209246_40961.jpg'),
(10, 'HELM', '1790209107_59327.jpg'),
(11, 'RANSEL', '1790209322_58358.jpg'),
(12, 'PARFUM', '1790209406_40103.jpg');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_penjualan`
--

CREATE TABLE `tbl_penjualan` (
  `id_penjualan` int(11) NOT NULL,
  `no_faktur` varchar(30) NOT NULL,
  `tanggal_waktu` datetime NOT NULL,
  `total_item` int(11) NOT NULL,
  `grand_total` int(11) NOT NULL,
  `nominal_bayar` int(11) NOT NULL,
  `kembalian` int(11) NOT NULL,
  `metode_pembayaran` varchar(50) DEFAULT 'Cash'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `tbl_penjualan`
--

INSERT INTO `tbl_penjualan` (`id_penjualan`, `no_faktur`, `tanggal_waktu`, `total_item`, `grand_total`, `nominal_bayar`, `kembalian`, `metode_pembayaran`) VALUES
(400, 'INV-202607-795800', '2026-07-01 18:06:00', 3, 330000, 330000, 0, 'Cash'),
(401, 'INV-202607-749710', '2026-07-01 14:47:00', 3, 450000, 450000, 0, 'Cash'),
(402, 'INV-202607-286501', '2026-07-02 15:34:00', 8, 1040000, 1040000, 0, 'Cash'),
(403, 'INV-202607-713111', '2026-07-02 18:34:00', 4, 540000, 540000, 0, 'Cash'),
(404, 'INV-202607-192221', '2026-07-02 16:53:00', 1, 130000, 130000, 0, 'Cash'),
(405, 'INV-202607-928802', '2026-07-03 09:48:00', 7, 970000, 970000, 0, 'Cash'),
(406, 'INV-202607-297912', '2026-07-03 14:19:00', 7, 880000, 880000, 0, 'Cash'),
(407, 'INV-202607-596322', '2026-07-03 13:04:00', 3, 390000, 390000, 0, 'Cash'),
(408, 'INV-202607-102703', '2026-07-04 11:54:00', 2, 260000, 260000, 0, 'Cash'),
(409, 'INV-202607-901113', '2026-07-04 11:47:00', 7, 850000, 850000, 0, 'Cash'),
(410, 'INV-202607-427723', '2026-07-04 13:34:00', 2, 200000, 200000, 0, 'Cash'),
(411, 'INV-202607-435333', '2026-07-04 14:19:00', 5, 580000, 580000, 0, 'Cash'),
(412, 'INV-202607-211904', '2026-07-05 09:36:00', 4, 580000, 580000, 0, 'Cash'),
(413, 'INV-202607-682514', '2026-07-05 14:36:00', 1, 130000, 130000, 0, 'Cash'),
(414, 'INV-202607-683924', '2026-07-05 17:49:00', 2, 260000, 260000, 0, 'Cash'),
(415, 'INV-202607-663305', '2026-07-06 14:22:00', 3, 390000, 390000, 0, 'Cash'),
(416, 'INV-202607-595015', '2026-07-06 09:47:00', 7, 950000, 950000, 0, 'Cash'),
(417, 'INV-202607-297725', '2026-07-06 13:21:00', 4, 490000, 490000, 0, 'Cash'),
(418, 'INV-202607-630206', '2026-07-07 17:10:00', 3, 390000, 390000, 0, 'Cash'),
(419, 'INV-202607-651416', '2026-07-07 18:29:00', 3, 390000, 390000, 0, 'Cash'),
(420, 'INV-202607-542507', '2026-07-08 13:11:00', 5, 560000, 560000, 0, 'Cash'),
(421, 'INV-202607-799917', '2026-07-08 13:55:00', 3, 300000, 300000, 0, 'Cash'),
(422, 'INV-202607-281027', '2026-07-08 13:31:00', 1, 130000, 130000, 0, 'Cash'),
(423, 'INV-202607-565108', '2026-07-09 11:34:00', 2, 260000, 260000, 0, 'Cash'),
(424, 'INV-202607-162918', '2026-07-09 10:13:00', 3, 390000, 390000, 0, 'Cash'),
(425, 'INV-202607-368628', '2026-07-09 15:34:00', 7, 820000, 820000, 0, 'Cash'),
(426, 'INV-202607-415038', '2026-07-09 20:50:00', 3, 390000, 390000, 0, 'Cash'),
(427, 'INV-202607-765309', '2026-07-10 13:10:00', 3, 430000, 430000, 0, 'Cash'),
(428, 'INV-202607-897519', '2026-07-10 10:37:00', 8, 1040000, 1040000, 0, 'Cash'),
(429, 'INV-202607-496929', '2026-07-10 15:07:00', 2, 260000, 260000, 0, 'Cash'),
(430, 'INV-202607-771239', '2026-07-10 13:47:00', 5, 620000, 620000, 0, 'Cash'),
(431, 'INV-202607-7350010', '2026-07-11 19:48:00', 2, 300000, 300000, 0, 'Cash'),
(432, 'INV-202607-2349110', '2026-07-11 17:42:00', 6, 810000, 810000, 0, 'Cash'),
(433, 'INV-202607-6070210', '2026-07-11 20:51:00', 2, 280000, 280000, 0, 'Cash'),
(434, 'INV-202607-8700011', '2026-07-12 16:46:00', 3, 430000, 430000, 0, 'Cash'),
(435, 'INV-202607-5551111', '2026-07-12 19:29:00', 6, 800000, 800000, 0, 'Cash'),
(436, 'INV-202607-6765211', '2026-07-12 14:34:00', 3, 390000, 390000, 0, 'Cash'),
(437, 'INV-202607-1842311', '2026-07-12 13:54:00', 3, 390000, 390000, 0, 'Cash'),
(438, 'INV-202607-8901012', '2026-07-13 09:05:00', 6, 780000, 780000, 0, 'Cash'),
(439, 'INV-202607-4586013', '2026-07-14 16:15:00', 3, 410000, 410000, 0, 'Cash'),
(440, 'INV-202607-9994113', '2026-07-14 09:51:00', 3, 390000, 390000, 0, 'Cash'),
(441, 'INV-202607-6201213', '2026-07-14 16:44:00', 5, 650000, 650000, 0, 'Cash'),
(442, 'INV-202607-2655014', '2026-07-15 10:10:00', 2, 280000, 280000, 0, 'Cash'),
(443, 'INV-202607-6427114', '2026-07-15 12:26:00', 4, 560000, 560000, 0, 'Cash'),
(444, 'INV-202607-1649015', '2026-07-16 09:44:00', 8, 1040000, 1040000, 0, 'Cash'),
(445, 'INV-202607-8021115', '2026-07-16 17:08:00', 3, 390000, 390000, 0, 'Cash'),
(446, 'INV-202607-9974016', '2026-07-17 13:42:00', 9, 990000, 990000, 0, 'Cash'),
(447, 'INV-202607-6366116', '2026-07-17 13:53:00', 2, 260000, 260000, 0, 'Cash'),
(448, 'INV-202607-6512216', '2026-07-17 10:50:00', 4, 600000, 600000, 0, 'Cash'),
(449, 'INV-202607-4118316', '2026-07-17 20:12:00', 6, 660000, 660000, 0, 'Cash'),
(450, 'INV-202607-8339017', '2026-07-18 20:37:00', 7, 970000, 970000, 0, 'Cash'),
(451, 'INV-202607-8143018', '2026-07-19 12:46:00', 7, 890000, 890000, 0, 'Cash'),
(452, 'INV-202607-9957118', '2026-07-19 13:38:00', 8, 1000000, 1000000, 0, 'Cash'),
(453, 'INV-202607-1469218', '2026-07-19 16:09:00', 2, 200000, 200000, 0, 'Cash'),
(454, 'INV-202607-8902318', '2026-07-19 16:26:00', 11, 1430000, 1430000, 0, 'Cash'),
(455, 'INV-202607-4667019', '2026-07-20 20:53:00', 8, 1040000, 1040000, 0, 'Cash'),
(456, 'INV-202607-1910020', '2026-07-21 09:23:00', 5, 650000, 650000, 0, 'Cash'),
(457, 'INV-202607-2262120', '2026-07-21 14:03:00', 4, 520000, 520000, 0, 'Cash'),
(458, 'INV-202607-1553021', '2026-07-22 11:59:00', 6, 660000, 660000, 0, 'Cash'),
(459, 'INV-202607-3626121', '2026-07-22 09:32:00', 3, 390000, 390000, 0, 'Cash'),
(460, 'INV-202607-9001221', '2026-07-22 15:24:00', 3, 390000, 390000, 0, 'Cash'),
(461, 'INV-202607-6472022', '2026-07-23 12:10:00', 8, 920000, 920000, 0, 'Cash'),
(462, 'INV-202607-7831122', '2026-07-23 19:45:00', 7, 790000, 790000, 0, 'Cash'),
(463, 'INV-202607-2582023', '2026-07-24 19:52:00', 6, 750000, 750000, 0, 'Cash'),
(464, 'INV-202607-9157123', '2026-07-24 17:34:00', 4, 460000, 460000, 0, 'Cash'),
(465, 'INV-202607-2665223', '2026-07-24 10:16:00', 9, 1230000, 1230000, 0, 'Cash'),
(466, 'INV-202607-7933323', '2026-07-24 09:06:00', 3, 450000, 450000, 0, 'Cash'),
(467, 'INV-202607-7538024', '2026-07-25 11:36:00', 6, 660000, 660000, 0, 'Cash'),
(468, 'INV-202607-1069124', '2026-07-25 10:52:00', 11, 1300000, 1300000, 0, 'Cash'),
(469, 'INV-202607-3657224', '2026-07-25 19:55:00', 9, 1090000, 1090000, 0, 'Cash'),
(470, 'INV-202607-5005324', '2026-07-25 19:03:00', 4, 520000, 520000, 0, 'Cash'),
(471, 'INV-202607-8497025', '2026-07-26 18:32:00', 4, 600000, 600000, 0, 'Cash'),
(472, 'INV-202607-5325026', '2026-07-27 14:22:00', 3, 390000, 390000, 0, 'Cash'),
(473, 'INV-202607-6312126', '2026-07-27 12:21:00', 4, 500000, 500000, 0, 'Cash'),
(474, 'INV-202607-6552027', '2026-07-28 18:17:00', 8, 1160000, 1160000, 0, 'Cash'),
(475, 'INV-202607-9200028', '2026-07-29 11:36:00', 10, 1380000, 1380000, 0, 'Cash'),
(476, 'INV-202607-5803029', '2026-07-30 13:46:00', 3, 390000, 390000, 0, 'Cash'),
(477, 'INV-202607-2051030', '2026-07-31 15:04:00', 3, 390000, 390000, 0, 'Cash'),
(478, 'INV-202607-8003130', '2026-07-31 09:03:00', 3, 390000, 390000, 0, 'Cash'),
(479, 'INV-202607-6726230', '2026-07-31 14:51:00', 2, 260000, 260000, 0, 'Cash'),
(480, 'INV-202607-1269330', '2026-07-31 10:51:00', 10, 1210000, 1210000, 0, 'Cash'),
(481, 'INV-202608-9627031', '2026-08-01 18:34:00', 8, 1080000, 1080000, 0, 'Cash'),
(482, 'INV-202608-5050131', '2026-08-01 13:17:00', 4, 520000, 520000, 0, 'Cash'),
(483, 'INV-202608-3075032', '2026-08-02 17:36:00', 8, 960000, 960000, 0, 'Cash'),
(484, 'INV-202608-2790132', '2026-08-02 09:05:00', 4, 520000, 520000, 0, 'Cash'),
(485, 'INV-202608-7321232', '2026-08-02 15:51:00', 8, 1120000, 1120000, 0, 'Cash'),
(486, 'INV-202608-4575033', '2026-08-03 16:22:00', 3, 390000, 390000, 0, 'Cash'),
(487, 'INV-202608-1656133', '2026-08-03 09:00:00', 11, 1490000, 1490000, 0, 'Cash'),
(488, 'INV-202608-5659034', '2026-08-04 13:08:00', 4, 520000, 520000, 0, 'Cash'),
(489, 'INV-202608-3908134', '2026-08-04 16:46:00', 4, 520000, 520000, 0, 'Cash'),
(490, 'INV-202608-7757234', '2026-08-04 16:00:00', 6, 780000, 780000, 0, 'Cash'),
(491, 'INV-202608-6131035', '2026-08-05 10:36:00', 11, 1510000, 1510000, 0, 'Cash'),
(492, 'INV-202608-6792036', '2026-08-06 20:42:00', 4, 600000, 600000, 0, 'Cash'),
(493, 'INV-202608-5007136', '2026-08-06 12:47:00', 2, 200000, 200000, 0, 'Cash'),
(494, 'INV-202608-4020037', '2026-08-07 17:34:00', 8, 920000, 920000, 0, 'Cash'),
(495, 'INV-202608-8598137', '2026-08-07 12:00:00', 6, 820000, 820000, 0, 'Cash'),
(496, 'INV-202608-5638237', '2026-08-07 09:22:00', 10, 1380000, 1380000, 0, 'Cash'),
(497, 'INV-202608-8179038', '2026-08-08 11:48:00', 7, 800000, 800000, 0, 'Cash'),
(498, 'INV-202608-3082039', '2026-08-09 11:21:00', 9, 1170000, 1170000, 0, 'Cash'),
(499, 'INV-202608-8179139', '2026-08-09 12:49:00', 4, 520000, 520000, 0, 'Cash'),
(500, 'INV-202608-4846239', '2026-08-09 14:06:00', 7, 790000, 790000, 0, 'Cash'),
(501, 'INV-202608-5247339', '2026-08-09 14:42:00', 2, 260000, 260000, 0, 'Cash'),
(502, 'INV-202608-2096040', '2026-08-10 20:18:00', 5, 600000, 600000, 0, 'Cash'),
(503, 'INV-202608-5941041', '2026-08-11 17:23:00', 7, 900000, 900000, 0, 'Cash'),
(504, 'INV-202608-8402141', '2026-08-11 14:14:00', 8, 950000, 950000, 0, 'Cash'),
(505, 'INV-202608-4852241', '2026-08-11 15:19:00', 10, 1210000, 1210000, 0, 'Cash'),
(506, 'INV-202608-7261042', '2026-08-12 20:57:00', 10, 1240000, 1240000, 0, 'Cash'),
(507, 'INV-202608-5217142', '2026-08-12 12:46:00', 6, 780000, 780000, 0, 'Cash'),
(508, 'INV-202608-1007242', '2026-08-12 10:48:00', 6, 860000, 860000, 0, 'Cash'),
(509, 'INV-202608-5065342', '2026-08-12 09:17:00', 6, 840000, 840000, 0, 'Cash'),
(510, 'INV-202608-1887043', '2026-08-13 16:00:00', 3, 450000, 450000, 0, 'Cash'),
(511, 'INV-202608-6197143', '2026-08-13 18:39:00', 9, 1020000, 1020000, 0, 'Cash'),
(512, 'INV-202608-3690044', '2026-08-14 17:44:00', 9, 1250000, 1250000, 0, 'Cash'),
(513, 'INV-202608-1846144', '2026-08-14 17:26:00', 6, 780000, 780000, 0, 'Cash'),
(514, 'INV-202608-1797244', '2026-08-14 09:05:00', 6, 660000, 660000, 0, 'Cash'),
(515, 'INV-202608-3078045', '2026-08-15 16:34:00', 13, 1600000, 1600000, 0, 'Cash'),
(516, 'INV-202608-1289145', '2026-08-15 16:24:00', 13, 1770000, 1770000, 0, 'Cash'),
(517, 'INV-202608-8960046', '2026-08-16 12:04:00', 5, 650000, 650000, 0, 'Cash'),
(518, 'INV-202608-6246146', '2026-08-16 19:13:00', 12, 1440000, 1440000, 0, 'Cash'),
(519, 'INV-202608-7894246', '2026-08-16 09:21:00', 9, 1270000, 1270000, 0, 'Cash'),
(520, 'INV-202608-4060346', '2026-08-16 19:43:00', 11, 1430000, 1430000, 0, 'Cash'),
(521, 'INV-202608-4217047', '2026-08-17 12:54:00', 8, 800000, 800000, 0, 'Cash'),
(522, 'INV-202608-3032147', '2026-08-17 16:08:00', 7, 900000, 900000, 0, 'Cash'),
(523, 'INV-202608-2298048', '2026-08-18 19:40:00', 10, 1300000, 1300000, 0, 'Cash'),
(524, 'INV-202608-1399148', '2026-08-18 11:28:00', 5, 750000, 750000, 0, 'Cash'),
(525, 'INV-202608-5780049', '2026-08-19 12:50:00', 4, 520000, 520000, 0, 'Cash'),
(526, 'INV-202608-6102050', '2026-08-20 13:52:00', 12, 1410000, 1410000, 0, 'Cash'),
(527, 'INV-202608-6076150', '2026-08-20 16:07:00', 10, 1300000, 1300000, 0, 'Cash'),
(528, 'INV-202608-3960250', '2026-08-20 11:13:00', 12, 1410000, 1410000, 0, 'Cash'),
(529, 'INV-202608-8760350', '2026-08-20 09:20:00', 8, 950000, 950000, 0, 'Cash'),
(530, 'INV-202608-9605051', '2026-08-21 10:26:00', 6, 600000, 600000, 0, 'Cash'),
(531, 'INV-202608-9016151', '2026-08-21 18:43:00', 12, 1550000, 1550000, 0, 'Cash'),
(532, 'INV-202608-7348251', '2026-08-21 12:42:00', 8, 1100000, 1100000, 0, 'Cash'),
(533, 'INV-202608-5448351', '2026-08-21 19:13:00', 5, 650000, 650000, 0, 'Cash'),
(534, 'INV-202608-7164052', '2026-08-22 10:44:00', 11, 1340000, 1340000, 0, 'Cash'),
(535, 'INV-202608-4339152', '2026-08-22 17:20:00', 8, 890000, 890000, 0, 'Cash'),
(536, 'INV-202608-1702053', '2026-08-23 09:32:00', 14, 1520000, 1520000, 0, 'Cash'),
(537, 'INV-202608-3429054', '2026-08-24 11:38:00', 14, 1920000, 1920000, 0, 'Cash'),
(538, 'INV-202608-5675055', '2026-08-25 09:57:00', 10, 1300000, 1300000, 0, 'Cash'),
(539, 'INV-202608-7881155', '2026-08-25 12:45:00', 9, 1170000, 1170000, 0, 'Cash'),
(540, 'INV-202608-1639255', '2026-08-25 10:42:00', 4, 400000, 400000, 0, 'Cash'),
(541, 'INV-202608-6265355', '2026-08-25 09:44:00', 3, 300000, 300000, 0, 'Cash'),
(542, 'INV-202608-6146056', '2026-08-26 16:48:00', 4, 520000, 520000, 0, 'Cash'),
(543, 'INV-202608-2211156', '2026-08-26 18:44:00', 14, 1520000, 1520000, 0, 'Cash'),
(544, 'INV-202608-1479057', '2026-08-27 09:53:00', 11, 1490000, 1490000, 0, 'Cash'),
(545, 'INV-202608-1527157', '2026-08-27 19:02:00', 7, 910000, 910000, 0, 'Cash'),
(546, 'INV-202608-6852257', '2026-08-27 14:41:00', 12, 1640000, 1640000, 0, 'Cash'),
(547, 'INV-202608-8968357', '2026-08-27 15:29:00', 12, 1440000, 1440000, 0, 'Cash'),
(548, 'INV-202608-5920058', '2026-08-28 15:49:00', 5, 650000, 650000, 0, 'Cash'),
(549, 'INV-202608-9058158', '2026-08-28 10:56:00', 7, 910000, 910000, 0, 'Cash'),
(550, 'INV-202608-7080258', '2026-08-28 18:06:00', 9, 1170000, 1170000, 0, 'Cash'),
(551, 'INV-202608-9768358', '2026-08-28 11:08:00', 9, 1140000, 1140000, 0, 'Cash'),
(552, 'INV-202608-3507059', '2026-08-29 18:15:00', 5, 650000, 650000, 0, 'Cash'),
(553, 'INV-202608-6349060', '2026-08-30 16:13:00', 13, 1500000, 1500000, 0, 'Cash'),
(554, 'INV-202608-3439061', '2026-08-31 17:10:00', 5, 650000, 650000, 0, 'Cash'),
(555, 'INV-202609-2693062', '2026-09-01 14:21:00', 9, 1150000, 1150000, 0, 'Cash'),
(556, 'INV-202609-9385162', '2026-09-01 13:52:00', 10, 1300000, 1300000, 0, 'Cash'),
(557, 'INV-202609-3267063', '2026-09-02 10:07:00', 11, 1340000, 1340000, 0, 'Cash'),
(558, 'INV-202609-2896163', '2026-09-02 17:47:00', 11, 1490000, 1490000, 0, 'Cash'),
(559, 'INV-202609-3322064', '2026-09-03 16:34:00', 3, 450000, 450000, 0, 'Cash'),
(560, 'INV-202609-9811164', '2026-09-03 10:33:00', 3, 390000, 390000, 0, 'Cash'),
(561, 'INV-202609-1397065', '2026-09-04 12:56:00', 10, 1400000, 1400000, 0, 'Cash'),
(562, 'INV-202609-1029165', '2026-09-04 14:40:00', 3, 450000, 450000, 0, 'Cash'),
(563, 'INV-202609-7753066', '2026-09-05 15:27:00', 10, 1210000, 1210000, 0, 'Cash'),
(564, 'INV-202609-3704166', '2026-09-05 19:33:00', 5, 650000, 650000, 0, 'Cash'),
(565, 'INV-202609-9270266', '2026-09-05 09:26:00', 5, 750000, 750000, 0, 'Cash'),
(566, 'INV-202609-7607067', '2026-09-06 18:01:00', 12, 1440000, 1440000, 0, 'Cash'),
(567, 'INV-202609-1025068', '2026-09-07 15:50:00', 10, 1360000, 1360000, 0, 'Cash'),
(568, 'INV-202609-9030168', '2026-09-07 10:53:00', 4, 520000, 520000, 0, 'Cash'),
(569, 'INV-202609-8902069', '2026-09-08 09:38:00', 5, 650000, 650000, 0, 'Cash'),
(570, 'INV-202609-4488169', '2026-09-08 14:23:00', 11, 1430000, 1430000, 0, 'Cash'),
(571, 'INV-202609-8798070', '2026-09-09 11:15:00', 13, 1790000, 1790000, 0, 'Cash'),
(572, 'INV-202609-7335170', '2026-09-09 10:56:00', 5, 650000, 650000, 0, 'Cash'),
(573, 'INV-202609-1263071', '2026-09-10 11:24:00', 3, 390000, 390000, 0, 'Cash'),
(574, 'INV-202609-9786171', '2026-09-10 19:35:00', 3, 450000, 450000, 0, 'Cash'),
(575, 'INV-202609-8271271', '2026-09-10 16:52:00', 9, 1050000, 1050000, 0, 'Cash'),
(576, 'INV-202609-5468072', '2026-09-11 15:42:00', 6, 780000, 780000, 0, 'Cash'),
(577, 'INV-202609-8979073', '2026-09-12 11:26:00', 7, 790000, 790000, 0, 'Cash'),
(578, 'INV-202609-6247173', '2026-09-12 10:22:00', 7, 970000, 970000, 0, 'Cash'),
(579, 'INV-202609-2738074', '2026-09-13 15:28:00', 8, 1040000, 1040000, 0, 'Cash'),
(580, 'INV-202609-1867174', '2026-09-13 11:42:00', 13, 1570000, 1570000, 0, 'Cash'),
(581, 'INV-202609-7949075', '2026-09-14 16:05:00', 5, 650000, 650000, 0, 'Cash'),
(582, 'INV-202609-9481076', '2026-09-15 14:54:00', 4, 520000, 520000, 0, 'Cash'),
(583, 'INV-202609-5769176', '2026-09-15 18:02:00', 14, 1670000, 1670000, 0, 'Cash'),
(584, 'INV-202609-5595276', '2026-09-15 13:23:00', 6, 600000, 600000, 0, 'Cash'),
(585, 'INV-202609-3953376', '2026-09-15 13:23:00', 17, 2310000, 2310000, 0, 'Cash'),
(586, 'INV-202609-9173077', '2026-09-16 09:53:00', 15, 1800000, 1800000, 0, 'Cash'),
(587, 'INV-202609-5713078', '2026-09-17 12:19:00', 10, 1420000, 1420000, 0, 'Cash'),
(588, 'INV-202609-6340079', '2026-09-18 10:19:00', 4, 520000, 520000, 0, 'Cash'),
(589, 'INV-202609-5132080', '2026-09-19 20:52:00', 4, 600000, 600000, 0, 'Cash'),
(590, 'INV-202609-2653180', '2026-09-19 10:59:00', 4, 400000, 400000, 0, 'Cash'),
(591, 'INV-202609-8858081', '2026-09-20 11:59:00', 6, 780000, 780000, 0, 'Cash'),
(592, 'INV-202609-3954181', '2026-09-20 09:20:00', 6, 600000, 600000, 0, 'Cash'),
(593, 'INV-202609-7392281', '2026-09-20 17:08:00', 15, 1920000, 1920000, 0, 'Cash'),
(594, 'INV-202609-2438082', '2026-09-21 20:45:00', 9, 1020000, 1020000, 0, 'Cash'),
(595, 'INV-202609-4766182', '2026-09-21 11:16:00', 12, 1560000, 1560000, 0, 'Cash'),
(596, 'INV-202609-6365083', '2026-09-22 16:48:00', 10, 1420000, 1420000, 0, 'Cash'),
(597, 'INV-202609-6220183', '2026-09-22 11:13:00', 10, 1380000, 1380000, 0, 'Cash'),
(598, 'INV-202609-7507283', '2026-09-22 11:39:00', 6, 780000, 780000, 0, 'Cash'),
(599, 'INV-202609-8123084', '2026-09-23 14:26:00', 10, 1420000, 1420000, 0, 'Cash'),
(600, 'INV-202609-1239184', '2026-09-23 19:23:00', 6, 600000, 600000, 0, 'Cash'),
(601, 'INV-202609-3192085', '2026-09-24 20:18:00', 16, 2030000, 2030000, 0, 'Cash'),
(602, 'INV-202609-5767185', '2026-09-24 19:45:00', 4, 520000, 520000, 0, 'Cash'),
(603, 'INV-202609-7325285', '2026-09-24 10:11:00', 12, 1560000, 1560000, 0, 'Cash'),
(604, 'INV-202609-6725385', '2026-09-24 14:30:00', 10, 1300000, 1300000, 0, 'Cash'),
(605, 'INV-202609-8213086', '2026-09-25 15:34:00', 4, 600000, 600000, 0, 'Cash'),
(606, 'INV-202609-2996186', '2026-09-25 09:23:00', 6, 600000, 600000, 0, 'Cash'),
(607, 'INV-202609-1147087', '2026-09-26 13:18:00', 4, 520000, 520000, 0, 'Cash'),
(608, 'INV-202609-3245088', '2026-09-27 11:38:00', 9, 1250000, 1250000, 0, 'Cash'),
(609, 'INV-202609-6877188', '2026-09-27 15:03:00', 18, 2580000, 2580000, 0, 'Cash'),
(610, 'INV-202609-8016089', '2026-09-28 12:55:00', 9, 1270000, 1270000, 0, 'Cash');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_users`
--

CREATE TABLE `tbl_users` (
  `id_user` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `role` varchar(50) DEFAULT 'Admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `tbl_users`
--

INSERT INTO `tbl_users` (`id_user`, `username`, `password`, `nama_lengkap`, `role`) VALUES
(1, 'admin', '$2a$12$KR0zg/u/KHcnP7/aDptn3uq9tzFyj2FNOwa0Cm4sNeJHXDnF8wVsm', 'ALAM', 'Pimpinan'),
(2, 'kasir1', '$2a$12$KR0zg/u/KHcnP7/aDptn3uq9tzFyj2FNOwa0Cm4sNeJHXDnF8wVsm', 'anca', 'Kasir'),
(3, 'kasir2', '$2y$10$GB0AQ8sXfOFe7nWiKTqz5.3QL5L1NDn2myMOOwAljIglw2gYmY0eS', 'ansar', 'Kasir');

--
-- Indeks untuk tabel yang dibuang
--

--
-- Indeks untuk tabel `tbl_barang`
--
ALTER TABLE `tbl_barang`
  ADD PRIMARY KEY (`id_barang`),
  ADD UNIQUE KEY `kode_barang` (`kode_barang`);

--
-- Indeks untuk tabel `tbl_detail_penjualan`
--
ALTER TABLE `tbl_detail_penjualan`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_penjualan` (`id_penjualan`);

--
-- Indeks untuk tabel `tbl_kategori`
--
ALTER TABLE `tbl_kategori`
  ADD PRIMARY KEY (`id_kategori`),
  ADD UNIQUE KEY `nama_kategori` (`nama_kategori`);

--
-- Indeks untuk tabel `tbl_penjualan`
--
ALTER TABLE `tbl_penjualan`
  ADD PRIMARY KEY (`id_penjualan`),
  ADD UNIQUE KEY `no_faktur` (`no_faktur`);

--
-- Indeks untuk tabel `tbl_users`
--
ALTER TABLE `tbl_users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `tbl_barang`
--
ALTER TABLE `tbl_barang`
  MODIFY `id_barang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT untuk tabel `tbl_detail_penjualan`
--
ALTER TABLE `tbl_detail_penjualan`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=675;

--
-- AUTO_INCREMENT untuk tabel `tbl_kategori`
--
ALTER TABLE `tbl_kategori`
  MODIFY `id_kategori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `tbl_penjualan`
--
ALTER TABLE `tbl_penjualan`
  MODIFY `id_penjualan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=749;

--
-- AUTO_INCREMENT untuk tabel `tbl_users`
--
ALTER TABLE `tbl_users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `tbl_detail_penjualan`
--
ALTER TABLE `tbl_detail_penjualan`
  ADD CONSTRAINT `tbl_detail_penjualan_ibfk_1` FOREIGN KEY (`id_penjualan`) REFERENCES `tbl_penjualan` (`id_penjualan`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

</USER_REQUEST>
<ADDITIONAL_METADATA>
The current local time is: 2026-09-29T03:53:47+08:00.
</ADDITIONAL_METADATA>Created At: 2026-09-29T04:05:00+08:00
Tool is running as a background task with task id: 5fc65fb9-6663-462c-8da4-4eb78312f1b6/task-305
Task Description: python -c "
import json
import re

dump_text = ''
with open('C:/Users/US3R/.gemini/antigravity/brain/5fc65fb9-6663-462c-8da4-4eb78312f1b6/.system_generated/logs/transcript_full.jsonl', 'r', encoding='utf-8') as f:
    for line in f:
        if 'tbl_detail_penjualan' in line and 'INSERT INTO' in line:
            data = json.loads(line)
            if 'content' in data:
                dump_text += data['content']

with open('user_dump.sql', 'w', encoding='utf-8') as f:
    f.write(dump_text)
print('Dump saved! Length:', len(dump_text))
"
Task logs are available at: file:///C:/Users/US3R/.gemini/antigravity/brain/5fc65fb9-6663-462c-8da4-4eb78312f1b6/.system_generated/tasks/task-305.log