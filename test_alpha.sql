USE db_astore;

-- Hapus data dummy lama khusus produk RNSL01 jika ada
DELETE FROM tbl_detail_penjualan WHERE id_penjualan >= 301 AND id_penjualan <= 312;
DELETE FROM tbl_penjualan WHERE id_penjualan >= 301 AND id_penjualan <= 312;

-- Buat 12 transaksi mingguan
INSERT INTO `tbl_penjualan` (`id_penjualan`, `no_faktur`, `tanggal_waktu`, `total_item`, `grand_total`, `nominal_bayar`, `kembalian`) VALUES
(301, 'TEST-R-W1', '2026-07-13 10:00:00', 50, 500000, 500000, 0),
(302, 'TEST-R-W2', '2026-07-20 10:00:00', 52, 520000, 520000, 0),
(303, 'TEST-R-W3', '2026-07-27 10:00:00', 55, 550000, 550000, 0),
(304, 'TEST-R-W4', '2026-08-03 10:00:00', 53, 530000, 530000, 0),
(305, 'TEST-R-W5', '2026-08-10 10:00:00', 58, 580000, 580000, 0),
(306, 'TEST-R-W6', '2026-08-17 10:00:00', 60, 600000, 600000, 0),
(307, 'TEST-R-W7', '2026-08-24 10:00:00', 59, 590000, 590000, 0),
(308, 'TEST-R-W8', '2026-08-31 10:00:00', 63, 630000, 630000, 0),
(309, 'TEST-R-W9', '2026-09-07 10:00:00', 67, 670000, 670000, 0),
(310, 'TEST-R-W10', '2026-09-14 10:00:00', 65, 650000, 650000, 0),
(311, 'TEST-R-W11', '2026-09-21 10:00:00', 70, 700000, 700000, 0),
(312, 'TEST-R-W12', '2026-09-28 10:00:00', 72, 720000, 720000, 0);

INSERT INTO `tbl_detail_penjualan` (`id_penjualan`, `kode_barang`, `harga_satuan`, `qty`, `subtotal`) VALUES
(301, 'RNSL01', 10000, 50, 500000),
(302, 'RNSL01', 10000, 52, 520000),
(303, 'RNSL01', 10000, 55, 550000),
(304, 'RNSL01', 10000, 53, 530000),
(305, 'RNSL01', 10000, 58, 580000),
(306, 'RNSL01', 10000, 60, 600000),
(307, 'RNSL01', 10000, 59, 590000),
(308, 'RNSL01', 10000, 63, 630000),
(309, 'RNSL01', 10000, 67, 670000),
(310, 'RNSL01', 10000, 65, 650000),
(311, 'RNSL01', 10000, 70, 700000),
(312, 'RNSL01', 10000, 72, 720000);
