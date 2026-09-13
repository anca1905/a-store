<?php
// Halaman: Data Barang (Pimpinan)
$cari = $conn->real_escape_string($_GET['cari'] ?? '');
$sql  = "SELECT b.*, k.nama_kategori FROM tbl_barang b LEFT JOIN tbl_kategori k ON b.kategori = k.nama_kategori";
if ($cari !== '') {
    $sql .= " WHERE b.kode_barang LIKE '%$cari%' OR b.nama_produk LIKE '%$cari%' OR b.kategori LIKE '%$cari%'";
}
$sql  .= " ORDER BY b.id_barang DESC";
$query = get_query($conn, $sql);

// Daftar kategori untuk dropdown
$qKat = get_query($conn, "SELECT nama_kategori FROM tbl_kategori ORDER BY nama_kategori ASC");
$listKategori = [];
while ($rk = $qKat->fetch_assoc()) $listKategori[] = $rk['nama_kategori'];
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
        <!-- Action Bar -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
            <form action="" method="GET" style="display:flex; gap:8px; align-items:center;">
                <input type="hidden" name="page" value="barang">
                <div class="search-bar" style="background:#fff; border:1px solid var(--border-color); width:260px; margin-bottom:0;">
                    <i class="fa-solid fa-search"></i>
                    <input type="text" name="cari" placeholder="Cari barang..." value="<?= htmlspecialchars($cari) ?>">
                </div>
                <button type="submit" class="btn btn-outline" style="padding:9px 14px;">Cari</button>
            </form>
            <button class="btn btn-primary" onclick="openModal('modalTambah')">
                <i class="fa-solid fa-plus"></i> Tambah Barang
            </button>
        </div>

        <!-- Tabel -->
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th>Harga Beli</th>
                        <th>Harga Jual</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($query->num_rows > 0):
                        $no = 1;
                        while ($row = $query->fetch_assoc()):
                            $statusStok = ($row['stok_aktual'] <= $row['stok_min']) ? 'status-danger' : 'status-good';
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td>
                            <div style="display:flex; align-items:center; gap:12px;">
                                <?php if (!empty($row['foto_barang'])): ?>
                                    <img src="assets/img/barang/<?= htmlspecialchars($row['foto_barang']) ?>" alt="Foto" style="width:40px; height:40px; object-fit:cover; border-radius:6px; border:1px solid var(--border-color);">
                                <?php else: ?>
                                    <div style="width:40px; height:40px; border-radius:6px; background:var(--bg-body); display:flex; align-items:center; justify-content:center; color:var(--text-muted); border:1px solid var(--border-color);">
                                        <i class="fa-solid fa-box"></i>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <strong><?= htmlspecialchars($row['nama_produk']) ?></strong>
                                    <div style="font-size:12px; color:var(--text-muted);"><?= htmlspecialchars($row['kode_barang']) ?><?php if($row['satuan'] ?? ''): ?> · <?= htmlspecialchars($row['satuan']) ?><?php endif; ?></div>
                                </div>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($row['kategori'] ?? '-') ?></td>
                        <td>Rp <?= number_format($row['harga_beli'] ?? 0, 0, ',', '.') ?></td>
                        <td>Rp <?= number_format($row['harga_jual'] ?? 0, 0, ',', '.') ?></td>
                        <td><span class="status <?= $statusStok ?>"><?= $row['stok_aktual'] ?></span></td>
                        <td>
                            <button class="btn-action-icon btn-edit" onclick='editBarang(<?= htmlspecialchars(json_encode($row), ENT_QUOTES) ?>)' title="Edit">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <form action="action_barang.php" method="POST" style="display:inline;"
                                  onsubmit="return confirm('Hapus barang <?= htmlspecialchars($row['nama_produk'], ENT_QUOTES) ?>?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id_barang" value="<?= $row['id_barang'] ?>">
                                <button type="submit" class="btn-action-icon btn-delete" title="Hapus">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="7" style="text-align:center; padding:40px; color:var(--text-muted);">Tidak ada data barang.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ── MODAL TAMBAH ──────────────────────────────── -->
<div id="modalTambah" class="modal">
    <div class="modal-content" style="max-width:800px; padding:0; overflow:hidden;">
        <div class="modal-header" style="padding:24px 32px 0 32px; border:none; margin-bottom:8px;">
            <div>
                <h3 style="font-size:24px; font-weight:700; margin-bottom:4px;">Tambah Barang</h3>
                <p style="color:var(--text-muted); font-size:14px; margin:0;">Lengkapi informasi barang baru yang akan ditambahkan.</p>
            </div>
            <button class="close-btn" onclick="closeModal('modalTambah')"><i class="fa-solid fa-times"></i></button>
        </div>
        
        <form action="action_barang.php" method="POST" enctype="multipart/form-data" style="padding:0 32px 32px 32px; max-height:calc(90vh - 100px); overflow-y:auto;">
            <input type="hidden" name="action" value="insert">

            <!-- Bagian Informasi Barang -->
            <div style="margin-top:24px;">
                <h4 style="font-size:16px; font-weight:700; border-bottom:1px solid var(--border-color); padding-bottom:8px; margin-bottom:16px;">Informasi Barang</h4>
                
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:16px;">
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Kode Barang <span style="color:red">*</span></label>
                        <input type="text" name="kode_barang" class="form-control" required placeholder="BRG-00123" style="background:var(--bg-body); border:1px solid transparent;">
                        <small style="color:var(--text-muted); font-size:11px; margin-top:4px; display:block;">Kode akan dibuat otomatis oleh sistem.</small>
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Nama Barang <span style="color:red">*</span></label>
                        <input type="text" name="nama_produk" class="form-control" required placeholder="Masukkan nama barang">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:16px;">
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Kategori <span style="color:red">*</span></label>
                        <select name="kategori" class="form-control" required>
                            <option value="">Pilih kategori barang</option>
                            <?php foreach ($listKategori as $kat): ?>
                            <option value="<?= htmlspecialchars($kat) ?>"><?= htmlspecialchars($kat) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Satuan <span style="color:red">*</span></label>
                        <select name="satuan" class="form-control" required>
                            <option value="">Pilih satuan</option>
                            <option value="Pcs">Pcs</option>
                            <option value="Box">Box</option>
                            <option value="Lusin">Lusin</option>
                            <option value="Kg">Kg</option>
                            <option value="Liter">Liter</option>
                            <option value="Pack">Pack</option>
                        </select>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:16px;">
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Harga Beli (Rp) <span style="color:red">*</span></label>
                        <input type="number" name="harga_beli" class="form-control" required placeholder="0" min="0">
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Harga Jual (Rp) <span style="color:red">*</span></label>
                        <input type="number" name="harga_jual" class="form-control" required placeholder="0" min="0">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:16px;">
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Stok Awal <span style="color:red">*</span></label>
                        <input type="number" name="stok_aktual" class="form-control" required placeholder="0" min="0">
                        <small style="color:var(--text-muted); font-size:11px; margin-top:4px; display:block;">Jumlah stok pertama kali tersedia.</small>
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Minimal Stok (Opsional)</label>
                        <input type="number" name="stok_min" class="form-control" placeholder="0" min="0" value="0">
                        <small style="color:var(--text-muted); font-size:11px; margin-top:4px; display:block;">Batas minimal stok sebelum sistem menampilkan peringatan.</small>
                    </div>
                </div>

                <div class="form-group" style="margin:0; margin-bottom:16px;">
                    <label class="form-label">Deskripsi (Opsional)</label>
                    <textarea name="deskripsi" class="form-control" rows="3" placeholder="Masukkan deskripsi barang (ukuran, warna, bahan, dll)"></textarea>
                    <div style="text-align:right; font-size:11px; color:var(--text-muted); margin-top:4px;">0/255</div>
                </div>
            </div>

            <!-- Bagian Informasi Tambahan -->
            <div style="margin-top:32px; margin-bottom:32px;">
                <h4 style="font-size:16px; font-weight:700; border-bottom:1px solid var(--border-color); padding-bottom:8px; margin-bottom:16px;">Informasi Tambahan</h4>
                
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:16px;">
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Merk (Opsional)</label>
                        <input type="text" name="merk" class="form-control" placeholder="Masukkan merk">
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Warna (Opsional)</label>
                        <input type="text" name="warna" class="form-control" placeholder="Masukkan warna">
                    </div>
                </div>
                
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Foto Barang (Opsional)</label>
                    <input type="file" name="foto_barang" class="form-control" accept="image/*" style="padding: 10px;">
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:24px; padding-top:24px; border-top:1px solid var(--border-color);">
                <button type="button" class="btn btn-outline" style="padding:12px 24px;" onclick="closeModal('modalTambah')">
                    <i class="fa-solid fa-times" style="margin-right:4px;"></i> Batal
                </button>
                <button type="submit" class="btn btn-primary" style="padding:12px 24px; background:var(--primary-dark); color:#fff;">
                    <i class="fa-regular fa-floppy-disk" style="margin-right:4px;"></i> Simpan Barang
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ── MODAL EDIT ────────────────────────────────── -->
<div id="modalEdit" class="modal">
    <div class="modal-content" style="max-width:600px;">
        <div class="modal-header">
            <h3>Edit Data Barang</h3>
            <button class="close-btn" onclick="closeModal('modalEdit')"><i class="fa-solid fa-times"></i></button>
        </div>
        <form action="action_barang.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id_barang" id="edit_id">

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="form-group">
                    <label class="form-label">Kode Barang</label>
                    <input type="text" name="kode_barang" id="edit_kode" class="form-control" readonly style="background:#f1f5f9;">
                </div>
                <div class="form-group">
                    <label class="form-label">Nama Barang <span style="color:red">*</span></label>
                    <input type="text" name="nama_produk" id="edit_nama" class="form-control" required>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="form-group">
                    <label class="form-label">Kategori <span style="color:red">*</span></label>
                    <select name="kategori" id="edit_kategori" class="form-control" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php foreach ($listKategori as $kat): ?>
                        <option value="<?= htmlspecialchars($kat) ?>"><?= htmlspecialchars($kat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Satuan</label>
                    <input type="text" name="satuan" id="edit_satuan" class="form-control">
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="form-group">
                    <label class="form-label">Harga Beli (Rp) <span style="color:red">*</span></label>
                    <input type="number" name="harga_beli" id="edit_harga_beli" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Harga Jual (Rp) <span style="color:red">*</span></label>
                    <input type="number" name="harga_jual" id="edit_harga_jual" class="form-control" required>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="form-group">
                    <label class="form-label">Stok Aktual <span style="color:red">*</span></label>
                    <input type="number" name="stok_aktual" id="edit_stok_aktual" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Minimal Stok</label>
                    <input type="number" name="stok_min" id="edit_stok_min" class="form-control">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Deskripsi (Opsional)</label>
                <textarea name="deskripsi" id="edit_deskripsi" class="form-control" rows="2"></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Ganti Foto (Biarkan kosong jika tidak diganti)</label>
                <div id="edit_foto_preview" style="margin-bottom:8px;"></div>
                <input type="file" name="foto_barang" class="form-control" accept="image/*">
            </div>

            <div style="margin-top:20px; text-align:right;">
                <button type="button" class="btn btn-outline" style="margin-right:8px;" onclick="closeModal('modalEdit')">Batal</button>
                <button type="submit" class="btn btn-primary" style="background:var(--warning-color);border-color:var(--warning-color);">
                    <i class="fa-solid fa-save"></i> Perbarui
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function editBarang(data) {
    document.getElementById('edit_id').value          = data.id_barang;
    document.getElementById('edit_kode').value        = data.kode_barang;
    document.getElementById('edit_nama').value        = data.nama_produk;
    document.getElementById('edit_harga_beli').value  = data.harga_beli  ?? 0;
    document.getElementById('edit_harga_jual').value  = data.harga_jual  ?? 0;
    document.getElementById('edit_stok_aktual').value = data.stok_aktual;
    document.getElementById('edit_stok_min').value    = data.stok_min;
    document.getElementById('edit_satuan').value      = data.satuan      ?? '';
    document.getElementById('edit_deskripsi').value   = data.deskripsi   ?? '';

    // Set kategori dropdown
    const sel = document.getElementById('edit_kategori');
    for (let i = 0; i < sel.options.length; i++) {
        if (sel.options[i].value === data.kategori) { sel.selectedIndex = i; break; }
    }

    // Preview foto
    const preview = document.getElementById('edit_foto_preview');
    preview.innerHTML = data.foto_barang
        ? `<img src="assets/img/barang/${data.foto_barang}" style="height:60px; border-radius:6px; border:1px solid #e2e8f0;">`
        : `<span style="font-size:12px; color:var(--text-muted);"><i class="fa-solid fa-image"></i> Belum ada foto</span>`;

    openModal('modalEdit');
}
</script>
