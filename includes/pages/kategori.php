<?php
// Halaman: Kategori Barang (Pimpinan)
$qKat = get_query($conn, "SELECT * FROM tbl_kategori ORDER BY nama_kategori ASC");
?>

<div class="dashboard-content-wrapper">

    <?php if (isset($_GET['msg'])): ?>
    <div style="padding:12px 16px; background:var(--success-light); color:var(--success-color); border-radius:8px; margin-bottom:20px; font-weight:500; display:flex; align-items:center; gap:8px;">
        <i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($_GET['msg']) ?>
    </div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
    <div class="flash-error"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <div class="chart-card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h3 style="font-size:15px; font-weight:700;">Daftar Kategori Barang</h3>
            <button class="btn btn-primary" onclick="openModal('modalTambahKat')">
                <i class="fa-solid fa-plus"></i> Tambah Kategori
            </button>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Gambar</th>
                        <th>Nama Kategori</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($qKat->num_rows > 0):
                        $no = 1;
                        while ($row = $qKat->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td>
                            <?php if (!empty($row['foto_kategori'])): ?>
                                <img src="assets/img/kategori/<?= htmlspecialchars($row['foto_kategori']) ?>" alt="Foto" style="width:40px; height:40px; object-fit:cover; border-radius:6px; border:1px solid var(--border-color);">
                            <?php else: ?>
                                <div style="width:40px; height:40px; border-radius:6px; background:var(--bg-body); display:flex; align-items:center; justify-content:center; color:var(--text-muted); border:1px solid var(--border-color);">
                                    <i class="fa-solid fa-image"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= htmlspecialchars($row['nama_kategori']) ?></strong></td>
                        <td>
                            <button class="btn-action-icon btn-edit"
                                    onclick="editKategori(<?= $row['id_kategori'] ?>, '<?= htmlspecialchars($row['nama_kategori'], ENT_QUOTES) ?>', '<?= htmlspecialchars($row['foto_kategori'] ?? '', ENT_QUOTES) ?>')"
                                    title="Edit">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <form action="action_kategori.php" method="POST" style="display:inline;"
                                  onsubmit="return confirm('Hapus kategori <?= htmlspecialchars($row['nama_kategori'], ENT_QUOTES) ?>?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id_kategori" value="<?= $row['id_kategori'] ?>">
                                <button type="submit" class="btn-action-icon btn-delete" title="Hapus">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="4" style="text-align:center; padding:40px; color:var(--text-muted);">Belum ada kategori. Tambahkan kategori baru.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div id="modalTambahKat" class="modal">
    <div class="modal-content" style="max-width:420px;">
        <div class="modal-header">
            <h3>Tambah Kategori</h3>
            <button class="close-btn" onclick="closeModal('modalTambahKat')"><i class="fa-solid fa-times"></i></button>
        </div>
        <form action="action_kategori.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="insert">
            <div class="form-group">
                <label class="form-label">Nama Kategori <span style="color:red">*</span></label>
                <input type="text" name="nama_kategori" class="form-control" required placeholder="Cth: Minuman">
            </div>
            <div class="form-group">
                <label class="form-label">Foto Kategori (Opsional)</label>
                <input type="file" name="foto_kategori" class="form-control" accept="image/*">
            </div>
            <div style="text-align:right; margin-top:16px;">
                <button type="button" class="btn btn-outline" style="margin-right:8px;" onclick="closeModal('modalTambahKat')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div id="modalEditKat" class="modal">
    <div class="modal-content" style="max-width:420px;">
        <div class="modal-header">
            <h3>Edit Kategori</h3>
            <button class="close-btn" onclick="closeModal('modalEditKat')"><i class="fa-solid fa-times"></i></button>
        </div>
        <form action="action_kategori.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id_kategori" id="kat_edit_id">
            <div class="form-group">
                <label class="form-label">Nama Kategori <span style="color:red">*</span></label>
                <input type="text" name="nama_kategori" id="kat_edit_nama" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Ganti Foto Kategori (Biarkan kosong jika tidak diganti)</label>
                <div id="kat_edit_foto_preview" style="margin-bottom:8px;"></div>
                <input type="file" name="foto_kategori" class="form-control" accept="image/*">
            </div>
            <div style="text-align:right; margin-top:16px;">
                <button type="button" class="btn btn-outline" style="margin-right:8px;" onclick="closeModal('modalEditKat')">Batal</button>
                <button type="submit" class="btn btn-primary" style="background:var(--warning-color); border-color:var(--warning-color);">
                    <i class="fa-solid fa-save"></i> Perbarui
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function editKategori(id, nama, foto) {
    document.getElementById('kat_edit_id').value   = id;
    document.getElementById('kat_edit_nama').value = nama;
    
    const preview = document.getElementById('kat_edit_foto_preview');
    preview.innerHTML = foto
        ? `<img src="assets/img/kategori/${foto}" style="height:60px; border-radius:6px; border:1px solid #e2e8f0;">`
        : `<span style="font-size:12px; color:var(--text-muted);"><i class="fa-solid fa-image"></i> Belum ada foto</span>`;

    openModal('modalEditKat');
}
</script>
