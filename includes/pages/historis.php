<?php
$tahunFilter = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
$bulanFilter = isset($_GET['bulan']) ? $_GET['bulan'] : '';
$kbFilter = isset($_GET['kode_barang']) ? $_GET['kode_barang'] : '';
$katFilter = isset($_GET['kategori']) ? $_GET['kategori'] : '';
?>

<div class="dashboard-content">

    <div>
        <a href="?page=dashboard" class="btn-back-dashboard">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>

    <div>
        <h2 style="font-size:24px; font-weight:700; margin-bottom:4px;">Data Penjualan Historis</h2>
        <p style="color:var(--text-muted); font-size:14px;">Rekapitulasi total produk yang terjual secara detail per hari (tanggal) dari database sebagai bahan analisis.</p>
    </div>

    <div class="chart-card">
        <form action="" method="GET" style="display:flex; gap:16px; margin-bottom:24px; align-items:flex-end; flex-wrap:wrap;">
            <input type="hidden" name="page" value="historis">
            <div style="flex:1; min-width:100px;">
                <label style="display:block;margin-bottom:6px;font-size:13px;font-weight:500;color:var(--text-muted);">Tahun</label>
                <select name="tahun" class="form-control" style="width:100%;padding:10px 14px;border:1px solid var(--border-color);border-radius:6px;font-family:inherit;font-size:14px;outline:none;">
                    <?php
                    $qY = get_query($conn, "SELECT DISTINCT YEAR(tanggal_waktu) as thn FROM tbl_penjualan ORDER BY thn DESC");
                    if($qY->num_rows > 0) { while($rY = $qY->fetch_assoc()) { $sel = ($rY['thn'] == $tahunFilter) ? 'selected' : ''; echo "<option value='{$rY['thn']}' $sel>{$rY['thn']}</option>"; } }
                    else { echo "<option value='".date('Y')."'>".date('Y')."</option>"; }
                    ?>
                </select>
            </div>
            <div style="flex:1; min-width:120px;">
                <label style="display:block;margin-bottom:6px;font-size:13px;font-weight:500;color:var(--text-muted);">Bulan</label>
                <select name="bulan" class="form-control" style="width:100%;padding:10px 14px;border:1px solid var(--border-color);border-radius:6px;font-family:inherit;font-size:14px;outline:none;">
                    <option value="">Semua Bulan</option>
                    <?php
                    $namaBulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                    for($i=1; $i<=12; $i++) {
                        $sel = ($i == $bulanFilter) ? 'selected' : '';
                        echo "<option value='$i' $sel>{$namaBulan[$i]}</option>";
                    }
                    ?>
                </select>
            </div>
            <div style="flex:2; min-width:150px;">
                <label style="display:block;margin-bottom:6px;font-size:13px;font-weight:500;color:var(--text-muted);">Pilih Kategori (Opsional)</label>
                <select name="kategori" class="form-control" style="width:100%;padding:10px 14px;border:1px solid var(--border-color);border-radius:6px;font-family:inherit;font-size:14px;outline:none;">
                    <option value="">-- Semua Kategori --</option>
                    <?php
                    $qKat = get_query($conn, "SELECT nama_kategori FROM tbl_kategori ORDER BY nama_kategori ASC");
                    while($rKat = $qKat->fetch_assoc()) { 
                        $sel = ($rKat['nama_kategori'] == $katFilter) ? 'selected' : ''; 
                        echo "<option value='{$rKat['nama_kategori']}' $sel>{$rKat['nama_kategori']}</option>"; 
                    }
                    ?>
                </select>
            </div>
            <div style="flex:2; min-width:150px;">
                <label style="display:block;margin-bottom:6px;font-size:13px;font-weight:500;color:var(--text-muted);">Pilih Barang (Opsional)</label>
                <select name="kode_barang" class="form-control" style="width:100%;padding:10px 14px;border:1px solid var(--border-color);border-radius:6px;font-family:inherit;font-size:14px;outline:none;">
                    <option value="">-- Semua Barang --</option>
                    <?php
                    $qB = get_query($conn, "SELECT kode_barang, nama_produk FROM tbl_barang ORDER BY nama_produk");
                    while($rB = $qB->fetch_assoc()) { $sel = ($rB['kode_barang'] == $kbFilter) ? 'selected' : ''; echo "<option value='{$rB['kode_barang']}' $sel>{$rB['kode_barang']} - {$rB['nama_produk']}</option>"; }
                    ?>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-search"></i> Tampilkan</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr><th>Kode Barang</th><th>Nama Produk</th><th>Tanggal</th><th>Total Terjual (Qty)</th></tr>
                </thead>
                <tbody>
                <?php
                $kondisi = "YEAR(p.tanggal_waktu) = '$tahunFilter'";
                if($bulanFilter != '') $kondisi .= " AND MONTH(p.tanggal_waktu) = '$bulanFilter'";
                if($kbFilter != '') $kondisi .= " AND d.kode_barang = '$kbFilter'";
                if($katFilter != '') $kondisi .= " AND b.kategori = '$katFilter'";
                
                $sqlHist = "SELECT d.kode_barang, b.nama_produk, DATE(p.tanggal_waktu) as tgl, SUM(d.qty) as total_qty FROM tbl_detail_penjualan d JOIN tbl_penjualan p ON d.id_penjualan=p.id_penjualan JOIN tbl_barang b ON d.kode_barang=b.kode_barang WHERE $kondisi GROUP BY d.kode_barang, DATE(p.tanggal_waktu) ORDER BY tgl ASC";
                $qHist = get_query($conn, $sqlHist);
                
                if($qHist->num_rows > 0): while($row = $qHist->fetch_assoc()): ?>
                <tr>
                    <td><strong><?= $row['kode_barang'] ?></strong></td>
                    <td><?= $row['nama_produk'] ?></td>
                    <td><?= date('d/m/Y', strtotime($row['tgl'])) ?></td>
                    <td><strong style="color:var(--primary-color)"><?= $row['total_qty'] ?> Unit</strong></td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="4" style="text-align:center;padding:40px;color:var(--text-muted);">Tidak ada data historis di rentang ini.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
