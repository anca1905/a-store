<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'simpan_riwayat') {
    $kb = $conn->real_escape_string($_POST['id_kategori']);
    $nama = $conn->real_escape_string($_POST['nama_kategori']);
    $periode = $conn->real_escape_string($_POST['n_periode']);
    $alpha_val = (float)$_POST['alpha'];
    $hasil = (int)$_POST['hasil'];
    $akurasi_val = (float)$_POST['akurasi'];
    $target = $_POST['target_waktu'];
    
    $sqlIns = "INSERT INTO tbl_riwayat_peramalan (tanggal_hitung, tipe, referensi, nama_referensi, periode, alpha, target_waktu, hasil, akurasi) 
               VALUES (NOW(), 'kategori', '$kb', '$nama', '$periode', $alpha_val, '$target', $hasil, $akurasi_val)";
    get_query($conn, $sqlIns);
    
    $msgRiwayat = "Riwayat peramalan berhasil disimpan!";
}
?>
<?php
// Halaman: Peramalan Stok Kategori (DES)
$id_kategori = isset($_GET['id_kategori']) ? (int)$_GET['id_kategori'] : 0;
$target_peramalan = $_GET['target_peramalan'] ?? 'mingguan';
$target_peramalan = in_array($target_peramalan, ['mingguan', 'bulanan']) ? $target_peramalan : 'mingguan';

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

$n_periode = max(3, (int)($_GET['n_periode'] ?? $default_n));
$alpha     = isset($_GET['alpha']) ? floatval($_GET['alpha']) : 0.1;
$alpha     = in_array($alpha, [0.1, 0.2, 0.3, 0.4, 0.5, 0.6, 0.7, 0.8, 0.9]) ? $alpha : 0.1;
$hitung    = isset($_GET['id_kategori']) && $id_kategori > 0;

// Ambil daftar kategori
$qK = get_query($conn, "SELECT id_kategori, nama_kategori FROM tbl_kategori ORDER BY nama_kategori");

// Logika Peramalan DES (Brown's Double Exponential Smoothing)
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
        // PERAMALAN BULANAN (History Mingguan)
        $sql = "SELECT 
                    YEARWEEK(p.tanggal_waktu, 1) as periode_grup,
                    MIN(DATE(p.tanggal_waktu)) as tgl_awal,
                    SUM(d.qty) as total_qty
                FROM tbl_detail_penjualan d
                JOIN tbl_penjualan p ON d.id_penjualan = p.id_penjualan
                JOIN tbl_barang b ON d.kode_barang = b.kode_barang
                JOIN tbl_kategori k ON b.kategori = k.nama_kategori
                WHERE k.id_kategori = $id_kategori
                GROUP BY periode_grup
                ORDER BY periode_grup DESC
                LIMIT $n_periode";
    } else {
        // PERAMALAN MINGGUAN (History Harian)
        $sql = "SELECT 
                    DATE(p.tanggal_waktu) as periode_grup,
                    DATE(p.tanggal_waktu) as tgl_awal,
                    SUM(d.qty) as total_qty
                FROM tbl_detail_penjualan d
                JOIN tbl_penjualan p ON d.id_penjualan = p.id_penjualan
                JOIN tbl_barang b ON d.kode_barang = b.kode_barang
                JOIN tbl_kategori k ON b.kategori = k.nama_kategori
                WHERE k.id_kategori = $id_kategori
                GROUP BY periode_grup
                ORDER BY periode_grup DESC
                LIMIT $n_periode";
    }

    $qD = get_query($conn, $sql);
    $raw_data = [];
    while ($r = $qD->fetch_assoc()) {
        $r['tgl_awal'] = date('d M Y', strtotime($r['tgl_awal']));
        $raw_data[] = $r;
    }
    $raw_data = array_reverse($raw_data);
    
    $rows = $raw_data;
    $n = count($rows);
    
    if ($n >= 3) {
        $dataCukup = true;
        $aktual = array_map(function($item) { return (float)$item['total_qty']; }, $rows);
        
        // Brown's Double Exponential Smoothing
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
            $forecast_breakdown[] = [
                'm'         => $m,
                'group_num' => $group_num,
                'label'     => $satuan_data . ' ' . $m,
                'prediksi'  => round($prediksi)
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
            $group_breakdown[] = [
                'nomor'     => $g,
                'label'     => $satuan_waktu . ' ke-' . $g,
                'sublabel'  => '(' . $satuan_data . ' ' . $start_step . ' s/d ' . $end_step . ')',
                'total_qty' => $g_total
            ];
        }
        
        $avg_mape = ($count_mape > 0) ? round($sum_mape / $count_mape, 2) : 0;
        $akurasi  = round(100 - $avg_mape, 1);
        
        if ($avg_mape <= 10) {
            $kualitas = "Sangat Baik";
        } elseif ($avg_mape <= 25) {
            $kualitas = "Baik";
        } else {
            $kualitas = "Cukup / Perlu Perhatian";
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
        <div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:12px;">
            <div><span style="color:#64748b;">Kategori:</span> <strong style="color:#0f172a;"><?= htmlspecialchars($nk) ?></strong></div>
            <div><span style="color:#64748b;">Target Peramalan:</span> <strong style="color:#0f172a;"><?= htmlspecialchars($label_target_detail) ?></strong></div>
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
                    Kategori <strong><?= htmlspecialchars($nk) ?></strong> baru memiliki <strong><?= $n ?> transaksi asli</strong> di database.<br>
                    Metode DES membutuhkan minimal <strong>3 periode</strong> transaksi penjualan nyata agar peramalan akurat dan sesuai histori penjualan.
                </div>
                <div style="font-size:12px; color:#9a3412; margin-top:12px; background:#ffedd5; padding:8px 12px; border-radius:6px; display:inline-block;">
                    <i class="fa-solid fa-circle-info"></i> Silakan lakukan transaksi kasir untuk produk kategori ini terlebih dahulu, atau pilih kategori lain yang memiliki riwayat penjualan.
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
                <div style="font-size:12px; color:var(--text-muted); margin-bottom:4px;">Interpretasi</div>
                <div style="font-size:13px; line-height:1.5;">
                    Diperkirakan total kebutuhan stok untuk <strong><?= htmlspecialchars($label_target_detail) ?></strong> sebanyak <strong><?= number_format($hasilPeramalan, 0, ',', '.') ?> unit</strong>. Akurasi model <?= max(0, $akurasi) ?>% (<?= $kualitas ?>).
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

            <button onclick="document.getElementById('detailPerhitungan').style.display = 'block'" class="btn btn-outline" style="width:100%; justify-content:center; font-size:13px; margin-bottom:12px;">
                <i class="fa-solid fa-table-list" style="margin-right:6px;"></i> Detail Historis &amp; Perhitungan
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
                    <input type="hidden" name="target_waktu" value="<?= htmlspecialchars($label_target_detail) ?>">
                    <button type="submit" class="btn btn-primary" style="background:#22c55e; width:100%; justify-content:center; font-size:13px;">
                        <i class="fa-solid fa-save"></i> Simpan Riwayat
                    </button>
                </form>
                <button onclick="cetakPeramalan()" class="btn btn-primary" style="background:#3b82f6; flex:1; justify-content:center; font-size:13px;">
                    <i class="fa-solid fa-print"></i> Cetak Peramalan
                </button>
            </div>
            
            <?php if(isset($msgRiwayat)): ?>
                <div style="margin-top:12px; padding:10px; background:#dcfce7; color:#166534; border-radius:6px; font-size:13px; text-align:center;">
                    <?= $msgRiwayat ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div style="color:var(--text-muted); font-size:13px; display:flex; height:100%; align-items:center; opacity:0.6;">
                Pilih kategori dan klik Proses Peramalan untuk melihat hasil.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($hitung && isset($dataCukup) && $dataCukup): ?>
<!-- ── TABEL DATA HISTORIS & DETAIL PERHITUNGAN (Hidden by default) ── -->
<div id="detailPerhitungan" style="display:none; background:#fff; border:1px solid var(--border-color); border-radius:16px; overflow:hidden; animation: fadeIn 0.3s; margin-bottom:24px;">
    <div style="padding:20px; border-bottom:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h3 style="font-size:15px; font-weight:700; margin:0;">Data Historis <?= $satuan_data ?> &amp; Perhitungan DES (α = <?= $alpha ?>)</h3>
            <div style="font-size:12px; color:var(--text-muted); margin-top:2px;">Brown's Double Exponential Smoothing — Target: <?= htmlspecialchars($label_target_detail) ?></div>
        </div>
        <button onclick="document.getElementById('detailPerhitungan').style.display = 'none'" class="close-btn"><i class="fa-solid fa-times"></i></button>
    </div>
    
    <div class="table-responsive">
        <table class="data-table" style="text-align:center;">
            <thead>
                <tr>
                    <th><?= $satuan_data ?> Ke-</th>
                    <th>Tanggal Awal</th>
                    <th>Aktual (X_t)</th>
                    <th>S'_t</th>
                    <th>S"_t</th>
                    <th>a_t</th>
                    <th>b_t</th>
                    <th>Peramalan (F_t)</th>
                    <th>MAPE (%)</th>
                </tr>
            </thead>
            <tbody>
                <?php for ($i = 0; $i < $n; $i++):
                    $fi_disp    = ($i == 0)   ? '—' : number_format($F[$i], 2);
                    $mape_disp  = '—';
                    if ($i > 0 && $aktual[$i] > 0) {
                        $err = abs($aktual[$i] - $F[$i]);
                        $mape_disp = number_format(($err / $aktual[$i]) * 100, 2);
                    }
                ?>
                <tr>
                    <td style="font-weight:600;"><?= $i + 1 ?></td>
                    <td><?= $rows[$i]['tgl_awal'] ?></td>
                    <td style="font-weight:600;"><?= $aktual[$i] ?></td>
                    <td><?= number_format($S1[$i], 2) ?></td>
                    <td><?= number_format($S2[$i], 2) ?></td>
                    <td><?= number_format($a[$i], 2) ?></td>
                    <td><?= number_format($b[$i], 2) ?></td>
                    <td style="color:var(--primary-color);"><?= $fi_disp ?></td>
                    <td><?= $mape_disp ?></td>
                </tr>
                <?php endfor; ?>
                <!-- Baris peramalan masa depan -->
                <tr style="background:var(--primary-light); font-weight:700;">
                    <td><?= $n + 1 ?></td>
                    <td><?= htmlspecialchars($label_target_detail) ?></td>
                    <td>—</td>
                    <td>—</td>
                    <td>—</td>
                    <td>—</td>
                    <td>—</td>
                    <td style="color:var(--primary-color); font-size:15px;"><?= number_format($hasilPeramalan, 0, ',', '.') ?> Unit</td>
                    <td>—</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Tabel Proyeksi Masa Depan Langkah ke-m -->
    <div style="padding:16px 20px; background:#f8fafc; border-top:1px solid var(--border-color); border-bottom:1px solid var(--border-color);">
        <h4 style="margin:0; font-size:13px; font-weight:700; color:var(--text-main);">
            <i class="fa-solid fa-forward" style="color:var(--primary-color); margin-right:6px;"></i> Proyeksi Peramalan Masa Depan (<?= htmlspecialchars($label_target_detail) ?>)
        </h4>
        <p style="margin:4px 0 0 0; font-size:11px; color:var(--text-muted);">
            Rumus DES Proyeksi Langkah ke-m: <code>F(t+m) = a_t + (m &times; b_t)</code> dengan a_t = <strong><?= number_format($a[$n-1], 2) ?></strong> dan b_t = <strong><?= number_format($b[$n-1], 2) ?></strong>
        </p>
    </div>
    <div class="table-responsive">
        <table class="data-table" style="text-align:center;">
            <thead>
                <tr>
                    <th>Langkah (m)</th>
                    <th>Periode Target</th>
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
                    <td style="font-weight:600;">m = <?= $m_val ?></td>
                    <td><?= $fb['label'] ?></td>
                    <td><span class="badge" style="background:var(--bg-body); color:var(--text-main); font-size:11px; padding:2px 8px; border-radius:12px;"><?= $grp_label ?></span></td>
                    <td style="font-family:monospace; font-size:12px;">
                        <?= number_format($a[$n-1], 2) ?> + (<?= $m_val ?> &times; <?= number_format($b[$n-1], 2) ?>) = <?= number_format($calc_val, 2) ?>
                    </td>
                    <td style="font-weight:700; color:var(--primary-color); font-size:13px;"><?= $fb['prediksi'] ?></td>
                </tr>
                <?php endforeach; ?>
                <tr style="background:var(--primary-light); font-weight:800;">
                    <td colspan="4" style="text-align:right; padding-right:16px; font-size:13px;">
                        TOTAL ESTIMASI KEBUTUHAN STOK (<?= htmlspecialchars($label_target_detail) ?>):
                    </td>
                    <td style="color:var(--primary-color); font-size:16px;">
                        <?= number_format($hasilPeramalan, 0, ',', '.') ?> Unit
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
</div> <!-- Close peramalan-print-area -->

<!-- RIWAYAT PERAMALAN -->
<div style="background:#fff; border:1px solid var(--border-color); border-radius:16px; overflow:hidden; margin-bottom:24px;">
    <div style="padding:20px; border-bottom:1px solid var(--border-color);">
        <h3 style="font-size:15px; font-weight:700;">Riwayat Peramalan Kategori</h3>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Waktu Hitung</th>
                    <th>ID Kategori</th>
                    <th>Nama Kategori</th>
                    <th>Target Waktu</th>
                    <th>Periode</th>
                    <th>Alpha</th>
                    <th>Hasil Peramalan</th>
                    <th>Akurasi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $qHist = get_query($conn, "SELECT * FROM tbl_riwayat_peramalan WHERE tipe='kategori' ORDER BY id_riwayat DESC LIMIT 20");
                if($qHist->num_rows > 0):
                    while($rh = $qHist->fetch_assoc()):
                ?>
                <tr>
                    <td><?= date('d/m/Y H:i', strtotime($rh['tanggal_hitung'])) ?></td>
                    <td><strong><?= $rh['referensi'] ?></strong></td>
                    <td><?= $rh['nama_referensi'] ?></td>
                    <td><span style="font-weight:600; color:var(--text-main);"><?= $rh['target_waktu'] ?></span></td>
                    <td><?= $rh['periode'] ?> <?= strpos($rh['target_waktu'], 'Minggu') !== false ? 'Hari' : 'Minggu' ?></td>
                    <td><?= $rh['alpha'] ?></td>
                    <td style="color:var(--primary-color); font-weight:bold;"><?= number_format($rh['hasil'],0,',','.') ?> Unit</td>
                    <td><?= $rh['akurasi'] ?>%</td>
                </tr>
                <?php endwhile; else: ?>
                <tr>
                    <td colspan="8" style="text-align:center; padding:30px; color:var(--text-muted);">Belum ada riwayat peramalan kategori.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
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
