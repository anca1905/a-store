<?php
$conn = new mysqli('localhost', 'root', '', 'db_astore');

// Kosongkan dummy lama (ID di atas 400)
$conn->query("DELETE FROM tbl_detail_penjualan WHERE id_penjualan >= 400");
$conn->query("DELETE FROM tbl_penjualan WHERE id_penjualan >= 400");

$products = [];
$qP = $conn->query("SELECT kode_barang, harga_jual FROM tbl_barang");
while ($row = $qP->fetch_assoc()) {
    $products[] = [$row['kode_barang'], (int)$row['harga_jual']];
}
if (empty($products)) {
    die("Tidak ada produk di tbl_barang untuk di-seed.");
}

$start_date = strtotime("2026-07-01");
$end_date = strtotime("2026-09-28");
$days = round(($end_date - $start_date) / (60 * 60 * 24));

$id_penjualan = 400;

for ($day = 0; $day <= $days; $day++) {
    $current_date = strtotime("+$day days", $start_date);
    
    // Kurangi jumlah transaksi: 1 sampai 2 transaksi per hari (bahkan kadang 0)
    $num_trx = rand(0, 2);
    for ($t = 0; $t < $num_trx; $t++) {
        $hour = rand(9, 20);
        $minute = rand(0, 59);
        $dt = date("Y-m-d", $current_date) . " " . sprintf("%02d:%02d:00", $hour, $minute);
        
        $no_faktur = "INV-" . date("Ym", $current_date) . "-" . rand(1000, 9999) . $t . $day;
        
        // Cuma 1 atau 2 macam barang per transaksi
        $num_items = rand(1, 2);
        shuffle($products);
        $chosen = array_slice($products, 0, $num_items);
        
        $grand_total = 0;
        $total_qty = 0;
        
        $details = [];
        foreach ($chosen as $item) {
            $kode = $item[0];
            $harga = $item[1];
            // Qty sangat kecil, cuma 1 atau 2 pcs
            $qty = rand(1, 2);
            
            // Tambahkan 1 qty ekstra sebulan sekali agar ada variasi pelan
            if (rand(1, 30) == 1) {
                $qty += 1;
            }
            
            $subtotal = $harga * $qty;
            
            $details[] = [
                'kode' => $kode,
                'harga' => $harga,
                'qty' => $qty,
                'subtotal' => $subtotal
            ];
            
            $grand_total += $subtotal;
            $total_qty += $qty;
        }
        
        $conn->query("INSERT INTO tbl_penjualan (id_penjualan, no_faktur, tanggal_waktu, total_item, grand_total, nominal_bayar, kembalian) 
                      VALUES ($id_penjualan, '$no_faktur', '$dt', $total_qty, $grand_total, $grand_total, 0)");
                      
        foreach ($details as $d) {
            $conn->query("INSERT INTO tbl_detail_penjualan (id_penjualan, kode_barang, harga_satuan, qty, subtotal) 
                          VALUES ($id_penjualan, '{$d['kode']}', {$d['harga']}, {$d['qty']}, {$d['subtotal']})");
        }
        
        $id_penjualan++;
    }
}
echo "Berhasil generate data!";
?>
