<?php
// Halaman: Riwayat Stok (Pimpinan) — historis stok penjualan per bulan
$tahunFilter = $_GET['tahun'] ?? date('Y');
$kbFilter    = $conn->real_escape_string($_GET['kode_barang'] ?? '');
$tahunFilter = $conn->real_escape_string($tahunFilter);
?>

<div class="dashboard-content-wrapper">

    <div class="chart-card">
        <form action="" method="GET" style="display:flex; gap:16px; margin-bottom:24px; align-items:flex-end; flex-wrap:wrap;">
            <input type="hidden" name="page" value="riwayat_stok">
            <div>
                <label class="form-label">Tahun</label>
                <select name="tahun" class="form-control" style="width:120px;">
                    <?php
                    $qY = get_query($conn, "SELECT DISTINCT YEAR(tanggal_waktu) as thn FROM tbl_penjualan ORDER BY thn DESC");
                    if ($qY->num_rows > 0) {
                        while ($rY = $qY->fetch_assoc()) {
                            $sel = ($rY['thn'] == $tahunFilter) ? 'selected' : '';
                            echo "<option value='{$rY['thn']}' $sel>{$rY['thn']}</option>";
                        }
                    } else {
                        echo "<option value='" . date('Y') . "'>" . date('Y') . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div>
                <label class="form-label">Pilih Barang (Opsional)</label>
                <select name="kode_barang" class="form-control" style="min-width:220px;">
                    <option value="">-- Semua Barang --</option>
                    <?php
                    $qB = get_query($conn, "SELECT kode_barang, nama_produk FROM tbl_barang ORDER BY nama_produk");
                    while ($rB = $qB->fetch_assoc()) {
                        $sel = ($rB['kode_barang'] === $kbFilter) ? 'selected' : '';
                        echo "<option value='{$rB['kode_barang']}' $sel>{$rB['kode_barang']} — {$rB['nama_produk']}</option>";
                    }
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
                    <tr>
                        <th>Kode Barang</th>
                        <th>Nama Produk</th>
                        <th>Bulan</th>
                        <th>Total Terjual (Qty)</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $namaBulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                $kondisi = "YEAR(p.tanggal_waktu) = '$tahunFilter'";
                if ($kbFilter !== '') $kondisi .= " AND d.kode_barang = '$kbFilter'";

                $sqlHist = "SELECT d.kode_barang, b.nama_produk, MONTH(p.tanggal_waktu) as bln, SUM(d.qty) as total_qty
                            FROM tbl_detail_penjualan d
                            JOIN tbl_penjualan p ON d.id_penjualan = p.id_penjualan
                            JOIN tbl_barang b ON d.kode_barang = b.kode_barang
                            WHERE $kondisi
                            GROUP BY d.kode_barang, MONTH(p.tanggal_waktu)
                            ORDER BY bln ASC";
                $qHist = get_query($conn, $sqlHist);

                if ($qHist->num_rows > 0):
                    while ($row = $qHist->fetch_assoc()):
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($row['kode_barang']) ?></strong></td>
                    <td><?= htmlspecialchars($row['nama_produk']) ?></td>
                    <td><?= $namaBulan[$row['bln']] ?> <?= $tahunFilter ?></td>
                    <td><strong style="color:var(--primary-color);"><?= $row['total_qty'] ?> Unit</strong></td>
                </tr>
                <?php endwhile; else: ?>
                <tr>
                    <td colspan="4" style="text-align:center; padding:40px; color:var(--text-muted);">
                        Tidak ada data historis di rentang ini.
                    </td>
                </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
