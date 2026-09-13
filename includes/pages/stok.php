<?php
// Halaman: Manajemen Stok (Pimpinan) — overview semua barang + stok kritis
$cari = $conn->real_escape_string($_GET['cari'] ?? '');
$filter = $_GET['filter'] ?? 'semua'; // semua | kritis

$sql = "SELECT * FROM tbl_barang";
$kondisi = [];
if ($cari !== '') {
    $kondisi[] = "(kode_barang LIKE '%$cari%' OR nama_produk LIKE '%$cari%')";
}
if ($filter === 'kritis') {
    $kondisi[] = "stok_aktual <= stok_min";
}
if (!empty($kondisi)) {
    $sql .= " WHERE " . implode(' AND ', $kondisi);
}
$sql .= " ORDER BY stok_aktual ASC";
$query = get_query($conn, $sql);

// Hitung ringkasan
$qSummary = get_query($conn, "SELECT COUNT(*) as total, SUM(IF(stok_aktual <= stok_min, 1, 0)) as kritis FROM tbl_barang");
$summary  = $qSummary->fetch_assoc();
?>

<div class="dashboard-content-wrapper">

    <!-- Summary cards -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(160px, 1fr)); gap:16px; margin-bottom:24px;">
        <div class="chart-card" style="text-align:center; padding:20px;">
            <div style="font-size:28px; font-weight:800; color:var(--text-main);"><?= $summary['total'] ?></div>
            <div style="font-size:13px; color:var(--text-muted); margin-top:4px;">Total Produk</div>
        </div>
        <div class="chart-card" style="text-align:center; padding:20px;">
            <div style="font-size:28px; font-weight:800; color:var(--danger-color);"><?= $summary['kritis'] ?></div>
            <div style="font-size:13px; color:var(--text-muted); margin-top:4px;">Stok Kritis</div>
        </div>
        <div class="chart-card" style="text-align:center; padding:20px;">
            <div style="font-size:28px; font-weight:800; color:var(--success-color);"><?= $summary['total'] - $summary['kritis'] ?></div>
            <div style="font-size:13px; color:var(--text-muted); margin-top:4px;">Stok Aman</div>
        </div>
    </div>

    <div class="chart-card">
        <!-- Filter -->
        <form method="GET" style="display:flex; gap:8px; margin-bottom:20px; flex-wrap:wrap; align-items:center;">
            <input type="hidden" name="page" value="stok">
            <div class="search-bar" style="background:#fff; border:1px solid var(--border-color); width:240px; margin-bottom:0;">
                <i class="fa-solid fa-search"></i>
                <input type="text" name="cari" placeholder="Cari barang..." value="<?= htmlspecialchars($cari) ?>">
            </div>
            <select name="filter" class="form-control" style="width:160px;">
                <option value="semua" <?= $filter==='semua' ? 'selected':'' ?>>Semua</option>
                <option value="kritis" <?= $filter==='kritis' ? 'selected':'' ?>>Hanya Kritis</option>
            </select>
            <button type="submit" class="btn btn-primary" style="padding:9px 14px;">Filter</button>
        </form>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th>Harga Jual</th>
                        <th>Stok Aktual</th>
                        <th>Min. Stok</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($query->num_rows > 0):
                        while ($row = $query->fetch_assoc()):
                            $kritis = $row['stok_aktual'] <= $row['stok_min'];
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($row['kode_barang']) ?></strong></td>
                        <td><?= htmlspecialchars($row['nama_produk']) ?></td>
                        <td><?= htmlspecialchars($row['kategori'] ?? '-') ?></td>
                        <td>Rp <?= number_format($row['harga_jual'] ?? 0, 0, ',', '.') ?></td>
                        <td style="font-weight:700; <?= $kritis ? 'color:var(--danger-color);' : '' ?>"><?= $row['stok_aktual'] ?></td>
                        <td><?= $row['stok_min'] ?></td>
                        <td>
                            <?php if ($kritis): ?>
                                <span class="status status-danger"><i class="fa-solid fa-triangle-exclamation"></i> Kritis</span>
                            <?php else: ?>
                                <span class="status status-good">Aman</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="7" style="text-align:center; padding:40px; color:var(--text-muted);">Tidak ada data.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
