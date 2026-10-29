<?php
// Handle Hapus Riwayat Peramalan Kategori
if (isset($_GET['action']) && $_GET['action'] == 'hapus_riwayat' && isset($_GET['id_riwayat'])) {
    $idHapus = (int)$_GET['id_riwayat'];
    get_query($conn, "DELETE FROM tbl_riwayat_peramalan WHERE id_riwayat=$idHapus AND tipe='kategori'");
    $msgRiwayat = "Riwayat peramalan kategori berhasil dihapus!";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'simpan_riwayat') {
    $kb = $conn->real_escape_string($_POST['id_kategori']);
    $nama = $conn->real_escape_string($_POST['nama_kategori']);
    $periode = $conn->real_escape_string($_POST['n_periode']);
    $alpha_val = (float)$_POST['alpha'];
    $hasil = (int)$_POST['hasil'];
    $akurasi_val = (float)$_POST['akurasi'];
    $mape_val = isset($_POST['mape']) ? (float)$_POST['mape'] : round(max(0, 100 - $akurasi_val), 2);
    $target = $conn->real_escape_string($_POST['target_waktu']);
    $detail_json = !empty($_POST['detail_json']) ? "'" . $conn->real_escape_string($_POST['detail_json']) . "'" : "NULL";
    
    $checkMapeCol = $conn->query("SHOW COLUMNS FROM tbl_riwayat_peramalan LIKE 'mape'");
    if ($checkMapeCol && $checkMapeCol->num_rows > 0) {
        $sqlIns = "INSERT INTO tbl_riwayat_peramalan (tanggal_hitung, tipe, referensi, nama_referensi, periode, alpha, target_waktu, hasil, akurasi, mape, detail_json) 
                   VALUES (NOW(), 'kategori', '$kb', '$nama', '$periode', $alpha_val, '$target', $hasil, $akurasi_val, $mape_val, $detail_json)";
    } else {
        $sqlIns = "INSERT INTO tbl_riwayat_peramalan (tanggal_hitung, tipe, referensi, nama_referensi, periode, alpha, target_waktu, hasil, akurasi, detail_json) 
                   VALUES (NOW(), 'kategori', '$kb', '$nama', '$periode', $alpha_val, '$target', $hasil, $akurasi_val, $detail_json)";
    }
    get_query($conn, $sqlIns);
    
    $msgRiwayat = "Riwayat peramalan berhasil disimpan!";
}
?>
<?php
// Halaman: Peramalan Stok Kategori (DES)
$id_kategori = isset($_GET['id_kategori']) ? (int)$_GET['id_kategori'] : 0;
$target_peramalan = $_GET['target_peramalan'] ?? 'mingguan';
$target_peramalan = in_array($target_peramalan, ['mingguan', 'bulanan']) ? $target_peramalan : 'mingguan';

// Tanggal Acuan Perhitungan (Mundur dari tanggal ini)
$tanggal_acuan = !empty($_GET['tanggal_acuan']) ? preg_replace('/[^0-9\-]/', '', $_GET['tanggal_acuan']) : date('Y-m-d');
$ts_acuan = strtotime($tanggal_acuan);
if (!$ts_acuan) {
    $tanggal_acuan = date('Y-m-d');
    $ts_acuan = strtotime($tanggal_acuan);
}

// Jumlah periode kedepan (1 minggu, 2 minggu, dst / 1 bulan, 2 bulan, dst)
$jumlah_target = max(1, (int)($_GET['jumlah_target'] ?? 1));

// Hitung parameter dan satuan waktu
if ($target_peramalan === 'mingguan') {
    $step_per_group      = 7; // 1 minggu = 7 hari
    $total_steps         = $jumlah_target * $step_per_group;
    $satuan_waktu        = 'Minggu';
    $satuan_data         = 'Hari';
    $label_target_waktu  = ($jumlah_target == 1) ? '1 Minggu Depan' : $jumlah_target . ' Minggu Depan';
    $label_target_detail = $label_target_waktu . ' (' . $total_steps . ' Hari)';
    $default_n           = max(7, $total_steps);
} else {
    $step_per_group      = 4; // 1 bulan = 4 minggu
    $total_steps         = $jumlah_target * $step_per_group;
    $satuan_waktu        = 'Bulan';
    $satuan_data         = 'Minggu';
    $label_target_waktu  = ($jumlah_target == 1) ? '1 Bulan Depan' : $jumlah_target . ' Bulan Depan';
    $label_target_detail = $label_target_waktu . ' (' . $total_steps . ' Minggu)';
    $default_n           = max(4, $total_steps);
}

$prev_target = $_GET['prev_target'] ?? $target_peramalan;
$target_berubah = isset($_GET['prev_target']) && $_GET['prev_target'] !== $target_peramalan;

if ($target_berubah || !isset($_GET['n_periode'])) {
    $n_periode = $default_n;
} else {
    $n_periode = max(3, (int)$_GET['n_periode']);
}

$alpha     = isset($_GET['alpha']) ? floatval($_GET['alpha']) : 0.1;
$alpha     = in_array($alpha, [0.1, 0.2, 0.3, 0.4, 0.5, 0.6, 0.7, 0.8, 0.9]) ? $alpha : 0.1;
$hitung    = isset($_GET['id_kategori']) && $id_kategori > 0;

// Ambil daftar kategori
$qK = get_query($conn, "SELECT id_kategori, nama_kategori FROM tbl_kategori ORDER BY nama_kategori");

// Logika Peramalan DES (Double exponential smoothing)
$rows = [];
$aktual = [];
$S1 = []; // S'
$S2 = []; // S"
$a  = []; // Level
$b  = []; // Trend
$F  = []; // Forecast
$n = 0;
$hasilPeramalan = 0;
$akurasi = 0;
$kualitas = '';

// Variabel tambahan untuk breakdown peramalan
$forecast_breakdown = [];
$group_breakdown = [];
$nk = '';

if ($hitung) {
    $qNk = $conn->query("SELECT nama_kategori FROM tbl_kategori WHERE id_kategori=$id_kategori"); 
    $nk = ($qNk && $qNk->num_rows > 0) ? $qNk->fetch_assoc()['nama_kategori'] : '';
    if ($target_peramalan === 'bulanan') {
        // PERAMALAN BULANAN (History Mingguan: Interval 7 Hari Mundur dari Tanggal Acuan)
        $oldest_start = date('Y-m-d', strtotime('-' . (($n_periode - 1) * 7 + 6) . ' days', $ts_acuan));
        $newest_end   = date('Y-m-d', $ts_acuan);

        $sql = "SELECT 
                    DATE(p.tanggal_waktu) as tgl,
                    SUM(d.qty) as total_qty
                FROM tbl_detail_penjualan d
                JOIN tbl_penjualan p ON d.id_penjualan = p.id_penjualan
                JOIN tbl_barang b ON d.kode_barang = b.kode_barang
                JOIN tbl_kategori k ON b.kategori = k.nama_kategori
                WHERE k.id_kategori = $id_kategori
                  AND DATE(p.tanggal_waktu) BETWEEN '$oldest_start' AND '$newest_end'
                GROUP BY DATE(p.tanggal_waktu)";

        $qD = get_query($conn, $sql);
        $daily_sales = [];
        while ($r = $qD->fetch_assoc()) {
            $daily_sales[$r['tgl']] = (float)$r['total_qty'];
        }

        $rows = [];
        for ($w = 1; $w <= $n_periode; $w++) {
            $step_back = $n_periode - $w;
            $end_ts   = strtotime("-" . ($step_back * 7) . " days", $ts_acuan);
            $start_ts = strtotime("-" . ($step_back * 7 + 6) . " days", $ts_acuan);

            $period_qty = 0;
            $cur_ts = $start_ts;
            while ($cur_ts <= $end_ts) {
                $cur_ymd = date('Y-m-d', $cur_ts);
                if (isset($daily_sales[$cur_ymd])) {
                    $period_qty += $daily_sales[$cur_ymd];
                }
                $cur_ts = strtotime("+1 day", $cur_ts);
            }

            $rows[] = [
                'periode_num'   => $w,
                'label_rentang' => date('d M Y', $start_ts) . ' - ' . date('d M Y', $end_ts),
                'tgl_awal'      => date('d M Y', $start_ts),
                'tgl_akhir'     => date('d M Y', $end_ts),
                'total_qty'     => $period_qty
            ];
        }
    } else {
        // PERAMALAN MINGGUAN (History Harian: Interval 1 Hari Mundur dari Tanggal Acuan)
        $oldest_start = date('Y-m-d', strtotime('-' . ($n_periode - 1) . ' days', $ts_acuan));
        $newest_end   = date('Y-m-d', $ts_acuan);

        $sql = "SELECT 
                    DATE(p.tanggal_waktu) as tgl,
                    SUM(d.qty) as total_qty
                FROM tbl_detail_penjualan d
                JOIN tbl_penjualan p ON d.id_penjualan = p.id_penjualan
                JOIN tbl_barang b ON d.kode_barang = b.kode_barang
                JOIN tbl_kategori k ON b.kategori = k.nama_kategori
                WHERE k.id_kategori = $id_kategori
                  AND DATE(p.tanggal_waktu) BETWEEN '$oldest_start' AND '$newest_end'
                GROUP BY DATE(p.tanggal_waktu)";

        $qD = get_query($conn, $sql);
        $daily_sales = [];
        while ($r = $qD->fetch_assoc()) {
            $daily_sales[$r['tgl']] = (float)$r['total_qty'];
        }

        $rows = [];
        for ($h = 1; $h <= $n_periode; $h++) {
            $step_back = $n_periode - $h;
            $cur_ts = strtotime("-" . $step_back . " days", $ts_acuan);
            $cur_ymd = date('Y-m-d', $cur_ts);
            $qty = isset($daily_sales[$cur_ymd]) ? $daily_sales[$cur_ymd] : 0;

            $rows[] = [
                'periode_num'   => $h,
                'label_rentang' => date('d M Y', $cur_ts),
                'tgl_awal'      => date('d M Y', $cur_ts),
                'tgl_akhir'     => date('d M Y', $cur_ts),
                'total_qty'     => $qty
            ];
        }
    }

    $n = count($rows);
    $total_aktual_terjual = array_sum(array_column($rows, 'total_qty'));

    if ($n >= 3 && $total_aktual_terjual > 0) {
        $dataCukup = true;
        $aktual = array_map(function($item) { return (float)$item['total_qty']; }, $rows);
        
        // Double exponential smoothing
        $S1[0] = $aktual[0];
        $S2[0] = $aktual[0];
        $a[0]  = 2 * $S1[0] - $S2[0];
        $b[0]  = ($alpha / (1 - $alpha)) * ($S1[0] - $S2[0]);
        $F[0]  = 0; // Tidak ada forecast untuk periode pertama
        
        $sum_mape = 0;
        $count_mape = 0;
        
        for ($i = 1; $i < $n; $i++) {
            $X_i = $aktual[$i];
            
            $S1[$i] = $alpha * $X_i + (1 - $alpha) * $S1[$i-1];
            $S2[$i] = $alpha * $S1[$i] + (1 - $alpha) * $S2[$i-1];
            
            $a[$i] = 2 * $S1[$i] - $S2[$i];
            $b[$i] = ($alpha / (1 - $alpha)) * ($S1[$i] - $S2[$i]);
            
            $F[$i] = $a[$i-1] + $b[$i-1] * 1;
            
            if ($X_i > 0) {
                $err = abs($X_i - $F[$i]);
                $mape = ($err / $X_i) * 100;
                $sum_mape += $mape;
                $count_mape++;
            }
        }
        
        // Prediksi untuk masa depan (m = 1 s/d total_steps)
        $hasilPeramalan = 0;
        
        for ($m = 1; $m <= $total_steps; $m++) {
            $prediksi = max(0, $a[$n-1] + ($m * $b[$n-1]));
            $group_num = (int)ceil($m / $step_per_group);

            if ($target_peramalan === 'bulanan') {
                $start_m_ts = strtotime("+" . (($m - 1) * 7 + 1) . " days", $ts_acuan);
                $end_m_ts   = strtotime("+" . ($m * 7) . " days", $ts_acuan);
                $label_m    = 'Minggu Depan ' . $m;
                $rentang_m  = date('d M Y', $start_m_ts) . ' - ' . date('d M Y', $end_m_ts);
            } else {
                $m_ts       = strtotime("+" . $m . " days", $ts_acuan);
                $label_m    = 'Hari Depan ' . $m;
                $rentang_m  = date('d M Y', $m_ts);
            }

            $forecast_breakdown[] = [
                'm'             => $m,
                'group_num'     => $group_num,
                'label'         => $label_m,
                'rentang_m'     => $rentang_m,
                'prediksi'      => round($prediksi),
                'prediksi_raw'  => $prediksi
            ];
            $hasilPeramalan += $prediksi;
        }
        $hasilPeramalan = round($hasilPeramalan);
        
        // Ringkasan per kelompok (Per Minggu atau Per Bulan)
        for ($g = 1; $g <= $jumlah_target; $g++) {
            $start_step = ($g - 1) * $step_per_group + 1;
            $end_step   = $g * $step_per_group;
            $g_total    = 0;
            for ($s = $start_step; $s <= $end_step; $s++) {
                if (isset($forecast_breakdown[$s - 1])) {
                    $g_total += $forecast_breakdown[$s - 1]['prediksi'];
                }
            }

            if ($target_peramalan === 'bulanan') {
                $g_start_ts = strtotime("+" . (($g - 1) * 28 + 1) . " days", $ts_acuan);
                $g_end_ts   = strtotime("+" . ($g * 28) . " days", $ts_acuan);
                $sublabel   = date('d M Y', $g_start_ts) . ' - ' . date('d M Y', $g_end_ts);
            } else {
                $g_start_ts = strtotime("+" . (($g - 1) * 7 + 1) . " days", $ts_acuan);
                $g_end_ts   = strtotime("+" . ($g * 7) . " days", $ts_acuan);
                $sublabel   = date('d M Y', $g_start_ts) . ' - ' . date('d M Y', $g_end_ts);
            }

            $group_breakdown[] = [
                'nomor'     => $g,
                'label'     => $satuan_waktu . ' ke-' . $g,
                'sublabel'  => $sublabel,
                'total_qty' => $g_total
            ];
        }
        
        $avg_mape = ($count_mape > 0) ? round($sum_mape / $count_mape, 2) : 0;
        $akurasi  = round(max(0, 100 - $avg_mape), 1);
        
        if ($avg_mape <= 10) {
            $kualitas = "Sangat Baik";
        } elseif ($avg_mape <= 20) {
            $kualitas = "Baik";
        } elseif ($avg_mape <= 50) {
            $kualitas = "Cukup / Wajar";
        } else {
            $kualitas = "Kurang / Tidak Akurat";
        }
    } else {
        $dataCukup = false;
    }
}
?>

<style>
@media print {
    @page {
        size: landscape;
        margin: 10mm;
    }

    /* Sembunyikan elemen antarmuka web */
    .app-sidebar,
    .main-header,
    .sidebar-footer,
    .breadcrumb-area,
    .btn,
    .close-btn,
    form,
    select,
    input,
    .no-print {
        display: none !important;
    }

    /* Reset layout container agar mengisi penuh halaman landscape */
    html, body {
        background: #fff !important;
        color: #000 !important;
        width: 100% !important;
        height: auto !important;
        overflow: visible !important;
    }

    .app-container,
    .main-wrapper,
    .content-area {
        display: block !important;
        height: auto !important;
        overflow: visible !important;
        padding: 0 !important;
        margin: 0 !important;
        background: transparent !important;
    }

    #peramalan-print-area {
        display: block !important;
        position: static !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    /* Card Utama saat Cetak */
    .card-peramalan-main {
        display: block !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px !important;
        padding: 20px !important;
        margin-bottom: 20px !important;
        box-shadow: none !important;
    }

    .card-peramalan-right {
        border-left: none !important;
        padding-left: 0 !important;
    }

    /* Tabel Historis WAJIB TAMPIL saat cetak */
    #detailPerhitungan {
        display: block !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px !important;
        box-shadow: none !important;
        margin-bottom: 20px !important;
        page-break-inside: auto !important;
    }

    #detailPerhitungan .close-btn {
        display: none !important;
    }

    .data-table {
        width: 100% !important;
        border-collapse: collapse !important;
        font-size: 11px !important;
    }

    .data-table th, .data-table td {
        border: 1px solid #cbd5e1 !important;
        padding: 6px 8px !important;
    }

    .data-table th {
        background-color: #f1f5f9 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
        font-weight: bold !important;
    }

    .print-only {
        display: block !important;
    }
}
</style>

<!-- Breadcrumb -->
<div class="breadcrumb-area" style="font-size:13px; color:var(--text-muted); margin-bottom:20px;">
    Peramalan / <strong style="color:var(--text-main);">Peramalan Stok Kategori (DES)</strong>
</div>

<!-- Main Card Split Layout -->
<div id="peramalan-print-area">

    <!-- Header & Info Khusus Cetak -->
    <?php if ($hitung && isset($hasilPeramalan)): ?>
    <div class="print-only" style="display:none; border-bottom:2px solid #000; padding-bottom:10px; margin-bottom:16px;">
        <div style="display:flex; justify-content:space-between; align-items:flex-end;">
            <div>
                <h2 style="margin:0; font-size:20px; font-weight:800; text-transform:uppercase; letter-spacing:0.5px;">A STORE</h2>
                <div style="font-size:12px; color:#475569; margin-top:2px;">Laporan Hasil Peramalan Stok Kategori — Double Exponential Smoothing (DES)</div>
            </div>
            <div style="text-align:right; font-size:11px; color:#475569;">
                Tanggal Cetak: <strong><?= date('d M Y H:i') ?></strong>
            </div>
        </div>
    </div>

    <div class="print-only" style="display:none; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px 16px; margin-bottom:16px; font-size:12px;">
        <div style="display:grid; grid-template-columns: repeat(5, 1fr); gap:12px;">
            <div><span style="color:#64748b;">Kategori:</span> <strong style="color:#0f172a;"><?= htmlspecialchars($nk) ?></strong></div>
            <div><span style="color:#64748b;">Target Peramalan:</span> <strong style="color:#0f172a;"><?= htmlspecialchars($label_target_detail) ?></strong></div>
            <div><span style="color:#64748b;">Tanggal Acuan:</span> <strong style="color:#0f172a;"><?= date('d M Y', $ts_acuan) ?></strong></div>
            <div><span style="color:#64748b;">Periode Data:</span> <strong style="color:#0f172a;"><?= $n_periode ?> <?= $satuan_data ?></strong></div>
            <div><span style="color:#64748b;">Nilai Alpha (α):</span> <strong style="color:#0f172a;"><?= $alpha ?></strong></div>
        </div>
    </div>
    <?php endif; ?>

<div class="card-peramalan-main" style="background:#fff; border-radius:16px; border:1px solid var(--border-color); padding:32px; display:grid; grid-template-columns: 1fr 320px; gap:40px; margin-bottom:24px;">
    
    <!-- Left: Form -->
    <div class="no-print">
        <form method="GET" id="formPeramalan" style="display:flex; flex-direction:column; gap:16px;">
            <input type="hidden" name="page" value="des_kategori">
            <input type="hidden" name="prev_target" value="<?= $target_peramalan ?>">
            
            <!-- Tipe Target Peramalan -->
            <div style="display:grid; grid-template-columns: 160px 1fr; align-items:center;">
                <label class="form-label" style="margin:0;">Target Peramalan</label>
                <select name="target_peramalan" id="target_peramalan" class="form-control" onchange="gantiTipeTarget(this.value)" style="background:var(--bg-body); border-color:transparent;">
                    <option value="mingguan" <?= $target_peramalan === 'mingguan' ? 'selected' : '' ?>>Peramalan Mingguan (Data Harian)</option>
                    <option value="bulanan" <?= $target_peramalan === 'bulanan' ? 'selected' : '' ?>>Peramalan Bulanan (Data Mingguan)</option>
                </select>
            </div>

            <!-- Jangka Waktu Peramalan (1 minggu, 2 minggu, dst / 1 bulan, 2 bulan, dst) -->
            <div style="display:grid; grid-template-columns: 160px 1fr; align-items:center;">
                <label class="form-label" style="margin:0;">Jangka Waktu</label>
                <div style="display:flex; gap:8px; align-items:center;">
                    <select id="pilihan_durasi" class="form-control" onchange="gantiDurasi(this.value)" style="background:var(--bg-body); border-color:transparent; flex:1;">
                        <!-- Opsi digenerate oleh initDurasiDropdown() -->
                    </select>
                    <div id="wrapper_custom" style="display:none; align-items:center; gap:6px;">
                        <input type="number" id="input_custom_durasi" class="form-control" min="1" max="100" value="<?= $jumlah_target ?>" style="width:75px; background:var(--bg-body); border-color:transparent;" oninput="inputCustomDurasi(this.value)">
                        <span id="label_custom_unit" style="font-size:12px; font-weight:600; color:var(--text-muted); white-space:nowrap;"><?= $satuan_waktu ?></span>
                    </div>
                    <input type="hidden" name="jumlah_target" id="input_jumlah_target" value="<?= $jumlah_target ?>">
                </div>
            </div>

            <!-- Tanggal Acuan (Mulai Hitung Mundur dari Tanggal Ini) -->
            <div style="display:grid; grid-template-columns: 160px 1fr; align-items:center;">
                <label class="form-label" style="margin:0;">
                    Tanggal Acuan
                    <small style="display:block; color:var(--text-muted); font-size:10px; font-weight:normal;">Mundur dari tgl ini</small>
                </label>
                <input type="date" name="tanggal_acuan" id="tanggal_acuan" class="form-control" value="<?= htmlspecialchars($tanggal_acuan) ?>" style="background:var(--bg-body); border-color:transparent;">
            </div>

            <!-- Pilih Kategori -->
            <div style="display:grid; grid-template-columns: 160px 1fr; align-items:center;">
                <label class="form-label" style="margin:0;">Pilih Kategori</label>
                <select name="id_kategori" class="form-control" required style="background:var(--bg-body); border-color:transparent;">
                    <option value="">Pilih kategori</option>
                    <?php 
                    $qK->data_seek(0);
                    while ($rk = $qK->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($rk['id_kategori']) ?>"
                        <?= ($rk['id_kategori'] == $id_kategori) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($rk['nama_kategori']) ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Periode Data Historis -->
            <div style="display:grid; grid-template-columns: 160px 1fr; align-items:center;">
                <label class="form-label" style="margin:0;" id="label_n_periode">Periode Data (<?= $satuan_data ?>)</label>
                <input type="number" name="n_periode" id="input_n_periode" class="form-control" value="<?= $n_periode ?>" min="3" max="365" style="background:var(--bg-body); border-color:transparent;">
            </div>

            <!-- Nilai Alpha -->
            <div style="display:grid; grid-template-columns: 160px 1fr; align-items:center;">
                <label class="form-label" style="margin:0;">Nilai Alpha (α)<br><small style="color:var(--text-muted); font-size:10px;">Level Smoothing</small></label>
                <select name="alpha" class="form-control" style="background:var(--bg-body); border-color:transparent;">
                    <?php for($i = 0.1; $i <= 0.9; $i += 0.1): $val = round($i, 1); ?>
                        <option value="<?= $val ?>" <?= $alpha == $val ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <!-- Periode Output -->
            <div style="display:grid; grid-template-columns: 160px 1fr; align-items:center;">
                <label class="form-label" style="margin:0;">Periode Output</label>
                <input type="text" id="preview_output" class="form-control" value="<?= htmlspecialchars($label_target_detail) ?>" disabled style="background:var(--bg-body); border-color:transparent; font-weight:600; color:var(--text-main);">
            </div>

            <div style="display:flex; justify-content:flex-end; margin-top:16px;">
                <button type="submit" class="btn btn-primary" style="background:#111; color:#fff; width:200px; justify-content:center;">
                    Proses Peramalan
                </button>
            </div>
        </form>
    </div>

    <!-- Right: Hasil Peramalan -->
    <div class="card-peramalan-right" style="border-left:1px solid var(--border-color); padding-left:36px;">
        <h3 style="font-size:16px; font-weight:700; margin-bottom:20px;">Hasil Peramalan</h3>
        
        <?php if ($hitung && isset($dataCukup) && !$dataCukup): ?>
            <div style="background:#fff7ed; border:1px solid #fed7aa; border-radius:12px; padding:20px; text-align:center;">
                <i class="fa-solid fa-triangle-exclamation" style="font-size:32px; color:#ea580c; margin-bottom:12px; display:block;"></i>
                <div style="font-size:15px; font-weight:700; color:#9a3412; margin-bottom:8px;">Data Penjualan Belum Mencukupi</div>
                <div style="font-size:13px; color:#7c2d12; line-height:1.6;">
                    Kategori <strong><?= htmlspecialchars($nk) ?></strong> tidak memiliki data penjualan pada rentang <strong><?= !empty($rows) ? ($rows[0]['label_rentang'] . ' s/d ' . $rows[$n-1]['label_rentang']) : 'yang dipilih' ?></strong>.<br>
                    Metode DES membutuhkan data penjualan nyata agar peramalan akurat dan sesuai histori penjualan.
                </div>
                <div style="font-size:12px; color:#9a3412; margin-top:12px; background:#ffedd5; padding:8px 12px; border-radius:6px; display:inline-block;">
                    <i class="fa-solid fa-circle-info"></i> Silakan sesuaikan <strong>Tanggal Acuan</strong> atau pilih kategori lain yang memiliki riwayat penjualan pada rentang tersebut.
                </div>
            </div>
        <?php elseif ($hitung && isset($hasilPeramalan)): ?>
            <div style="margin-bottom:16px;">
                <div style="font-size:12px; color:var(--text-muted); margin-bottom:4px;">Target Waktu</div>
                <div style="font-size:14px; font-weight:700; color:var(--text-main);"><?= htmlspecialchars($label_target_detail) ?></div>
            </div>
            
            <div style="margin-bottom:16px;">
                <div style="font-size:12px; color:var(--text-muted); margin-bottom:4px;">Prediksi Total Stok</div>
                <div style="font-size:18px; font-weight:800; color:var(--primary-color);">
                    <?= number_format($hasilPeramalan, 0, ',', '.') ?> <span style="font-size:13px; font-weight:600; color:var(--text-main);">Unit</span>
                </div>
            </div>
            
            <div style="margin-bottom:20px;">
                <div style="font-size:12px; color:var(--text-muted); margin-bottom:8px;">Evaluasi Model (MAPE)</div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:8px;">
                    <div style="background:var(--bg-body); border:1px solid var(--border-color); padding:8px 10px; border-radius:8px;">
                        <div style="font-size:10px; color:var(--text-muted); text-transform:uppercase; font-weight:600;">Rata-rata MAPE</div>
                        <div style="font-size:16px; font-weight:800; color:#ea580c; margin-top:2px;"><?= number_format($avg_mape, 2) ?>%</div>
                        <div style="font-size:10px; color:var(--text-muted);">Tingkat Error</div>
                    </div>
                    <div style="background:var(--bg-body); border:1px solid var(--border-color); padding:8px 10px; border-radius:8px;">
                        <div style="font-size:10px; color:var(--text-muted); text-transform:uppercase; font-weight:600;">Kategori Model</div>
                        <div style="font-size:16px; font-weight:800; color:var(--primary-color); margin-top:2px;"><?= $kualitas ?></div>
                        <div style="font-size:10px; color:var(--text-muted);">Standar Lewis</div>
                    </div>
                </div>
                <div style="font-size:12px; line-height:1.5; color:var(--text-muted);">
                    Rata-rata kesalahan peramalan (MAPE) adalah <strong><?= number_format($avg_mape, 2) ?>%</strong> (Kategori: <strong><?= $kualitas ?></strong>).
                </div>
            </div>

            <!-- Ringkasan Per Periode (Per Minggu atau Per Bulan) jika jumlah_target > 1 -->
            <?php if ($jumlah_target > 1 && !empty($group_breakdown)): ?>
            <div style="margin-bottom:20px; background:var(--bg-body); padding:14px; border-radius:10px; border:1px solid var(--border-color);">
                <div style="font-size:12px; font-weight:700; margin-bottom:10px; color:var(--text-main); display:flex; justify-content:space-between; align-items:center;">
                    <span><i class="fa-solid fa-layer-group" style="color:var(--primary-color); margin-right:4px;"></i> Ringkasan Per <?= $satuan_waktu ?>:</span>
                    <span style="font-size:11px; color:var(--text-muted); font-weight:600;"><?= $jumlah_target ?> <?= $satuan_waktu ?></span>
                </div>
                <div style="display:flex; flex-direction:column; gap:6px; max-height:170px; overflow-y:auto; padding-right:2px;">
                    <?php foreach ($group_breakdown as $gb): ?>
                    <div style="background:#fff; border:1px solid var(--border-color); padding:8px 10px; border-radius:6px; display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <div style="font-size:12px; font-weight:700; color:var(--text-main);"><?= $gb['label'] ?></div>
                            <div style="font-size:10px; color:var(--text-muted);"><?= $gb['sublabel'] ?></div>
                        </div>
                        <div style="font-size:14px; font-weight:700; color:var(--primary-color);">
                            <?= number_format($gb['total_qty'], 0, ',', '.') ?> <span style="font-size:10px; font-weight:500; color:var(--text-muted);">Unit</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Breakdown Detail Hari / Minggu -->
            <?php if (!empty($forecast_breakdown)): ?>
            <div style="margin-bottom:20px; background:var(--bg-body); padding:14px; border-radius:10px; border:1px solid var(--border-color);">
                <div style="font-size:12px; font-weight:700; margin-bottom:10px; color:var(--text-main); display:flex; justify-content:space-between; align-items:center;">
                    <span><i class="fa-solid fa-chart-simple" style="color:var(--primary-color); margin-right:4px;"></i> Rincian Tiap <?= $satuan_data ?> (<?= $total_steps ?> <?= $satuan_data ?>):</span>
                </div>
                <div style="display:grid; grid-template-columns:repeat(<?= ($total_steps <= 7) ? $total_steps : 4 ?>, 1fr); gap:6px; max-height:160px; overflow-y:auto; padding:2px; text-align:center;">
                    <?php foreach ($forecast_breakdown as $fh): ?>
                    <div style="background:#fff; border:1px solid var(--border-color); padding:6px 2px; border-radius:6px;">
                        <div style="font-size:9px; color:var(--text-muted); line-height:1.1;"><?= $fh['label'] ?></div>
                        <div style="font-size:12px; font-weight:700; color:var(--primary-color); margin-top:3px;"><?= $fh['prediksi'] ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <button type="button" onclick="document.getElementById('detailPerhitungan').style.display = 'block'; document.getElementById('detailPerhitungan').scrollIntoView({behavior:'smooth'});" class="btn btn-outline" style="width:100%; justify-content:center; font-size:13px; margin-bottom:12px;">
                <i class="fa-solid fa-chart-line" style="margin-right:6px;"></i> Proyeksi Masa Depan &amp; Detail Perhitungan
            </button>
            
            <div style="display:flex; gap:12px;">
                <form method="POST" action="" style="flex:1;">
                    <input type="hidden" name="action" value="simpan_riwayat">
                    <input type="hidden" name="id_kategori" value="<?= $id_kategori ?>">
                    <input type="hidden" name="nama_kategori" value="<?= htmlspecialchars($nk) ?>">
                    <input type="hidden" name="n_periode" value="<?= $n_periode ?>">
                    <input type="hidden" name="alpha" value="<?= $alpha ?>">
                    <input type="hidden" name="hasil" value="<?= $hasilPeramalan ?>">
                    <input type="hidden" name="akurasi" value="<?= max(0, $akurasi) ?>">
                    <input type="hidden" name="mape" value="<?= round($avg_mape, 2) ?>">
                    <input type="hidden" name="target_waktu" value="<?= htmlspecialchars($label_target_detail) ?>">
                    <?php
                    $history_rows = [];
                    $total_hist_err = 0;
                    for ($hi = 0; $hi < $n; $hi++) {
                        $err_val = null;
                        $mape_val = null;
                        if ($hi > 0) {
                            $err_val = round(abs($aktual[$hi] - $F[$hi]), 2);
                            $total_hist_err += $err_val;
                            if ($aktual[$hi] > 0) {
                                $mape_val = round(($err_val / $aktual[$hi]) * 100, 2);
                            }
                        }
                        $history_rows[] = [
                            'i'        => $hi + 1,
                            'label'    => $rows[$hi]['label_rentang'],
                            'aktual'   => $aktual[$hi],
                            's1'       => round($S1[$hi], 2),
                            's2'       => round($S2[$hi], 2),
                            'a'        => round($a[$hi], 2),
                            'b'        => round($b[$hi], 2),
                            'f'        => ($hi > 0) ? round($F[$hi], 2) : null,
                            'err'      => $err_val,
                            'mape'     => $mape_val
                        ];
                    }
                    $snapshotData = [
                        'tipe' => 'kategori',
                        'referensi' => $id_kategori,
                        'nama_referensi' => $nk,
                        'target_waktu' => $label_target_detail,
                        'target_peramalan' => $target_peramalan,
                        'jumlah_target' => $jumlah_target,
                        'tanggal_acuan' => $tanggal_acuan,
                        'periode' => $n_periode,
                        'satuan_data' => $satuan_data,
                        'satuan_waktu' => $satuan_waktu,
                        'alpha' => $alpha,
                        'hasil' => $hasilPeramalan,
                        'avg_mape' => $avg_mape,
                        'akurasi' => max(0, $akurasi),
                        'kualitas' => $kualitas,
                        'a_t' => isset($a[$n-1]) ? round($a[$n-1], 2) : 0,
                        'b_t' => isset($b[$n-1]) ? round($b[$n-1], 2) : 0,
                        'rentang_historis' => (!empty($rows) && $n > 0) ? ($rows[0]['label_rentang'] . ' s/d ' . $rows[$n-1]['label_rentang']) : '',
                        'history_rows' => $history_rows,
                        'total_aktual' => array_sum($aktual),
                        'total_abs_err' => round($total_hist_err, 2),
                        'group_breakdown' => $group_breakdown,
                        'forecast_breakdown' => $forecast_breakdown
                    ];
                    $jsonSnapshot = json_encode($snapshotData);
                    ?>
                    <input type="hidden" name="detail_json" value="<?= htmlspecialchars($jsonSnapshot, ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="btn btn-primary" style="background:#22c55e; width:100%; justify-content:center; font-size:13px;">
                        <i class="fa-solid fa-save"></i> Simpan Riwayat
                    </button>
                </form>
                <button onclick="cetakPeramalan()" class="btn btn-primary" style="background:#3b82f6; flex:1; justify-content:center; font-size:13px;">
                    <i class="fa-solid fa-print"></i> Cetak Peramalan
                </button>
            </div>
            
            <?php if(isset($msgRiwayat)): ?>
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    showSuccess(<?= json_encode($msgRiwayat) ?>);
                });
                </script>
                <noscript>
                <div style="margin-top:12px; padding:10px; background:#dcfce7; color:#166534; border-radius:6px; font-size:13px; text-align:center;">
                    <?= $msgRiwayat ?>
                </div>
                </noscript>
            <?php endif; ?>
        <?php else: ?>
            <div style="color:var(--text-muted); font-size:13px; display:flex; height:100%; align-items:center; opacity:0.6;">
                Pilih kategori dan klik Proses Peramalan untuk melihat hasil.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($hitung && isset($dataCukup) && $dataCukup): ?>
<!-- ── TABEL PROYEKSI PERAMALAN MASA DEPAN & DATA HISTORIS (Hidden by default) ── -->
<div id="detailPerhitungan" style="display:<?= (isset($_GET['show_detail']) && $_GET['show_detail'] == '1') ? 'block' : 'none' ?>; background:#fff; border:1px solid var(--border-color); border-radius:16px; overflow:hidden; animation: fadeIn 0.3s; margin-bottom:24px; box-shadow:0 4px 12px rgba(0,0,0,0.04);">
    <?php if (isset($_GET['show_detail']) && $_GET['show_detail'] == '1'): ?>
    <div style="padding:12px 20px; background:#eff6ff; border-bottom:1px solid #bfdbfe; color:#1e40af; font-size:13px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
        <div style="display:flex; align-items:center; gap:8px; font-weight:600;">
            <i class="fa-solid fa-circle-check" style="color:#2563eb; font-size:16px;"></i>
            <span>Mode Tampilan Penuh (Bukan Modal): Rincian proyeksi langkah ke-m dan audit data historis disajikan terbuka penuh tanpa batasan scroll popup.</span>
        </div>
        <a href="#tabelRiwayat" class="btn btn-outline btn-sm" style="font-size:11px; padding:4px 10px; border-color:#93c5fd; background:#fff; text-decoration:none; color:#1d4ed8; border-radius:6px;">
            <i class="fa-solid fa-clock-rotate-left"></i> Riwayat Peramalan
        </a>
    </div>
    <?php endif; ?>
    <!-- Header Utama -->
    <div style="padding:20px; border-bottom:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center; background:#fcfcfc;">
        <div>
            <h3 style="font-size:16px; font-weight:800; margin:0; display:flex; align-items:center; gap:8px;">
                <i class="fa-solid fa-forward" style="color:var(--primary-color);"></i>
                Proyeksi Peramalan Masa Depan &amp; Detail Perhitungan DES
            </h3>
            <div style="font-size:12px; color:var(--text-muted); margin-top:3px;">
                Double exponential smoothing (α = <?= $alpha ?>) &bull; Target: <strong><?= htmlspecialchars($label_target_detail) ?></strong>
            </div>
        </div>
        <button type="button" onclick="document.getElementById('detailPerhitungan').style.display = 'none'" class="close-btn" style="background:transparent; border:none; font-size:18px; cursor:pointer; color:var(--text-muted); padding:4px 8px;" title="Tutup Detail">
            <i class="fa-solid fa-times"></i>
        </button>
    </div>

    <!-- 1. BAGIAN UTAMA: PROYEKSI PERAMALAN MASA DEPAN (Langkah ke-m) -->
    <div style="padding:16px 20px; background:#f8fafc; border-bottom:1px solid var(--border-color);">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
            <div>
                <h4 style="margin:0; font-size:14px; font-weight:700; color:var(--text-main); display:flex; align-items:center; gap:6px;">
                    <i class="fa-solid fa-chart-line" style="color:var(--primary-color);"></i>
                    Proyeksi Kebutuhan Stok Masa Depan (<?= htmlspecialchars($label_target_detail) ?>)
                </h4>
                <p style="margin:4px 0 0 0; font-size:12px; color:var(--text-muted);">
                    Rumus DES Proyeksi Langkah ke-m: <code>F(t+m) = a_t + (m &times; b_t)</code>
                </p>
            </div>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <div style="background:#fff; border:1px solid var(--border-color); padding:5px 12px; border-radius:8px; font-size:12px;">
                    <span style="color:var(--text-muted);">Level Terakhir (a_t):</span> <strong><?= number_format($a[$n-1], 2) ?></strong>
                </div>
                <div style="background:#fff; border:1px solid var(--border-color); padding:5px 12px; border-radius:8px; font-size:12px;">
                    <span style="color:var(--text-muted);">Tren Terakhir (b_t):</span> <strong><?= number_format($b[$n-1], 2) ?></strong>
                </div>
            </div>
        </div>
    </div>
    
    <div class="table-responsive">
        <table class="data-table" style="text-align:center;">
            <thead>
                <tr>
                    <th>Langkah (m)</th>
                    <th>Periode Target</th>
                    <th>Rentang Waktu</th>
                    <th>Kelompok</th>
                    <th>Perhitungan: a_t + (m &times; b_t)</th>
                    <th>Hasil Peramalan (Unit)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($forecast_breakdown as $fb): 
                    $m_val = $fb['m'];
                    $calc_val = $a[$n-1] + ($m_val * $b[$n-1]);
                    $grp_label = $satuan_waktu . ' ke-' . $fb['group_num'];
                ?>
                <tr>
                    <td style="font-weight:700; color:var(--primary-color);">m = <?= $m_val ?></td>
                    <td style="font-weight:600;"><?= $fb['label'] ?></td>
                    <td><span style="font-size:11px; color:var(--text-muted); font-weight:600;"><?= $fb['rentang_m'] ?></span></td>
                    <td><span class="badge" style="background:var(--bg-body); color:var(--text-main); font-size:11px; padding:3px 10px; border-radius:12px; border:1px solid var(--border-color);"><?= $grp_label ?></span></td>
                    <td style="font-family:monospace; font-size:12px;">
                        <?= number_format($a[$n-1], 2) ?> + (<?= $m_val ?> &times; <?= number_format($b[$n-1], 2) ?>) = <?= number_format($calc_val, 2) ?>
                    </td>
                    <td style="font-weight:800; color:var(--primary-color); font-size:14px;"><?= $fb['prediksi'] ?></td>
                </tr>
                <?php endforeach; ?>
                <tr style="background:var(--primary-light); font-weight:800; border-top:2px solid var(--primary-color);">
                    <td colspan="5" style="text-align:right; padding-right:16px; font-size:13px; color:var(--text-main);">
                        TOTAL ESTIMASI KEBUTUHAN STOK (<?= htmlspecialchars($label_target_detail) ?>):
                    </td>
                    <td style="color:var(--primary-color); font-size:16px;">
                        <?= number_format($hasilPeramalan, 0, ',', '.') ?> Unit
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- 2. DATA HISTORIS HARI/MINGGU & PERHITUNGAN DES SMOOTHING (Dipindahkan ke bawah proyeksi) -->
    <div style="padding:16px 20px; background:#f8fafc; border-top:2px solid var(--border-color); border-bottom:1px solid var(--border-color); margin-top:8px;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
            <div>
                <h4 style="margin:0; font-size:14px; font-weight:700; color:var(--text-main); display:flex; align-items:center; gap:6px;">
                    <i class="fa-solid fa-clock-rotate-left" style="color:#64748b;"></i>
                    Data Historis <?= $satuan_data ?> &amp; Audit Perhitungan DES (α = <?= $alpha ?>)
                </h4>
                <p style="margin:4px 0 0 0; font-size:12px; color:var(--text-muted);">
                    Data penjualan aktual <?= $n ?> <?= strtolower($satuan_data) ?> historis sebagai dasar perhitungan parameter pemulusan ganda (Level a_t dan Tren b_t).
                </p>
            </div>
            <div style="font-size:12px; color:var(--text-muted); background:#fff; border:1px solid var(--border-color); padding:5px 12px; border-radius:8px;">
                Rata-rata MAPE: <strong style="color:#ea580c;"><?= number_format($avg_mape, 2) ?>%</strong> (<?= $kualitas ?>)
            </div>
        </div>
    </div>
    
    <div class="table-responsive">
        <table class="data-table" style="text-align:center;">
            <thead>
                <tr>
                    <th><?= $satuan_data ?> Ke-</th>
                    <th><?= ($target_peramalan === 'bulanan') ? 'Rentang Waktu (7 Hari)' : 'Tanggal' ?></th>
                    <th>Aktual (X_t)</th>
                    <th>S'_t</th>
                    <th>S"_t</th>
                    <th>a_t</th>
                    <th>b_t</th>
                    <th>Peramalan (F_t)</th>
                    <th>Error Absolut (|e_t|)</th>
                    <th>MAPE (%)</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total_abs_err = 0;
                for ($i = 0; $i < $n; $i++):
                    $fi_disp    = ($i == 0)   ? '—' : number_format($F[$i], 2);
                    $err_disp   = '—';
                    $mape_disp  = '—';
                    if ($i > 0) {
                        $err = abs($aktual[$i] - $F[$i]);
                        $total_abs_err += $err;
                        $err_disp = number_format($err, 2);
                        if ($aktual[$i] > 0) {
                            $mape_disp = number_format(($err / $aktual[$i]) * 100, 2) . '%';
                        } else {
                            $mape_disp = '<span style="color:var(--text-muted); font-size:11px;" title="Penjualan aktual 0, dilewati agar tidak terjadi division by zero">0%*</span>';
                        }
                    }
                ?>
                <tr>
                    <td style="font-weight:600;"><?= $i + 1 ?></td>
                    <td><?= $rows[$i]['label_rentang'] ?></td>
                    <td style="font-weight:700;"><?= $aktual[$i] ?></td>
                    <td><?= number_format($S1[$i], 2) ?></td>
                    <td><?= number_format($S2[$i], 2) ?></td>
                    <td style="font-weight:600;"><?= number_format($a[$i], 2) ?></td>
                    <td style="font-weight:600;"><?= number_format($b[$i], 2) ?></td>
                    <td style="color:var(--primary-color); font-weight:600;"><?= $fi_disp ?></td>
                    <td style="font-weight:600;"><?= $err_disp ?></td>
                    <td style="font-weight:600;"><?= $mape_disp ?></td>
                </tr>
                <?php endfor; ?>
            </tbody>
            <tfoot>
                <tr style="background:#f8fafc; font-weight:700;">
                    <td colspan="2" style="text-align:right; font-weight:700;">Total Evaluasi:</td>
                    <td style="font-weight:700;"><?= array_sum($aktual) ?></td>
                    <td colspan="5"></td>
                    <td style="font-weight:700; color:var(--text-main); font-size:13px;"><?= number_format($total_abs_err, 2) ?><br><span style="font-size:10px; font-weight:normal; color:var(--text-muted);">&Sigma; |e_t|</span></td>
                    <td style="font-weight:800; color:#ea580c; font-size:13px;"><?= number_format($avg_mape, 2) ?>%<br><span style="font-size:10px; font-weight:normal; color:var(--text-muted);">Rata-rata MAPE</span></td>
                </tr>
                <tr style="background:#f1f5f9; font-weight:700;">
                    <td colspan="8" style="text-align:right; padding-right:16px;">
                        <span style="color:var(--text-main); font-size:13px;">Kategori Evaluasi Model (Standar Lewis):</span>
                    </td>
                    <td colspan="2" style="color:var(--primary-color); font-size:14px; text-align:center;">
                        <strong><?= $kualitas ?></strong> <span style="font-size:11px; font-weight:600; color:#ea580c;">(MAPE <?= number_format($avg_mape, 2) ?>%)</span>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
    <div style="padding:10px 16px; background:#fafafa; border-top:1px solid var(--border-color); font-size:11px; color:var(--text-muted); line-height:1.6;">
        <i class="fa-solid fa-circle-info" style="color:var(--primary-color); margin-right:4px;"></i>
        <strong>Keterangan Rumus &amp; Evaluasi Model:</strong><br>
        &bull; <strong>Error Absolut (|e_t|)</strong>: |Aktual (X_t) - Peramalan (F_t)|.<br>
        &bull; <strong>MAPE per Periode</strong>: (|e_t| / X_t) &times; 100%. Periode ke-1 merupakan inisialisasi ($S'_0, S''_0$) sehingga evaluasi dimulai periode ke-2.<br>
        &bull; <strong>Rata-rata MAPE</strong>: Rata-rata persentase kesalahan dari <?= $count_mape ?> periode uji yang memiliki transaksi penjualan.<br>
        &bull; <strong>Kategori Model (Standar Lewis)</strong>: &le;10% Sangat Baik, 10-20% Baik, 20-50% Cukup/Wajar, &gt;50% Kurang/Tidak Akurat (Kategori peramalan ini: <strong><?= $kualitas ?></strong>).
    </div>
</div>
<?php endif; ?>
</div> <!-- Close peramalan-print-area -->

<!-- RIWAYAT PERAMALAN -->
<div id="tabelRiwayat" class="no-print" style="background:#fff; border:1px solid var(--border-color); border-radius:16px; overflow:hidden; margin-bottom:24px;">
    <div style="padding:20px; border-bottom:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h3 style="font-size:15px; font-weight:700; margin:0;">Riwayat Peramalan Kategori</h3>
            <div style="font-size:12px; color:var(--text-muted); margin-top:2px;">Daftar peramalan kategori yang telah disimpan beserta laporan rinciannya.</div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Waktu Acuan</th>
                    <th>ID Kategori</th>
                    <th>Nama Kategori</th>
                    <th>Target Waktu</th>
                    <th>Periode</th>
                    <th>Alpha</th>
                    <th>Hasil Peramalan</th>
                    <th>MAPE</th>
                    <th style="text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $riwayatDataStore = [];
                $qHist = get_query($conn, "SELECT * FROM tbl_riwayat_peramalan WHERE tipe='kategori' ORDER BY id_riwayat DESC LIMIT 30");
                if($qHist && $qHist->num_rows > 0):
                    while($rh = $qHist->fetch_assoc()):
                        $riwayatDataStore[$rh['id_riwayat']] = $rh;
                        $detRow = !empty($rh['detail_json']) ? json_decode($rh['detail_json'], true) : [];
                        $targetTypeRow = $detRow['target_peramalan'] ?? (strpos($rh['target_waktu'], 'Bulan') !== false ? 'bulanan' : 'mingguan');
                        $jumlahTRow = $detRow['jumlah_target'] ?? 1;
                        $tglARow = $detRow['tanggal_acuan'] ?? '';
                        $waktuAcuanDisplay = !empty($tglARow) ? date('d/m/Y', strtotime($tglARow)) : date('d/m/Y', strtotime($rh['tanggal_hitung']));
                ?>
                <tr>
                    <td title="Waktu simpan: <?= date('d/m/Y H:i', strtotime($rh['tanggal_hitung'])) ?>"><?= $waktuAcuanDisplay ?></td>
                    <td><strong><?= htmlspecialchars($rh['referensi']) ?></strong></td>
                    <td><?= htmlspecialchars($rh['nama_referensi']) ?></td>
                    <td><span style="font-weight:600; color:var(--text-main);"><?= htmlspecialchars($rh['target_waktu']) ?></span></td>
                    <td><?= $rh['periode'] ?> <?= strpos($rh['target_waktu'], 'Minggu') !== false ? 'Hari' : 'Minggu' ?></td>
                    <td><?= $rh['alpha'] ?></td>
                    <td style="color:var(--primary-color); font-weight:bold;"><?= number_format($rh['hasil'],0,',','.') ?> Unit</td>
                    <?php
                    $linkKalkulator = "?page=des_kategori&id_kategori=" . urlencode($rh['referensi']) . "&target_peramalan=" . urlencode($targetTypeRow) . "&jumlah_target=" . urlencode($jumlahTRow) . "&n_periode=" . urlencode($rh['periode']) . "&alpha=" . urlencode($rh['alpha']) . "&show_detail=1";
                    if ($tglARow) {
                        $linkKalkulator .= "&tanggal_acuan=" . urlencode($tglARow);
                    }
                    
                    if (isset($rh['mape']) && $rh['mape'] !== null && $rh['mape'] !== '') {
                        $mapeFormatted = number_format((float)$rh['mape'], 1) . '%';
                    } elseif (isset($detRow['avg_mape']) && $detRow['avg_mape'] !== null && $detRow['avg_mape'] !== '') {
                        $mapeFormatted = number_format((float)$detRow['avg_mape'], 1) . '%';
                    } elseif (isset($rh['akurasi'])) {
                        $calcMape = max(0, 100 - (float)$rh['akurasi']);
                        $mapeFormatted = number_format($calcMape, 1) . '%';
                    } else {
                        $mapeFormatted = '-';
                    }
                    ?>
                    <td style="color:#ea580c; font-weight:700;"><?= $mapeFormatted ?></td>
                    <td style="white-space:nowrap; text-align:center;">
                        <a href="<?= $linkKalkulator ?>" class="btn btn-primary btn-sm" style="padding:4px 10px; font-size:12px; border-radius:6px; display:inline-flex; align-items:center; gap:4px; margin-right:4px; text-decoration:none; background:#111; color:#fff;" title="Buka Detail Perhitungan Penuh di Halaman (Bukan Modal)">
                            <i class="fa-solid fa-calculator"></i> Buka Detail
                        </a>
                        <button type="button" class="btn btn-outline btn-sm" onclick="bukaModalDetailRiwayat(<?= $rh['id_riwayat'] ?>)" style="padding:4px 8px; font-size:12px; border-radius:6px; display:inline-flex; align-items:center; gap:4px; margin-right:4px;" title="Lihat Ringkasan Modal">
                            <i class="fa-solid fa-eye" style="color:var(--primary-color);"></i>
                        </button>
                        <a href="?page=des_kategori&action=hapus_riwayat&id_riwayat=<?= $rh['id_riwayat'] ?>" onclick="return confirmDeleteLink(event, this.href, 'Apakah Anda yakin ingin menghapus riwayat peramalan ini?');" class="btn btn-outline btn-sm" style="padding:4px 8px; font-size:12px; border-radius:6px; color:#ef4444; border-color:#fee2e2;" title="Hapus Riwayat">
                            <i class="fa-solid fa-trash-can"></i>
                        </a>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr>
                    <td colspan="9" style="text-align:center; padding:30px; color:var(--text-muted);">Belum ada riwayat peramalan kategori.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Detail Riwayat Peramalan Kategori -->
<div id="modalDetailRiwayat" class="no-print" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.6); z-index:9999; backdrop-filter:blur(4px); align-items:center; justify-content:center; padding:16px;">
    <div style="background:#fff; border-radius:16px; width:100%; max-width:800px; max-height:90vh; display:flex; flex-direction:column; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); overflow:hidden; animation: fadeIn 0.2s ease-out;">
        
        <!-- Modal Header -->
        <div style="padding:18px 24px; border-bottom:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center; background:#fafafa;">
            <div>
                <h3 style="margin:0; font-size:16px; font-weight:800; color:var(--text-main); display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-file-waveform" style="color:var(--primary-color);"></i>
                    Laporan Detail Riwayat Peramalan Kategori
                </h3>
                <div id="mdr_subtitle" style="font-size:12px; color:var(--text-muted); margin-top:3px;">
                    Riwayat # &bull; Waktu Acuan: -
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
                <a id="mdr_btn_header_full" href="#" class="btn btn-primary btn-sm" style="background:#111; color:#fff; font-size:12px; padding:6px 14px; border-radius:8px; display:inline-flex; align-items:center; gap:6px; text-decoration:none;" title="Buka Detail Perhitungan Penuh di Halaman (Bukan Modal)">
                    <i class="fa-solid fa-expand"></i> Buka Layar Penuh
                </a>
                <button type="button" onclick="tutupModalDetailRiwayat()" style="background:transparent; border:none; font-size:18px; color:var(--text-muted); cursor:pointer; padding:4px 8px; border-radius:6px;">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Modal Body (Scrollable) -->
        <div style="padding:24px; overflow-y:auto; flex:1; display:flex; flex-direction:column; gap:20px;">
            
            <!-- Grid Ringkasan Atas -->
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(210px, 1fr)); gap:12px;">
                <div style="background:var(--bg-body); border:1px solid var(--border-color); border-radius:12px; padding:14px;">
                    <div style="font-size:11px; color:var(--text-muted); font-weight:600; text-transform:uppercase;">Kategori Peramalan</div>
                    <div id="mdr_objek" style="font-size:14px; font-weight:800; color:var(--text-main); margin-top:4px;">-</div>
                    <div id="mdr_kode" style="font-size:11px; color:var(--text-muted); margin-top:2px;">-</div>
                </div>
                <div style="background:var(--bg-body); border:1px solid var(--border-color); border-radius:12px; padding:14px;">
                    <div style="font-size:11px; color:var(--text-muted); font-weight:600; text-transform:uppercase;">Target Proyeksi</div>
                    <div id="mdr_target" style="font-size:14px; font-weight:800; color:var(--text-main); margin-top:4px;">-</div>
                    <div id="mdr_rentang_histori" style="font-size:11px; color:var(--text-muted); margin-top:2px;">-</div>
                </div>
                <div style="background:var(--primary-light); border:1px solid rgba(var(--primary-rgb),0.2); border-radius:12px; padding:14px;">
                    <div style="font-size:11px; color:var(--text-muted); font-weight:600; text-transform:uppercase;">Prediksi Total Stok</div>
                    <div id="mdr_hasil" style="font-size:20px; font-weight:900; color:var(--primary-color); margin-top:2px;">-</div>
                    <div id="mdr_akurasi" style="font-size:11px; color:var(--text-main); font-weight:600; margin-top:2px;">-</div>
                </div>
            </div>

            <!-- Detail Parameter DES -->
            <div style="background:#fff; border:1px solid var(--border-color); border-radius:12px; padding:16px;">
                <div style="font-size:12px; font-weight:700; color:var(--text-main); margin-bottom:10px; display:flex; align-items:center; gap:6px;">
                    <i class="fa-solid fa-sliders" style="color:var(--primary-color);"></i>
                    Parameter Model Double exponential smoothing
                </div>
                <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:12px; text-align:center;">
                    <div style="background:var(--bg-body); padding:10px; border-radius:8px;">
                        <div style="font-size:10px; color:var(--text-muted);">Alpha (&alpha;)</div>
                        <div id="mdr_alpha" style="font-size:14px; font-weight:700; color:var(--text-main); margin-top:2px;">-</div>
                    </div>
                    <div style="background:var(--bg-body); padding:10px; border-radius:8px;">
                        <div style="font-size:10px; color:var(--text-muted);">Periode Historis</div>
                        <div id="mdr_periode" style="font-size:14px; font-weight:700; color:var(--text-main); margin-top:2px;">-</div>
                    </div>
                    <div style="background:var(--bg-body); padding:10px; border-radius:8px;">
                        <div style="font-size:10px; color:var(--text-muted);">Level Terakhir (a_t)</div>
                        <div id="mdr_at" style="font-size:14px; font-weight:700; color:var(--text-main); margin-top:2px;">-</div>
                    </div>
                    <div style="background:var(--bg-body); padding:10px; border-radius:8px;">
                        <div style="font-size:10px; color:var(--text-muted);">Tren Terakhir (b_t)</div>
                        <div id="mdr_bt" style="font-size:14px; font-weight:700; color:var(--text-main); margin-top:2px;">-</div>
                    </div>
                </div>
            </div>

            <!-- 1. Container Data Historis & Audit Perhitungan DES (S', S'', a, b, F, Error, MAPE) -->
            <div id="mdr_history_container" style="background:#fff; border:1px solid var(--border-color); border-radius:12px; overflow:hidden;">
                <div style="padding:12px 16px; background:#f8fafc; border-bottom:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                    <div>
                        <div style="font-size:12px; font-weight:700; color:var(--text-main); display:flex; align-items:center; gap:6px;">
                            <i class="fa-solid fa-clock-rotate-left" style="color:var(--primary-color);"></i>
                            Audit Detail Perhitungan DES &amp; Evaluasi MAPE (Data Historis)
                        </div>
                        <div style="font-size:11px; color:var(--text-muted); margin-top:2px;">
                            Data penjualan historis aktual beserta parameter pemulusan ganda, peramalan F_t, nilai selisih error absolut, dan MAPE.
                        </div>
                    </div>
                </div>
                <div class="table-responsive" style="overflow-x:auto;">
                    <table class="data-table" style="text-align:center; font-size:12px; margin:0;">
                        <thead>
                            <tr style="background:#f1f5f9;">
                                <th>Ke-</th>
                                <th>Tanggal / Rentang</th>
                                <th>Aktual (X_t)</th>
                                <th>S'_t</th>
                                <th>S"_t</th>
                                <th>a_t</th>
                                <th>b_t</th>
                                <th>Peramalan (F_t)</th>
                                <th>Error Absolut (|e_t|)</th>
                                <th>MAPE (%)</th>
                            </tr>
                        </thead>
                        <tbody id="mdr_history_tbody">
                            <!-- Injected by JS -->
                        </tbody>
                        <tfoot id="mdr_history_tfoot">
                            <!-- Injected by JS -->
                        </tfoot>
                    </table>
                </div>
                <div style="padding:8px 14px; background:#fafafa; border-top:1px solid var(--border-color); font-size:11px; color:var(--text-muted); line-height:1.5;">
                    &bull; <strong>Error Absolut (|e_t|)</strong> = |Aktual (X_t) - Peramalan (F_t)| &bull; <strong>MAPE</strong> = (|e_t| / X_t) &times; 100% &bull; Periode ke-1 adalah inisialisasi ($S'_0, S''_0$).
                </div>
            </div>

            <!-- 2. Container Rincian Proyeksi Langkah ke-m -->
            <div id="mdr_breakdown_container" style="background:#fff; border:1px solid var(--border-color); border-radius:12px; overflow:hidden;">
                <div style="padding:12px 16px; background:#f8fafc; border-bottom:1px solid var(--border-color); font-size:12px; font-weight:700; color:var(--text-main); display:flex; justify-content:space-between; align-items:center;">
                    <span><i class="fa-solid fa-list-check" style="color:var(--primary-color); margin-right:6px;"></i> Rincian Proyeksi Peramalan Langkah ke-m:</span>
                    <span style="font-family:monospace; font-size:11px; color:var(--text-muted); font-weight:normal;">F(t+m) = a_t + (m &times; b_t)</span>
                </div>
                <div class="table-responsive" style="overflow-x:auto;">
                    <table class="data-table" style="text-align:center; font-size:12px; margin:0;">
                        <thead>
                            <tr style="background:#f1f5f9;">
                                <th>Langkah (m)</th>
                                <th>Periode</th>
                                <th>Rentang</th>
                                <th>Kelompok</th>
                                <th>Hitungan DES</th>
                                <th>Prediksi</th>
                            </tr>
                        </thead>
                        <tbody id="mdr_breakdown_tbody">
                            <!-- Injected by JS -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Group Breakdown Cards (jika ada sub kelompok minggu/bulan) -->
            <div id="mdr_groups_container" style="display:none;">
                <div style="font-size:12px; font-weight:700; color:var(--text-main); margin-bottom:8px;">
                    <i class="fa-solid fa-layer-group" style="color:var(--primary-color); margin-right:4px;"></i> Ringkasan Sub-Periode:
                </div>
                <div id="mdr_groups_list" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(180px, 1fr)); gap:8px;">
                    <!-- Injected by JS -->
                </div>
            </div>

        </div>

        <!-- Modal Footer -->
        <div style="padding:16px 24px; border-top:1px solid var(--border-color); background:#fafafa; display:flex; justify-content:space-between; align-items:center; gap:12px;">
            <div style="font-size:11px; color:var(--text-muted);">
                Data tersimpan di basis data riwayat peramalan toko.
            </div>
            <div style="display:flex; gap:10px;">
                <a id="mdr_btn_muat" href="#" class="btn btn-primary" style="background:#111; color:#fff; font-size:12px; padding:9px 18px; border-radius:8px; display:inline-flex; align-items:center; gap:6px; text-decoration:none;" title="Buka Detail Perhitungan Penuh di Halaman (Bukan Modal)">
                    <i class="fa-solid fa-calculator"></i> Buka di Kalkulator (Tampilan Penuh)
                </a>
                <button type="button" onclick="cetakModalRiwayat()" class="btn btn-outline" style="font-size:12px; padding:8px 14px; border-radius:8px; display:inline-flex; align-items:center; gap:6px;">
                    <i class="fa-solid fa-print"></i> Cetak Dokumen
                </button>
                <button type="button" onclick="tutupModalDetailRiwayat()" class="btn btn-outline" style="font-size:12px; padding:8px 14px; border-radius:8px;">
                    Tutup
                </button>
            </div>
        </div>

    </div>
</div>

<script>
const riwayatDataStore = <?= json_encode($riwayatDataStore, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

function bukaModalDetailRiwayat(id) {
    const data = riwayatDataStore[id];
    if (!data) return;
    window.currentDetailRiwayat = data;
    
    let detail = null;
    try {
        if (data.detail_json) {
            detail = typeof data.detail_json === 'string' ? JSON.parse(data.detail_json) : data.detail_json;
        }
    } catch(e) {
        console.error("JSON parse error:", e);
    }
    
    let tglAcuanFormatted = '-';
    if (detail && detail.tanggal_acuan) {
        const parts = detail.tanggal_acuan.split('-');
        if (parts.length === 3) {
            tglAcuanFormatted = `${parts[2]}/${parts[1]}/${parts[0]}`;
        } else {
            tglAcuanFormatted = detail.tanggal_acuan;
        }
    } else if (data.tanggal_hitung) {
        const tglOnly = data.tanggal_hitung.split(' ')[0];
        const parts = tglOnly.split('-');
        if (parts.length === 3) {
            tglAcuanFormatted = `${parts[2]}/${parts[1]}/${parts[0]}`;
        } else {
            tglAcuanFormatted = tglOnly;
        }
    }
    
    document.getElementById('mdr_subtitle').innerHTML = `Riwayat #${data.id_riwayat} &bull; Waktu Acuan: <strong>${tglAcuanFormatted}</strong>`;
    document.getElementById('mdr_objek').textContent = data.nama_referensi;
    document.getElementById('mdr_kode').textContent = 'ID Kategori: ' + data.referensi;
    document.getElementById('mdr_target').textContent = data.target_waktu;
    
    const rentangText = (detail && detail.rentang_historis) ? ('Data Historis: ' + detail.rentang_historis) : '';
    document.getElementById('mdr_rentang_histori').textContent = rentangText;
    
    document.getElementById('mdr_hasil').innerHTML = Number(data.hasil).toLocaleString('id-ID') + ' <span style="font-size:13px; font-weight:600; color:var(--text-main);">Unit</span>';
    const kualitasText = (detail && detail.kualitas) ? ` (${detail.kualitas})` : '';
    const mapeValDisplay = (detail && detail.avg_mape !== undefined) ? detail.avg_mape : ((data.mape) ? data.mape : null);
    document.getElementById('mdr_akurasi').innerHTML = (mapeValDisplay !== null ? 'MAPE: ' + mapeValDisplay + '%' : '') + kualitasText;
    
    document.getElementById('mdr_alpha').textContent = data.alpha;
    const satuanD = (detail && detail.satuan_data) ? detail.satuan_data : (data.target_waktu.indexOf('Minggu') !== -1 ? 'Hari' : 'Minggu');
    document.getElementById('mdr_periode').textContent = data.periode + ' ' + satuanD;
    
    document.getElementById('mdr_at').textContent = (detail && detail.a_t !== undefined) ? detail.a_t : '—';
    document.getElementById('mdr_bt').textContent = (detail && detail.b_t !== undefined) ? detail.b_t : '—';
    
    // 1. Audit Data Historis Perhitungan DES (X_t, S', S'', a, b, F, Error, MAPE)
    const histContainer = document.getElementById('mdr_history_container');
    const histTbody = document.getElementById('mdr_history_tbody');
    const histTfoot = document.getElementById('mdr_history_tfoot');
    histTbody.innerHTML = '';
    histTfoot.innerHTML = '';

    if (detail && detail.history_rows && detail.history_rows.length > 0) {
        let totalAkt = 0;
        let totalErr = 0;
        let countMape = 0;
        let sumMape = 0;

        detail.history_rows.forEach(hr => {
            totalAkt += parseFloat(hr.aktual) || 0;
            const ftDisp = (hr.f !== null && hr.f !== undefined) ? Number(hr.f).toFixed(2) : '—';
            const errDisp = (hr.err !== null && hr.err !== undefined) ? Number(hr.err).toFixed(2) : '—';
            let mapeDisp = '—';

            if (hr.err !== null && hr.err !== undefined) {
                totalErr += parseFloat(hr.err);
                if (hr.mape !== null && hr.mape !== undefined) {
                    mapeDisp = Number(hr.mape).toFixed(2) + '%';
                    sumMape += parseFloat(hr.mape);
                    countMape++;
                } else {
                    mapeDisp = '<span style="color:var(--text-muted); font-size:11px;" title="Penjualan aktual 0, dilewati agar tidak terjadi division by zero">0%*</span>';
                }
            }

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td style="font-weight:600;">${hr.i}</td>
                <td>${hr.label}</td>
                <td style="font-weight:700;">${hr.aktual}</td>
                <td>${Number(hr.s1).toFixed(2)}</td>
                <td>${Number(hr.s2).toFixed(2)}</td>
                <td style="font-weight:600;">${Number(hr.a).toFixed(2)}</td>
                <td style="font-weight:600;">${Number(hr.b).toFixed(2)}</td>
                <td style="color:var(--primary-color); font-weight:600;">${ftDisp}</td>
                <td style="font-weight:600;">${errDisp}</td>
                <td style="font-weight:600;">${mapeDisp}</td>
            `;
            histTbody.appendChild(tr);
        });

        const avgMape = (detail.avg_mape !== undefined) ? detail.avg_mape : ((countMape > 0) ? (sumMape / countMape).toFixed(2) : 0);
        const akurasiVal = (detail.akurasi !== undefined) ? detail.akurasi : data.akurasi;
        const kualitasVal = (detail.kualitas) ? detail.kualitas : '';

        histTfoot.innerHTML = `
            <tr style="background:#f8fafc; font-weight:700;">
                <td colspan="2" style="text-align:right;">Total Evaluasi:</td>
                <td style="font-weight:700;">${totalAkt}</td>
                <td colspan="5"></td>
                <td style="font-weight:700; font-size:13px;">${totalErr.toFixed(2)}<br><span style="font-size:10px; font-weight:normal; color:var(--text-muted);">&Sigma; |e_t|</span></td>
                <td style="font-weight:800; color:#ea580c; font-size:13px;">${Number(avgMape).toFixed(2)}%<br><span style="font-size:10px; font-weight:normal; color:var(--text-muted);">Rata-rata MAPE</span></td>
            </tr>
            <tr style="background:#f1f5f9; font-weight:700;">
                <td colspan="8" style="text-align:right; padding-right:16px;">
                    <span style="font-size:13px;">Kategori Evaluasi Model (Standar Lewis):</span>
                </td>
                <td colspan="2" style="color:var(--primary-color); font-size:14px; text-align:center;">
                    <strong>${kualitasVal}</strong> <span style="font-size:11px; font-weight:600; color:#ea580c;">(MAPE ${Number(avgMape).toFixed(2)}%)</span>
                </td>
            </tr>
        `;
        histContainer.style.display = 'block';
    } else {
        histTbody.innerHTML = `<tr><td colspan="10" style="padding:16px; color:var(--text-muted); font-size:12px;"><i class="fa-solid fa-spinner fa-spin" style="margin-right:6px;"></i> Memuat audit detail perhitungan historis...</td></tr>`;
        histContainer.style.display = 'block';

        fetch(`index.php?action=ajax_get_riwayat_detail&id_riwayat=${data.id_riwayat}`)
            .then(res => res.json())
            .then(resData => {
                if (resData && resData.success && resData.history_rows) {
                    if (!detail) detail = {};
                    detail.history_rows = resData.history_rows;
                    detail.avg_mape = resData.avg_mape;
                    detail.total_abs_err = resData.total_abs_err;
                    data.detail_json = JSON.stringify(detail);
                    bukaModalDetailRiwayat(data.id_riwayat);
                } else {
                    histTbody.innerHTML = `<tr><td colspan="10" style="padding:16px; color:var(--text-muted); font-size:12px;">Data historis peramalan ini tersimpan sebelum pembaruan sistem. Silakan simpan peramalan baru untuk melihat audit perhitungan lengkap.</td></tr>`;
                }
            })
            .catch(err => {
                histTbody.innerHTML = `<tr><td colspan="10" style="padding:16px; color:var(--text-muted); font-size:12px;">Detail audit historis tidak dapat dimuat.</td></tr>`;
            });
    }

    // 2. Breakdown langkah
    const tbody = document.getElementById('mdr_breakdown_tbody');
    tbody.innerHTML = '';
    
    if (detail && detail.forecast_breakdown && detail.forecast_breakdown.length > 0) {
        const satW = detail.satuan_waktu || 'Minggu';
        const atVal = parseFloat(detail.a_t) || 0;
        const btVal = parseFloat(detail.b_t) || 0;
        
        detail.forecast_breakdown.forEach(fb => {
            const m = fb.m;
            const calc = (atVal + (m * btVal)).toFixed(2);
            const grp = satW + ' ke-' + (fb.group_num || 1);
            
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td style="font-weight:700; color:var(--primary-color);">m = ${m}</td>
                <td style="font-weight:600;">${fb.label}</td>
                <td><span style="font-size:11px; color:var(--text-muted); font-weight:600;">${fb.rentang_m || '-'}</span></td>
                <td><span class="badge" style="background:var(--bg-body); color:var(--text-main); font-size:11px; padding:2px 8px; border-radius:12px; border:1px solid var(--border-color);">${grp}</span></td>
                <td style="font-family:monospace; font-size:11px;">${atVal} + (${m} &times; ${btVal}) = ${calc}</td>
                <td style="font-weight:700; color:var(--primary-color);">${fb.prediksi}</td>
            `;
            tbody.appendChild(tr);
        });
        document.getElementById('mdr_breakdown_container').style.display = 'block';
    } else {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td colspan="6" style="padding:16px; color:var(--text-muted); font-size:12px;">
                Ringkasan total hasil peramalan: <strong>${Number(data.hasil).toLocaleString('id-ID')} Unit</strong> dengan evaluasi MAPE <strong>${Number(detail && detail.avg_mape !== undefined ? detail.avg_mape : (data.mape || 0)).toFixed(2)}%</strong>.<br>
                Gunakan tombol <em>Buka di Kalkulator</em> di bawah untuk menjalankan simulasi lengkap secara langsung.
            </td>
        `;
        tbody.appendChild(tr);
        document.getElementById('mdr_breakdown_container').style.display = 'block';
    }
    
    // 3. Group breakdown cards
    const grpContainer = document.getElementById('mdr_groups_container');
    const grpList = document.getElementById('mdr_groups_list');
    grpList.innerHTML = '';
    if (detail && detail.group_breakdown && detail.group_breakdown.length > 1) {
        detail.group_breakdown.forEach(gb => {
            const card = document.createElement('div');
            card.style.cssText = 'background:#fff; border:1px solid var(--border-color); border-radius:8px; padding:8px 12px; display:flex; justify-content:space-between; align-items:center;';
            card.innerHTML = `
                <div>
                    <div style="font-size:12px; font-weight:700; color:var(--text-main);">${gb.label}</div>
                    <div style="font-size:10px; color:var(--text-muted);">${gb.sublabel || ''}</div>
                </div>
                <div style="font-size:13px; font-weight:700; color:var(--primary-color);">
                    ${Number(gb.total_qty).toLocaleString('id-ID')} <span style="font-size:10px; font-weight:normal; color:var(--text-muted);">Unit</span>
                </div>
            `;
            grpList.appendChild(card);
        });
        grpContainer.style.display = 'block';
    } else {
        grpContainer.style.display = 'none';
    }
    
    // Tombol muat ke kalkulator (dengan parameter &show_detail=1)
    const targetType = (detail && detail.target_peramalan) ? detail.target_peramalan : (data.target_waktu.indexOf('Bulan') !== -1 ? 'bulanan' : 'mingguan');
    const jumlahT = (detail && detail.jumlah_target) ? detail.jumlah_target : 1;
    const tglA = (detail && detail.tanggal_acuan) ? detail.tanggal_acuan : '';
    let urlMuat = `index.php?page=des_kategori&id_kategori=${encodeURIComponent(data.referensi)}&target_peramalan=${targetType}&jumlah_target=${jumlahT}&n_periode=${data.periode}&alpha=${data.alpha}&show_detail=1`;
    if (tglA) {
        urlMuat += `&tanggal_acuan=${encodeURIComponent(tglA)}`;
    }
    document.getElementById('mdr_btn_muat').href = urlMuat;
    const btnHeaderFull = document.getElementById('mdr_btn_header_full');
    if (btnHeaderFull) btnHeaderFull.href = urlMuat;
    
    const modal = document.getElementById('modalDetailRiwayat');
    modal.style.display = 'flex';
}

function tutupModalDetailRiwayat() {
    document.getElementById('modalDetailRiwayat').style.display = 'none';
}

function cetakModalRiwayat() {
    const data = window.currentDetailRiwayat;
    if (!data) return;
    let detail = null;
    try {
        if (data.detail_json) {
            detail = typeof data.detail_json === 'string' ? JSON.parse(data.detail_json) : data.detail_json;
        }
    } catch(e) {}
    
    let tglAcuanFormatted = '-';
    if (detail && detail.tanggal_acuan) {
        const parts = detail.tanggal_acuan.split('-');
        if (parts.length === 3) {
            tglAcuanFormatted = `${parts[2]}/${parts[1]}/${parts[0]}`;
        } else {
            tglAcuanFormatted = detail.tanggal_acuan;
        }
    } else if (data.tanggal_hitung) {
        const tglOnly = data.tanggal_hitung.split(' ')[0];
        const parts = tglOnly.split('-');
        if (parts.length === 3) {
            tglAcuanFormatted = `${parts[2]}/${parts[1]}/${parts[0]}`;
        } else {
            tglAcuanFormatted = tglOnly;
        }
    }
    
    const printWin = window.open('', '_blank', 'width=950,height=750');
    if (!printWin) {
        showAlert('warning', 'Pop-up Diblokir', 'Izinkan pop-up peramban untuk mencetak laporan riwayat.');
        return;
    }
    
    // 1. Render Tabel Data Historis DES untuk cetak
    let histRowsHtml = '';
    if (detail && detail.history_rows && detail.history_rows.length > 0) {
        let totalAkt = 0;
        let totalErr = 0;
        detail.history_rows.forEach(hr => {
            totalAkt += parseFloat(hr.aktual) || 0;
            const ft = (hr.f !== null && hr.f !== undefined) ? Number(hr.f).toFixed(2) : '—';
            const err = (hr.err !== null && hr.err !== undefined) ? Number(hr.err).toFixed(2) : '—';
            let mape = '—';
            if (hr.err !== null && hr.err !== undefined) {
                totalErr += parseFloat(hr.err);
                mape = (hr.mape !== null && hr.mape !== undefined) ? Number(hr.mape).toFixed(2) + '%' : '0%*';
            }
            histRowsHtml += `<tr>
                <td style="padding:6px; border:1px solid #cbd5e1; text-align:center;">${hr.i}</td>
                <td style="padding:6px; border:1px solid #cbd5e1; text-align:center;">${hr.label}</td>
                <td style="padding:6px; border:1px solid #cbd5e1; text-align:center; font-weight:bold;">${hr.aktual}</td>
                <td style="padding:6px; border:1px solid #cbd5e1; text-align:center;">${Number(hr.s1).toFixed(2)}</td>
                <td style="padding:6px; border:1px solid #cbd5e1; text-align:center;">${Number(hr.s2).toFixed(2)}</td>
                <td style="padding:6px; border:1px solid #cbd5e1; text-align:center;">${Number(hr.a).toFixed(2)}</td>
                <td style="padding:6px; border:1px solid #cbd5e1; text-align:center;">${Number(hr.b).toFixed(2)}</td>
                <td style="padding:6px; border:1px solid #cbd5e1; text-align:center; color:#0284c7; font-weight:bold;">${ft}</td>
                <td style="padding:6px; border:1px solid #cbd5e1; text-align:center;">${err}</td>
                <td style="padding:6px; border:1px solid #cbd5e1; text-align:center;">${mape}</td>
            </tr>`;
        });
        const avgMapeVal = detail.avg_mape !== undefined ? Number(detail.avg_mape).toFixed(2) + '%' : '-';
        histRowsHtml += `
            <tr style="background:#f8fafc; font-weight:bold;">
                <td colspan="2" style="text-align:right; padding:6px; border:1px solid #cbd5e1;">Total Evaluasi:</td>
                <td style="text-align:center; padding:6px; border:1px solid #cbd5e1;">${totalAkt}</td>
                <td colspan="5" style="border:1px solid #cbd5e1;"></td>
                <td style="text-align:center; padding:6px; border:1px solid #cbd5e1;">${totalErr.toFixed(2)}</td>
                <td style="text-align:center; padding:6px; border:1px solid #cbd5e1; color:#ea580c;">${avgMapeVal}</td>
            </tr>
            <tr style="background:#f1f5f9; font-weight:bold;">
                <td colspan="8" style="text-align:right; padding:6px 12px; border:1px solid #cbd5e1;">Kategori Evaluasi Model (Standar Lewis):</td>
                <td colspan="2" style="text-align:center; padding:6px; border:1px solid #cbd5e1; color:#0284c7; font-size:13px;">${detail.kualitas ? detail.kualitas : '-'} ${detail.avg_mape !== undefined ? '(MAPE ' + detail.avg_mape + '%)' : ''}</td>
            </tr>
        `;
    }

    // 2. Render Tabel Proyeksi Masa Depan
    let rowsHtml = '';
    if (detail && detail.forecast_breakdown && detail.forecast_breakdown.length > 0) {
        const satW = detail.satuan_waktu || 'Minggu';
        const atVal = parseFloat(detail.a_t) || 0;
        const btVal = parseFloat(detail.b_t) || 0;
        detail.forecast_breakdown.forEach(fb => {
            const m = fb.m;
            const calc = (atVal + (m * btVal)).toFixed(2);
            rowsHtml += `<tr>
                <td style="padding:6px; border:1px solid #cbd5e1; text-align:center;">m = ${m}</td>
                <td style="padding:6px; border:1px solid #cbd5e1; text-align:center;">${fb.label}</td>
                <td style="padding:6px; border:1px solid #cbd5e1; text-align:center;">${fb.rentang_m || '-'}</td>
                <td style="padding:6px; border:1px solid #cbd5e1; font-family:monospace; text-align:center;">${atVal} + (${m} &times; ${btVal}) = ${calc}</td>
                <td style="padding:6px; border:1px solid #cbd5e1; font-weight:bold; text-align:center; color:#0284c7;">${fb.prediksi} Unit</td>
            </tr>`;
        });
    } else {
        rowsHtml = `<tr>
            <td colspan="5" style="padding:16px; border:1px solid #cbd5e1; text-align:center; font-weight:bold;">
                Total Prediksi Kebutuhan Stok: ${Number(data.hasil).toLocaleString('id-ID')} Unit
            </td>
        </tr>`;
    }
    
    const html = `<!DOCTYPE html>
    <html>
    <head>
        <title>Laporan Detail Riwayat Peramalan Kategori #${data.id_riwayat}</title>
        <style>
            body { font-family: 'Segoe UI', Arial, sans-serif; padding: 24px; color: #1e293b; line-height: 1.5; font-size: 11px; }
            table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 11px; }
            th { background-color: #f1f5f9; border: 1px solid #cbd5e1; padding: 6px; font-weight: 700; text-align: center; }
            td { border: 1px solid #cbd5e1; }
            .kop { border-bottom: 2px solid #000; padding-bottom: 12px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: flex-end; }
            .grid-info { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; font-size: 11px; background: #f8fafc; border: 1px solid #e2e8f0; padding: 12px; border-radius: 6px; }
            h4 { margin: 18px 0 6px 0; font-size: 12px; font-weight: 700; }
            @media print {
                body { padding: 0; }
                @page { margin: 12mm; size: auto; }
            }
        </style>
    </head>
    <body>
        <div class="kop">
            <div>
                <h2 style="margin:0; font-size:20px; font-weight:900; letter-spacing:1px;">A STORE</h2>
                <div style="font-size:12px; color:#475569; margin-top:3px;">Laporan Rincian Riwayat Peramalan Stok Kategori — Double Exponential Smoothing</div>
            </div>
            <div style="text-align:right; font-size:11px; color:#64748b;">
                ID Riwayat: <strong>#${data.id_riwayat}</strong><br>
                Waktu Acuan: <strong>${tglAcuanFormatted}</strong>
            </div>
        </div>

        <div class="grid-info">
            <div><strong>Kategori Analisis:</strong> ${data.referensi} — ${data.nama_referensi}</div>
            <div><strong>Target Proyeksi:</strong> ${data.target_waktu}</div>
            <div><strong>Parameter Model:</strong> Alpha (α) = ${data.alpha} | Periode = ${data.periode}</div>
            <div><strong>Hasil &amp; Evaluasi:</strong> <span style="font-size:13px; font-weight:bold; color:#0284c7;">${Number(data.hasil).toLocaleString('id-ID')} Unit</span> (${detail && detail.avg_mape !== undefined ? 'MAPE: ' + detail.avg_mape + '%' : ''}${detail && detail.kualitas ? ' - Kategori: ' + detail.kualitas : ''})</div>
        </div>

        ${histRowsHtml ? `
        <h4>1. Audit Detail Perhitungan DES &amp; Evaluasi MAPE (Data Historis):</h4>
        <table>
            <thead>
                <tr>
                    <th>Ke-</th>
                    <th>Tanggal / Rentang</th>
                    <th>Aktual (X_t)</th>
                    <th>S'_t</th>
                    <th>S"_t</th>
                    <th>a_t</th>
                    <th>b_t</th>
                    <th>Peramalan (F_t)</th>
                    <th>Error Absolut (|e_t|)</th>
                    <th>MAPE (%)</th>
                </tr>
            </thead>
            <tbody>
                ${histRowsHtml}
            </tbody>
        </table>
        ` : ''}

        <h4>2. Rincian Proyeksi Peramalan Masa Depan (Brown's DES):</h4>
        <table>
            <thead>
                <tr>
                    <th>Langkah (m)</th>
                    <th>Periode Target</th>
                    <th>Rentang Waktu</th>
                    <th>Rumus: a_t + (m &times; b_t)</th>
                    <th>Estimasi Kebutuhan</th>
                </tr>
            </thead>
            <tbody>
                ${rowsHtml}
                <tr style="background:#f1f5f9; font-weight:bold;">
                    <td colspan="4" style="text-align:right; padding:8px 12px; border:1px solid #cbd5e1;">TOTAL ESTIMASI KEBUTUHAN STOK:</td>
                    <td style="text-align:center; padding:8px; border:1px solid #cbd5e1; font-size:13px; color:#0284c7;">${Number(data.hasil).toLocaleString('id-ID')} Unit</td>
                </tr>
            </tbody>
        </table>

        <div style="margin-top:35px; display:flex; justify-content:space-between; font-size:11px;">
            <div style="color:#64748b;">Dicetak dari Sistem Manajemen Inventori A STORE</div>
            <div style="text-align:center; width:200px;">
                <div>Mengetahui,</div>
                <div style="margin-top:55px; font-weight:bold; border-bottom:1px solid #000; padding-bottom:2px;">Pimpinan Toko</div>
            </div>
        </div>

        <script>
            window.onload = function() {
                window.print();
            };
        <\/script>
    </body>
    </html>`;
    
    printWin.document.open();
    printWin.document.write(html);
    printWin.document.close();
}

// Auto-scroll jika show_detail=1
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('show_detail') === '1') {
        const dp = document.getElementById('detailPerhitungan');
        if (dp) {
            dp.style.display = 'block';
            setTimeout(() => {
                const yOffset = -70;
                const y = dp.getBoundingClientRect().top + window.pageYOffset + yOffset;
                window.scrollTo({ top: y, behavior: 'smooth' });
            }, 150);
        }
    }
});

const presetsMingguan = [
    { val: 1, label: '1 Minggu Kedepan (7 Hari)' },
    { val: 2, label: '2 Minggu Kedepan (14 Hari)' },
    { val: 3, label: '3 Minggu Kedepan (21 Hari)' },
    { val: 4, label: '4 Minggu Kedepan (28 Hari)' },
    { val: 5, label: '5 Minggu Kedepan (35 Hari)' },
    { val: 6, label: '6 Minggu Kedepan (42 Hari)' },
    { val: 8, label: '8 Minggu Kedepan (56 Hari)' },
    { val: 'custom', label: 'Lebih / Kustom (Tentukan Minggu)...' }
];

const presetsBulanan = [
    { val: 1, label: '1 Bulan Kedepan (4 Minggu)' },
    { val: 2, label: '2 Bulan Kedepan (8 Minggu)' },
    { val: 3, label: '3 Bulan Kedepan (12 Minggu)' },
    { val: 4, label: '4 Bulan Kedepan (16 Minggu)' },
    { val: 5, label: '5 Bulan Kedepan (20 Minggu)' },
    { val: 6, label: '6 Bulan Kedepan (24 Minggu)' },
    { val: 12, label: '12 Bulan / 1 Tahun (48 Minggu)' },
    { val: 'custom', label: 'Lebih / Kustom (Tentukan Bulan)...' }
];

function initDurasiDropdown() {
    const targetType = document.getElementById('target_peramalan').value;
    const durasiSelect = document.getElementById('pilihan_durasi');
    const hiddenInput = document.getElementById('input_jumlah_target');
    const customWrapper = document.getElementById('wrapper_custom');
    const customInput = document.getElementById('input_custom_durasi');
    const customLabel = document.getElementById('label_custom_unit');
    const currentVal = parseInt(hiddenInput.value) || 1;
    
    durasiSelect.innerHTML = '';
    const presets = (targetType === 'mingguan') ? presetsMingguan : presetsBulanan;
    let isPresetFound = false;
    
    presets.forEach(p => {
        const opt = document.createElement('option');
        opt.value = p.val;
        opt.textContent = p.label;
        if (p.val === currentVal) {
            opt.selected = true;
            isPresetFound = true;
        }
        durasiSelect.appendChild(opt);
    });
    
    customLabel.textContent = (targetType === 'mingguan') ? 'Minggu' : 'Bulan';
    
    if (!isPresetFound) {
        durasiSelect.value = 'custom';
        customWrapper.style.display = 'inline-flex';
        customInput.value = currentVal;
    } else {
        customWrapper.style.display = 'none';
        customInput.value = currentVal;
    }
    updatePreviewText();
}

function gantiTipeTarget(val) {
    const nInput = document.getElementById('input_n_periode');
    const durasi = parseInt(document.getElementById('input_jumlah_target').value) || 1;
    if (nInput) {
        nInput.value = (val === 'mingguan') ? Math.max(7, durasi * 7) : Math.max(4, durasi * 4);
    }
    document.getElementById('formPeramalan').submit();
}

function gantiDurasi(val) {
    const customWrapper = document.getElementById('wrapper_custom');
    const customInput = document.getElementById('input_custom_durasi');
    const hiddenInput = document.getElementById('input_jumlah_target');
    const targetType = document.getElementById('target_peramalan').value;
    const nInput = document.getElementById('input_n_periode');
    
    if (val === 'custom') {
        customWrapper.style.display = 'inline-flex';
        let num = parseInt(customInput.value) || 1;
        hiddenInput.value = num;
        customInput.focus();
    } else {
        customWrapper.style.display = 'none';
        hiddenInput.value = val;
        customInput.value = val;
        let num = parseInt(val);
        if (nInput) {
            nInput.value = (targetType === 'mingguan') ? Math.max(7, num * 7) : Math.max(4, num * 4);
        }
    }
    updatePreviewText();
}

function inputCustomDurasi(val) {
    let num = Math.max(1, parseInt(val) || 1);
    document.getElementById('input_jumlah_target').value = num;
    const targetType = document.getElementById('target_peramalan').value;
    const nInput = document.getElementById('input_n_periode');
    if (nInput) {
        nInput.value = (targetType === 'mingguan') ? Math.max(7, num * 7) : Math.max(4, num * 4);
    }
    updatePreviewText();
}

function updatePreviewText() {
    const targetType = document.getElementById('target_peramalan').value;
    const hiddenInput = document.getElementById('input_jumlah_target');
    const preview = document.getElementById('preview_output');
    const num = Math.max(1, parseInt(hiddenInput.value) || 1);
    
    if (targetType === 'mingguan') {
        const days = num * 7;
        const txt = (num === 1) ? '1 Minggu Depan (7 Hari)' : num + ' Minggu Depan (' + days + ' Hari)';
        if (preview) preview.value = txt;
    } else {
        const weeks = num * 4;
        const txt = (num === 1) ? '1 Bulan Depan (4 Minggu)' : num + ' Bulan Depan (' + weeks + ' Minggu)';
        if (preview) preview.value = txt;
    }
}

function cetakPeramalan() {
    var detail = document.getElementById('detailPerhitungan');
    if (detail) {
        detail.style.display = 'block';
    }
    setTimeout(function() {
        window.print();
    }, 100);
}

// Inisialisasi dropdown saat halaman selesai dimuat
document.addEventListener('DOMContentLoaded', initDurasiDropdown);
</script>
