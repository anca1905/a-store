<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config.php';
include_once 'cek_sesi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ─── 1. AKSI: UBAH TANGGAL PENJUALAN (KHUSUS ADMIN / PIMPINAN) ──────
    if ($action === 'update_tanggal') {
        if (!isPimpinan()) {
            header("Location: index.php?error=" . urlencode("Akses ditolak. Fitur ubah tanggal transaksi hanya untuk Admin / Pimpinan."));
            exit;
        }

        $id = (int)($_POST['id_penjualan'] ?? 0);
        $tgl_input = trim($_POST['tanggal_waktu'] ?? '');
        $sesuaikan = isset($_POST['sesuaikan_faktur']) && $_POST['sesuaikan_faktur'] == '1';
        $redirect = !empty($_POST['redirect_url']) ? $_POST['redirect_url'] : 'index.php?page=riwayat_transaksi';

        $parsed = strtotime($tgl_input);
        if (!$id || !$parsed) {
            header("Location: " . $redirect . (strpos($redirect, '?') !== false ? '&' : '?') . "error=" . urlencode("Gagal: Tanggal atau ID transaksi tidak valid."));
            exit;
        }

        $newTgl = date('Y-m-d H:i:s', $parsed);

        // Ambil data faktur lama
        $qOld = get_query($conn, "SELECT no_faktur FROM tbl_penjualan WHERE id_penjualan = $id");
        if ($qOld && $qOld->num_rows > 0) {
            $oldRow = $qOld->fetch_assoc();
            $currFaktur = $oldRow['no_faktur'];
            $newFaktur = $currFaktur;

            // Jika opsi penyesuaian nomor faktur aktif, sinkronkan kode tahun-bulan
            if ($sesuaikan) {
                $newYm = date('Ym', $parsed);
                if (preg_match('/^INV-\d{6}(.*)$/', $currFaktur, $m)) {
                    $newFaktur = "INV-" . $newYm . $m[1];
                } else {
                    $newFaktur = "INV-" . $newYm . "-" . rand(1000, 9999);
                }
            }

            get_query($conn, "UPDATE tbl_penjualan SET tanggal_waktu = '$newTgl', no_faktur = '$newFaktur' WHERE id_penjualan = $id");

            $msg = "Tanggal transaksi " . htmlspecialchars($newFaktur) . " berhasil diubah ke " . date('d M Y H:i', $parsed) . ".";
            header("Location: " . $redirect . (strpos($redirect, '?') !== false ? '&' : '?') . "sukses=" . urlencode($msg));
            exit;
        } else {
            header("Location: " . $redirect . (strpos($redirect, '?') !== false ? '&' : '?') . "error=" . urlencode("Transaksi tidak ditemukan."));
            exit;
        }
    }

    // ─── 2. AKSI: HAPUS TRANSAKSI PENJUALAN (KHUSUS ADMIN / PIMPINAN) ───
    if ($action === 'hapus_transaksi') {
        if (!isPimpinan()) {
            header("Location: index.php?error=" . urlencode("Akses ditolak. Fitur hapus transaksi hanya untuk Admin / Pimpinan."));
            exit;
        }

        $id = (int)($_POST['id_penjualan'] ?? 0);
        $kembalikan = isset($_POST['kembalikan_stok']) && $_POST['kembalikan_stok'] == '1';
        $redirect = !empty($_POST['redirect_url']) ? $_POST['redirect_url'] : 'index.php?page=riwayat_transaksi';

        $qP = get_query($conn, "SELECT no_faktur FROM tbl_penjualan WHERE id_penjualan = $id");
        if ($qP && $qP->num_rows > 0) {
            $rowP = $qP->fetch_assoc();
            $faktur = $rowP['no_faktur'];

            // Kembalikan stok barang jika dicentang
            if ($kembalikan) {
                $qDet = get_query($conn, "SELECT kode_barang, qty FROM tbl_detail_penjualan WHERE id_penjualan = $id");
                while ($d = $qDet->fetch_assoc()) {
                    $kd = $conn->real_escape_string($d['kode_barang']);
                    $qty = (int)$d['qty'];
                    get_query($conn, "UPDATE tbl_barang SET stok_aktual = stok_aktual + $qty WHERE kode_barang = '$kd'");
                }
            }

            get_query($conn, "DELETE FROM tbl_detail_penjualan WHERE id_penjualan = $id");
            get_query($conn, "DELETE FROM tbl_penjualan WHERE id_penjualan = $id");

            $msg = "Transaksi '$faktur' berhasil dihapus" . ($kembalikan ? " dan stok barang telah dikembalikan." : ".");
            header("Location: " . $redirect . (strpos($redirect, '?') !== false ? '&' : '?') . "sukses=" . urlencode($msg));
            exit;
        } else {
            header("Location: " . $redirect . (strpos($redirect, '?') !== false ? '&' : '?') . "error=" . urlencode("Transaksi tidak ditemukan."));
            exit;
        }
    }

    // ─── 3. SIMPAN TRANSAKSI BARU (INPUT PENJUALAN KASIR / ADMIN) ───────
    $cart_data_json = $_POST['cart_data'] ?? '';
    $total_item = (int)($_POST['total_item'] ?? 0);
    $grand_total = (int)($_POST['grand_total'] ?? 0);
    $nominal_bayar = (int)($_POST['nominal_bayar'] ?? 0);
    $kembalian = (int)($_POST['kembalian'] ?? 0);
    $metode_pembayaran = $conn->real_escape_string($_POST['metode_pembayaran'] ?? 'Cash');
    
    // Validasi Jika Keranjang Kosong
    $cart = json_decode($cart_data_json, true);
    if (empty($cart) || $grand_total == 0) {
        header("Location: index.php?page=penjualan&msg=" . urlencode("Gagal: Keranjang belanja kosong!"));
        exit;
    }

    // Tanggal transaksi (Admin dapat mengatur tanggal lampau/backdate)
    $tgl = date('Y-m-d H:i:s');
    if (isPimpinan() && !empty($_POST['tanggal_waktu'])) {
        $parsedCustom = strtotime($_POST['tanggal_waktu']);
        if ($parsedCustom !== false) {
            $tgl = date('Y-m-d H:i:s', $parsedCustom);
        }
    }

    // Format nomor faktur unik yang sesuai dengan bulan transaksi
    $ym = date('Ym', strtotime($tgl));
    $no_faktur = "INV-" . $ym . "-" . rand(1000, 9999);
    
    $sqlJual = "INSERT INTO tbl_penjualan (no_faktur, tanggal_waktu, total_item, grand_total, nominal_bayar, kembalian, metode_pembayaran) 
                VALUES ('$no_faktur', '$tgl', $total_item, $grand_total, $nominal_bayar, $kembalian, '$metode_pembayaran')";
                
    if (get_query($conn, $sqlJual)) {
        $id_penjualan = $conn->insert_id;
        
        // Loop simpan detail item dan potong stok
        foreach ($cart as $item) {
            $kd = $conn->real_escape_string($item['kode_barang']);
            $hrg = (int)$item['harga'];
            $qty = (int)$item['qty'];
            $sub = (int)$item['subtotal'];
            
            $sqlDetail = "INSERT INTO tbl_detail_penjualan (id_penjualan, kode_barang, harga_satuan, qty, subtotal) 
                          VALUES ($id_penjualan, '$kd', $hrg, $qty, $sub)";
            get_query($conn, $sqlDetail);
            
            $sqlUpdateStok = "UPDATE tbl_barang SET stok_aktual = stok_aktual - $qty WHERE kode_barang = '$kd'";
            get_query($conn, $sqlUpdateStok);
        }
        
        header("Location: index.php?page=penjualan&msg=" . urlencode("Sukses! Transaksi '$no_faktur' tersimpan.") . "&print_id=" . $no_faktur);
        exit;
    } else {
        header("Location: index.php?page=penjualan&error=" . urlencode("Gagal memproses transaksi. Terjadi kesalahan pada server."));
        exit;
    }
} else {
    header("Location: index.php?page=penjualan");
    exit;
}
?>
