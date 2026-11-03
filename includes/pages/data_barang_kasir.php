<?php
// Halaman: Data Barang Kasir (view-only)
$cari       = trim($_GET['cari'] ?? '');
$filterKat  = trim($_GET['kategori'] ?? '');
$filterStok = trim($_GET['status_stok'] ?? '');

$where = [];
if ($cari !== '') {
    $cEsc = $conn->real_escape_string($cari);
    $where[] = "(kode_barang LIKE '%$cEsc%' OR nama_produk LIKE '%$cEsc%' OR kategori LIKE '%$cEsc%')";
}
if ($filterKat !== '' && $filterKat !== 'all') {
    $kEsc = $conn->real_escape_string($filterKat);
    $where[] = "kategori = '$kEsc'";
}
if ($filterStok === 'kritis') {
    $where[] = "stok_aktual <= stok_min AND stok_aktual > 0";
} elseif ($filterStok === 'habis') {
    $where[] = "stok_aktual <= 0";
} elseif ($filterStok === 'aman') {
    $where[] = "stok_aktual > stok_min";
}

$sql = "SELECT kode_barang, nama_produk, kategori, harga_jual, stok_aktual, foto_barang FROM tbl_barang";
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY nama_produk ASC";
$query = get_query($conn, $sql);

// Daftar kategori dan jumlah barang
$qKatCounts = get_query($conn, "
    SELECT COALESCE(NULLIF(TRIM(kategori), ''), 'Lainnya') AS kat_nama, COUNT(*) AS total
    FROM tbl_barang
    GROUP BY kat_nama
    ORDER BY kat_nama ASC
");
$kategoriCounts = [];
$totalSemuaBarang = 0;
while ($rk = $qKatCounts->fetch_assoc()) {
    $kategoriCounts[] = $rk;
    $totalSemuaBarang += (int)$rk['total'];
}
?>

<div class="dashboard-content-wrapper">

    <div class="chart-card">
        <!-- Search & Filter bar -->
        <form method="GET" id="formFilterKasir" style="display:flex; flex-direction:column; gap:14px; margin-bottom:20px;">
            <input type="hidden" name="page" value="data_barang_kasir">
            
            <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                <div class="search-bar" style="background:#fff; border:1px solid var(--border-color); flex:1; min-width:240px; margin-bottom:0;">
                    <i class="fa-solid fa-search"></i>
                    <input type="text" name="cari" id="inputCariKasir" placeholder="Scan / cari kode atau nama barang..." value="<?= htmlspecialchars($cari) ?>" autocomplete="off">
                    <?php if ($cari !== ''): ?>
                    <button type="button" onclick="document.getElementById('inputCariKasir').value=''; document.getElementById('formFilterKasir').submit();" style="border:none; background:transparent; cursor:pointer; color:#94a3b8; padding:0 6px;">
                        <i class="fa-solid fa-circle-xmark"></i>
                    </button>
                    <?php endif; ?>
                </div>

                <div style="min-width:160px;">
                    <select name="kategori" id="selectKategoriKasir" class="form-control" onchange="this.form.submit()" style="font-size:13px; font-weight:600; padding:8px 12px; height:auto; cursor:pointer; border-radius:8px;">
                        <option value="">Semua Kategori (<?= $totalSemuaBarang ?>)</option>
                        <?php foreach ($kategoriCounts as $kc): ?>
                        <option value="<?= htmlspecialchars($kc['kat_nama']) ?>" <?= ($filterKat === $kc['kat_nama']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($kc['kat_nama']) ?> (<?= $kc['total'] ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="min-width:145px;">
                    <select name="status_stok" id="selectStatusStokKasir" class="form-control" onchange="this.form.submit()" style="font-size:13px; font-weight:600; padding:8px 12px; height:auto; cursor:pointer; border-radius:8px;">
                        <option value="">Semua Stok</option>
                        <option value="aman" <?= $filterStok === 'aman' ? 'selected' : '' ?>>Stok Ada</option>
                        <option value="kritis" <?= $filterStok === 'kritis' ? 'selected' : '' ?>>Stok Kritis</option>
                        <option value="habis" <?= $filterStok === 'habis' ? 'selected' : '' ?>>Stok Habis (0)</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary" style="padding:8px 16px;"><i class="fa-solid fa-search"></i> Cari</button>

                <?php if ($cari !== '' || ($filterKat !== '' && $filterKat !== 'all') || $filterStok !== ''): ?>
                <a href="?page=data_barang_kasir" class="btn btn-outline" style="padding:8px 14px; color:#ef4444; border-color:#fca5a5;">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                </a>
                <?php endif; ?>
            </div>

            <!-- Quick Category Filter Pills -->
            <div class="category-filter-bar">
                <button type="button" class="cat-pill <?= ($filterKat === '' || $filterKat === 'all') ? 'active' : '' ?>" onclick="pilihKategoriKasir('')">
                    <i class="fa-solid fa-border-all" style="font-size:11px; margin-right:6px;"></i> Semua (<?= $totalSemuaBarang ?>)
                </button>
                <?php foreach ($kategoriCounts as $kc): ?>
                <button type="button" class="cat-pill <?= ($filterKat === $kc['kat_nama']) ? 'active' : '' ?>" onclick="pilihKategoriKasir('<?= htmlspecialchars(addslashes($kc['kat_nama'])) ?>')">
                    <?= htmlspecialchars($kc['kat_nama']) ?> <span style="font-size:11px; opacity:0.75; margin-left:4px;">(<?= $kc['total'] ?>)</span>
                </button>
                <?php endforeach; ?>
            </div>
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
function pilihKategoriKasir(kat) {
    const sel = document.getElementById('selectKategoriKasir');
    if (sel) {
        sel.value = kat;
        document.getElementById('formFilterKasir').submit();
    }
}

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

<style>
.category-filter-bar {
    display: flex;
    align-items: center;
    gap: 8px;
    overflow-x: auto;
    padding: 4px 2px 8px 2px;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
}
.category-filter-bar::-webkit-scrollbar { height: 4px; }
.category-filter-bar::-webkit-scrollbar-track { background: transparent; }
.category-filter-bar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
.category-filter-bar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

.cat-pill {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12.5px;
    font-weight: 600;
    white-space: nowrap;
    border: 1px solid var(--border-color);
    background: #fff;
    color: var(--text-main);
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    font-family: inherit;
    user-select: none;
}
.cat-pill:hover {
    border-color: var(--primary-color);
    color: var(--primary-color);
    background: #f8fafc;
    transform: translateY(-1px);
}
.cat-pill.active {
    background: #000;
    color: #fff;
    border-color: #000;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
}
</style>
