<?php
// Halaman: Riwayat Transaksi (Kasir)
$tglDari  = $_GET['tgl_dari']  ?? date('Y-m-01');
$tglSampai = $_GET['tgl_sampai'] ?? date('Y-m-d');
$tglDari  = $conn->real_escape_string($tglDari);
$tglSampai = $conn->real_escape_string($tglSampai);

$qTrx = get_query($conn, "
    SELECT p.id_penjualan, p.no_faktur, p.tanggal_waktu, p.total_item, p.grand_total, p.nominal_bayar, p.kembalian, p.metode_pembayaran
    FROM tbl_penjualan p
    WHERE DATE(p.tanggal_waktu) BETWEEN '$tglDari' AND '$tglSampai'
    ORDER BY p.tanggal_waktu DESC
");

// Ambil detail nama barang per transaksi
$itemsByTrx = [];
$qItems = get_query($conn, "
    SELECT d.id_penjualan, d.kode_barang, COALESCE(b.nama_produk, d.kode_barang) AS nama_produk, d.qty, d.harga_satuan, d.subtotal
    FROM tbl_detail_penjualan d
    JOIN tbl_penjualan p ON d.id_penjualan = p.id_penjualan
    LEFT JOIN tbl_barang b ON d.kode_barang = b.kode_barang
    WHERE DATE(p.tanggal_waktu) BETWEEN '$tglDari' AND '$tglSampai'
    ORDER BY d.id_detail ASC
");
if ($qItems && $qItems->num_rows > 0) {
    while ($it = $qItems->fetch_assoc()) {
        $itemsByTrx[$it['id_penjualan']][] = $it;
    }
}
?>

<div class="dashboard-content-wrapper">

    <!-- Filter -->
    <div class="chart-card" style="margin-bottom:20px;">
        <form method="GET" style="display:flex; gap:16px; flex-wrap:wrap; align-items:flex-end;">
            <input type="hidden" name="page" value="riwayat_transaksi">
            <div>
                <label class="form-label">Dari Tanggal</label>
                <input type="date" name="tgl_dari" class="form-control" value="<?= htmlspecialchars($tglDari) ?>">
            </div>
            <div>
                <label class="form-label">Sampai Tanggal</label>
                <input type="date" name="tgl_sampai" class="form-control" value="<?= htmlspecialchars($tglSampai) ?>">
            </div>
            <div>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-search"></i> Tampilkan</button>
            </div>
        </form>
    </div>

    <!-- Tabel -->
    <div class="chart-card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 155px;">No Faktur</th>
                        <th style="width: 140px;">Tanggal & Waktu</th>
                        <th>Nama Barang</th>
                        <th style="width: 90px; text-align: center;">Total Item</th>
                        <th style="width: 110px;">Metode Bayar</th>
                        <th style="width: 120px; text-align: right;">Grand Total</th>
                        <th style="width: 110px; text-align: right;">Dibayar</th>
                        <th style="width: 110px; text-align: right;">Kembalian</th>
                        <th style="width: 60px; text-align: center;">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $totalPcsAll = 0;
                    $totalOmsetAll = 0;
                    if ($qTrx->num_rows > 0):
                        while ($row = $qTrx->fetch_assoc()):
                            $totalPcsAll += (int)$row['total_item'];
                            $totalOmsetAll += (int)$row['grand_total'];
                            $idTrx = $row['id_penjualan'];
                            $detailItems = $itemsByTrx[$idTrx] ?? [];
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($row['no_faktur']) ?></strong></td>
                        <td><?= date('d M Y H:i', strtotime($row['tanggal_waktu'])) ?></td>
                        <td>
                            <?php if (!empty($detailItems)): ?>
                                <?php if (count($detailItems) === 1): 
                                    $it = $detailItems[0];
                                ?>
                                    <div>
                                        <span style="font-weight: 600;"><?= htmlspecialchars($it['nama_produk']) ?></span>
                                        <?php if ($it['qty'] > 1): ?>
                                            <span style="font-size: 12px; color: var(--text-muted); font-weight: normal;">(<?= (int)$it['qty'] ?> pcs)</span>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <ul style="margin: 0; padding-left: 16px; list-style-type: disc;">
                                        <?php foreach ($detailItems as $it): ?>
                                            <li style="margin-bottom: 2px;">
                                                <span style="font-weight: 600;"><?= htmlspecialchars($it['nama_produk']) ?></span>
                                                <span style="font-size: 12px; color: var(--text-muted); font-weight: normal;">(<?= (int)$it['qty'] ?> pcs)</span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="color: var(--text-muted); font-style: italic;">-</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: center;"><?= $row['total_item'] ?> pcs</td>
                        <td><?= htmlspecialchars($row['metode_pembayaran'] ?? 'Cash') ?></td>
                        <td style="font-weight:700; color:var(--primary-color); text-align: right;">Rp <?= number_format($row['grand_total'], 0, ',', '.') ?></td>
                        <td style="text-align: right;">Rp <?= number_format($row['nominal_bayar'], 0, ',', '.') ?></td>
                        <td style="text-align: right;">Rp <?= number_format($row['kembalian'], 0, ',', '.') ?></td>
                        <td style="text-align: center;">
                            <button class="btn-action-icon" style="background:var(--primary-color);"
                                    onclick="lihatDetail('<?= htmlspecialchars($row['no_faktur']) ?>')">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <tfoot>
                        <tr style="background:#f8fafc; font-weight:700; border-top:2px solid var(--border-color);">
                            <td colspan="3" style="text-align:right; font-size:14px; text-transform:uppercase;">TOTAL KESELURUHAN:</td>
                            <td style="font-size:15px; color:var(--primary-color); text-align: center;"><?= number_format($totalPcsAll, 0, ',', '.') ?> pcs</td>
                            <td></td>
                            <td style="font-size:15px; color:var(--primary-color); text-align: right;">Rp <?= number_format($totalOmsetAll, 0, ',', '.') ?></td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                    <?php else: ?>
                    <tr><td colspan="9" style="text-align:center; padding:40px; color:var(--text-muted);">Tidak ada transaksi di rentang ini.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Detail Transaksi -->
<div id="modalDetail" class="modal">
    <div class="modal-content" style="max-width:560px;">
        <div class="modal-header">
            <h3 id="modalDetailTitle">Detail Transaksi</h3>
            <button class="close-btn" onclick="closeModal('modalDetail')"><i class="fa-solid fa-times"></i></button>
        </div>
        <div id="modalDetailBody" style="min-height:100px; display:flex; align-items:center; justify-content:center;">
            <i class="fa-solid fa-spinner fa-spin"></i>
        </div>
    </div>
</div>

<script>
function lihatDetail(noFaktur) {
    document.getElementById('modalDetailTitle').textContent = 'Detail — ' + noFaktur;
    document.getElementById('modalDetailBody').innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
    openModal('modalDetail');

    fetch('?page=riwayat_transaksi&ajax_detail=' + encodeURIComponent(noFaktur))
        .then(r => r.text())
        .then(html => { document.getElementById('modalDetailBody').innerHTML = html; });
}
</script>
