<?php
// Halaman: Data Barang Kasir (view-only)
$cari      = trim($_GET['cari'] ?? '');
$filterKat = trim($_GET['kategori'] ?? '');

$where = [];
if ($cari !== '') {
    $cEsc = $conn->real_escape_string($cari);
    $where[] = "(kode_barang LIKE '%$cEsc%' OR nama_produk LIKE '%$cEsc%' OR kategori LIKE '%$cEsc%')";
}
if ($filterKat !== '' && $filterKat !== 'all') {
    $kEsc = $conn->real_escape_string($filterKat);
    $where[] = "kategori = '$kEsc'";
}

$sql = "SELECT kode_barang, nama_produk, kategori, harga_jual, stok_aktual, foto_barang FROM tbl_barang";
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY nama_produk ASC";
$query = get_query($conn, $sql);

// Daftar kategori
$qKat = get_query($conn, "SELECT DISTINCT kategori FROM tbl_barang WHERE kategori IS NOT NULL AND kategori != '' ORDER BY kategori ASC");
$listKategori = [];
while ($rk = $qKat->fetch_assoc()) {
    if (!empty($rk['kategori'])) $listKategori[] = $rk['kategori'];
}
?>

<div class="dashboard-content-wrapper">

    <div class="chart-card">
        <!-- Search & Filter bar -->
        <form method="GET" id="formFilterKasir" style="margin-bottom:20px;">
            <input type="hidden" name="page" value="data_barang_kasir">
            
            <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                <div class="search-bar" style="background:#fff; border:1px solid var(--border-color); flex:1; min-width:240px; margin-bottom:0;">
                    <i class="fa-solid fa-search"></i>
                    <input type="text" name="cari" id="inputCariKasir" placeholder="Scan / cari kode atau nama barang..." value="<?= htmlspecialchars($cari) ?>" autocomplete="off">
                    <?php if ($cari !== ''): ?>
                    <button type="button" onclick="document.getElementById('inputCariKasir').value=''; document.getElementById('formFilterKasir').submit();" style="border:none; background:transparent; cursor:pointer; color:#94a3b8; padding:0 6px;" title="Hapus pencarian">
                        <i class="fa-solid fa-circle-xmark"></i>
                    </button>
                    <?php endif; ?>
                </div>

                <div style="min-width:180px;">
                    <select name="kategori" id="selectKategoriKasir" class="form-control" onchange="this.form.submit()" style="font-size:13px; font-weight:600; padding:8px 12px; height:auto; cursor:pointer; border-radius:8px;">
                        <option value="">Kategori</option>
                        <?php foreach ($listKategori as $kat): ?>
                        <option value="<?= htmlspecialchars($kat) ?>" <?= ($filterKat === $kat) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($kat) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if ($cari !== '' || ($filterKat !== '' && $filterKat !== 'all')): ?>
                <a href="?page=data_barang_kasir" class="btn btn-outline" style="padding:8px 14px; color:#ef4444; border-color:#fca5a5;" title="Reset filter">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                </a>
                <?php endif; ?>
            </div>

            <?php if ($cari !== '' || ($filterKat !== '' && $filterKat !== 'all')): ?>
            <div style="margin-top:12px; font-size:12.5px; color:var(--text-muted);">
                Menampilkan <strong style="color:var(--text-main);"><?= $query ? $query->num_rows : 0 ?></strong> barang<?= ($filterKat !== '') ? ' kategori <strong style="color:var(--primary-color);">' . htmlspecialchars($filterKat) . '</strong>' : '' ?>
            </div>
            <?php endif; ?>
        </form>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:60px;">Gambar</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
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
                                     style="width:44px; height:44px; object-fit:cover; border-radius:8px; border:1px solid var(--border-color);">
                            <?php else: ?>
                                <div style="width:44px; height:44px; background:var(--bg-body); border-radius:8px; display:flex; align-items:center; justify-content:center; color:var(--text-light); border:1px solid var(--border-color);">
                                    <i class="fa-solid fa-image"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($row['nama_produk']) ?></strong>
                            <div style="font-size:12px; color:var(--text-muted);"><?= htmlspecialchars($row['kode_barang']) ?></div>
                        </td>
                        <td><span style="font-size:12px; font-weight:600; background:var(--bg-body); padding:2px 8px; border-radius:10px;"><?= htmlspecialchars($row['kategori'] ?? '-') ?></span></td>
                        <td style="font-weight:600; color:var(--primary-color);">Rp <?= number_format($row['harga_jual'] ?? 0, 0, ',', '.') ?></td>
                        <td style="<?= $stokStyle ?>"><?= $row['stok_aktual'] ?> Pcs</td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="5" style="text-align:center; padding:40px; color:var(--text-muted);">Tidak ada barang ditemukan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputCari = document.getElementById('inputCariKasir');
    if (inputCari) {
        inputCari.addEventListener('input', function() {
            const val = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('.data-table tbody tr');
            rows.forEach(row => {
                if (row.cells.length <= 1) return;
                const text = row.innerText.toLowerCase();
                row.style.display = (!val || text.includes(val)) ? '' : 'none';
            });
        });
    }
});
</script>
