USE db_astore;

DELETE FROM tbl_detail_penjualan WHERE id_penjualan >= 201 AND id_penjualan <= 205;
DELETE FROM tbl_penjualan WHERE id_penjualan >= 201 AND id_penjualan <= 205;

INSERT INTO `tbl_penjualan` (`id_penjualan`, `no_faktur`, `tanggal_waktu`, `total_item`, `grand_total`, `nominal_bayar`, `kembalian`) VALUES
(201, 'TEST-W1', '2026-08-24 10:00:00', 544, 544000, 544000, 0),
(202, 'TEST-W2', '2026-08-31 10:00:00', 546, 546000, 546000, 0),
(203, 'TEST-W3', '2026-09-07 10:00:00', 602, 602000, 602000, 0),
(204, 'TEST-W4', '2026-09-14 10:00:00', 604, 604000, 604000, 0),
(205, 'TEST-W5', '2026-09-21 10:00:00', 716, 716000, 716000, 0);

INSERT INTO `tbl_detail_penjualan` (`id_penjualan`, `kode_barang`, `harga_satuan`, `qty`, `subtotal`) VALUES
(201, 'HLM01', 130000, 544, 544*130000),
(202, 'HLM01', 130000, 546, 546*130000),
(203, 'HLM01', 130000, 602, 602*130000),
(204, 'HLM01', 130000, 604, 604*130000),
(205, 'HLM01', 130000, 716, 716*130000);
