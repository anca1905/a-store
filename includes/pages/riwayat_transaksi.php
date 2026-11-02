<?php
// Halaman: Riwayat Transaksi (Kasir & Admin/Pimpinan)
$tglDari   = $_GET['tgl_dari']   ?? date('Y-m-01');
$tglSampai = $_GET['tgl_sampai'] ?? date('Y-m-d');
$cari      = isset($_GET['cari']) ? trim($_GET['cari']) : '';

$tglDariEsc   = $conn->real_escape_string($tglDari);
$tglSampaiEsc = $conn->real_escape_string($tglSampai);
$cariEsc      = $conn->real_escape_string($cari);

$whereClause = "DATE(p.tanggal_waktu) BETWEEN '$tglDariEsc' AND '$tglSampaiEsc'";
if ($cariEsc !== '') {
    $whereClause .= " AND (p.no_faktur LIKE '%$cariEsc%' OR EXISTS (
        SELECT 1 FROM tbl_detail_penjualan d2 
        LEFT JOIN tbl_barang b2 ON d2.kode_barang = b2.kode_barang 
        WHERE d2.id_penjualan = p.id_penjualan 
          AND (b2.nama_produk LIKE '%$cariEsc%' OR d2.kode_barang LIKE '%$cariEsc%')
    ))";
}

$qTrx = get_query($conn, "
    SELECT p.id_penjualan, p.no_faktur, p.tanggal_waktu, p.total_item, p.grand_total, p.nominal_bayar, p.kembalian, p.metode_pembayaran
    FROM tbl_penjualan p
    WHERE $whereClause
    ORDER BY p.tanggal_waktu DESC
");

// Ambil detail nama barang per transaksi
$itemsByTrx = [];
$qItems = get_query($conn, "
    SELECT d.id_penjualan, d.kode_barang, COALESCE(b.nama_produk, d.kode_barang) AS nama_produk, d.qty, d.harga_satuan, d.subtotal
    FROM tbl_detail_penjualan d
    JOIN tbl_penjualan p ON d.id_penjualan = p.id_penjualan
    LEFT JOIN tbl_barang b ON d.kode_barang = b.kode_barang
    WHERE $whereClause
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
                <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px;">Cari Faktur / Barang</label>
                <input type="text" name="cari" class="form-control" placeholder="No faktur / nama..." value="<?= htmlspecialchars($cari) ?>" style="min-width:180px;">
            </div>
            <div>
                <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px;">Dari Tanggal</label>
                <input type="date" name="tgl_dari" class="form-control" value="<?= htmlspecialchars($tglDari) ?>">
            </div>
            <div>
                <label class="form-label" style="font-size:12px; font-weight:700; margin-bottom:4px;">Sampai Tanggal</label>
                <input type="date" name="tgl_sampai" class="form-control" value="<?= htmlspecialchars($tglSampai) ?>">
            </div>
            <div style="display:flex; gap:8px;">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-search"></i> Tampilkan</button>
                <?php if ($cari !== '' || $tglDari !== date('Y-m-01') || $tglSampai !== date('Y-m-d')): ?>
                <a href="?page=riwayat_transaksi" class="btn btn-outline" title="Reset Filter"><i class="fa-solid fa-rotate-left"></i> Reset</a>
                <?php endif; ?>
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
                        <th style="width: <?= isPimpinan() ? '115px' : '60px' ?>; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $totalPcsAll = 0;
                    $totalOmsetAll = 0;
                    if ($qTrx && $qTrx->num_rows > 0):
                        while ($row = $qTrx->fetch_assoc()):
                            $totalPcsAll += (int)$row['total_item'];
                            $totalOmsetAll += (int)$row['grand_total'];
                            $idTrx = $row['id_penjualan'];
                            $detailItems = $itemsByTrx[$idTrx] ?? [];
                            $tglRaw = date('Y-m-d\TH:i', strtotime($row['tanggal_waktu']));
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
                        <td style="text-align: center; white-space: nowrap;">
                            <button class="btn-action-icon" style="background:var(--primary-color);"
                                    onclick="lihatDetail('<?= htmlspecialchars($row['no_faktur']) ?>')"
                                    title="Lihat Detail Struk">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                            <?php if (isPimpinan()): ?>
                            <button class="btn-action-icon" style="background:#f59e0b;"
                                    onclick="bukaModalEditTanggal(<?= $row['id_penjualan'] ?>, '<?= htmlspecialchars($row['no_faktur'], ENT_QUOTES) ?>', '<?= $tglRaw ?>')"
                                    title="Ubah Tanggal Transaksi (Admin)">
                                <i class="fa-solid fa-calendar-days"></i>
                            </button>
                            <button class="btn-action-icon" style="background:#ef4444;"
                                    onclick="hapusPenjualan(<?= $row['id_penjualan'] ?>, '<?= htmlspecialchars($row['no_faktur'], ENT_QUOTES) ?>')"
                                    title="Hapus Transaksi (Admin)">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                            <?php endif; ?>
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

<?php if (isPimpinan()): ?>
<!-- Modal Ubah Tanggal Transaksi (Khusus Admin/Pimpinan) -->
<div id="modalEditTanggal" class="modal">
    <div class="modal-content" style="max-width:440px;">
        <div class="modal-header">
            <h3 style="font-size:16px; font-weight:700;">
                <i class="fa-solid fa-calendar-days" style="color:var(--primary-color); margin-right:6px;"></i> Ubah Tanggal Penjualan
            </h3>
            <button class="close-btn" onclick="closeModal('modalEditTanggal')"><i class="fa-solid fa-times"></i></button>
        </div>
        <form action="action_penjualan.php" method="POST" id="formEditTanggal">
            <input type="hidden" name="action" value="update_tanggal">
            <input type="hidden" name="id_penjualan" id="editTglId">
            <input type="hidden" name="redirect_url" id="editTglRedirect">
            
            <div style="padding:16px 20px;">
                <div style="margin-bottom:12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:10px 14px;">
                    <div style="font-size:11px; color:#64748b; font-weight:600; text-transform:uppercase;">No. Faktur</div>
                    <div id="editTglFaktur" style="font-size:15px; font-weight:700; color:#0f172a;"></div>
                </div>
                
                <div style="margin-bottom:14px;">
                    <label class="form-label" style="font-weight:700; font-size:13px; margin-bottom:6px;">Tanggal & Waktu Baru</label>
                    <input type="datetime-local" name="tanggal_waktu" id="editTglInput" class="form-control" required style="font-size:14px; padding:10px; font-weight:600;">
                    <small style="font-size:11px; color:var(--text-muted); display:block; margin-top:4px;">
                        Pilih tanggal sebenarnya saat transaksi terjadi (misal: mundur ke bulan Oktober).
                    </small>
                </div>
                
                <div style="margin-bottom:8px;">
                    <label style="display:flex; align-items:flex-start; gap:8px; font-size:12.5px; cursor:pointer; line-height:1.4;">
                        <input type="checkbox" name="sesuaikan_faktur" value="1" checked style="margin-top:2px;">
                        <span>Sesuaikan kode bulan pada No Faktur (contoh: <code>INV-202610-xxxx</code> jika tanggal dipindah ke Oktober).</span>
                    </label>
                </div>
            </div>
            
            <div style="padding:12px 20px; background:#f8fafc; border-top:1px solid var(--border-color); display:flex; justify-content:flex-end; gap:8px;">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalEditTanggal')">Batal</button>
                <button type="submit" class="btn btn-primary" style="background:#000; border:none;">
                    <i class="fa-solid fa-save"></i> Simpan Tanggal
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
function lihatDetail(noFaktur) {
    document.getElementById('modalDetailTitle').textContent = 'Detail — ' + noFaktur;
    document.getElementById('modalDetailBody').innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
    openModal('modalDetail');

    fetch('?page=riwayat_transaksi&ajax_detail=' + encodeURIComponent(noFaktur))
        .then(r => r.text())
        .then(html => { document.getElementById('modalDetailBody').innerHTML = html; });
}

function bukaModalEditTanggal(id, noFaktur, tglWaktu) {
    document.getElementById('editTglId').value = id;
    document.getElementById('editTglFaktur').textContent = noFaktur;
    document.getElementById('editTglInput').value = tglWaktu;
    document.getElementById('editTglRedirect').value = window.location.href;
    openModal('modalEditTanggal');
}

function hapusPenjualan(id, noFaktur) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Hapus Transaksi?',
            html: `Yakin ingin menghapus transaksi <strong>${noFaktur}</strong>?<br><br>` +
                  `<label style="font-size:13px; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">` +
                  `<input type="checkbox" id="swalKembalikanStok" checked> Kembalikan stok barang yang terjual</label>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const kembalikan = document.getElementById('swalKembalikanStok').checked ? 1 : 0;
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'action_penjualan.php';
                form.innerHTML = `
                    <input type="hidden" name="action" value="hapus_transaksi">
                    <input type="hidden" name="id_penjualan" value="${id}">
                    <input type="hidden" name="kembalikan_stok" value="${kembalikan}">
                    <input type="hidden" name="redirect_url" value="${encodeURI(window.location.href)}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        });
    } else {
        if (confirm(`Yakin ingin menghapus transaksi ${noFaktur}?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'action_penjualan.php';
            form.innerHTML = `
                <input type="hidden" name="action" value="hapus_transaksi">
                <input type="hidden" name="id_penjualan" value="${id}">
                <input type="hidden" name="kembalikan_stok" value="1">
                <input type="hidden" name="redirect_url" value="${encodeURI(window.location.href)}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }
}
</script>
