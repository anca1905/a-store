<?php
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
$kode_barang = isset($_GET['kode_barang']) ? $_GET['kode_barang'] : '';
$n_periode = isset($_GET['n_periode']) ? (int)$_GET['n_periode'] : 3;
?>

<div class="dashboard-content-wrapper">
    <div style="margin-bottom: 32px;">
        <h2 style="font-size: 24px; font-weight: 700; color: var(--text-main); margin-bottom: 24px;">Peramalan Stock (Single moving average)</h2>
        
        <!-- Filter Form Mockup Style -->
        <form method="GET" style="display: flex; align-items: center; gap: 24px; background: #FFFFFF; padding: 24px; border-radius: 16px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); margin-bottom: 32px;">
            <input type="hidden" name="page" value="sma">
            
            <div style="display: flex; align-items: center; gap: 12px;">
                <label style="font-size: 14px; font-weight: 600; color: var(--text-main); white-space: nowrap;">Pilih Produk</label>
                <select name="kode_barang" class="form-control" required style="min-width: 240px; padding: 10px 16px; border: 1.5px solid var(--border-color); border-radius: 10px; font-family: inherit; font-size: 14px; outline: none; background-color: #FDFDFD;">
                    <option value="">-- Pilih Produk --</option>
                    <?php
                    $qB = get_query($conn, "SELECT kode_barang, nama_produk FROM tbl_barang ORDER BY nama_produk");
                    while($rB = $qB->fetch_assoc()) {
                        $sel = ($rB['kode_barang'] == $kode_barang) ? 'selected' : '';
                        echo "<option value='{$rB['kode_barang']}' $sel>{$rB['nama_produk']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div style="display: flex; align-items: center; gap: 12px;">
                <label style="font-size: 14px; font-weight: 600; color: var(--text-main); white-space: nowrap;">Periode SMA</label>
                <select name="n_periode" class="form-control" style="min-width: 140px; padding: 10px 16px; border: 1.5px solid var(--border-color); border-radius: 10px; font-family: inherit; font-size: 14px; outline: none; background-color: #FDFDFD;">
                    <option value="3" <?= ($n_periode==3)?'selected':'' ?>>3 Bulan</option>
                    <option value="5" <?= ($n_periode==5)?'selected':'' ?>>5 Bulan</option>
                </select>
            </div>

            <div style="display: flex; align-items: center; gap: 12px;">
                <label style="font-size: 14px; font-weight: 600; color: var(--text-main); white-space: nowrap;">Tahun</label>
                <input type="number" name="tahun" class="form-control" value="<?= $tahun ?>" required style="width: 100px; padding: 10px 16px; border: 1.5px solid var(--border-color); border-radius: 10px; font-family: inherit; font-size: 14px; outline: none; background-color: #FDFDFD;">
            </div>

            <button type="submit" class="btn-primary" style="padding: 10px 24px; border-radius: 10px; border: none; background: var(--primary-color); color: white; font-weight: 700; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 12px rgba(2, 132, 199, 0.2);">
                Hitung
            </button>
        </form>

        <!-- Tabel Kalkulasi Mockup Style -->
        <div style="background: #FFFFFF; border-radius: 16px; border: 1px solid var(--border-color); overflow: hidden; box-shadow: var(--shadow-sm); margin-bottom: 24px;">
            <table class="data-table" style="width: 100%; border-collapse: collapse; text-align: center;">
                <thead style="background: #F9FAFB; border-bottom: 1.5px solid var(--border-color);">
                    <tr>
                        <th style="padding: 16px; font-size: 14px; font-weight: 700; color: var(--text-main); border-right: 1.5px solid var(--border-color);">Bulan</th>
                        <th style="padding: 16px; font-size: 14px; font-weight: 700; color: var(--text-main); border-right: 1.5px solid var(--border-color);">Penjualan Aktual (Pcs)</th>
                        <th style="padding: 16px; font-size: 14px; font-weight: 700; color: var(--text-main); border-right: 1.5px solid var(--border-color);">Peramalan (SMA <?= $n_periode ?>)</th>
                        <th style="padding: 16px; font-size: 14px; font-weight: 700; color: var(--text-main); border-right: 1.5px solid var(--border-color);">Error Absolut</th>
                        <th style="padding: 16px; font-size: 14px; font-weight: 700; color: var(--text-main);">MAPE (%)</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if($kode_barang != ''):
                    $arrAktual = array_fill(1, 12, 0);
                    $namaBulan = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agust','Sep','Oct','Nov','Des'];

                    $sqlA = "SELECT MONTH(p.tanggal_waktu) as bln, SUM(d.qty) as tq
                             FROM tbl_detail_penjualan d
                             JOIN tbl_penjualan p ON d.id_penjualan = p.id_penjualan
                             WHERE d.kode_barang='$kode_barang' AND YEAR(p.tanggal_waktu)='$tahun'
                             GROUP BY bln";
                    $qA = get_query($conn, $sqlA);
                    while($rA = $qA->fetch_assoc()) $arrAktual[$rA['bln']] = $rA['tq'];

                    $sumMAPE = 0;
                    $countMAPE = 0;
                    $prediksi_mendatang = 0;

                    for($i = 1; $i <= 12; $i++) {
                        $aktual   = $arrAktual[$i];
                        $ft_disp  = "-";
                        $error_disp = "-";
                        $mape_disp  = "-";

                        if($i > $n_periode) {
                            $sum = 0;
                            for($x = 1; $x <= $n_periode; $x++) {
                                $idx = $i - $x;
                                $sum += $arrAktual[$idx];
                            }
                            $ft_val  = round($sum / $n_periode, 2);
                            $ft_disp = $ft_val;

                            if($aktual > 0) {
                                $err_val  = abs($aktual - $ft_val);
                                $error_disp = $err_val;
                                $mape_val  = round(($err_val / $aktual) * 100, 2);
                                $sumMAPE  += $mape_val;
                                $countMAPE++;
                                $mape_disp  = $mape_val."%";
                            } else {
                                if($prediksi_mendatang == 0) {
                                    $prediksi_mendatang = $ft_val;
                                }
                            }
                        }

                        echo "<tr style='border-bottom: 1px solid #F1F5F9;'>
                                <td style='padding: 14px; font-size: 14px; font-weight: 600; border-right: 1.5px solid var(--border-color); background: #FDFDFD;'>{$namaBulan[$i]}</td>
                                <td style='padding: 14px; font-size: 14px; border-right: 1.5px solid var(--border-color);'>".($aktual > 0 ? $aktual : '-')."</td>
                                <td style='padding: 14px; font-size: 14px; border-right: 1.5px solid var(--border-color);'>$ft_disp</td>
                                <td style='padding: 14px; font-size: 14px; border-right: 1.5px solid var(--border-color);'>$error_disp</td>
                                <td style='padding: 14px; font-size: 14px;'>$mape_disp</td>
                              </tr>";
                    }

                    if($prediksi_mendatang == 0) {
                        $sum = 0;
                        for($x = 0; $x < $n_periode; $x++) {
                            $idx = 12 - $x;
                            $sum += $arrAktual[$idx];
                        }
                        $prediksi_mendatang = round($sum / $n_periode, 2);
                    }

                    $rata_mape = ($countMAPE > 0) ? round($sumMAPE / $countMAPE, 2) : 0;
                    
                    if($rata_mape <= 10) {
                        $kualitas = "Sangat Baik";
                    } elseif($rata_mape <= 25) {
                        $kualitas = "Baik";
                    } else {
                        $kualitas = "Perlu Perhatian";
                    }
                else:
                    echo "<tr><td colspan='5' style='padding: 60px; color: var(--text-muted);'>Silakan pilih produk dan klik <strong>Hitung</strong></td></tr>";
                endif;
                ?>
                </tbody>
            </table>
        </div>

        <?php if($kode_barang != ''): ?>
            <!-- MAPE Footer Box Mockup Style -->
            <div style="background: #FFFFFF; border: 1.5px solid var(--border-color); border-radius: 12px; padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: var(--shadow-sm);">
                <span style="font-size: 16px; font-weight: 700; color: var(--text-main);">Rata-rata MAPE</span>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 18px; font-weight: 800; color: var(--text-main);"><?= $rata_mape ?>%</span>
                    <span style="font-size: 16px; font-weight: 600; color: var(--text-muted);">(<?= $kualitas ?>)</span>
                </div>
            </div>
            
            <!-- Extra Info: Forecast for Next Month -->
            <div style="margin-top: 24px; padding: 20px; background: var(--primary-light); border-radius: 12px; display: flex; align-items: center; justify-content: center; gap: 12px; border: 1.5px solid var(--primary-color);">
                <i class="fa-solid fa-lightbulb" style="color: var(--primary-color);"></i>
                <span style="font-size: 15px; font-weight: 600; color: var(--primary-dark);">Rekomendasi stok untuk bulan selanjutnya: <strong style="font-size: 18px;"><?= $prediksi_mendatang ?> Pcs</strong></span>
            </div>
        <?php endif; ?>
    </div>
</div>

