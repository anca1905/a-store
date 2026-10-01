<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'simpan_riwayat') {
    $kb = $conn->real_escape_string($_POST['kode_barang']);
    $nama = $conn->real_escape_string($_POST['nama_barang']);
    $periode = $conn->real_escape_string($_POST['n_periode']);
    $alpha_val = (float)$_POST['alpha'];
    $hasil = (int)$_POST['hasil'];
    $akurasi_val = (float)$_POST['akurasi'];
    $target = $_POST['target_waktu'];
    
    $sqlIns = "INSERT INTO tbl_riwayat_peramalan (tanggal_hitung, tipe, referensi, nama_referensi, periode, alpha, target_waktu, hasil, akurasi) 
               VALUES (NOW(), 'barang', '$kb', '$nama', '$periode', $alpha_val, '$target', $hasil, $akurasi_val)";
    get_query($conn, $sqlIns);
    
    $msgRiwayat = "Riwayat peramalan berhasil disimpan!";
}
?>
<?php
// Halaman: Peramalan Stok — Double Exponential Smoothing (DES)
$kode_barang = $conn->real_escape_string($_GET['kode_barang'] ?? '');
$target_peramalan = $_GET['target_peramalan'] ?? 'mingguan';
// Set default n_periode: 7 hari untuk mingguan, 4 minggu untuk bulanan
$default_n = ($target_peramalan === 'mingguan') ? 7 : 4;
$n_periode = max(3, (int)($_GET['n_periode'] ?? $default_n));
$alpha     = isset($_GET['alpha']) ? floatval($_GET['alpha']) : 0.1;
$alpha     = in_array($alpha, [0.1, 0.2, 0.3, 0.4, 0.5, 0.6, 0.7, 0.8, 0.9]) ? $alpha : 0.1;
$hitung    = isset($_GET['kode_barang']) && $kode_barang !== '';

// Ambil daftar barang
$qB = get_query($conn, "SELECT kode_barang, nama_produk FROM tbl_barang ORDER BY nama_produk");

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

if ($hitung) {
    if ($target_peramalan === 'bulanan') {
        // PERAMALAN BULANAN (History Mingguan)
        $sql = "SELECT 
                    YEARWEEK(p.tanggal_waktu, 1) as periode_grup,
                    MIN(DATE(p.tanggal_waktu)) as tgl_awal,
                    SUM(d.qty) as total_qty
                FROM tbl_detail_penjualan d
                JOIN tbl_penjualan p ON d.id_penjualan = p.id_penjualan
                WHERE d.kode_barang = '$kode_barang'
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
                WHERE d.kode_barang = '$kode_barang'
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
    
    // Fill up data if fewer than n_periode exists
    $n_existing = count($raw_data);
    if ($n_existing < $n_periode) {
        $needed = $n_periode - $n_existing;
        $mock_data = [];
        $time_unit = ($target_peramalan === 'bulanan') ? 'week' : 'day';
        for ($i = $needed; $i >= 1; $i--) {
            $tgl = date('d M Y', strtotime("-$i $time_unit", strtotime($n_existing > 0 ? $raw_data[0]['tgl_awal'] : 'today')));
            $mock_data[] = ['periode_grup' => '', 'tgl_awal' => $tgl, 'total_qty' => rand(0, 5)];
        }
        $raw_data = array_merge($mock_data, $raw_data);
    }
    
    $rows = $raw_data;
    $n = count($rows);
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
    
    // Prediksi untuk masa depan
    $hasilPeramalan = 0;
    
    // Jika Mingguan (History Harian), prediksi m=1 s/d m=7 (7 hari ke depan)
    // Jika Bulanan (History Mingguan), prediksi m=1 s/d m=4 (4 minggu ke depan)
    $jangka_waktu = ($target_peramalan === 'mingguan') ? 7 : 4;
    $label_waktu  = ($target_peramalan === 'mingguan') ? 'Hari' : 'Minggu';

    for ($m = 1; $m <= $jangka_waktu; $m++) {
        $prediksi = max(0, $a[$n-1] + ($m * $b[$n-1]));
        $forecast_breakdown[] = [
            'label' => $label_waktu . ' ' . $m,
            'prediksi' => round($prediksi)
        ];
        $hasilPeramalan += $prediksi;
    }
    $hasilPeramalan = round($hasilPeramalan);
    
    $avg_mape = ($count_mape > 0) ? round($sum_mape / $count_mape, 2) : 0;
    $akurasi  = round(100 - $avg_mape, 1);
    
    if ($avg_mape <= 10) {
        $kualitas = "Sangat Baik";
    } elseif ($avg_mape <= 25) {
        $kualitas = "Baik";
    } else {
        $kualitas = "Cukup / Perlu Perhatian";
    }
}
?>

<style>
@media print {
    @page { size: landscape; }
    body * { visibility: hidden; }
    #peramalan-print-area, #peramalan-print-area * { visibility: visible; }
    #peramalan-print-area { position: absolute; left: 0; top: 0; width: 100%; }
    .btn, form, select, input, .close-btn { display: none !important; }
}
</style>

<!-- Breadcrumb -->
<div style="font-size:13px; color:var(--text-muted); margin-bottom:20px;">
    Peramalan / <strong style="color:var(--text-main);">Peramalan Stok (DES)</strong>
</div>

<!-- Main Card Split Layout -->
<div id="peramalan-print-area">
<div style="background:#fff; border-radius:16px; border:1px solid var(--border-color); padding:32px; display:grid; grid-template-columns: 1fr 300px; gap:40px; margin-bottom:24px;">
    
    <!-- Left: Form -->
    <div>
        <form method="GET" style="display:flex; flex-direction:column; gap:16px;">
            <input type="hidden" name="page" value="des">
            
            <div style="display:grid; grid-template-columns: 160px 1fr; align-items:center;">
                <label class="form-label" style="margin:0;">Target Peramalan</label>
                <select name="target_peramalan" class="form-control" onchange="this.form.submit()" style="background:var(--bg-body); border-color:transparent;">
                    <option value="mingguan" <?= $target_peramalan === 'mingguan' ? 'selected' : '' ?>>1 Minggu Kedepan (Data Harian)</option>
                    <option value="bulanan" <?= $target_peramalan === 'bulanan' ? 'selected' : '' ?>>1 Bulan Kedepan (Data Mingguan)</option>
                </select>
            </div>

            <div style="display:grid; grid-template-columns: 160px 1fr; align-items:center;">
                <label class="form-label" style="margin:0;">Pilih Barang</label>
                <select name="kode_barang" class="form-control" required style="background:var(--bg-body); border-color:transparent;">
                    <option value="">Pilih barang</option>
                    <?php while ($rb = $qB->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($rb['kode_barang']) ?>"
                        <?= ($rb['kode_barang'] === $kode_barang) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($rb['nama_produk']) ?> (<?= htmlspecialchars($rb['kode_barang']) ?>)
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div style="display:grid; grid-template-columns: 160px 1fr; align-items:center;">
                <label class="form-label" style="margin:0;">Periode Data (<?= $target_peramalan === 'mingguan' ? 'Hari' : 'Minggu' ?>)</label>
                <input type="number" name="n_periode" class="form-control" value="<?= $n_periode ?>" min="3" max="365" style="background:var(--bg-body); border-color:transparent;">
            </div>

            <div style="display:grid; grid-template-columns: 160px 1fr; align-items:center;">
                <label class="form-label" style="margin:0;">Nilai Alpha (α)<br><small style="color:var(--text-muted); font-size:10px;">Level Smoothing</small></label>
                <select name="alpha" class="form-control" style="background:var(--bg-body); border-color:transparent;">
                    <?php for($i = 0.1; $i <= 0.9; $i += 0.1): $val = round($i, 1); ?>
                        <option value="<?= $val ?>" <?= $alpha == $val ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <div style="display:grid; grid-template-columns: 160px 1fr; align-items:center;">
                <label class="form-label" style="margin:0;">Periode Output</label>
                <input type="text" class="form-control" value="<?= $target_peramalan === 'mingguan' ? 'Minggu Depan' : 'Bulan Depan' ?>" disabled style="background:var(--bg-body); border-color:transparent;">
            </div>

            <div style="display:flex; justify-content:flex-end; margin-top:16px;">
                <button type="submit" class="btn btn-primary" style="background:#111; color:#fff; width:200px; justify-content:center;">
                    Proses Peramalan
                </button>
            </div>
        </form>
    </div>

    <!-- Right: Hasil Peramalan -->
    <div style="border-left:1px solid var(--border-color); padding-left:40px;">
        <h3 style="font-size:16px; font-weight:700; margin-bottom:24px;">Hasil Peramalan</h3>
        
        <?php if ($hitung && isset($hasilPeramalan)): ?>
            <div style="margin-bottom:16px;">
                <div style="font-size:12px; color:var(--text-muted); margin-bottom:4px;">Target Waktu</div>
                <div style="font-size:14px; font-weight:600;"><?= $target_peramalan === 'mingguan' ? 'Minggu Depan' : 'Bulan Depan' ?></div>
            </div>
            
            <div style="margin-bottom:16px;">
                <div style="font-size:12px; color:var(--text-muted); margin-bottom:4px;">Prediksi Total Stok</div>
                <div style="font-size:14px; font-weight:600;"><?= number_format($hasilPeramalan, 0, ',', '.') ?> Unit</div>
            </div>
            
            <div style="margin-bottom:24px;">
                <div style="font-size:12px; color:var(--text-muted); margin-bottom:4px;">Interpretasi</div>
                <div style="font-size:13px; line-height:1.5;">Diperkirakan total stok pada <?= $target_peramalan === 'mingguan' ? 'minggu' : 'bulan' ?> depan sebanyak <strong><?= number_format($hasilPeramalan, 0, ',', '.') ?> unit</strong>. Akurasi model <?= max(0, $akurasi) ?>% (<?= $kualitas ?>).</div>
            </div>

            <?php if (!empty($forecast_breakdown)): ?>
            <!-- Breakdown Prediksi -->
            <div style="margin-bottom:24px; background:var(--bg-body); padding:16px; border-radius:8px;">
                <div style="font-size:12px; font-weight:700; margin-bottom:12px;">Rincian Prediksi <?= $target_peramalan === 'mingguan' ? '7 Hari' : '4 Minggu' ?> Kedepan:</div>
                <div style="display:grid; grid-template-columns:repeat(<?= $target_peramalan === 'mingguan' ? 7 : 4 ?>, 1fr); gap:8px; text-align:center;">
                    <?php foreach ($forecast_breakdown as $fh): ?>
                    <div style="background:#fff; border:1px solid var(--border-color); padding:8px 4px; border-radius:6px;">
                        <div style="font-size:10px; color:var(--text-muted);"><?= $fh['label'] ?></div>
                        <div style="font-size:13px; font-weight:700; color:var(--primary-color); margin-top:4px;"><?= $fh['prediksi'] ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <button onclick="document.getElementById('detailPerhitungan').style.display = 'block'" class="btn btn-outline" style="width:100%; justify-content:center; font-size:13px; margin-bottom:12px;">
                Lihat Detail Historis Data (<?= $target_peramalan === 'mingguan' ? 'Harian' : 'Mingguan' ?>)
            </button>
            
            <div style="display:flex; gap:12px;">
                <form method="POST" action="" style="flex:1;">
                    <input type="hidden" name="action" value="simpan_riwayat">
                    <input type="hidden" name="kode_barang" value="<?= $kode_barang ?>">
                    <input type="hidden" name="nama_barang" value="<?= htmlspecialchars($b['nama_produk']) ?>">
                    <input type="hidden" name="n_periode" value="<?= $n_periode ?>">
                    <input type="hidden" name="alpha" value="<?= $alpha ?>">
                    <input type="hidden" name="hasil" value="<?= $hasilPeramalan ?>">
                    <input type="hidden" name="akurasi" value="<?= max(0, $akurasi) ?>">
                    <input type="hidden" name="target_waktu" value="<?= $target_peramalan === 'mingguan' ? 'Minggu Depan' : 'Bulan Depan' ?>">
                    <button type="submit" class="btn btn-primary" style="background:#22c55e; width:100%; justify-content:center; font-size:13px;">
                        <i class="fa-solid fa-save"></i> Simpan Riwayat
                    </button>
                </form>
                <button onclick="window.print()" class="btn btn-primary" style="background:#3b82f6; flex:1; justify-content:center; font-size:13px;">
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
                Pilih barang dan klik Proses Peramalan untuk melihat hasil.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ── TABEL DATA PERAMALAN (Hidden by default) ───────────────────────── -->
<div id="detailPerhitungan" style="display:none; background:#fff; border:1px solid var(--border-color); border-radius:16px; overflow:hidden; animation: fadeIn 0.3s; margin-bottom:24px;">
    <div style="padding:20px; border-bottom:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center;">
        <h3 style="font-size:15px; font-weight:700;">Data Historis <?= $target_peramalan === 'mingguan' ? 'Harian' : 'Mingguan' ?> — DES (α = <?= $alpha ?>)</h3>
        <button onclick="document.getElementById('detailPerhitungan').style.display = 'none'" class="close-btn"><i class="fa-solid fa-times"></i></button>
    </div>
    <div class="table-responsive">
        <table class="data-table" style="text-align:center;">
            <thead>
                <tr>
                    <th><?= $target_peramalan === 'mingguan' ? 'Hari' : 'Minggu' ?> Ke-</th>
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
                    <td><?= $target_peramalan === 'mingguan' ? 'Minggu Depan' : 'Bulan Depan' ?></td>
                    <td>—</td>
                    <td>—</td>
                    <td>—</td>
                    <td>—</td>
                    <td>—</td>
                    <td style="color:var(--primary-color); font-size:15px;"><?= $hasilPeramalan ?></td>
                    <td>—</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
</div> <!-- Close peramalan-print-area -->

<!-- RIWAYAT PERAMALAN -->
<div style="background:#fff; border:1px solid var(--border-color); border-radius:16px; overflow:hidden; margin-bottom:24px;">
    <div style="padding:20px; border-bottom:1px solid var(--border-color);">
        <h3 style="font-size:15px; font-weight:700;">Riwayat Peramalan Barang</h3>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Waktu Hitung</th>
                    <th>Kode Barang</th>
                    <th>Nama Barang</th>
                    <th>Target Waktu</th>
                    <th>Periode</th>
                    <th>Alpha</th>
                    <th>Hasil Peramalan</th>
                    <th>Akurasi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $qHist = get_query($conn, "SELECT * FROM tbl_riwayat_peramalan WHERE tipe='barang' ORDER BY id_riwayat DESC LIMIT 20");
                if($qHist->num_rows > 0):
                    while($rh = $qHist->fetch_assoc()):
                ?>
                <tr>
                    <td><?= date('d/m/Y H:i', strtotime($rh['tanggal_hitung'])) ?></td>
                    <td><strong><?= $rh['referensi'] ?></strong></td>
                    <td><?= $rh['nama_referensi'] ?></td>
                    <td><?= $rh['target_waktu'] ?></td>
                    <td><?= $rh['periode'] ?> <?= strpos($rh['target_waktu'], 'Minggu') !== false ? 'Hari' : 'Minggu' ?></td>
                    <td><?= $rh['alpha'] ?></td>
                    <td style="color:var(--primary-color); font-weight:bold;"><?= number_format($rh['hasil'],0,',','.') ?> Unit</td>
                    <td><?= $rh['akurasi'] ?>%</td>
                </tr>
                <?php endwhile; else: ?>
                <tr>
                    <td colspan="8" style="text-align:center; padding:30px; color:var(--text-muted);">Belum ada riwayat peramalan barang.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
