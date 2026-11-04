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

// Data nama bulan Indonesia
$namaBulanIndo = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

// Ambil riwayat bulan & tahun yang tersedia di tbl_penjualan
$qMonthsDB = get_query($conn, "
    SELECT DISTINCT DATE_FORMAT(tanggal_waktu, '%Y-%m') as ym,
           YEAR(tanggal_waktu) as thn,
           MONTH(tanggal_waktu) as bln,
           COUNT(*) as jml
    FROM tbl_penjualan
    WHERE tanggal_waktu IS NOT NULL
    GROUP BY ym, thn, bln
    ORDER BY ym DESC
");
$listBulanDB = [];
$listTahunDB = [];
while ($rm = $qMonthsDB->fetch_assoc()) {
    $blnIndo = $namaBulanIndo[(int)$rm['bln']] ?? $rm['bln'];
    $listBulanDB[] = [
        'ym'    => $rm['ym'],
        'label' => $blnIndo . ' ' . $rm['thn'],
        'thn'   => (int)$rm['thn'],
        'bln'   => (int)$rm['bln'],
        'jml'   => (int)$rm['jml']
    ];
    if (!in_array((int)$rm['thn'], $listTahunDB)) {
        $listTahunDB[] = (int)$rm['thn'];
    }
}
$curYear = (int)date('Y');
if (!in_array($curYear, $listTahunDB)) {
    array_unshift($listTahunDB, $curYear);
}

// Parameter Filter Grafik
$filterPeriode = trim($_GET['periode'] ?? '7hari');
$tglMulai      = trim($_GET['tgl_mulai'] ?? '');
$tglSelesai    = trim($_GET['tgl_selesai'] ?? '');

$labelsChart   = [];
$salesChart    = [];
$chartType     = 'line';
$rataUnit      = 'hari';
$badgePeriode  = '7 Hari Terakhir';
$dateStartCond = '';
$dateEndCond   = '';

if (strpos($filterPeriode, 'm_') === 0) {
    // Mode Bulan Tertentu (contoh: m_2026-09)
    $ym = substr($filterPeriode, 2);
    $parts = explode('-', $ym);
    $thn = (int)($parts[0] ?? date('Y'));
    $bln = (int)($parts[1] ?? date('m'));
    $totalDays = cal_days_in_month(CAL_GREGORIAN, $bln, $thn);

    $badgePeriode = ($namaBulanIndo[$bln] ?? $bln) . ' ' . $thn;
    $dateStartCond = sprintf('%04d-%02d-01 00:00:00', $thn, $bln);
    $dateEndCond   = sprintf('%04d-%02d-%02d 23:59:59', $thn, $bln, $totalDays);

    for ($d = 1; $d <= $totalDays; $d++) {
        $tglStr = sprintf('%04d-%02d-%02d', $thn, $bln, $d);
        $labelsChart[] = sprintf('%02d %s', $d, substr($namaBulanIndo[$bln], 0, 3));
        $salesChart[$tglStr] = 0;
    }

    $qData = get_query($conn, "
        SELECT DATE(tanggal_waktu) as tgl, SUM(grand_total) as total
        FROM tbl_penjualan
        WHERE tanggal_waktu >= '$dateStartCond' AND tanggal_waktu <= '$dateEndCond'
        GROUP BY tgl
    ");
    while ($r = $qData->fetch_assoc()) {
        if (isset($salesChart[$r['tgl']])) {
            $salesChart[$r['tgl']] = (int)$r['total'];
        }
    }
} elseif (strpos($filterPeriode, 'y_') === 0) {
    // Mode Tahunan (contoh: y_2026)
    $thn = (int)substr($filterPeriode, 2);
    $chartType = 'bar';
    $rataUnit = 'bulan';
    $badgePeriode = 'Tahun ' . $thn;
    $dateStartCond = sprintf('%04d-01-01 00:00:00', $thn);
    $dateEndCond   = sprintf('%04d-12-31 23:59:59', $thn);

    for ($m = 1; $m <= 12; $m++) {
        $labelsChart[] = substr($namaBulanIndo[$m], 0, 3);
        $salesChart[$m] = 0;
    }

    $qData = get_query($conn, "
        SELECT MONTH(tanggal_waktu) as bln, SUM(grand_total) as total
        FROM tbl_penjualan
        WHERE YEAR(tanggal_waktu) = '$thn'
        GROUP BY bln
    ");
    while ($r = $qData->fetch_assoc()) {
        $salesChart[(int)$r['bln']] = (int)$r['total'];
    }
} elseif ($filterPeriode === 'bulan_ini') {
    $thn = (int)date('Y');
    $bln = (int)date('m');
    $totalDays = cal_days_in_month(CAL_GREGORIAN, $bln, $thn);
    $badgePeriode = 'Bulan Ini (' . $namaBulanIndo[$bln] . ' ' . $thn . ')';
    $dateStartCond = sprintf('%04d-%02d-01 00:00:00', $thn, $bln);
    $dateEndCond   = sprintf('%04d-%02d-%02d 23:59:59', $thn, $bln, $totalDays);

    for ($d = 1; $d <= $totalDays; $d++) {
        $tglStr = sprintf('%04d-%02d-%02d', $thn, $bln, $d);
        $labelsChart[] = sprintf('%02d %s', $d, substr($namaBulanIndo[$bln], 0, 3));
        $salesChart[$tglStr] = 0;
    }

    $qData = get_query($conn, "
        SELECT DATE(tanggal_waktu) as tgl, SUM(grand_total) as total
        FROM tbl_penjualan
        WHERE tanggal_waktu >= '$dateStartCond' AND tanggal_waktu <= '$dateEndCond'
        GROUP BY tgl
    ");
    while ($r = $qData->fetch_assoc()) {
        if (isset($salesChart[$r['tgl']])) {
            $salesChart[$r['tgl']] = (int)$r['total'];
        }
    }
} elseif ($filterPeriode === 'bulan_lalu') {
    $lastMonthTs = strtotime('first day of last month');
    $thn = (int)date('Y', $lastMonthTs);
    $bln = (int)date('m', $lastMonthTs);
    $totalDays = cal_days_in_month(CAL_GREGORIAN, $bln, $thn);
    $badgePeriode = 'Bulan Lalu (' . $namaBulanIndo[$bln] . ' ' . $thn . ')';
    $dateStartCond = sprintf('%04d-%02d-01 00:00:00', $thn, $bln);
    $dateEndCond   = sprintf('%04d-%02d-%02d 23:59:59', $thn, $bln, $totalDays);

    for ($d = 1; $d <= $totalDays; $d++) {
        $tglStr = sprintf('%04d-%02d-%02d', $thn, $bln, $d);
        $labelsChart[] = sprintf('%02d %s', $d, substr($namaBulanIndo[$bln], 0, 3));
        $salesChart[$tglStr] = 0;
    }

    $qData = get_query($conn, "
        SELECT DATE(tanggal_waktu) as tgl, SUM(grand_total) as total
        FROM tbl_penjualan
        WHERE tanggal_waktu >= '$dateStartCond' AND tanggal_waktu <= '$dateEndCond'
        GROUP BY tgl
    ");
    while ($r = $qData->fetch_assoc()) {
        if (isset($salesChart[$r['tgl']])) {
            $salesChart[$r['tgl']] = (int)$r['total'];
        }
    }
} elseif ($filterPeriode === '30hari') {
    $badgePeriode = '30 Hari Terakhir';
    $dateStartCond = date('Y-m-d 00:00:00', strtotime('-29 days'));
    $dateEndCond   = date('Y-m-d 23:59:59');

    for ($i = 29; $i >= 0; $i--) {
        $tgl = date('Y-m-d', strtotime("-$i days"));
        $labelsChart[] = date('d M', strtotime($tgl));
        $salesChart[$tgl] = 0;
    }

    $qData = get_query($conn, "
        SELECT DATE(tanggal_waktu) as tgl, SUM(grand_total) as total
        FROM tbl_penjualan
        WHERE tanggal_waktu >= '$dateStartCond' AND tanggal_waktu <= '$dateEndCond'
        GROUP BY tgl
    ");
    while ($r = $qData->fetch_assoc()) {
        if (isset($salesChart[$r['tgl']])) {
            $salesChart[$r['tgl']] = (int)$r['total'];
        }
    }
} elseif ($filterPeriode === 'custom' && !empty($tglMulai) && !empty($tglSelesai)) {
    $badgePeriode = date('d M Y', strtotime($tglMulai)) . ' - ' . date('d M Y', strtotime($tglSelesai));
    $dateStartCond = $tglMulai . ' 00:00:00';
    $dateEndCond   = $tglSelesai . ' 23:59:59';

    $curr = strtotime($tglMulai);
    $end  = strtotime($tglSelesai);
    $dayCount = round(($end - $curr) / 86400) + 1;

    if ($dayCount <= 62) {
        while ($curr <= $end) {
            $tglStr = date('Y-m-d', $curr);
            $labelsChart[] = date('d M', $curr);
            $salesChart[$tglStr] = 0;
            $curr = strtotime('+1 day', $curr);
        }
        $qData = get_query($conn, "
            SELECT DATE(tanggal_waktu) as tgl, SUM(grand_total) as total
            FROM tbl_penjualan
            WHERE tanggal_waktu >= '$dateStartCond' AND tanggal_waktu <= '$dateEndCond'
            GROUP BY tgl
        ");
        while ($r = $qData->fetch_assoc()) {
            if (isset($salesChart[$r['tgl']])) {
                $salesChart[$r['tgl']] = (int)$r['total'];
            }
        }
    } else {
        $chartType = 'bar';
        $rataUnit = 'bulan';
        $curr = strtotime(date('Y-m-01', $curr));
        while ($curr <= $end) {
            $ym = date('Y-m', $curr);
            $labelsChart[] = date('M Y', $curr);
            $salesChart[$ym] = 0;
            $curr = strtotime('+1 month', $curr);
        }
        $qData = get_query($conn, "
            SELECT DATE_FORMAT(tanggal_waktu, '%Y-%m') as ym, SUM(grand_total) as total
            FROM tbl_penjualan
            WHERE tanggal_waktu >= '$dateStartCond' AND tanggal_waktu <= '$dateEndCond'
            GROUP BY ym
        ");
        while ($r = $qData->fetch_assoc()) {
            if (isset($salesChart[$r['ym']])) {
                $salesChart[$r['ym']] = (int)$r['total'];
            }
        }
    }
} else {
    // Default: 7 Hari Terakhir
    $filterPeriode = '7hari';
    $badgePeriode = '7 Hari Terakhir';
    $dateStartCond = date('Y-m-d 00:00:00', strtotime('-6 days'));
    $dateEndCond   = date('Y-m-d 23:59:59');

    for ($i = 6; $i >= 0; $i--) {
        $tgl = date('Y-m-d', strtotime("-$i days"));
        $labelsChart[] = date('d M', strtotime($tgl));
        $salesChart[$tgl] = 0;
    }

    $qData = get_query($conn, "
        SELECT DATE(tanggal_waktu) as tgl, SUM(grand_total) as total
        FROM tbl_penjualan
        WHERE tanggal_waktu >= '$dateStartCond' AND tanggal_waktu <= '$dateEndCond'
        GROUP BY tgl
    ");
    while ($r = $qData->fetch_assoc()) {
        if (isset($salesChart[$r['tgl']])) {
            $salesChart[$r['tgl']] = (int)$r['total'];
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
                    <div class="kpi-value" style="font-size:18px;">Rp <?= number_format($totalPenjualanHariIni, 0, ',', '.') ?></div>
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
                    <div style="font-size:12px; color:var(--text-muted); margin-top:4px;">
                        Total: <strong style="color:var(--text-main); font-size:13px;">Rp <?= number_format($totalOmsetPeriode, 0, ',', '.') ?></strong>
                        <span style="margin:0 5px; color:#cbd5e1;">•</span>
                        <strong style="color:var(--text-main);"><?= number_format($totalTrxPeriode) ?></strong> Transaksi
                        <span style="margin:0 5px; color:#cbd5e1;">•</span>
                        Rata-rata: <strong style="color:var(--text-main);">Rp <?= number_format($rataRataPeriode, 0, ',', '.') ?></strong> / <?= $rataUnit ?>
                    </div>
                </div>

                <!-- Form Filter Periode -->
                <form method="GET" action="" id="formFilterGrafik" style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                    <input type="hidden" name="page" value="dashboard">

                    <!-- Dropdown Periode -->
                    <div>
                        <select name="periode" id="selectPeriodeGrafik" class="form-control" onchange="handlePeriodeChange(this.value)" style="font-size:12.5px; font-weight:600; padding:6px 12px; height:auto; cursor:pointer; border-radius:8px; min-width:185px;">
                            <optgroup label="Preset Waktu">
                                <option value="7hari" <?= $filterPeriode === '7hari' ? 'selected' : '' ?>>7 Hari Terakhir</option>
                                <option value="30hari" <?= $filterPeriode === '30hari' ? 'selected' : '' ?>>30 Hari Terakhir</option>
                                <option value="bulan_ini" <?= $filterPeriode === 'bulan_ini' ? 'selected' : '' ?>>Bulan Ini (<?= date('M Y') ?>)</option>
                                <option value="bulan_lalu" <?= $filterPeriode === 'bulan_lalu' ? 'selected' : '' ?>>Bulan Lalu</option>
                            </optgroup>
                            <?php if (!empty($listBulanDB)): ?>
                            <optgroup label="Bulan Riwayat Sebelumnya">
                                <?php foreach ($listBulanDB as $lb): ?>
                                <option value="m_<?= $lb['ym'] ?>" <?= $filterPeriode === 'm_' . $lb['ym'] ? 'selected' : '' ?>>
                                    <?= $lb['label'] ?> (<?= $lb['jml'] ?> trx)
                                </option>
                                <?php endforeach; ?>
                            </optgroup>
                            <?php endif; ?>
                            <?php if (!empty($listTahunDB)): ?>
                            <optgroup label="Tahunan (12 Bulan)">
                                <?php foreach ($listTahunDB as $th): ?>
                                <option value="y_<?= $th ?>" <?= $filterPeriode === 'y_' . $th ? 'selected' : '' ?>>
                                    Tahun <?= $th ?> (Jan - Des)
                                </option>
                                <?php endforeach; ?>
                            </optgroup>
                            <?php endif; ?>
                            <optgroup label="Kustom">
                                <option value="custom" <?= $filterPeriode === 'custom' ? 'selected' : '' ?>>Rentang Tanggal Custom...</option>
                            </optgroup>
                        </select>
                    </div>

                    <!-- Panel Input Custom Rentang Tanggal -->
                    <div id="panelCustomDate" style="display:<?= $filterPeriode === 'custom' ? 'flex' : 'none' ?>; gap:6px; align-items:center;">
                        <input type="date" name="tgl_mulai" value="<?= htmlspecialchars($tglMulai) ?>" class="form-control" style="font-size:12px; padding:6px 8px; height:auto; width:130px;">
                        <span style="font-size:12px; color:var(--text-muted);">-</span>
                        <input type="date" name="tgl_selesai" value="<?= htmlspecialchars($tglSelesai) ?>" class="form-control" style="font-size:12px; padding:6px 8px; height:auto; width:130px;">
                        <button type="submit" class="btn btn-primary" style="padding:6px 12px; font-size:12px;">
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>
                </form>
            </div>

            <?php if ($totalTrxPeriode == 0 && !empty($listBulanDB) && strpos($filterPeriode, 'm_') !== 0): ?>
            <!-- Alert jika periode terpilih masih kosong & ada riwayat transaksi sebelumnya -->
            <div style="background:#fffbeb; border:1px solid #fef3c7; color:#92400e; padding:9px 13px; border-radius:8px; font-size:12px; margin-bottom:14px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                <div>
                    <i class="fa-solid fa-circle-info" style="color:#d97706; margin-right:5px;"></i>
                    Belum ada transaksi di <strong><?= htmlspecialchars($badgePeriode) ?></strong>. Terakhir ada data pada <strong><?= $listBulanDB[0]['label'] ?></strong>.
                </div>
                <a href="?page=dashboard&periode=m_<?= $listBulanDB[0]['ym'] ?>" style="color:#b45309; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:4px; font-size:11.5px; background:#fef3c7; padding:3px 9px; border-radius:6px; border:1px solid #fde68a;">
                    Buka Grafik <?= $listBulanDB[0]['label'] ?> <i class="fa-solid fa-arrow-right" style="font-size:10px;"></i>
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
function handlePeriodeChange(val) {
    const panel = document.getElementById('panelCustomDate');
    if (val === 'custom') {
        if (panel) panel.style.display = 'flex';
    } else {
        if (panel) panel.style.display = 'none';
        document.getElementById('formFilterGrafik').submit();
    }
}

document.addEventListener('DOMContentLoaded', () => {
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

        new Chart(ctx, {
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