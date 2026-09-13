<?php
// Halaman: Kelola Kasir (Pimpinan)
$qKasir = get_query($conn, "SELECT id_user, username, nama_lengkap, role FROM tbl_users WHERE role='Kasir' ORDER BY id_user ASC");
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
            <h3 style="font-size:15px; font-weight:700;">Daftar Akun Kasir</h3>
            <button class="btn btn-primary" onclick="openModal('modalTambahKasir')">
                <i class="fa-solid fa-user-plus"></i> Tambah Kasir
            </button>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Username</th>
                        <th>Nama Lengkap</th>
                        <th>Role</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($qKasir->num_rows > 0):
                        $no = 1;
                        while ($row = $qKasir->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><strong><?= htmlspecialchars($row['username']) ?></strong></td>
                        <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                        <td><span class="status status-good"><?= htmlspecialchars($row['role']) ?></span></td>
                        <td>
                            <button class="btn-action-icon btn-edit"
                                    onclick="editKasir(<?= $row['id_user'] ?>, '<?= htmlspecialchars($row['nama_lengkap'], ENT_QUOTES) ?>')"
                                    title="Edit">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <form action="action_kasir.php" method="POST" style="display:inline;"
                                  onsubmit="return confirm('Hapus akun <?= htmlspecialchars($row['username'], ENT_QUOTES) ?>?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id_user" value="<?= $row['id_user'] ?>">
                                <button type="submit" class="btn-action-icon btn-delete" title="Hapus">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="5" style="text-align:center; padding:40px; color:var(--text-muted);">Belum ada akun kasir.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Kasir -->
<div id="modalTambahKasir" class="modal">
    <div class="modal-content" style="max-width:460px;">
        <div class="modal-header">
            <h3>Tambah Akun Kasir</h3>
            <button class="close-btn" onclick="closeModal('modalTambahKasir')"><i class="fa-solid fa-times"></i></button>
        </div>
        <form action="action_kasir.php" method="POST">
            <input type="hidden" name="action" value="insert">
            <div class="form-group">
                <label class="form-label">Username <span style="color:red">*</span></label>
                <input type="text" name="username" class="form-control" required placeholder="Cth: kasir2">
            </div>
            <div class="form-group">
                <label class="form-label">Nama Lengkap <span style="color:red">*</span></label>
                <input type="text" name="nama_lengkap" class="form-control" required placeholder="Nama kasir">
            </div>
            <div class="form-group">
                <label class="form-label">Password <span style="color:red">*</span></label>
                <input type="password" name="password" class="form-control" required placeholder="Password kasir">
            </div>
            <div style="text-align:right; margin-top:16px;">
                <button type="button" class="btn btn-outline" style="margin-right:8px;" onclick="closeModal('modalTambahKasir')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Kasir -->
<div id="modalEditKasir" class="modal">
    <div class="modal-content" style="max-width:460px;">
        <div class="modal-header">
            <h3>Edit Akun Kasir</h3>
            <button class="close-btn" onclick="closeModal('modalEditKasir')"><i class="fa-solid fa-times"></i></button>
        </div>
        <form action="action_kasir.php" method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id_user" id="kasir_edit_id">
            <div class="form-group">
                <label class="form-label">Nama Lengkap <span style="color:red">*</span></label>
                <input type="text" name="nama_lengkap" id="kasir_edit_nama" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Password Baru <span style="font-weight:400; color:var(--text-muted);">(kosongkan jika tidak diubah)</span></label>
                <input type="password" name="password" class="form-control" placeholder="Password baru...">
            </div>
            <div style="text-align:right; margin-top:16px;">
                <button type="button" class="btn btn-outline" style="margin-right:8px;" onclick="closeModal('modalEditKasir')">Batal</button>
                <button type="submit" class="btn btn-primary" style="background:var(--warning-color); border-color:var(--warning-color);">
                    <i class="fa-solid fa-save"></i> Perbarui
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function editKasir(id, nama) {
    document.getElementById('kasir_edit_id').value   = id;
    document.getElementById('kasir_edit_nama').value = nama;
    openModal('modalEditKasir');
}
</script>
