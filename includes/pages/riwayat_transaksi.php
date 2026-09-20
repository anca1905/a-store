<?php
// Halaman: Riwayat Transaksi (Kasir)
$tglDari  = $_GET['tgl_dari']  ?? date('Y-m-01');
$tglSampai = $_GET['tgl_sampai'] ?? date('Y-m-d');
$tglDari  = $conn->real_escape_string($tglDari);
$tglSampai = $conn->real_escape_string($tglSampai);

$qTrx = get_query($conn, "
    SELECT p.no_faktur, p.tanggal_waktu, p.total_item, p.grand_total, p.nominal_bayar, p.kembalian, p.metode_pembayaran
    FROM tbl_penjualan p
    WHERE DATE(p.tanggal_waktu) BETWEEN '$tglDari' AND '$tglSampai'
    ORDER BY p.tanggal_waktu DESC
");
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
                        <th>No Faktur</th>
                        <th>Tanggal & Waktu</th>
                        <th>Total Item</th>
                        <th>Metode Bayar</th>
                        <th>Grand Total</th>
                        <th>Dibayar</th>
                        <th>Kembalian</th>
                        <th>Detail</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($qTrx->num_rows > 0):
                        while ($row = $qTrx->fetch_assoc()):
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($row['no_faktur']) ?></strong></td>
                        <td><?= date('d M Y H:i', strtotime($row['tanggal_waktu'])) ?></td>
                        <td><?= $row['total_item'] ?> pcs</td>
                        <td><?= htmlspecialchars($row['metode_pembayaran'] ?? 'Cash') ?></td>
                        <td style="font-weight:700; color:var(--primary-color);">Rp <?= number_format($row['grand_total'], 0, ',', '.') ?></td>
                        <td>Rp <?= number_format($row['nominal_bayar'], 0, ',', '.') ?></td>
                        <td>Rp <?= number_format($row['kembalian'], 0, ',', '.') ?></td>
                        <td>
                            <button class="btn-action-icon" style="background:var(--primary-color);"
                                    onclick="lihatDetail('<?= htmlspecialchars($row['no_faktur']) ?>')">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="8" style="text-align:center; padding:40px; color:var(--text-muted);">Tidak ada transaksi di rentang ini.</td></tr>
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
