<?php
// Halaman: Stok Barang (Kasir) — view-only
$qStok = get_query($conn, "SELECT kode_barang, nama_produk, kategori, stok_aktual, stok_min, satuan FROM tbl_barang ORDER BY stok_aktual ASC");
?>

<div class="dashboard-content-wrapper">
    <div class="chart-card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th>Stok</th>
                        <th>Min. Stok</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($qStok->num_rows > 0):
                        while ($row = $qStok->fetch_assoc()):
                            $kritis = $row['stok_aktual'] <= $row['stok_min'];
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($row['kode_barang']) ?></strong></td>
                        <td><?= htmlspecialchars($row['nama_produk']) ?></td>
                        <td><?= htmlspecialchars($row['kategori'] ?? '-') ?></td>
                        <td style="font-weight:700; <?= $kritis ? 'color:var(--danger-color);' : '' ?>">
                            <?= $row['stok_aktual'] ?> <?= htmlspecialchars($row['satuan'] ?? 'Pcs') ?>
                        </td>
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
                    <tr><td colspan="6" style="text-align:center; padding:40px; color:var(--text-muted);">Tidak ada data stok.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
