<?php
$conn = new mysqli('localhost', 'root', '', 'db_astore');

// Kosongkan dummy lama (ID di atas 400)
$conn->query("DELETE FROM tbl_detail_penjualan WHERE id_penjualan >= 400");
$conn->query("DELETE FROM tbl_penjualan WHERE id_penjualan >= 400");

$products = [
    ['MYBSC01', 130000], ['RNSL01', 150000], ['RNSL02', 130000],
    ['RNSL03', 130000], ['RNSL04', 130000], ['RNSL05', 130000],
    ['RNSL06', 150000], ['SLMPNG01', 100000], ['SLMPNG02', 100000],
    ['HLM01', 130000]
];

$start_date = strtotime("2026-07-01");
$end_date = strtotime("2026-09-28");
$days = round(($end_date - $start_date) / (60 * 60 * 24));

$id_penjualan = 400;

for ($day = 0; $day <= $days; $day++) {
    $current_date = strtotime("+$day days", $start_date);
    
    // 1 to 4 trxs per day
    $num_trx = rand(1, 4);
    for ($t = 0; $t < $num_trx; $t++) {
        $hour = rand(9, 20);
        $minute = rand(0, 59);
        $dt = date("Y-m-d", $current_date) . " " . sprintf("%02d:%02d:00", $hour, $minute);
        
        $no_faktur = "INV-" . date("Ym", $current_date) . "-" . rand(1000, 9999) . $t . $day;
        
        $num_items = rand(1, 3);
        shuffle($products);
        $chosen = array_slice($products, 0, $num_items);
        
        $grand_total = 0;
        $total_qty = 0;
        
        $details = [];
        foreach ($chosen as $item) {
            $kode = $item[0];
            $harga = $item[1];
            $qty = rand(1, 3);
            
            // Buat pola tren naik tipis supaya forecasting keliatan real
            $qty += round($day / 30); // Tiap 30 hari nambah sedikit qty
            
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
