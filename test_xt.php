<?php
$conn = new mysqli('localhost', 'root', '', 'db_astore');

$conn->query("SET FOREIGN_KEY_CHECKS = 0");
$conn->query("DELETE FROM tbl_penjualan");
$conn->query("DELETE FROM tbl_detail_penjualan");
$conn->query("DELETE FROM tbl_barang");
$conn->query("DELETE FROM tbl_kategori");
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

// Load the dump
$sql = file_get_contents('user_dump.sql');
$conn->multi_query($sql);
while ($conn->next_result()) {;}

echo "Loaded!\n";

$sql = "SELECT 
            YEARWEEK(p.tanggal_waktu, 1) as periode_grup,
            MIN(DATE(p.tanggal_waktu)) as tgl_awal,
            SUM(d.qty) as total_qty
        FROM tbl_detail_penjualan d
        JOIN tbl_penjualan p ON d.id_penjualan = p.id_penjualan
        JOIN tbl_barang b ON d.kode_barang = b.kode_barang
        JOIN tbl_kategori k ON b.kategori = k.nama_kategori
        WHERE k.id_kategori = 8
        GROUP BY periode_grup
        ORDER BY periode_grup DESC
        LIMIT 8";
$qD = $conn->query($sql);
$raw_data = [];
while ($r = $qD->fetch_assoc()) {
    $raw_data[] = $r;
}
$raw_data = array_reverse($raw_data);
print_r($raw_data);
