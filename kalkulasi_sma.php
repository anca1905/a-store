<?php
include 'config.php';
include 'cek_sesi.php';
$pageTitle = "Kalkulasi Rekomendasi Restock (SMA)";

$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
$kode_barang = isset($_GET['kode_barang']) ? $_GET['kode_barang'] : '';
$n_periode = isset($_GET['n_periode']) ? (int)$_GET['n_periode'] : 3;

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
        .formula-box { background: var(--primary-light); border: 1px dashed var(--primary-color); padding: 20px; border-radius: var(--border-radius-md); margin-bottom: 24px; text-align: center; }
        .formula-text { font-size: 18px; font-weight: 600; color: var(--primary-dark); font-family: monospace; }
        .filter-row { display: flex; gap: 16px; margin-bottom: 24px; align-items: flex-end; flex-wrap:wrap;}
        .form-group { flex: 1; min-width: 200px;}
        .form-label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 500; color: var(--text-muted); }
        .form-control { width: 100%; padding: 10px 14px; border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); font-family: inherit; font-size: 14px; outline: none; }
        .sma-result { color: var(--warning-color); font-weight: bold; background: var(--warning-light); padding: 4px 8px; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="layout-wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <div class="main-content">
            <?php include 'includes/topbar.php'; ?>

            <main class="dashboard-content">
                <div class="page-header">
                    <h2>Kalkulasi Peramalan (Single Moving Average)</h2>
                    <p>Rumus ini menggunakan data penjualan historis (aktual di database) untuk menemukan estimasi penjualan bulan selanjutnya.</p>
                </div>

                <div class="formula-box">
                    <p style="margin-bottom: 8px; color: var(--text-main);">Rumus SMA (Periodik n=<?= $n_periode ?>):</p>
                    <div class="formula-text">Ft = ( A(t-1) + A(t-2) + ... + A(t-<?= $n_periode ?>) ) / <?= $n_periode ?></div>
                </div>

                <div class="chart-card">
                    <form method="GET" class="filter-row">
                        <div class="form-group">
                            <label class="form-label">Tahun Data</label>
                            <input type="number" name="tahun" class="form-control" value="<?= $tahun ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Pilih Barang</label>
                            <select name="kode_barang" class="form-control" required>
                                <option value="">-- Pilih --</option>
                                <?php
                                $qB = get_query($conn, "SELECT kode_barang, nama_produk FROM tbl_barang ORDER BY nama_produk");
                                while($rB = $qB->fetch_assoc()) {
                                    $sel = ($rB['kode_barang'] == $kode_barang) ? 'selected' : '';
                                    echo "<option value='{$rB['kode_barang']}' $sel>{$rB['kode_barang']} - {$rB['nama_produk']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Periode Waktu (N)</label>
                            <select name="n_periode" class="form-control">
                                <option value="3" <?= ($n_periode==3) ? 'selected' : '' ?>>Sebanyak 3 Bulan (n=3)</option>
                                <option value="5" <?= ($n_periode==5) ? 'selected' : '' ?>>Sebanyak 5 Bulan (n=5)</option>
                            </select>
                        </div>
                        <div class="form-group" style="flex:0">
                            <button type="submit" class="btn btn-primary" style="height:40px;"><i class="fa-solid fa-cogs"></i> Hitung!</button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Periode Bulan (t)</th>
                                    <th>Data Aktual (At)</th>
                                    <th>Teks Perhitungan ∑ / n</th>
                                    <th>Hasil Peramalan (Ft)</th>
                                    <th>Absolut Error (|At - Ft|)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if($kode_barang != ''):
                                    // Susun 12 bulan array default 0
                                    $arrAktual = array_fill(1, 12, 0);
                                    $namaBulan = ['', 'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                                    
                                    // Tarik data yg ada
                                    $sqlA = "SELECT MONTH(p.tanggal_waktu) as bln, SUM(d.qty) as tq 
                                            FROM tbl_detail_penjualan d 
                                            JOIN tbl_penjualan p ON d.id_penjualan = p.id_penjualan 
                                            WHERE d.kode_barang='$kode_barang' AND YEAR(p.tanggal_waktu)='$tahun' 
                                            GROUP BY bln";
                                    $qA = get_query($conn, $sqlA);
                                    while($rA = $qA->fetch_assoc()) {
                                        $arrAktual[$rA['bln']] = $rA['tq'];
                                    }

                                    $prediksi_mendatang = 0;
                                    $rumus_mendatang = "";
                                    
                                    // Looping proses perhitungan SMA
                                    for($i = 1; $i <= 12; $i++) {
                                        $aktual = $arrAktual[$i];
                                        $ft = "-";
                                        $strCalc = "-";
                                        $error = "-";

                                        // Apakah periode ke-$i sudah bisa dihitung dari mundur $n_periode bulan?
                                        if($i > $n_periode) {
                                            $sum = 0;
                                            $arrStr = [];
                                            for($x = 1; $x <= $n_periode; $x++) {
                                                $idx_mundur = $i - $x;
                                                $sum += $arrAktual[$idx_mundur];
                                                $arrStr[] = $arrAktual[$idx_mundur];
                                            }
                                            $ft_val = round($sum / $n_periode, 2);
                                            $ft = "<span class='sma-result'>$ft_val</span>";
                                            $strCalc = "<span style='color:var(--text-muted)'>(".implode(' + ', $arrStr).") / $n_periode</span>";
                                            
                                            if($aktual > 0) { // Jika bulan tsb ada Aktual, hitung MAD (Error)
                                                $err_val = abs($aktual - $ft_val);
                                                $error = "<span style='color:red'>{$err_val}</span>";
                                            } else {
                                                // Kalau gak ada aktual, bulan ini adalah Prediksi Kosong / Bulan Depan
                                                if($prediksi_mendatang == 0) {
                                                    $prediksi_mendatang = $ft_val;
                                                    $rumus_mendatang = $strCalc;
                                                }
                                            }
                                        }
                                        
                                        echo "<tr>
                                                <td>Bulan {$i} ({$namaBulan[$i]})</td>
                                                <td><strong>" . ($aktual>0?$aktual:'-') . "</strong></td>
                                                <td>{$strCalc}</td>
                                                <td>{$ft}</td>
                                                <td>{$error}</td>
                                              </tr>";
                                    }

                                    // Baris Tambahan Bulan ke 13 (Tahun Depan) jika Januari yang diramal
                                    if($prediksi_mendatang == 0) {
                                        // Cari array 1 bulan setelah desember
                                        $sum = 0;
                                        $arrStr = [];
                                        for($x = 0; $x < $n_periode; $x++) {
                                            $idx_mundur = 12 - $x;
                                            $sum += $arrAktual[$idx_mundur];
                                            $arrStr[] = $arrAktual[$idx_mundur];
                                        }
                                        $prediksi_mendatang = round($sum / $n_periode, 2);
                                        $rumus_mendatang = "<span style='color:var(--text-muted)'>(".implode(' + ', $arrStr).") / $n_periode</span>";
                                    }
                                    
                                ?>
                                    <tr style="background:var(--primary-light)">
                                        <td><strong>Prediksi Bulan Pertama Tahun Selanjutnya</strong></td>
                                        <td><strong>?</strong></td>
                                        <td><strong><?= $rumus_mendatang ?></strong></td>
                                        <td><span class="sma-result" style="font-size:18px;"><?= $prediksi_mendatang ?></span></td>
                                        <td>-</td>
                                    </tr>
                                <?php
                                else:
                                    echo "<tr><td colspan='5' style='text-align:center; padding:30px;'>Pilih barang dan klik Hitung untuk melihat algoritma SMA di database.</td></tr>";
                                endif;
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
