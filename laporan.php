<?php
include 'config.php';
include 'cek_sesi.php';
$pageTitle = "Cetak Pusat Laporan";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - A STORE BI System</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .report-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; }
        .report-card { background: var(--surface-card); padding: 30px; border-radius: var(--border-radius-lg); text-align: center; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); transition: 0.3s; }
        .report-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-card); border-color: var(--primary-color); }
        .report-icon { font-size: 40px; color: var(--primary-color); margin-bottom: 20px; }
        .report-title { font-size: 18px; font-weight: 600; margin-bottom: 10px; color: var(--text-main); }
        .report-desc { font-size: 14px; color: var(--text-muted); margin-bottom: 24px; }
        .btn-print { width: 100%; border-radius: 8px; justify-content: center; }
        .form-control { width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; font-family: inherit; margin-bottom: 16px; outline: none; }
    </style>
</head>
<body>
    <div class="layout-wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <div class="main-content">
            <?php include 'includes/topbar.php'; ?>

            <main class="dashboard-content">
                <div class="page-header">
                    <h2>Pusat Cetak Laporan</h2>
                    <p>Hasilkan laporan dinamis yang merekap data dari Database MySQL ke format fisik (PDF/Excel).</p>
                </div>

                <div class="report-grid">
                    <div class="report-card">
                        <i class="fa-solid fa-file-invoice-dollar report-icon"></i>
                        <h3 class="report-title">Laporan Penjualan (Riil)</h3>
                        <p class="report-desc">Cetak invoice data penjualan berdasarkan transaksi table SQL Penjualan bulan tertentu.</p>
                        <form action="" onsubmit="alert('Fitur Cetak PDF membutuhkan FPDF/DomPDF PHP Library. Ini adalah simulasi.'); return false;">
                            <div style="text-align: left;">
                                <label style="font-size:13px; color:var(--text-muted); display:block; margin-bottom:5px;">Pilih Bulan</label>
                                <input type="month" required class="form-control" value="<?= date('Y-m') ?>">
                            </div>
                            <button type="submit" class="btn btn-primary btn-print"><i class="fa-solid fa-print"></i> Eksekusi Cetak</button>
                        </form>
                    </div>

                    <div class="report-card">
                        <i class="fa-solid fa-chart-line report-icon" style="color:var(--warning-color)"></i>
                        <h3 class="report-title">Laporan Kalkulasi Prediksi</h3>
                        <p class="report-desc">Print kalkulator matrix Prediksi SMA untuk diserahkan ke tim gudang persediaan.</p>
                        <form action="" onsubmit="alert('Fitur Cetak PDF Rekap SMA.'); return false;">
                            <div style="text-align: left;">
                                <label style="font-size:13px; color:var(--text-muted); display:block; margin-bottom:5px;">Barang yang Diprediksi</label>
                                <select class="form-control">
                                    <option>Semua Barang</option>
                                    <?php
                                    $qB = get_query($conn, "SELECT kode_barang FROM tbl_barang LIMIT 3");
                                    while($rB=$qB->fetch_assoc()) { echo "<option>{$rB['kode_barang']}</option>"; }
                                    ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary btn-print" style="background:var(--warning-color); border:none;"><i class="fa-solid fa-print"></i> Cetak Dokumen</button>
                        </form>
                    </div>

                    <div class="report-card">
                        <i class="fa-solid fa-boxes-stacked report-icon" style="color:var(--success-color)"></i>
                        <h3 class="report-title">Status Stok Aktual</h3>
                        <p class="report-desc">Download format Excel langsung tarikan dari tabel `tbl_barang` yang berisi status stok terkini.</p>
                        <form action="" onsubmit="alert('Cetak format CSV/Excel ke lokal...'); return false;">
                            <div style="text-align: left;">
                                <label style="font-size:13px; color:var(--text-muted); display:block; margin-bottom:5px;">Kondisi Barang</label>
                                <select class="form-control">
                                    <option>Bebas (Semua Data)</option>
                                    <option>Hanya Area Kritis (<= Stok Minimal)</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary btn-print" style="background:var(--success-color); border:none;"><i class="fa-solid fa-file-excel"></i> Download Xlsx</button>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
