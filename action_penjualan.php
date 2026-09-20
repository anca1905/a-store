<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cart_data_json = $_POST['cart_data'];
    $total_item = (int)$_POST['total_item'];
    $grand_total = (int)$_POST['grand_total'];
    $nominal_bayar = (int)$_POST['nominal_bayar'];
    $kembalian = (int)$_POST['kembalian'];
    $metode_pembayaran = $conn->real_escape_string($_POST['metode_pembayaran'] ?? 'Cash');
    
    // Validasi Jika Keranjang Kosong
    $cart = json_decode($cart_data_json, true);
    if(empty($cart) || $grand_total == 0) {
        header("Location: index.php?page=penjualan&msg=" . urlencode("Gagal: Keranjang belanja kosong!"));
        exit;
    }

    // 1. Simpan Header Penjualan
    // Membuat nomor faktur unik: INV-Ym-Rand
    $no_faktur = "INV-" . date("Ym") . "-" . rand(1000, 9999);
    $tgl = date('Y-m-d H:i:s');
    
    $sqlJual = "INSERT INTO tbl_penjualan (no_faktur, tanggal_waktu, total_item, grand_total, nominal_bayar, kembalian, metode_pembayaran) 
                VALUES ('$no_faktur', '$tgl', $total_item, $grand_total, $nominal_bayar, $kembalian, '$metode_pembayaran')";
                
    if(get_query($conn, $sqlJual)) {
        // Tarik ID penjualan yang barusan insert (auto_increment)
        $id_penjualan = $conn->insert_id;
        
        // 2. Loop & Simpan Detail Penjualan + Update Stok
        foreach($cart as $item) {
            $kd = $conn->real_escape_string($item['kode_barang']);
            $hrg = (int)$item['harga'];
            $qty = (int)$item['qty'];
            $sub = (int)$item['subtotal'];
            
            // Insert Detail
            $sqlDetail = "INSERT INTO tbl_detail_penjualan (id_penjualan, kode_barang, harga_satuan, qty, subtotal) 
                          VALUES ($id_penjualan, '$kd', $hrg, $qty, $sub)";
            get_query($conn, $sqlDetail);
            
        // Update Stok (Pemotongan)
        $sqlUpdateStok = "UPDATE tbl_barang SET stok_aktual = stok_aktual - $qty WHERE kode_barang = '$kd'";
        get_query($conn, $sqlUpdateStok);
    }
    
    header("Location: index.php?page=penjualan&msg=" . urlencode("Sukses! Transaksi '$no_faktur' tersimpan.") . "&print_id=" . $no_faktur);
} else {
    header("Location: index.php?page=penjualan&error=" . urlencode("Gagal memproses transaksi. Error Server."));
}
} else {
    header("Location: index.php?page=penjualan");
}
?>
