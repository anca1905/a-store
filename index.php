<?php
session_start();
include 'config.php';
include 'cek_sesi.php';

$role = $_SESSION['role'] ?? 'Kasir';

// Auto-migrate foto_kategori
$checkKat = $conn->query("SHOW COLUMNS FROM tbl_kategori LIKE 'foto_kategori'");
if ($checkKat && $checkKat->num_rows == 0) {
    $conn->query("ALTER TABLE tbl_kategori ADD COLUMN foto_kategori VARCHAR(255) DEFAULT NULL");
}

// Auto-migrate metode_pembayaran
$checkMetode = $conn->query("SHOW COLUMNS FROM tbl_penjualan LIKE 'metode_pembayaran'");
if ($checkMetode && $checkMetode->num_rows == 0) {
    $conn->query("ALTER TABLE tbl_penjualan ADD COLUMN metode_pembayaran VARCHAR(50) DEFAULT 'Cash'");
}

// Auto-migrate foto_profil di tbl_users
$checkFotoProfil = $conn->query("SHOW COLUMNS FROM tbl_users LIKE 'foto_profil'");
if ($checkFotoProfil && $checkFotoProfil->num_rows == 0) {
    $conn->query("ALTER TABLE tbl_users ADD COLUMN foto_profil VARCHAR(255) DEFAULT NULL AFTER nama_lengkap");
}

// Auto-migrate detail_json dan mape di tbl_riwayat_peramalan
try {
    $checkDetailJson = $conn->query("SHOW COLUMNS FROM tbl_riwayat_peramalan LIKE 'detail_json'");
    if ($checkDetailJson && $checkDetailJson->num_rows == 0) {
        $conn->query("ALTER TABLE tbl_riwayat_peramalan ADD COLUMN detail_json LONGTEXT DEFAULT NULL");
    }
    $checkMapeCol = $conn->query("SHOW COLUMNS FROM tbl_riwayat_peramalan LIKE 'mape'");
    if ($checkMapeCol && $checkMapeCol->num_rows == 0) {
        $conn->query("ALTER TABLE tbl_riwayat_peramalan ADD COLUMN mape FLOAT DEFAULT NULL AFTER akurasi");
    }
} catch (Exception $e) {}

// AJAX: Ambil detail perhitungan historis untuk modal riwayat peramalan
if (isset($_GET['action']) && $_GET['action'] === 'ajax_get_riwayat_detail' && isset($_GET['id_riwayat'])) {
    header('Content-Type: application/json');
    $idR = (int)$_GET['id_riwayat'];
    $qR = $conn->query("SELECT * FROM tbl_riwayat_peramalan WHERE id_riwayat = $idR");
    if (!$qR || $qR->num_rows == 0) {
        echo json_encode(['success' => false, 'message' => 'Riwayat tidak ditemukan']);
        exit;
    }
    $rRow = $qR->fetch_assoc();
    $det = !empty($rRow['detail_json']) ? json_decode($rRow['detail_json'], true) : [];
    
    if (!empty($det['history_rows'])) {
        echo json_encode([
            'success' => true,
            'history_rows' => $det['history_rows'],
            'avg_mape' => $det['avg_mape'] ?? null,
            'total_abs_err' => $det['total_abs_err'] ?? null,
            'total_aktual' => $det['total_aktual'] ?? null
        ]);
        exit;
    }
    
    // Rekonstruksi jika riwayat lama belum menyimpan history_rows
    $tipe = $rRow['tipe'];
    $ref = $conn->real_escape_string($rRow['referensi']);
    $n_periode = max(3, (int)$rRow['periode']);
    $alpha = (float)$rRow['alpha'];
    $target_peramalan = $det['target_peramalan'] ?? (strpos($rRow['target_waktu'], 'Bulan') !== false ? 'bulanan' : 'mingguan');
    $tanggal_acuan = !empty($det['tanggal_acuan']) ? $det['tanggal_acuan'] : date('Y-m-d', strtotime($rRow['tanggal_hitung']));
    $ts_acuan = strtotime($tanggal_acuan);
    
    $rows = [];
    if ($tipe === 'barang') {
        if ($target_peramalan === 'bulanan') {
            $oldest_start = date('Y-m-d', strtotime('-' . (($n_periode - 1) * 7 + 6) . ' days', $ts_acuan));
            $newest_end   = date('Y-m-d', $ts_acuan);
            $sql = "SELECT DATE(p.tanggal_waktu) as tgl, SUM(d.qty) as total_qty
                    FROM tbl_detail_penjualan d
                    JOIN tbl_penjualan p ON d.id_penjualan = p.id_penjualan
                    WHERE d.kode_barang = '$ref' AND DATE(p.tanggal_waktu) BETWEEN '$oldest_start' AND '$newest_end'
                    GROUP BY DATE(p.tanggal_waktu)";
            $qD = $conn->query($sql);
            $daily_sales = [];
            while ($r = $qD->fetch_assoc()) { $daily_sales[$r['tgl']] = (float)$r['total_qty']; }
            for ($w = 1; $w <= $n_periode; $w++) {
                $step_back = $n_periode - $w;
                $end_ts   = strtotime("-" . ($step_back * 7) . " days", $ts_acuan);
                $start_ts = strtotime("-" . ($step_back * 7 + 6) . " days", $ts_acuan);
                $period_qty = 0;
                $cur_ts = $start_ts;
                while ($cur_ts <= $end_ts) {
                    $cur_ymd = date('Y-m-d', $cur_ts);
                    if (isset($daily_sales[$cur_ymd])) $period_qty += $daily_sales[$cur_ymd];
                    $cur_ts = strtotime("+1 day", $cur_ts);
                }
                $rows[] = ['label' => date('d M Y', $start_ts) . ' - ' . date('d M Y', $end_ts), 'qty' => $period_qty];
            }
        } else {
            $oldest_start = date('Y-m-d', strtotime('-' . ($n_periode - 1) . ' days', $ts_acuan));
            $newest_end   = date('Y-m-d', $ts_acuan);
            $sql = "SELECT DATE(p.tanggal_waktu) as tgl, SUM(d.qty) as total_qty
                    FROM tbl_detail_penjualan d
                    JOIN tbl_penjualan p ON d.id_penjualan = p.id_penjualan
                    WHERE d.kode_barang = '$ref' AND DATE(p.tanggal_waktu) BETWEEN '$oldest_start' AND '$newest_end'
                    GROUP BY DATE(p.tanggal_waktu)";
            $qD = $conn->query($sql);
            $daily_sales = [];
            while ($r = $qD->fetch_assoc()) { $daily_sales[$r['tgl']] = (float)$r['total_qty']; }
            for ($h = 1; $h <= $n_periode; $h++) {
                $step_back = $n_periode - $h;
                $cur_ts = strtotime("-" . $step_back . " days", $ts_acuan);
                $cur_ymd = date('Y-m-d', $cur_ts);
                $rows[] = ['label' => date('d M Y', $cur_ts), 'qty' => $daily_sales[$cur_ymd] ?? 0];
            }
        }
    } else {
        $kat_nama = $rRow['nama_referensi'];
        if ($target_peramalan === 'bulanan') {
            $oldest_start = date('Y-m-d', strtotime('-' . (($n_periode - 1) * 7 + 6) . ' days', $ts_acuan));
            $newest_end   = date('Y-m-d', $ts_acuan);
            $sql = "SELECT DATE(p.tanggal_waktu) as tgl, SUM(d.qty) as total_qty
                    FROM tbl_detail_penjualan d
                    JOIN tbl_penjualan p ON d.id_penjualan = p.id_penjualan
                    JOIN tbl_barang b ON d.kode_barang = b.kode_barang
                    WHERE (b.kategori = '$kat_nama' OR b.id_kategori = '$ref')
                      AND DATE(p.tanggal_waktu) BETWEEN '$oldest_start' AND '$newest_end'
                    GROUP BY DATE(p.tanggal_waktu)";
            $qD = $conn->query($sql);
            $daily_sales = [];
            while ($r = $qD->fetch_assoc()) { $daily_sales[$r['tgl']] = (float)$r['total_qty']; }
            for ($w = 1; $w <= $n_periode; $w++) {
                $step_back = $n_periode - $w;
                $end_ts   = strtotime("-" . ($step_back * 7) . " days", $ts_acuan);
                $start_ts = strtotime("-" . ($step_back * 7 + 6) . " days", $ts_acuan);
                $period_qty = 0;
                $cur_ts = $start_ts;
                while ($cur_ts <= $end_ts) {
                    $cur_ymd = date('Y-m-d', $cur_ts);
                    if (isset($daily_sales[$cur_ymd])) $period_qty += $daily_sales[$cur_ymd];
                    $cur_ts = strtotime("+1 day", $cur_ts);
                }
                $rows[] = ['label' => date('d M Y', $start_ts) . ' - ' . date('d M Y', $end_ts), 'qty' => $period_qty];
            }
        } else {
            $oldest_start = date('Y-m-d', strtotime('-' . ($n_periode - 1) . ' days', $ts_acuan));
            $newest_end   = date('Y-m-d', $ts_acuan);
            $sql = "SELECT DATE(p.tanggal_waktu) as tgl, SUM(d.qty) as total_qty
                    FROM tbl_detail_penjualan d
                    JOIN tbl_penjualan p ON d.id_penjualan = p.id_penjualan
                    JOIN tbl_barang b ON d.kode_barang = b.kode_barang
                    WHERE (b.kategori = '$kat_nama' OR b.id_kategori = '$ref')
                      AND DATE(p.tanggal_waktu) BETWEEN '$oldest_start' AND '$newest_end'
                    GROUP BY DATE(p.tanggal_waktu)";
            $qD = $conn->query($sql);
            $daily_sales = [];
            while ($r = $qD->fetch_assoc()) { $daily_sales[$r['tgl']] = (float)$r['total_qty']; }
            for ($h = 1; $h <= $n_periode; $h++) {
                $step_back = $n_periode - $h;
                $cur_ts = strtotime("-" . $step_back . " days", $ts_acuan);
                $cur_ymd = date('Y-m-d', $cur_ts);
                $rows[] = ['label' => date('d M Y', $cur_ts), 'qty' => $daily_sales[$cur_ymd] ?? 0];
            }
        }
    }
    
    $n = count($rows);
    if ($n < 3) {
        echo json_encode(['success' => false, 'message' => 'Data historis tidak mencukupi']);
        exit;
    }
    
    $aktual = array_column($rows, 'qty');
    $S1 = []; $S2 = []; $a = []; $b = []; $F = [];
    $S1[0] = $aktual[0]; $S2[0] = $aktual[0];
    $a[0] = 2 * $S1[0] - $S2[0];
    $b[0] = ($alpha / (1 - $alpha)) * ($S1[0] - $S2[0]);
    $F[0] = 0;
    
    $sum_mape = 0; $count_mape = 0; $total_abs_err = 0;
    for ($i = 1; $i < $n; $i++) {
        $Xi = $aktual[$i];
        $S1[$i] = $alpha * $Xi + (1 - $alpha) * $S1[$i-1];
        $S2[$i] = $alpha * $S1[$i] + (1 - $alpha) * $S2[$i-1];
        $a[$i]  = 2 * $S1[$i] - $S2[$i];
        $b[$i]  = ($alpha / (1 - $alpha)) * ($S1[$i] - $S2[$i]);
        $F[$i]  = $a[$i-1] + $b[$i-1] * 1;
        $err = abs($Xi - $F[$i]);
        $total_abs_err += $err;
        if ($Xi > 0) {
            $sum_mape += ($err / $Xi) * 100;
            $count_mape++;
        }
    }
    
    $history_rows = [];
    for ($i = 0; $i < $n; $i++) {
        $err_val = ($i > 0) ? round(abs($aktual[$i] - $F[$i]), 2) : null;
        $mape_val = ($i > 0 && $aktual[$i] > 0) ? round(($err_val / $aktual[$i]) * 100, 2) : null;
        $history_rows[] = [
            'i' => $i + 1,
            'label' => $rows[$i]['label'],
            'aktual' => $aktual[$i],
            's1' => round($S1[$i], 2),
            's2' => round($S2[$i], 2),
            'a' => round($a[$i], 2),
            'b' => round($b[$i], 2),
            'f' => ($i > 0) ? round($F[$i], 2) : null,
            'err' => $err_val,
            'mape' => $mape_val
        ];
    }
    
    $avg_mape = ($count_mape > 0) ? round($sum_mape / $count_mape, 2) : 0;
    $det['history_rows'] = $history_rows;
    $det['avg_mape'] = $avg_mape;
    $det['total_abs_err'] = round($total_abs_err, 2);
    $det['total_aktual'] = array_sum($aktual);
    
    $newJsonEsc = $conn->real_escape_string(json_encode($det));
    $conn->query("UPDATE tbl_riwayat_peramalan SET detail_json='$newJsonEsc' WHERE id_riwayat=$idR");
    
    echo json_encode([
        'success' => true,
        'history_rows' => $history_rows,
        'avg_mape' => $avg_mape,
        'total_abs_err' => round($total_abs_err, 2),
        'total_aktual' => array_sum($aktual)
    ]);
    exit;
}

// Auto-migrate foreign keys (Relasi antar tabel untuk phpMyAdmin Designer)
try {
    $checkFkBarang = $conn->query("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=DATABASE() AND TABLE_NAME='tbl_barang' AND CONSTRAINT_NAME='fk_barang_kategori'");
    if ($checkFkBarang && $checkFkBarang->num_rows == 0) {
        $conn->query("ALTER TABLE tbl_barang MODIFY kategori VARCHAR(100) NOT NULL");
        $conn->query("ALTER TABLE tbl_barang ADD CONSTRAINT fk_barang_kategori FOREIGN KEY (kategori) REFERENCES tbl_kategori(nama_kategori) ON UPDATE CASCADE ON DELETE RESTRICT");
    }
} catch (Exception $e) {}

try {
    $checkFkDetail = $conn->query("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=DATABASE() AND TABLE_NAME='tbl_detail_penjualan' AND CONSTRAINT_NAME='fk_detail_barang'");
    if ($checkFkDetail && $checkFkDetail->num_rows == 0) {
        $conn->query("ALTER TABLE tbl_detail_penjualan ADD CONSTRAINT fk_detail_barang FOREIGN KEY (kode_barang) REFERENCES tbl_barang(kode_barang) ON UPDATE CASCADE ON DELETE RESTRICT");
    }
} catch (Exception $e) {}

// Sinkronisasi foto_profil ke session (agar topbar selalu terkini)
if (isset($_SESSION['user_id']) && !isset($_SESSION['foto_profil'])) {
    $uid_sync = (int)$_SESSION['user_id'];
    $qSync = $conn->query("SELECT foto_profil FROM tbl_users WHERE id_user=$uid_sync");
    if ($qSync && $row_sync = $qSync->fetch_assoc()) {
        $_SESSION['foto_profil'] = $row_sync['foto_profil'];
    }
}

// Auto-fix filenames
$dirImg = __DIR__ . '/assets/img/barang/';
$qImg = $conn->query("SELECT id_barang, foto_barang FROM tbl_barang WHERE foto_barang IS NOT NULL");
if ($qImg) {
    while ($row = $qImg->fetch_assoc()) {
        $old = $row['foto_barang'];
        if (preg_match('/[^a-zA-Z0-9\.\-_]/', $old)) {
            $new = preg_replace('/[^a-zA-Z0-9\.\-_]/', '_', $old);
            if (file_exists($dirImg . $old)) {
                rename($dirImg . $old, $dirImg . $new);
                $conn->query("UPDATE tbl_barang SET foto_barang='$new' WHERE id_barang={$row['id_barang']}");
            }
        }
    }
}
$dirKategori = __DIR__ . '/assets/img/kategori/';
$qKatImg = $conn->query("SELECT id_kategori, foto_kategori FROM tbl_kategori WHERE foto_kategori IS NOT NULL");
if ($qKatImg) {
    while ($row = $qKatImg->fetch_assoc()) {
        $old = $row['foto_kategori'];
        if (preg_match('/[^a-zA-Z0-9\.\-_]/', $old)) {
            $new = preg_replace('/[^a-zA-Z0-9\.\-_]/', '_', $old);
            if (file_exists($dirKategori . $old)) {
                rename($dirKategori . $old, $dirKategori . $new);
                $conn->query("UPDATE tbl_kategori SET foto_kategori='$new' WHERE id_kategori={$row['id_kategori']}");
            }
        }
    }
}


// ─── Definisi halaman per role ───────────────────────────────────────
$pagesForPimpinan = ['dashboard', 'barang', 'kategori', 'kelola_kasir', 'stok', 'riwayat_stok', 'laporan', 'des', 'des_kategori', 'pengaturan', 'historis', 'riwayat_transaksi'];
$pagesForKasir    = ['dashboard', 'penjualan', 'riwayat_transaksi', 'data_barang_kasir', 'stok_barang'];

$allValidPages = array_merge($pagesForPimpinan, $pagesForKasir);

// Default page per role
$defaultPage = ($role === 'Pimpinan') ? 'dashboard' : 'penjualan';
$page = isset($_GET['page']) ? $_GET['page'] : $defaultPage;

// Validasi akses halaman sesuai role
if ($role === 'Pimpinan' && !in_array($page, $pagesForPimpinan)) {
    $page = 'dashboard';
} elseif ($role === 'Kasir' && !in_array($page, $pagesForKasir)) {
    $page = 'penjualan';
} elseif (!in_array($page, $allValidPages)) {
    $page = $defaultPage;
}

// ─── Label halaman ────────────────────────────────────────────────────
$pageLabels = [
    'dashboard'         => ['icon' => 'fa-house',             'title' => 'Dasbor'],
    'barang'            => ['icon' => 'fa-box-open',           'title' => 'Data Barang'],
    'kategori'          => ['icon' => 'fa-tags',               'title' => 'Kategori Barang'],
    'kelola_kasir'      => ['icon' => 'fa-users',              'title' => 'Data Kasir'],
    'stok'              => ['icon' => 'fa-warehouse',          'title' => 'Manajemen Stok'],
    'riwayat_stok'      => ['icon' => 'fa-clock-rotate-left',  'title' => 'Riwayat Stok'],
    'historis'          => ['icon' => 'fa-clock-rotate-left',  'title' => 'Data Penjualan'],
    'laporan'           => ['icon' => 'fa-file-invoice',       'title' => 'Laporan Penjualan'],
    'des'               => ['icon' => 'fa-chart-line',         'title' => 'Peramalan Berdasarkan Barang'],
    'des_kategori'      => ['icon' => 'fa-chart-line',         'title' => 'Peramalan Berdasarkan Kategori'],
    'pengaturan'        => ['icon' => 'fa-gear',               'title' => 'Pengaturan'],
    'penjualan'         => ['icon' => 'fa-cash-register',      'title' => 'Penjualan'],
    'riwayat_transaksi' => ['icon' => 'fa-clock-rotate-left',  'title' => 'Riwayat Transaksi'],
    'data_barang_kasir' => ['icon' => 'fa-boxes-stacked',      'title' => 'Data Barang'],
    'stok_barang'       => ['icon' => 'fa-warehouse',          'title' => 'Stok Barang'],
];

$pageTitle  = $pageLabels[$page]['title'] ?? 'Dashboard';
$namaUser   = $_SESSION['nama_lengkap'] ?? 'User';
$roleUser   = $_SESSION['role'] ?? '';

// ─── Notifikasi stok kritis (hanya Pimpinan) ─────────────────────────
$jumlahKritis = 0;
$kritisListItems = [];
if ($role === 'Pimpinan') {
    $cekKritis    = get_query($conn, "SELECT COUNT(*) as jml FROM tbl_barang WHERE stok_aktual <= stok_min");
    $jumlahKritis = (int)($cekKritis->fetch_assoc()['jml'] ?? 0);
    if ($jumlahKritis > 0) {
        $qKItems = get_query($conn, "SELECT kode_barang, nama_produk, stok_aktual, stok_min FROM tbl_barang WHERE stok_aktual <= stok_min ORDER BY stok_aktual ASC LIMIT 15");
        if ($qKItems) {
            while ($rK = $qKItems->fetch_assoc()) {
                $kritisListItems[] = $rK;
            }
        }
    }
}

// ─── AJAX Handler Detail Transaksi (Mode Struk) ────────────────────────
if (isset($_GET['ajax_detail'])) {
    $val = $conn->real_escape_string($_GET['ajax_detail']);
    $qHdr = get_query($conn, "SELECT id_penjualan, no_faktur, tanggal_waktu, total_item, grand_total, nominal_bayar, kembalian, metode_pembayaran FROM tbl_penjualan WHERE id_penjualan = '$val' OR no_faktur = '$val'");
    if ($qHdr && $qHdr->num_rows > 0) {
        $hdr = $qHdr->fetch_assoc();
        $idTrx = $hdr['id_penjualan'];
        
        $qDet = get_query($conn, "
            SELECT d.kode_barang, b.nama_produk, d.harga_satuan, d.qty, d.subtotal
            FROM tbl_detail_penjualan d
            LEFT JOIN tbl_barang b ON d.kode_barang = b.kode_barang
            WHERE d.id_penjualan = $idTrx
        ");

        echo '<div id="strukPrintArea" style="max-width:320px; margin:0 auto; font-family:\'Courier New\', Courier, monospace; font-size:12px; color:#000; background:#fff; padding:20px 10px;">';
        
        // Header Struk
        echo '<div style="text-align:center; margin-bottom:16px;">';
        echo '<div style="display:flex; justify-content:center; align-items:center; margin-bottom:4px;">';
        echo '<h2 style="font-size:20px; font-weight:900; margin:0;">A STORE</h2>';
        echo '</div>';
        echo '<div style="font-size:10px;">Desa Anaiwoi, Kec. Tanggetada<br>Kab. Kolaka</div>';
        echo '</div>';
        
        echo '<div style="border-bottom:1px dashed #000; margin-bottom:8px;"></div>';
        
        // Info Transaksi
        echo '<table style="width:100%; font-size:11px; margin-bottom:8px; border-collapse:collapse;">';
        echo '<tr><td style="width:90px; padding:2px 0;">No. Transaksi</td><td style="width:10px;">:</td><td>' . htmlspecialchars($hdr['no_faktur']) . '</td></tr>';
        echo '<tr><td style="padding:2px 0;">Tanggal</td><td>:</td><td>' . date('d/m/Y H.i', strtotime($hdr['tanggal_waktu'])) . '</td></tr>';
        echo '<tr><td style="padding:2px 0;">Kasir</td><td>:</td><td>Kasir</td></tr>';
        $metode = isset($hdr['metode_pembayaran']) ? $hdr['metode_pembayaran'] : 'Cash';
        echo '<tr><td style="padding:2px 0;">Metode Bayar</td><td>:</td><td>' . htmlspecialchars($metode) . '</td></tr>';
        echo '</table>';
        
        echo '<div style="border-bottom:1px dashed #000; margin-bottom:8px;"></div>';
        
        // WA Text Generator - Header
        $waText = "*- A STORE -*\n";
        $waText .= "Desa Anaiwoi, Kec. Tanggetada\n\n";
        $waText .= "No. Transaksi: " . htmlspecialchars($hdr['no_faktur']) . "\n";
        $waText .= "Tanggal: " . date('d/m/Y H.i', strtotime($hdr['tanggal_waktu'])) . "\n";
        $waText .= "Metode Bayar: " . htmlspecialchars($metode) . "\n\n";
        $waText .= "--- DETAIL PESANAN ---\n";

        // Item List
        echo '<table style="width:100%; font-size:11px; border-collapse:collapse; margin-bottom:8px;">';
        echo '<thead><tr>';
        echo '<th style="text-align:left; padding:4px 0; width:20px;">No</th>';
        echo '<th style="text-align:left; padding:4px 0;">Barang</th>';
        echo '<th style="text-align:center; padding:4px 0; width:25px;">Qty</th>';
        echo '<th style="text-align:right; padding:4px 0; width:65px;">Harga</th>';
        echo '<th style="text-align:right; padding:4px 0; width:65px;">Subtotal</th>';
        echo '</tr></thead><tbody>';
        
        $no = 1;
        while ($d = $qDet->fetch_assoc()) {
            $namaBrg = htmlspecialchars($d['nama_produk'] ?? $d['kode_barang']);
            
            echo '<tr>';
            echo '<td style="padding:4px 0; vertical-align:top;">' . $no . '</td>';
            echo '<td style="padding:4px 0; padding-right:4px;">' . $namaBrg . '</td>';
            echo '<td style="padding:4px 0; text-align:center; vertical-align:top;">' . $d['qty'] . '</td>';
            echo '<td style="padding:4px 0; text-align:right; vertical-align:top;">Rp' . number_format($d['harga_satuan'], 0, '', '.') . '</td>';
            echo '<td style="padding:4px 0; text-align:right; vertical-align:top;">Rp' . number_format($d['subtotal'], 0, '', '.') . '</td>';
            echo '</tr>';

            $waText .= $no . ". " . $namaBrg . " x" . $d['qty'] . "\n";
            $waText .= "   Rp " . number_format($d['subtotal'], 0, '', '.') . "\n";
            $no++;
        }
        echo '</tbody></table>';
        
        echo '<div style="border-bottom:1px dashed #000; margin-bottom:8px;"></div>';
        
        // WA Text Generator - Footer
        $waText .= "----------------------\n";
        $waText .= "Total Belanja: Rp " . number_format($hdr['grand_total'], 0, '', '.') . "\n";
        $waText .= "Dibayar (" . htmlspecialchars($metode) . "): Rp " . number_format($hdr['nominal_bayar'], 0, '', '.') . "\n";
        $waText .= "Kembalian: Rp " . number_format($hdr['kembalian'], 0, '', '.') . "\n\n";
        $waText .= "Terima kasih atas kunjungan Anda!\n";
        $waEncoded = rawurlencode($waText);

        // Total
        echo '<table style="width:100%; font-size:11px; border-collapse:collapse; margin-bottom:16px;">';
        echo '<tr><td style="text-align:right; padding:2px 10px 2px 0;">Total Item</td><td style="width:10px;">:</td><td style="width:65px; text-align:right;">' . $hdr['total_item'] . '</td></tr>';
        echo '<tr><td style="text-align:right; padding:2px 10px 2px 0;">Total Belanja</td><td>:</td><td style="text-align:right;">Rp' . number_format($hdr['grand_total'], 0, '', '.') . '</td></tr>';
        echo '<tr><td style="text-align:right; padding:2px 10px 2px 0;">Bayar</td><td>:</td><td style="text-align:right;">Rp' . number_format($hdr['nominal_bayar'], 0, '', '.') . '</td></tr>';
        echo '<tr><td style="text-align:right; padding:2px 10px 2px 0; font-weight:bold;">Kembalian</td><td style="font-weight:bold;">:</td><td style="text-align:right; font-weight:bold;">Rp' . number_format($hdr['kembalian'], 0, '', '.') . '</td></tr>';
        echo '</table>';
        
        // Footer
        echo '<div style="text-align:center; font-size:11px; font-weight:bold;">Terima kasih atas kunjungan Anda!</div>';
        echo '</div>';
        
        // Buttons
        echo '<div style="margin-top:20px; border-top:1px solid #e2e8f0; padding-top:16px; display:flex; justify-content:center; gap:8px; flex-wrap:wrap;">';
        echo '<button onclick="printStruk()" style="background:#111; color:#fff; border:none; padding:10px 14px; border-radius:6px; font-weight:600; cursor:pointer; font-family:\'Outfit\', sans-serif;"><i class="fa-solid fa-print"></i> Cetak</button>';
        echo '<a href="https://wa.me/?text=' . $waEncoded . '" target="_blank" style="background:#25D366; color:#fff; border:none; padding:10px 14px; border-radius:6px; font-weight:600; cursor:pointer; font-family:\'Outfit\', sans-serif; text-decoration:none;"><i class="fa-brands fa-whatsapp"></i> Teks WA</a>';
        echo '<button onclick="shareStrukImage(this, \'' . htmlspecialchars($hdr['no_faktur']) . '\')" style="background:#0ea5e9; color:#fff; border:none; padding:10px 14px; border-radius:6px; font-weight:600; cursor:pointer; font-family:\'Outfit\', sans-serif;"><i class="fa-solid fa-image"></i> Gambar</button>';
        echo '</div>';
        
    } else {
        echo '<div style="text-align:center; padding:20px; font-family:\'Outfit\', sans-serif;">Data tidak ditemukan.</div>';
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> — A STORE</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/sweetalert2.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/sweetalert2.all.min.js"></script>
    <script src="assets/js/notifications.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php if (in_array($page, ['des', 'des_kategori'])): ?>
    <style>
        @page {
            size: landscape;
            margin: 10mm;
        }
    </style>
    <?php endif; ?>
    <style>
        /* ── Sidebar nav group label ─────────────────── */
        .nav-group-label {
            padding: 18px 20px 6px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: rgba(255,255,255,0.45);
        }

        /* ── Role badge di header ────────────────────── */
        .role-badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 600;
            padding: 2px 10px;
            border-radius: 20px;
            background: var(--primary-light);
            color: var(--primary-color);
        }

        /* ── Kritis badge di nav ─────────────────────── */
        .kritis-badge {
            background: var(--danger-color);
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            padding: 1px 7px;
            border-radius: 20px;
            margin-left: auto;
        }

        /* ── Error flash message ─────────────────────── */
        .flash-error {
            padding: 12px 16px;
            background: var(--danger-light, #fee2e2);
            color: var(--danger-color, #dc2626);
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* ── Form controls global ────────────────────── */
        .form-control {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-family: inherit;
            font-size: 14px;
            outline: none;
            box-sizing: border-box;
        }
        .form-group { margin-bottom: 16px; }
        .form-label {
            display: block;
            margin-bottom: 6px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
        }
        .btn-outline {
            background: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-main);
        }
        .btn-action-icon {
            width: 32px; height: 32px;
            border-radius: 6px;
            display: inline-flex; align-items: center; justify-content: center;
            border: none; cursor: pointer; color: white;
            margin-right: 4px; font-size: 14px;
        }
        .btn-edit   { background: var(--warning-color); }
        .btn-delete { background: var(--danger-color);  }

        /* ── Modal ───────────────────────────────────── */
        .modal {
            display: none; position: fixed; z-index: 1000;
            left: 0; top: 0; width: 100%; height: 100%;
            background: rgba(15,23,42,0.45); backdrop-filter: blur(4px);
            align-items: center; justify-content: center;
        }
        .modal.active { display: flex; }
        .modal-content {
            background: var(--surface-card);
            border-radius: var(--border-radius-lg);
            width: 100%; max-width: 520px;
            padding: 28px; box-shadow: var(--shadow-card);
            animation: slideUp 0.25s ease;
            max-height: 90vh; overflow-y: auto;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px) }
            to   { opacity: 1; transform: translateY(0) }
        }
        .modal-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 14px;
        }
        .modal-header h3 { font-size: 18px; font-weight: 700; }
        .close-btn { background: none; border: none; font-size: 20px; cursor: pointer; color: var(--text-muted); }
        .close-btn:hover { color: var(--danger-color); }

        /* ── POS Styles (penjualan) ──────────────────── */
        .pos-grid { display: flex; gap: 24px; flex-wrap: wrap; align-items: flex-start; }
        .pos-left  { flex: 7; display: flex; flex-direction: column; gap: 24px; min-width: 400px; }
        .pos-right { flex: 3; display: flex; flex-direction: column; gap: 0; min-width: 300px; position: sticky; top: 20px; }
        .search-product { display: flex; gap: 12px; margin-bottom: 20px; position: relative; }
        .autocomplete-dropdown {
            position: absolute; top: 100%; left: 0; right: 0;
            background: #fff; box-shadow: var(--shadow-md); border-radius: 8px;
            max-height: 240px; overflow-y: auto; z-index: 200;
            border: 1px solid var(--border-color); display: none;
        }
        .autocomplete-item {
            padding: 10px 16px; cursor: pointer; border-bottom: 1px solid var(--border-color);
            display: flex; justify-content: space-between; align-items: center;
        }
        .autocomplete-item:hover { background: var(--primary-light); }
        .cart-qty { width: 60px; padding: 6px; text-align: center; border: 1px solid var(--border-color); border-radius: 6px; }
        .cart-action { padding: 6px 10px; border-radius: 6px; color: var(--danger-color); background: var(--danger-light); border: none; cursor: pointer; }
        .summary-box { background: var(--surface-card); padding: 20px; border-radius: var(--border-radius-md); margin-bottom: 20px; border: 1px solid var(--border-color); }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 15px; color: var(--text-muted); }
        .summary-total { display: flex; justify-content: space-between; margin-top: 16px; padding-top: 16px; border-top: 2px dashed var(--border-color); font-size: 20px; font-weight: 700; }
        .btn-large { width: 100%; padding: 16px; font-size: 16px; font-weight: 600; justify-content: center; border-radius: 8px; display: flex; align-items: center; gap: 8px; font-family: inherit; cursor: pointer; transition: var(--transition-fast); }
        .btn-success { background-color: var(--success-color); color: white; border: none; }
        .btn-success:hover { background-color: #059669; }
    </style>
</head>

<body>
    <div class="app-container">
        <!-- ═══════════════ SIDEBAR ═══════════════ -->
        <aside class="app-sidebar">
            <div class="sidebar-header" style="align-items:center; gap:12px; border-bottom: 1px solid #334155; margin-bottom: 8px;">
                <img src="assets/img/logo.jpeg" alt="Logo" style="width: 36px; height: 36px; object-fit: contain; border-radius: 8px; background: #fff; padding: 3px;">
                <div>
                    <h1 class="sidebar-brand" style="line-height:1;">A STORE</h1>
                    <div style="font-size:10px; color:#9CA3AF; letter-spacing:0.5px; margin-top:4px; font-weight:600;"><?= htmlspecialchars($roleUser) ?></div>
                </div>
            </div>

            <nav class="sidebar-nav">
                <?php if ($role === 'Pimpinan'): ?>
                    <!-- ── PIMPINAN NAV ── -->
                    <a href="?page=dashboard" class="nav-item <?= $page === 'dashboard' ? 'active' : '' ?>">
                        <i class="fa-solid fa-table-columns"></i>
                        <span>Dasbor</span>
                    </a>

                    <div class="nav-group-label">Data Master</div>
                    <a href="?page=barang" class="nav-item <?= $page === 'barang' ? 'active' : '' ?>">
                        <i class="fa-solid fa-boxes-stacked"></i>
                        <span>Data Barang</span>
                    </a>
                    <a href="?page=kategori" class="nav-item <?= $page === 'kategori' ? 'active' : '' ?>">
                        <i class="fa-solid fa-tags"></i>
                        <span>Kategori Barang</span>
                    </a>
                    <a href="?page=kelola_kasir" class="nav-item <?= $page === 'kelola_kasir' ? 'active' : '' ?>">
                        <i class="fa-solid fa-user-tie"></i>
                        <span>Data Kasir</span>
                    </a>

                    <div class="nav-group-label">Stok</div>
                    <a href="?page=stok" class="nav-item <?= $page === 'stok' ? 'active' : '' ?>">
                        <i class="fa-solid fa-warehouse"></i>
                        <span>Manajemen Stok</span>
                        <?php if ($jumlahKritis > 0): ?><span class="kritis-badge"><?= $jumlahKritis ?></span><?php endif; ?>
                    </a>
                    <a href="?page=riwayat_stok" class="nav-item <?= $page === 'riwayat_stok' ? 'active' : '' ?>">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        <span>Riwayat Stok</span>
                    </a>

                    <div class="nav-group-label">Transaksi</div>
                    <a href="?page=riwayat_transaksi" class="nav-item <?= $page === 'riwayat_transaksi' ? 'active' : '' ?>">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <span>Penjualan</span>
                    </a>
                    <a href="?page=historis" class="nav-item <?= $page === 'historis' ? 'active' : '' ?>">
                        <i class="fa-solid fa-chart-column"></i>
                        <span>Data Penjualan (Historis)</span>
                    </a>
                    <a href="?page=laporan" class="nav-item <?= $page === 'laporan' ? 'active' : '' ?>">
                        <i class="fa-solid fa-file-invoice"></i>
                        <span>Laporan Penjualan</span>
                    </a>
                    <div class="nav-item-dropdown" onclick="toggleSubmenu('submenu-peramalan')">
                        <div class="nav-item <?= in_array($page, ['des', 'des_kategori']) ? 'active' : '' ?>" style="margin-bottom:0; cursor:pointer;">
                            <i class="fa-solid fa-chart-line"></i>
                            <span style="flex:1;">Peramalan Stok</span>
                            <i class="fa-solid fa-chevron-down" style="font-size:10px; width:auto; text-align:right;"></i>
                        </div>
                        <div id="submenu-peramalan" style="display:<?= in_array($page, ['des', 'des_kategori']) ? 'block' : 'none' ?>; padding-left:32px; padding-top:4px; margin-bottom:4px; flex-direction:column;">
                            <a href="?page=des_kategori" class="nav-item <?= $page === 'des_kategori' ? 'active' : '' ?>" style="padding: 8px 12px; font-size: 13px;">Berdasarkan Kategori</a>
                            <?php /* Disembunyikan sementara sesuai permintaan
                            <a href="?page=des" class="nav-item <?= $page === 'des' ? 'active' : '' ?>" style="padding: 8px 12px; font-size: 13px;">Berdasarkan Barang</a>
                            */ ?>
                        </div>
                    </div>
                    <a href="?page=pengaturan" class="nav-item <?= $page === 'pengaturan' ? 'active' : '' ?>">
                        <i class="fa-solid fa-gear"></i>
                        <span>Pengaturan</span>
                    </a>

                <?php else: ?>
                    <!-- ── KASIR NAV ── -->
                    <div class="nav-group-label">Transaksi</div>
                    <a href="?page=penjualan" class="nav-item <?= $page === 'penjualan' ? 'active' : '' ?>">
                        <i class="fa-solid fa-cash-register"></i>
                        <span>Penjualan</span>
                    </a>
                    <a href="?page=riwayat_transaksi" class="nav-item <?= $page === 'riwayat_transaksi' ? 'active' : '' ?>">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        <span>Riwayat Transaksi</span>
                    </a>
                    <a href="?page=data_barang_kasir" class="nav-item <?= $page === 'data_barang_kasir' ? 'active' : '' ?>">
                        <i class="fa-solid fa-boxes-stacked"></i>
                        <span>Data Barang</span>
                    </a>
                    <a href="?page=stok_barang" class="nav-item <?= $page === 'stok_barang' ? 'active' : '' ?>">
                        <i class="fa-solid fa-warehouse"></i>
                        <span>Stok Barang</span>
                    </a>
                <?php endif; ?>
            </nav>

            <div class="sidebar-footer">
                <a href="logout.php" class="nav-item logout-item" onclick="confirmLogout(event)">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Keluar</span>
                </a>
            </div>
        </aside>

        <!-- ═══════════════ MAIN CONTENT ═══════════════ -->
        <div class="main-wrapper">
            <header class="main-header" style="background:#fff; border-bottom:1px solid var(--border-color); padding:0 32px; z-index: 10;">
                <h2 class="page-title" style="font-size:20px;"><?= htmlspecialchars($pageTitle) ?></h2>
                <div class="user-controls" style="display:flex; align-items:center; gap:20px;">
                    <!-- Notification Bell -->
                    <div class="notification-wrapper" style="position:relative;" onclick="toggleNotifDropdown(event)">
                        <div class="notification-trigger" style="position:relative; width:36px; height:36px; display:flex; align-items:center; justify-content:center; background:var(--bg-body); border-radius:50%; cursor:pointer;">
                            <i class="fa-solid fa-bell" style="color:var(--text-muted); font-size:16px;"></i>
                            <?php if($jumlahKritis > 0): ?>
                            <span class="badge" style="position:absolute; top:-2px; right:-2px; width:16px; height:16px; background:var(--primary-dark); color:#fff; font-size:9px; border-radius:50%; display:flex; align-items:center; justify-content:center; border:2px solid #fff;"><?= $jumlahKritis ?></span>
                            <?php endif; ?>
                        </div>
                        <div id="notifDropdown" style="display:none; position:absolute; top:100%; right:-10px; background:#fff; border:1px solid var(--border-color); border-radius:8px; box-shadow:0 10px 25px rgba(0,0,0,0.05); min-width:260px; z-index:100; margin-top:10px; overflow:hidden;">
                            <div style="padding:12px 16px; font-weight:700; border-bottom:1px solid var(--border-color); font-size:13px; color:var(--text-main); display:flex; justify-content:space-between; align-items:center;">
                                <span>Notifikasi</span>
                                <?php if($jumlahKritis > 0): ?>
                                    <span style="font-size:11px; background:var(--danger-light); color:var(--danger-color); padding:2px 8px; border-radius:12px; font-weight:600;"><?= $jumlahKritis ?> Menipis</span>
                                <?php endif; ?>
                            </div>
                            <?php if($jumlahKritis > 0): ?>
                                <div style="padding:12px 16px; border-bottom:1px solid var(--border-color); background:var(--danger-light);">
                                    <div style="display:flex; align-items:center; gap:12px;">
                                        <div style="width:32px; height:32px; border-radius:50%; background:var(--danger-color); color:white; display:flex; align-items:center; justify-content:center; flex-shrink:0;"><i class="fa-solid fa-triangle-exclamation"></i></div>
                                        <div style="flex:1;">
                                            <div style="font-size:13px; font-weight:600;">Stok Kritis</div>
                                            <div style="font-size:11px; color:var(--text-muted); margin-top:2px;">Ada <?= $jumlahKritis ?> barang menipis</div>
                                        </div>
                                    </div>
                                    <div style="margin-top:10px; display:flex; gap:6px;">
                                        <button type="button" onclick="bukaModalNotifikasiKritis()" class="btn btn-outline" style="padding:5px 8px; font-size:11px; border-radius:4px; background:#fff; flex:1; justify-content:center;">
                                            <i class="fa-solid fa-bell"></i> Modal Detail
                                        </button>
                                        <a href="?page=stok&filter=kritis" class="btn btn-primary" style="padding:5px 8px; font-size:11px; border-radius:4px; flex:1; justify-content:center; text-decoration:none;">
                                            Kelola Stok
                                        </a>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div style="padding:20px; text-align:center; color:var(--text-muted); font-size:12px;">Tidak ada notifikasi baru.</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- User Profile Dropdown -->
                    <div class="profile-wrapper" style="position:relative;" onclick="toggleProfileDropdown(event)">
                        <div class="user-profile" style="display:flex; align-items:center; gap:12px; padding:4px 8px; cursor:pointer;">
                            <?php
                            $top_foto = $_SESSION['foto_profil'] ?? null;
                            if ($top_foto && file_exists(__DIR__ . '/assets/img/profil/' . $top_foto)) {
                                $top_avatarSrc = 'assets/img/profil/' . htmlspecialchars($top_foto);
                            } else {
                                $top_avatarSrc = 'https://ui-avatars.com/api/?name=' . urlencode($namaUser) . '&background=111111&color=fff&rounded=true';
                            }
                            ?>
                            <img src="<?= $top_avatarSrc ?>" alt="Avatar" class="avatar" style="width:36px; height:36px; border-radius:50%; object-fit:cover;">
                            <div class="user-info" style="display:flex; flex-direction:column; text-align:left;">
                                <span class="user-name" style="font-size:13px; font-weight:700; color:var(--text-main); line-height:1.2;"><?= htmlspecialchars($namaUser) ?></span>
                                <span class="user-role" style="font-size:11px; color:var(--text-muted);"><?= htmlspecialchars($roleUser) ?></span>
                            </div>
                            <i class="fa-solid fa-chevron-down dropdown-icon" style="font-size:10px; color:var(--text-muted); margin-left:4px;"></i>
                        </div>
                        <div id="profileDropdown" style="display:none; position:absolute; top:100%; right:0; background:#fff; border:1px solid var(--border-color); border-radius:8px; box-shadow:0 10px 25px rgba(0,0,0,0.05); min-width:180px; z-index:100; margin-top:10px; overflow:hidden;">
                            <a href="?page=pengaturan" style="display:block; padding:12px 16px; color:var(--text-main); text-decoration:none; font-size:13px; border-bottom:1px solid var(--border-color);"><i class="fa-solid fa-gear" style="width:20px; color:var(--text-muted);"></i> Pengaturan</a>
                            <a href="logout.php" style="display:block; padding:12px 16px; color:var(--danger-color); text-decoration:none; font-size:13px;" onclick="confirmLogout(event)"><i class="fa-solid fa-right-from-bracket" style="width:20px;"></i> Keluar</a>
                        </div>
                    </div>
                </div>
            </header>

            <main class="content-area" style="padding:24px 32px 40px 32px;">
                <?php
                $swalFlash = null;
                if (isset($_GET['msg'])) {
                    $swalFlash = ['type' => 'success', 'title' => 'Berhasil!', 'message' => $_GET['msg']];
                } elseif (isset($_GET['sukses'])) {
                    $swalFlash = ['type' => 'success', 'title' => 'Berhasil!', 'message' => $_GET['sukses']];
                } elseif (isset($_GET['error'])) {
                    $swalFlash = ['type' => 'error', 'title' => 'Perhatian / Terjadi Kesalahan', 'message' => $_GET['error']];
                } elseif (isset($_GET['warning'])) {
                    $swalFlash = ['type' => 'warning', 'title' => 'Peringatan', 'message' => $_GET['warning']];
                }
                ?>
                <noscript>
                <?php if (isset($_GET['error'])): ?>
                    <div class="flash-error">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <?= htmlspecialchars($_GET['error']) ?>
                    </div>
                <?php endif; ?>
                </noscript>

                <?php
                $pageFile = "includes/pages/{$page}.php";
                if (file_exists($pageFile)) {
                    include $pageFile;
                } else {
                    echo "<div style='padding:40px; text-align:center; color:var(--text-muted);'><i class='fa-solid fa-triangle-exclamation' style='font-size:40px; margin-bottom:16px; display:block;'></i>Halaman <strong>{$page}</strong> belum tersedia.</div>";
                }
                ?>
            </main>
        </div>
    </div>

    <script>
        function openModal(id) { document.getElementById(id).classList.add('active'); }
        function closeModal(id) { document.getElementById(id).classList.remove('active'); }
        function toggleSubmenu(id) { 
            const el = document.getElementById(id); 
            if (el) el.style.display = el.style.display === 'none' ? 'block' : 'none'; 
        }
        // ─── Dropdown Functions ─────────────────────────────────────────────
        function toggleNotifDropdown(e) {
            e.stopPropagation();
            const notif = document.getElementById('notifDropdown');
            const profile = document.getElementById('profileDropdown');
            profile.style.display = 'none'; // Close the other dropdown
            notif.style.display = notif.style.display === 'none' ? 'block' : 'none';
        }

        function toggleProfileDropdown(e) {
            e.stopPropagation();
            const profile = document.getElementById('profileDropdown');
            const notif = document.getElementById('notifDropdown');
            notif.style.display = 'none'; // Close the other dropdown
            profile.style.display = profile.style.display === 'none' ? 'block' : 'none';
        }

        document.addEventListener('click', function(e) {
            // Close modals
            if (e.target.classList.contains('modal')) {
                e.target.classList.remove('active');
            }
            
            // Close dropdowns
            const profile = document.getElementById('profileDropdown');
            const notif = document.getElementById('notifDropdown');
            if (profile && profile.style.display === 'block') profile.style.display = 'none';
            if (notif && notif.style.display === 'block') notif.style.display = 'none';
        });

        // ─── Global Struk Functions ─────────────────────────────────────────
        function printStruk() {
            var printContents = document.getElementById("strukPrintArea").innerHTML;
            var originalContents = document.body.innerHTML;
            document.body.innerHTML = "<div style='padding:20px; display:flex; justify-content:center;'>" + printContents + "</div>";
            window.print();
            document.body.innerHTML = originalContents;
            location.reload();
        }

        function shareStrukImage(btn, noFaktur) {
            if (typeof html2canvas === "undefined") {
                var script = document.createElement("script");
                script.src = "https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js";
                script.onload = () => processStrukImage(btn, noFaktur);
                document.head.appendChild(script);
            } else {
                processStrukImage(btn, noFaktur);
            }
        }

        function processStrukImage(btn, noFaktur) {
            const originalText = btn.innerHTML;
            btn.innerHTML = "<i class='fa-solid fa-spinner fa-spin'></i> Tunggu...";
            btn.disabled = true;

            const area = document.getElementById("strukPrintArea");
            html2canvas(area, { scale: 2, backgroundColor: "#ffffff" }).then(canvas => {
                canvas.toBlob(function(blob) {
                    const file = new File([blob], "Struk_" + noFaktur + ".png", { type: "image/png" });
                    
                    if (navigator.canShare && navigator.canShare({ files: [file] })) {
                        navigator.share({
                            files: [file],
                            title: "Struk " + noFaktur,
                            text: "Berikut adalah struk transaksi Anda."
                        }).catch(console.error).finally(() => { btn.innerHTML = originalText; btn.disabled = false; });
                    } else {
                        const url = URL.createObjectURL(blob);
                        const a = document.createElement("a");
                        a.href = url;
                        a.download = "Struk_" + noFaktur + ".png";
                        a.click();
                        URL.revokeObjectURL(url);
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil Diunduh!',
                            text: 'Gambar struk berhasil diunduh. Anda bisa melampirkannya langsung ke WhatsApp.',
                            confirmButtonColor: '#111111'
                        });
                        btn.innerHTML = originalText; 
                        btn.disabled = false;
                    }
                }, "image/png");
            });
        }

        // ─── Trigger SweetAlert Flash Message jika ada di URL ────────────────
        <?php if (!empty($swalFlash)): ?>
        document.addEventListener('DOMContentLoaded', function() {
            showAlert('<?= $swalFlash['type'] ?>', '<?= addslashes(htmlspecialchars($swalFlash['title'])) ?>', '<?= addslashes(htmlspecialchars($swalFlash['message'])) ?>', <?= $swalFlash['type'] === 'success' ? 3200 : 'null' ?>);
            cleanUrlFlashParams();
        });
        <?php endif; ?>

        // ─── Modal Notifikasi Stok Kritis ───────────────────────────────────
        const listKritisGlobal = <?= json_encode($kritisListItems) ?>;
        function bukaModalNotifikasiKritis() {
            if (!listKritisGlobal || listKritisGlobal.length === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'Stok Aman',
                    text: 'Semua persediaan barang dalam kondisi aman dan mencukupi.',
                    confirmButtonColor: '#111111'
                });
                return;
            }
            let html = '<div style="max-height:280px; overflow-y:auto; text-align:left; margin-top:10px;">';
            html += '<table style="width:100%; font-size:12.5px; border-collapse:collapse;">';
            html += '<tr style="border-bottom:1px solid #e2e8f0; color:#64748b; font-weight:600;"><th style="padding:8px 6px; text-align:left;">Kode</th><th style="padding:8px 6px; text-align:left;">Barang</th><th style="padding:8px 6px; text-align:center;">Sisa</th><th style="padding:8px 6px; text-align:center;">Batas Min</th></tr>';
            listKritisGlobal.forEach(item => {
                html += `<tr style="border-bottom:1px solid #f1f5f9;"><td style="padding:8px 6px; font-weight:600;">${item.kode_barang}</td><td style="padding:8px 6px;">${item.nama_produk}</td><td style="padding:8px 6px; text-align:center; color:#ef4444; font-weight:700;">${item.stok_aktual}</td><td style="padding:8px 6px; text-align:center; color:#64748b;">${item.stok_min}</td></tr>`;
            });
            html += '</table></div>';
            Swal.fire({
                title: '⚠️ Peringatan Stok Kritis',
                html: html,
                icon: 'warning',
                confirmButtonColor: '#111111',
                confirmButtonText: '<i class="fa-solid fa-boxes-stacked"></i> Buka Manajemen Stok',
                showCancelButton: true,
                cancelButtonText: 'Tutup',
                cancelButtonColor: '#64748b'
            }).then((res) => {
                if (res.isConfirmed) {
                    window.location.href = '?page=stok&filter=kritis';
                }
            });
        }
    </script>
</body>
</html>