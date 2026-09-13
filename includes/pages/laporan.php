<?php
// Halaman: Laporan Penjualan (Pimpinan)
$periode = $_GET['periode'] ?? date('Y-m'); // format: YYYY-MM
$tampil  = isset($_GET['periode']);

// Parse bulan & tahun
[$tahunFilter, $bulanFilter] = explode('-', $periode . '-00');

// KPI & Data (hanya jika form sudah disubmit)
$totalPenjualan  = 0;
$totalTransaksi  = 0;
$qTrx            = null;

if ($tampil) {
    $p = $conn->real_escape_string($periode);
    // KPI
    $qKpi = get_query($conn, "
        SELECT
            COALESCE(SUM(grand_total), 0)  AS total_penjualan,
            COUNT(*)                        AS total_transaksi
        FROM tbl_penjualan
        WHERE DATE_FORMAT(tanggal_waktu, '%Y-%m') = '$p'
    ");
    $kpi = $qKpi->fetch_assoc();
    $totalPenjualan = $kpi['total_penjualan'];
    $totalTransaksi = $kpi['total_transaksi'];

    // Tabel transaksi
    $qTrx = get_query($conn, "
        SELECT id_penjualan, no_faktur, tanggal_waktu, total_item, grand_total
        FROM tbl_penjualan
        WHERE DATE_FORMAT(tanggal_waktu, '%Y-%m') = '$p'
        ORDER BY tanggal_waktu DESC
    ");
}

// Nama bulan Indonesia
$namaBulan = ['','Januari','Februari','Maret','April','Mei','Juni',
              'Juli','Agustus','September','Oktober','November','Desember'];
$labelPeriode = $tampil
    ? ($namaBulan[(int)$bulanFilter] . ' ' . $tahunFilter)
    : '';
?>

<div class="dashboard-content-wrapper">

    <!-- ── HEADER BAR ─────────────────────────────── -->
    <div class="chart-card" style="margin-bottom:20px;">
        <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
            <form method="GET" style="display:flex; align-items:center; gap:10px; flex:1; flex-wrap:wrap;">
                <input type="hidden" name="page" value="laporan">
                <div>
                    <label class="form-label" style="margin-bottom:4px;">Periode</label>
                    <input type="month" name="periode" class="form-control"
                           value="<?= htmlspecialchars($periode) ?>"
                           style="width:200px;">
                </div>
                <div style="padding-top:22px;">
                    <button type="submit" class="btn btn-primary" style="padding:10px 24px;">
                        Tampilkan
                    </button>
                </div>
            </form>

            <?php if ($tampil && $totalTransaksi > 0): ?>
            <div style="padding-top:22px;">
                <button onclick="cetakLaporan()" class="btn btn-outline"
                        style="padding:10px 24px; border-color:var(--text-muted);">
                    <i class="fa-solid fa-print"></i> Cetak
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($tampil): ?>

    <!-- ── KPI CARDS ──────────────────────────────── -->
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">
        <div class="kpi-card">
            <span class="kpi-label">Total Penjualan</span>
            <div class="kpi-value-row">
                <div class="kpi-value-group">
                    <div class="kpi-value" style="font-size:20px;">Rp <?= number_format($totalPenjualan, 0, ',', '.') ?></div>
                    <div class="kpi-unit"><?= htmlspecialchars($labelPeriode) ?></div>
                </div>
                <i class="fa-solid fa-money-bill-wave kpi-icon"></i>
            </div>
        </div>
        <div class="kpi-card">
            <span class="kpi-label">Total Transaksi</span>
            <div class="kpi-value-row">
                <div class="kpi-value-group">
                    <div class="kpi-value"><?= number_format($totalTransaksi, 0, ',', '.') ?></div>
                    <div class="kpi-unit">Transaksi</div>
                </div>
                <i class="fa-solid fa-receipt kpi-icon"></i>
            </div>
        </div>
    </div>

    <!-- ── TABEL TRANSAKSI ────────────────────────── -->
    <div class="chart-card" id="areaCetak">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
            <h3 style="font-size:15px; font-weight:700;">
                Laporan Penjualan — <?= htmlspecialchars($labelPeriode) ?>
            </h3>
        </div>

        <div class="table-responsive">
            <table class="data-table" id="tabelLaporan">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal Transaksi</th>
                        <th>No Faktur</th>
                        <th>Item</th>
                        <th>Total</th>
                        <th class="no-print">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($qTrx && $qTrx->num_rows > 0):
                        $no = 1;
                        while ($row = $qTrx->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= date('d M Y H:i', strtotime($row['tanggal_waktu'])) ?></td>
                        <td><strong><?= htmlspecialchars($row['no_faktur']) ?></strong></td>
                        <td><?= $row['total_item'] ?> pcs</td>
                        <td style="font-weight:700; color:var(--primary-color);">
                            Rp <?= number_format($row['grand_total'], 0, ',', '.') ?>
                        </td>
                        <td class="no-print">
                            <button class="btn-action-icon" style="background:var(--primary-color);"
                                    onclick="lihatDetail(<?= $row['id_penjualan'] ?>, '<?= htmlspecialchars($row['no_faktur'], ENT_QUOTES) ?>')"
                                    title="Lihat Detail">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding:40px; color:var(--text-muted);">
                            <i class="fa-solid fa-inbox" style="font-size:28px; display:block; margin-bottom:8px; opacity:0.4;"></i>
                            Tidak ada transaksi pada periode <?= htmlspecialchars($labelPeriode) ?>.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php else: ?>
    <!-- Belum filter -->
    <div class="chart-card" style="text-align:center; padding:60px;">
        <i class="fa-solid fa-file-invoice-dollar" style="font-size:48px; color:var(--border-color); margin-bottom:16px; display:block;"></i>
        <p style="color:var(--text-muted); font-size:14px;">Pilih periode laporan, lalu klik <strong>Tampilkan</strong>.</p>
    </div>
    <?php endif; ?>
</div>

<!-- ── MODAL DETAIL TRANSAKSI ─────────────────── -->
<div id="modalDetailTrx" class="modal">
    <div class="modal-content" style="max-width:580px;">
        <div class="modal-header">
            <h3 id="detailTrxTitle">Detail Transaksi</h3>
            <button class="close-btn" onclick="closeModal('modalDetailTrx')">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
        <div id="detailTrxBody" style="min-height:80px; display:flex; align-items:center; justify-content:center;">
            <i class="fa-solid fa-spinner fa-spin"></i>
        </div>
    </div>
</div>

<!-- Print styles -->
<style>
    @media print {
        body * { visibility: hidden; }
        #areaCetak, #areaCetak * { visibility: visible; }
        #areaCetak { position: fixed; top: 0; left: 0; width: 100%; }
        .no-print { display: none !important; }
    }
</style>

<script>
function cetakLaporan() {
    window.print();
}

function lihatDetail(idTrx, noFaktur) {
    document.getElementById('detailTrxTitle').textContent = 'Detail — ' + noFaktur;
    document.getElementById('detailTrxBody').innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
    openModal('modalDetailTrx');

    fetch('?page=laporan&periode=<?= htmlspecialchars($periode) ?>&ajax_detail=' + idTrx)
        .then(r => r.text())
        .then(html => { document.getElementById('detailTrxBody').innerHTML = html; });
}
</script>
