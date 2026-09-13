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

// Data grafik: penjualan 7 hari terakhir
$sales7Days = [];
$labels7Days = [];
for ($i = 6; $i >= 0; $i--) {
    $tanggal = date('Y-m-d', strtotime("-$i days"));
    $labels7Days[] = date('d M', strtotime($tanggal));
    $sales7Days[$tanggal] = 0;
}
$q7Days = get_query($conn, "
    SELECT DATE(tanggal_waktu) as tgl, SUM(grand_total) as total
    FROM tbl_penjualan
    WHERE tanggal_waktu >= DATE_SUB(CURRENT_DATE, INTERVAL 6 DAY)
    GROUP BY tgl
");
while ($r = $q7Days->fetch_assoc()) {
    if (isset($sales7Days[$r['tgl']])) {
        $sales7Days[$r['tgl']] = (int)$r['total'];
    }
}
$jsSales7 = json_encode(array_values($sales7Days));
$jsLabels7 = json_encode($labels7Days);

// Tabel: Barang terjual (top 7 hari terakhir)
$qBarangTerjual = get_query($conn, "
    SELECT b.nama_produk, SUM(d.qty) as terjual
    FROM tbl_detail_penjualan d
    JOIN tbl_penjualan p ON d.id_penjualan = p.id_penjualan
    JOIN tbl_barang b ON d.kode_barang = b.kode_barang
    WHERE p.tanggal_waktu >= DATE_SUB(CURRENT_DATE, INTERVAL 6 DAY)
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
        <div class="kpi-card">
            <span class="kpi-label">Total Penjualan Hari Ini</span>
            <div class="kpi-value-row">
                <div class="kpi-value-group">
                    <div class="kpi-value" style="font-size:18px;">Rp <?= number_format($totalPenjualanHariIni, 0, ',', '.') ?></div>
                    <div class="kpi-unit">Pendapatan</div>
                </div>
                <i class="fa-solid fa-money-bill-wave kpi-icon"></i>
            </div>
        </div>

        <div class="kpi-card">
            <span class="kpi-label">Total Transaksi Hari Ini</span>
            <div class="kpi-value-row">
                <div class="kpi-value-group">
                    <div class="kpi-value"><?= $totalTransaksiHariIni ?></div>
                    <div class="kpi-unit">Transaksi</div>
                </div>
                <i class="fa-solid fa-receipt kpi-icon"></i>
            </div>
        </div>

        <div class="kpi-card">
            <span class="kpi-label">Stok Barang</span>
            <div class="kpi-value-row">
                <div class="kpi-value-group">
                    <div class="kpi-value"><?= number_format($totalStok, 0, ',', '.') ?></div>
                    <div class="kpi-unit"><?= $totalProduk ?> Produk<?php if($stokKritis > 0): ?> <span style="color:var(--danger-color);">• <?= $stokKritis ?> kritis</span><?php endif; ?></div>
                </div>
                <i class="fa-solid fa-boxes-stacked kpi-icon"></i>
            </div>
        </div>

        <div class="kpi-card">
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
        <!-- Grafik penjualan 7 hari -->
        <div class="chart-card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h3 style="font-size:15px; font-weight:700;">Grafik Penjualan 7 Hari Terakhir</h3>
            </div>
            <div style="height: 260px;">
                <canvas id="chart7Days"></canvas>
            </div>
        </div>

        <!-- Tabel barang terjual -->
        <div class="chart-card">
            <h3 style="font-size:15px; font-weight:700; margin-bottom:16px;">Barang Terjual <span style="font-size:12px; color:var(--text-muted); font-weight:400;">(7 hari terakhir)</span></h3>
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
                            <td style="padding:10px 0; font-size:13px; text-align:right; font-weight:600; color:var(--primary-color);"><?= $bt['terjual'] ?> pcs</td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="2" style="text-align:center; padding:30px; color:var(--text-muted); font-size:13px;">Belum ada penjualan 7 hari ini</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    Chart.defaults.font.family = "'Outfit', sans-serif";
    Chart.defaults.color = '#64748B';

    const ctx = document.getElementById('chart7Days');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= $jsLabels7 ?>,
                datasets: [{
                    label: 'Penjualan (Rp)',
                    data: <?= $jsSales7 ?>,
                    backgroundColor: 'transparent',
                    borderColor: '#111111',
                    borderWidth: 2,
                    pointBackgroundColor: '#111111',
                    pointBorderColor: '#111111',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#F1F5F9', borderDash: [5, 5] },
                        ticks: {
                            font: { size: 11 },
                            callback: function(value) {
                                if (value === 0) return '0';
                                if (value >= 1000000) return (value / 1000000).toFixed(1).replace('.0', '') + 'jt';
                                if (value >= 1000) return (value / 1000).toFixed(0) + 'rb';
                                return value;
                            }
                        }
                    },
                    x: { grid: { display: false }, ticks: { font: { size: 11 } } }
                }
            }
        });
    }
});
</script>