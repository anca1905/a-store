<?php
include 'config.php';
include 'cek_sesi.php';
$pageTitle = "Data Penjualan Historis";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - A STORE</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .filter-row { display: flex; gap: 16px; margin-bottom: 24px; align-items: flex-end; }
        .form-group { flex: 1; }
        .form-label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 500; color: var(--text-muted); }
        .form-control { width: 100%; padding: 10px 14px; border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); font-family: inherit; font-size: 14px; outline: none; }
    </style>
</head>
<body>
    <div class="layout-wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <div class="main-content">
            <?php include 'includes/topbar.php'; ?>

            <main class="dashboard-content">
                <div class="page-header">
                    <h2>Data Penjualan Historis</h2>
                    <p>Rekapitulasi total qty produk yang terjual per bulan dari database.</p>
                </div>

                <div class="chart-card">
                    <form action="" method="GET" class="filter-row">
                        <div class="form-group">
                            <label class="form-label">Tahun</label>
                            <select name="tahun" class="form-control">
                                <?php
                                $tahunFilter = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
                                // Cari range tahun dinamis dari tabel penjualan
                                $qY = get_query($conn, "SELECT DISTINCT YEAR(tanggal_waktu) as thn FROM tbl_penjualan ORDER BY thn DESC");
                                if($qY->num_rows > 0) {
                                    while($rY = $qY->fetch_assoc()) {
                                        $sel = ($rY['thn'] == $tahunFilter) ? 'selected' : '';
                                        echo "<option value='{$rY['thn']}' $sel>{$rY['thn']}</option>";
                                    }
                                } else {
                                    echo "<option value='".date('Y')."'>".date('Y')."</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Pilih Barang (Opsional)</label>
                            <select name="kode_barang" class="form-control">
                                <option value="">-- Semua Barang --</option>
                                <?php
                                $kbFilter = isset($_GET['kode_barang']) ? $_GET['kode_barang'] : '';
                                $qB = get_query($conn, "SELECT kode_barang, nama_produk FROM tbl_barang ORDER BY nama_produk");
                                while($rB = $qB->fetch_assoc()) {
                                    $sel = ($rB['kode_barang'] == $kbFilter) ? 'selected' : '';
                                    echo "<option value='{$rB['kode_barang']}' $sel>{$rB['kode_barang']} - {$rB['nama_produk']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group" style="flex:0">
                            <button type="submit" class="btn btn-primary" style="height:40px;"><i class="fa-solid fa-search"></i> Tampilkan</button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Kode Barang</th>
                                    <th>Nama Produk</th>
                                    <th>Bulan</th>
                                    <th>Total Terjual (Qty)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $kondisi = "YEAR(p.tanggal_waktu) = '$tahunFilter'";
                                if($kbFilter != '') {
                                    $kondisi .= " AND d.kode_barang = '$kbFilter'";
                                }

                                $sqlHist = "SELECT d.kode_barang, b.nama_produk, MONTH(p.tanggal_waktu) as bln, SUM(d.qty) as total_qty 
                                            FROM tbl_detail_penjualan d 
                                            JOIN tbl_penjualan p ON d.id_penjualan = p.id_penjualan 
                                            JOIN tbl_barang b ON d.kode_barang = b.kode_barang 
                                            WHERE $kondisi 
                                            GROUP BY d.kode_barang, MONTH(p.tanggal_waktu) 
                                            ORDER BY bln ASC";
                                
                                $qHist = get_query($conn, $sqlHist);
                                $namaBulan = ['', 'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

                                if($qHist->num_rows > 0):
                                    while($row = $qHist->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><strong><?= $row['kode_barang'] ?></strong></td>
                                    <td><?= $row['nama_produk'] ?></td>
                                    <td><?= $namaBulan[$row['bln']] ?> <?= $tahunFilter ?></td>
                                    <td><strong style="color:var(--primary-color)"><?= $row['total_qty'] ?> Unit</strong></td>
                                </tr>
                                <?php 
                                    endwhile;
                                else:
                                ?>
                                <tr><td colspan="4" style="text-align:center; padding:30px; color:var(--text-muted)">Tidak ada data historis di rentang ini.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
