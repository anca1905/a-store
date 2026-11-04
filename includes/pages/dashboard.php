<?php
// Dashboard — Pimpinan View
$hariIni = date('Y-m-d');

// KPI 1: Total penjualan hari ini (Rp)
$qPenjualanHariIni = get_query($conn, "SELECT COALESCE(SUM(grand_total), 0) as total FROM tbl_penjualan WHERE DATE(tanggal_waktu) = '$hariIni'");
$totalPenjualanHariIni = $qPenjualanHariIni->fetch_assoc()['total'];

// KPI 2: Total transaksi hari ini (jumlah faktur)
$qTransaksiHariIni = get_query($conn, "SELECT COUNT(*) as jml FROM tbl_penjualan WHERE DATE(tanggal_waktu) = '$hariIni'");
$totalTransaksiHariIni = $qTransaksiHariIni->fetch_assoc()['jml'];

// KPI 3: Total stok barang (jumlah item)
$qTotalStok = get_query($conn, "SELECT COUNT(*) as jml_produk, COALESCE(SUM(stok_aktual), 0) as total_stok FROM tbl_barang");
$dataStok = $qTotalStok->fetch_assoc();
$totalProduk = $dataStok['jml_produk'];
$totalStok   = $dataStok['total_stok'];

// KPI 4: Prediksi stok minggu depan (rata-rata 4 minggu terakhir per minggu)
$qPrediksi = get_query($conn, "
    SELECT COALESCE(SUM(d.qty), 0) as total_qty
    FROM tbl_detail_penjualan d
    JOIN tbl_penjualan p ON d.id_penjualan = p.id_penjualan
    WHERE p.tanggal_waktu >= DATE_SUB(CURRENT_DATE, INTERVAL 28 DAY)
");
$totalQty28 = $qPrediksi->fetch_assoc()['total_qty'];
$prediksiMingguDepan = round($totalQty28 / 4);

// Parameter Filter Rentang Waktu
$tglDari   = trim($_GET['tgl_dari'] ?? '');
$tglSampai = trim($_GET['tgl_sampai'] ?? '');

// Default rentang waktu: 7 hari terakhir
if ($tglDari === '' || $tglSampai === '') {
    $tglDari   = date('Y-m-d', strtotime('-6 days'));
    $tglSampai = date('Y-m-d');
}

// Pastikan urutan tanggal valid
if ($tglDari > $tglSampai) {
    $temp = $tglDari;
    $tglDari = $tglSampai;
    $tglSampai = $temp;
}

$tglDariEsc   = $conn->real_escape_string($tglDari);
$tglSampaiEsc = $conn->real_escape_string($tglSampai);
$dateStartCond = $tglDariEsc . ' 00:00:00';
$dateEndCond   = $tglSampaiEsc . ' 23:59:59';

// Format teks label periode
if ($tglDari === $tglSampai) {
    $badgePeriode = date('d M Y', strtotime($tglDari));
} else {
    $badgePeriode = date('d M Y', strtotime($tglDari)) . ' - ' . date('d M Y', strtotime($tglSampai));
}

// Hitung rentang hari
$currTs = strtotime($tglDari);
$endTs  = strtotime($tglSampai);
$dayCount = round(($endTs - $currTs) / 86400) + 1;

$labelsChart = [];
$salesChart  = [];
$chartType   = 'line';
$rataUnit    = 'hari';

if ($dayCount <= 62) {
    // Mode Harian (<= 62 hari)
    $chartType = 'line';
    $rataUnit  = 'hari';
    while ($currTs <= $endTs) {
        $tglStr = date('Y-m-d', $currTs);
        $labelsChart[] = date('d M', $currTs);
        $salesChart[$tglStr] = 0;
        $currTs = strtotime('+1 day', $currTs);
    }
    $qData = get_query($conn, "
        SELECT DATE(tanggal_waktu) as k, SUM(grand_total) as total
        FROM tbl_penjualan
        WHERE tanggal_waktu >= '$dateStartCond' AND tanggal_waktu <= '$dateEndCond'
        GROUP BY k
    ");
    while ($r = $qData->fetch_assoc()) {
        if (isset($salesChart[$r['k']])) {
            $salesChart[$r['k']] = (int)$r['total'];
        }
    }
} else {
    // Mode Bulanan (> 62 hari)
    $chartType = 'bar';
    $rataUnit  = 'bulan';
    $currMonth = strtotime(date('Y-m-01', $currTs));
    while ($currMonth <= $endTs) {
        $ym = date('Y-m', $currMonth);
        $labelsChart[] = date('M Y', $currMonth);
        $salesChart[$ym] = 0;
        $currMonth = strtotime('+1 month', $currMonth);
    }
    $qData = get_query($conn, "
        SELECT DATE_FORMAT(tanggal_waktu, '%Y-%m') as k, SUM(grand_total) as total
        FROM tbl_penjualan
        WHERE tanggal_waktu >= '$dateStartCond' AND tanggal_waktu <= '$dateEndCond'
        GROUP BY k
    ");
    while ($r = $qData->fetch_assoc()) {
        if (isset($salesChart[$r['k']])) {
            $salesChart[$r['k']] = (int)$r['total'];
        }
    }
}

// Ringkasan periode grafik
$qSummaryPeriode = get_query($conn, "
    SELECT COALESCE(SUM(grand_total), 0) as total_omset, COUNT(*) as total_trx
    FROM tbl_penjualan
    WHERE tanggal_waktu >= '$dateStartCond' AND tanggal_waktu <= '$dateEndCond'
");
$summaryPeriode = $qSummaryPeriode->fetch_assoc();
$totalOmsetPeriode = (int)$summaryPeriode['total_omset'];
$totalTrxPeriode   = (int)$summaryPeriode['total_trx'];
$dataPointsCount   = count($salesChart);
$rataRataPeriode   = ($dataPointsCount > 0) ? round($totalOmsetPeriode / $dataPointsCount) : 0;

$jsSalesChart  = json_encode(array_values($salesChart));
$jsLabelsChart = json_encode($labelsChart);
$jsChartType   = json_encode($chartType);

// Cek bulan transaksi terakhir untuk rekomendasi jika rentang saat ini kosong
$qLastTrx = get_query($conn, "SELECT DATE(tanggal_waktu) as tgl_akhir FROM tbl_penjualan ORDER BY tanggal_waktu DESC LIMIT 1");
$rLastTrx = $qLastTrx ? $qLastTrx->fetch_assoc() : null;
$saranTglDari = '';
$saranTglSampai = '';
$saranLabel = '';
if ($rLastTrx && !empty($rLastTrx['tgl_akhir'])) {
    $lastDate = $rLastTrx['tgl_akhir'];
    $saranTglDari = date('Y-m-01', strtotime($lastDate));
    $saranTglSampai = date('Y-m-t', strtotime($lastDate));
    $saranLabel = date('F Y', strtotime($lastDate));
}

// Tabel: Barang terjual pada periode terpilih
$qBarangTerjual = get_query($conn, "
    SELECT b.nama_produk, SUM(d.qty) as terjual
    FROM tbl_detail_penjualan d
    JOIN tbl_penjualan p ON d.id_penjualan = p.id_penjualan
    JOIN tbl_barang b ON d.kode_barang = b.kode_barang
    WHERE p.tanggal_waktu >= '$dateStartCond' AND p.tanggal_waktu <= '$dateEndCond'
    GROUP BY d.kode_barang
    ORDER BY terjual DESC
    LIMIT 6
");

// Stok kritis
$qKritis = get_query($conn, "SELECT COUNT(*) as jml FROM tbl_barang WHERE stok_aktual <= stok_min");
$stokKritis = $qKritis->fetch_assoc()['jml'];
?>

<div class="dashboard-content-wrapper">
    <!-- Greeting -->
    <div style="margin-bottom: 24px;">
        <h2 style="font-size:22px; font-weight:700; margin-bottom:4px;">Selamat datang, <?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Pimpinan') ?>!</h2>
        <p style="color:var(--text-muted); font-size:14px;">Kelola penjualan data, stok, dan peramalan dengan mudah.</p>
    </div>

    <!-- KPI GRID -->
    <div class="kpi-grid">
        <div class="kpi-card" onclick="window.location.href='?page=riwayat_transaksi'" style="cursor: pointer;">
            <span class="kpi-label">Total Penjualan Hari Ini</span>
            <div class="kpi-value-row">
                <div class="kpi-value-group">
                    <div class="kpi-value" style="font-size:18px;">
                        <span class="sensitive-value" data-value="Rp <?= number_format($totalPenjualanHariIni, 0, ',', '.') ?>">Rp ••••••••</span>
                    </div>
                    <div class="kpi-unit">Pendapatan</div>
                </div>
                <i class="fa-solid fa-money-bill-wave kpi-icon"></i>
            </div>
        </div>

        <div class="kpi-card" onclick="window.location.href='?page=riwayat_transaksi'" style="cursor: pointer;">
            <span class="kpi-label">Total Transaksi Hari Ini</span>
            <div class="kpi-value-row">
                <div class="kpi-value-group">
                    <div class="kpi-value"><?= $totalTransaksiHariIni ?></div>
                    <div class="kpi-unit">Transaksi</div>
                </div>
                <i class="fa-solid fa-receipt kpi-icon"></i>
            </div>
        </div>

        <div class="kpi-card" onclick="window.location.href='?page=stok'" style="cursor: pointer;">
            <span class="kpi-label">Stok Barang</span>
            <div class="kpi-value-row">
                <div class="kpi-value-group">
                    <div class="kpi-value"><?= number_format($totalStok, 0, ',', '.') ?></div>
                    <div class="kpi-unit"><?= $totalProduk ?> Produk<?php if($stokKritis > 0): ?> <span style="color:var(--danger-color);">• <?= $stokKritis ?> kritis</span><?php endif; ?></div>
                </div>
                <i class="fa-solid fa-boxes-stacked kpi-icon"></i>
            </div>
        </div>

        <div class="kpi-card" onclick="window.location.href='?page=des_kategori'" style="cursor: pointer;">
            <span class="kpi-label">Prediksi Stok Minggu Depan</span>
            <div class="kpi-value-row">
                <div class="kpi-value-group">
                    <div class="kpi-value"><?= number_format($prediksiMingguDepan, 0, ',', '.') ?></div>
                    <div class="kpi-unit">Pcs (perkiraan)</div>
                </div>
                <i class="fa-solid fa-arrow-trend-up kpi-icon"></i>
            </div>
        </div>
    </div>

    <!-- CHART + TABLE SECTION -->
    <div style="display: grid; grid-template-columns: 1fr 340px; gap: 24px; margin-top: 8px; flex-wrap: wrap;">
        <!-- Grafik Penjualan -->
        <div class="chart-card">
            <!-- Header & Filter Bar -->
            <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:14px; margin-bottom:16px;">
                <div>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <h3 style="font-size:16px; font-weight:700; margin:0;">Grafik Penjualan</h3>
                        <span style="background:var(--bg-body); border:1px solid var(--border-color); color:var(--text-main); font-size:11.5px; padding:2px 8px; border-radius:6px; font-weight:600;">
                            <?= htmlspecialchars($badgePeriode) ?>
                        </span>
                    </div>
                    <!-- Sensitive Nominals + Eye Toggle Button -->
                    <div style="font-size:12px; color:var(--text-muted); margin-top:5px; display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                        <span>
                            Total: <strong class="sensitive-value" data-value="Rp <?= number_format($totalOmsetPeriode, 0, ',', '.') ?>" style="color:var(--text-main); font-size:13px; font-weight:700;">Rp ••••••••</strong>
                            <span style="margin:0 5px; color:#cbd5e1;">•</span>
                            <strong style="color:var(--text-main);"><?= number_format($totalTrxPeriode) ?></strong> Transaksi
                            <span style="margin:0 5px; color:#cbd5e1;">•</span>
                            Rata-rata: <strong class="sensitive-value" data-value="Rp <?= number_format($rataRataPeriode, 0, ',', '.') ?>" style="color:var(--text-main); font-weight:700;">Rp ••••••••</strong> / <?= $rataUnit ?>
                        </span>
                        <button type="button" onclick="toggleSensitiveValues()" id="btnToggleSensitif" style="border:1px solid var(--border-color); background:#fff; border-radius:6px; padding:2px 8px; cursor:pointer; color:var(--text-muted); font-size:11.5px; display:inline-flex; align-items:center; gap:5px; line-height:1.2; box-shadow:0 1px 2px rgba(0,0,0,0.02);" title="Klik untuk menampilkan / menyembunyikan nominal">
                            <i class="fa-solid fa-eye-slash" id="iconSensitif"></i>
                            <span id="labelSensitif" style="font-weight:600;">Sembunyi</span>
                        </button>
                    </div>
                </div>

                <!-- Form Filter Rentang Waktu -->
                <form method="GET" action="" id="formFilterRentang" style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                    <input type="hidden" name="page" value="dashboard">
                    <div style="display:inline-flex; align-items:center; gap:6px; background:#fff; border:1px solid var(--border-color); padding:4px 10px; border-radius:8px; box-shadow:0 1px 2px rgba(0,0,0,0.02);">
                        <i class="fa-solid fa-calendar-days" style="color:var(--text-muted); font-size:12.5px;"></i>
                        <span style="font-size:12px; color:var(--text-muted); font-weight:600;">Rentang:</span>
                        <input type="date" name="tgl_dari" id="filterTglDari" value="<?= htmlspecialchars($tglDari) ?>" class="form-control" style="border:none; padding:2px 0; font-size:12.5px; width:125px; height:auto; box-shadow:none; cursor:pointer;" onchange="autoSubmitRentang()">
                        <span style="color:var(--text-muted); font-size:12px; font-weight:600;">s/d</span>
                        <input type="date" name="tgl_sampai" id="filterTglSampai" value="<?= htmlspecialchars($tglSampai) ?>" class="form-control" style="border:none; padding:2px 0; font-size:12.5px; width:125px; height:auto; box-shadow:none; cursor:pointer;" onchange="autoSubmitRentang()">
                    </div>
                    <?php if (isset($_GET['tgl_dari']) || isset($_GET['tgl_sampai'])): ?>
                    <a href="?page=dashboard" class="btn btn-outline" style="padding:5px 10px; font-size:12px; color:#ef4444; border-color:#fca5a5;" title="Kembali ke 7 hari terakhir">
                        <i class="fa-solid fa-rotate-left"></i> Reset
                    </a>
                    <?php endif; ?>
                </form>
            </div>

            <?php if ($totalTrxPeriode == 0 && !empty($saranTglDari)): ?>
            <!-- Alert jika rentang terpilih masih kosong & ada riwayat transaksi sebelumnya -->
            <div style="background:#fffbeb; border:1px solid #fef3c7; color:#92400e; padding:9px 13px; border-radius:8px; font-size:12px; margin-bottom:14px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                <div>
                    <i class="fa-solid fa-circle-info" style="color:#d97706; margin-right:5px;"></i>
                    Belum ada transaksi di rentang tanggal ini. Terakhir ada data pada <strong><?= $saranLabel ?></strong>.
                </div>
                <a href="?page=dashboard&tgl_dari=<?= $saranTglDari ?>&tgl_sampai=<?= $saranTglSampai ?>" style="color:#b45309; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:4px; font-size:11.5px; background:#fef3c7; padding:3px 9px; border-radius:6px; border:1px solid #fde68a;">
                    Buka Rentang <?= $saranLabel ?> <i class="fa-solid fa-arrow-right" style="font-size:10px;"></i>
                </a>
            </div>
            <?php endif; ?>

            <div style="height: 260px;">
                <canvas id="chartPenjualan"></canvas>
            </div>
        </div>

        <!-- Tabel barang terjual -->
        <div class="chart-card">
            <h3 style="font-size:15px; font-weight:700; margin-bottom:16px;">
                Barang Terjual <span style="font-size:12px; color:var(--text-muted); font-weight:400;">(<?= htmlspecialchars($badgePeriode) ?>)</span>
            </h3>
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid var(--border-color);">
                        <th style="text-align:left; padding:8px 0; font-size:13px; color:var(--text-muted); font-weight:600;">Barang</th>
                        <th style="text-align:right; padding:8px 0; font-size:13px; color:var(--text-muted); font-weight:600;">Terjual</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($qBarangTerjual->num_rows > 0): ?>
                        <?php while ($bt = $qBarangTerjual->fetch_assoc()): ?>
                        <tr style="border-bottom:1px solid #f9fafb;">
                            <td style="padding:10px 0; font-size:13px;"><?= htmlspecialchars($bt['nama_produk']) ?></td>
                            <td style="padding:10px 0; font-size:13px; text-align:right; font-weight:600; color:var(--primary-color);"><?= number_format($bt['terjual']) ?> pcs</td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="2" style="text-align:center; padding:30px; color:var(--text-muted); font-size:13px;">Belum ada penjualan di periode ini</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Logika Sembunyikan / Tampilkan Nominal Sensitif dengan Icon Mata
let isSensitiveHidden = (localStorage.getItem('dashboard_sensitif_hidden') !== 'false'); // Default: Sembunyi (true)

function applySensitiveState() {
    const elements = document.querySelectorAll('.sensitive-value');
    const icon = document.getElementById('iconSensitif');
    const label = document.getElementById('labelSensitif');

    elements.forEach(el => {
        const original = el.getAttribute('data-value');
        if (isSensitiveHidden) {
            el.textContent = 'Rp ••••••••';
            el.title = 'Nominal disembunyikan (klik icon mata untuk melihat)';
        } else {
            el.textContent = original;
            el.title = '';
        }
    });

    if (icon) {
        if (isSensitiveHidden) {
            icon.className = 'fa-solid fa-eye-slash';
            if (label) label.textContent = 'Sembunyi';
        } else {
            icon.className = 'fa-solid fa-eye';
            if (label) label.textContent = 'Terlihat';
        }
    }

    localStorage.setItem('dashboard_sensitif_hidden', isSensitiveHidden ? 'true' : 'false');
}

function toggleSensitiveValues() {
    isSensitiveHidden = !isSensitiveHidden;
    applySensitiveState();
    if (window.salesChartInstance) {
        window.salesChartInstance.update();
    }
}

// Otomatis submit form rentang waktu saat tanggal diubah
function autoSubmitRentang() {
    const dari = document.getElementById('filterTglDari').value;
    const sampai = document.getElementById('filterTglSampai').value;
    if (dari && sampai) {
        if (dari > sampai) {
            document.getElementById('filterTglSampai').value = dari;
        }
        document.getElementById('formFilterRentang').submit();
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // Terapkan state sensitif awal
    applySensitiveState();

    Chart.defaults.font.family = "'Outfit', sans-serif";
    Chart.defaults.color = '#64748B';

    const ctx = document.getElementById('chartPenjualan');
    if (ctx) {
        const chartType = <?= $jsChartType ?>;
        const labels    = <?= $jsLabelsChart ?>;
        const salesData = <?= $jsSalesChart ?>;

        let datasetConfig = {};
        if (chartType === 'bar') {
            datasetConfig = {
                label: 'Penjualan (Rp)',
                data: salesData,
                backgroundColor: '#111111',
                borderRadius: 6,
                maxBarThickness: 34
            };
        } else {
            datasetConfig = {
                label: 'Penjualan (Rp)',
                data: salesData,
                backgroundColor: 'rgba(17, 17, 17, 0.05)',
                borderColor: '#111111',
                borderWidth: 2,
                pointBackgroundColor: '#111111',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 1.5,
                pointRadius: (labels.length > 20) ? 2.5 : 4,
                pointHoverRadius: 6,
                fill: true,
                tension: 0.25
            };
        }

        window.salesChartInstance = new Chart(ctx, {
            type: chartType,
            data: {
                labels: labels,
                datasets: [datasetConfig]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleFont: { size: 12, weight: '600' },
                        bodyFont: { size: 12 },
                        padding: 10,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                if (isSensitiveHidden) {
                                    return 'Total: Rp ••••••••';
                                }
                                return 'Total: Rp ' + Number(context.parsed.y || 0).toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#F1F5F9', borderDash: [4, 4] },
                        ticks: {
                            font: { size: 11 },
                            callback: function(value) {
                                if (isSensitiveHidden) {
                                    if (value === 0) return '0';
                                    return '••••';
                                }
                                if (value === 0) return '0';
                                if (value >= 1000000) return (value / 1000000).toFixed(1).replace('.0', '') + 'jt';
                                if (value >= 1000) return (value / 1000).toFixed(0) + 'rb';
                                return value;
                            }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { size: 11 },
                            maxRotation: (labels.length > 15) ? 45 : 0,
                            autoSkip: true,
                            maxTicksLimit: (labels.length > 20) ? 15 : 31
                        }
                    }
                }
            }
        });
    }
});
</script>