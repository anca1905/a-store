<?php
// Halaman: Data Barang Kasir (view-only)
$cari   = $conn->real_escape_string($_GET['cari'] ?? '');
$sql    = "SELECT kode_barang, nama_produk, kategori, harga_jual, stok_aktual, foto_barang FROM tbl_barang";
if ($cari !== '') {
    $sql .= " WHERE kode_barang LIKE '%$cari%' OR nama_produk LIKE '%$cari%'";
}
$sql   .= " ORDER BY nama_produk ASC";
$query  = get_query($conn, $sql);
?>

<div class="dashboard-content-wrapper">

    <div class="chart-card">
        <!-- Search bar -->
        <form method="GET" style="display:flex; gap:8px; margin-bottom:20px; align-items:center;">
            <input type="hidden" name="page" value="data_barang_kasir">
            <div class="search-bar" style="background:#fff; border:1px solid var(--border-color); flex:1; margin-bottom:0; max-width:340px;">
                <i class="fa-solid fa-barcode"></i>
                <input type="text" name="cari" placeholder="Scan/ cari kode atau nama barang..." value="<?= htmlspecialchars($cari) ?>">
            </div>
            <button type="submit" class="btn btn-primary" style="padding:9px 16px;"><i class="fa-solid fa-search"></i></button>
        </form>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Gambar</th>
                        <th>Nama Barang</th>
                        <th>Harga</th>
                        <th>Stok</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($query->num_rows > 0):
                        while ($row = $query->fetch_assoc()):
                            $stokStyle = ($row['stok_aktual'] <= 0) ? 'color:var(--danger-color); font-weight:700;' : '';
                    ?>
                    <tr>
                        <td>
                            <?php if ($row['foto_barang'] && file_exists('assets/img/barang/' . $row['foto_barang'])): ?>
                                <img src="assets/img/barang/<?= htmlspecialchars($row['foto_barang']) ?>"
                                     style="width:48px; height:48px; object-fit:cover; border-radius:8px; border:1px solid var(--border-color);">
                            <?php else: ?>
                                <div style="width:48px; height:48px; background:var(--bg-body); border-radius:8px; display:flex; align-items:center; justify-content:center; color:var(--text-light); border:1px solid var(--border-color);">
                                    <i class="fa-solid fa-image"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($row['nama_produk']) ?></strong>
                            <div style="font-size:12px; color:var(--text-muted);"><?= htmlspecialchars($row['kode_barang']) ?></div>
                        </td>
                        <td style="font-weight:600; color:var(--primary-color);">Rp <?= number_format($row['harga_jual'] ?? 0, 0, ',', '.') ?></td>
                        <td style="<?= $stokStyle ?>"><?= $row['stok_aktual'] ?> Pcs</td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="4" style="text-align:center; padding:40px; color:var(--text-muted);">Tidak ada barang ditemukan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
