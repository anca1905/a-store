<?php
// Halaman: Data Barang (Pimpinan)
$cari      = trim($_GET['cari'] ?? '');
$filterKat = trim($_GET['kategori'] ?? '');

$where = [];
if ($cari !== '') {
    $cEsc = $conn->real_escape_string($cari);
    $where[] = "(b.kode_barang LIKE '%$cEsc%' OR b.nama_produk LIKE '%$cEsc%' OR b.kategori LIKE '%$cEsc%')";
}
if ($filterKat !== '' && $filterKat !== 'all') {
    $kEsc = $conn->real_escape_string($filterKat);
    $where[] = "b.kategori = '$kEsc'";
}

$sql = "SELECT b.*, k.nama_kategori FROM tbl_barang b LEFT JOIN tbl_kategori k ON b.kategori = k.nama_kategori";
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY b.id_barang DESC";
$query = get_query($conn, $sql);

// Daftar kategori untuk filter & modal Tambah & Edit
$qKat = get_query($conn, "SELECT DISTINCT kategori FROM tbl_barang WHERE kategori IS NOT NULL AND kategori != '' UNION SELECT nama_kategori AS kategori FROM tbl_kategori WHERE nama_kategori IS NOT NULL AND nama_kategori != '' ORDER BY kategori ASC");
$listKategori = [];
while ($rk = $qKat->fetch_assoc()) {
    if (!empty($rk['kategori'])) $listKategori[] = $rk['kategori'];
}
?>

<div class="dashboard-content-wrapper">

    <noscript>
    <?php if (isset($_GET['msg'])): ?>
    <div style="padding:12px 16px; background:var(--success-light); color:var(--success-color); border-radius:8px; margin-bottom:20px; font-weight:500; display:flex; align-items:center; gap:8px;">
        <i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($_GET['msg']) ?>
    </div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
    <div class="flash-error"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>
    </noscript>

    <div class="chart-card">
        <!-- Filter & Action Bar -->
        <form action="" method="GET" id="formFilterBarang" style="margin-bottom:20px;">
            <input type="hidden" name="page" value="barang">
            
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
                <!-- Filter Controls (Search & Kategori) -->
                <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap; flex:1;">
                    <!-- Search Bar -->
                    <div class="search-bar" style="background:#fff; border:1px solid var(--border-color); width:240px; margin-bottom:0; box-shadow:0 1px 3px rgba(0,0,0,0.02);">
                        <i class="fa-solid fa-search"></i>
                        <input type="text" name="cari" id="inputCariBarang" placeholder="Cari nama / kode..." value="<?= htmlspecialchars($cari) ?>" autocomplete="off">
                        <?php if ($cari !== ''): ?>
                        <button type="button" onclick="document.getElementById('inputCariBarang').value=''; document.getElementById('formFilterBarang').submit();" style="border:none; background:transparent; cursor:pointer; color:#94a3b8; padding:0 6px;" title="Hapus teks pencarian">
                            <i class="fa-solid fa-circle-xmark"></i>
                        </button>
                        <?php endif; ?>
                    </div>

                    <!-- Dropdown Filter Kategori -->
                    <div style="min-width:180px;">
                        <select name="kategori" id="selectKategori" class="form-control" onchange="this.form.submit()" style="font-size:13px; font-weight:600; padding:8px 12px; height:auto; cursor:pointer; border-radius:8px;">
                            <option value="">Kategori</option>
                            <?php foreach ($listKategori as $kat): ?>
                            <option value="<?= htmlspecialchars($kat) ?>" <?= ($filterKat === $kat) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($kat) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php if ($cari !== '' || ($filterKat !== '' && $filterKat !== 'all')): ?>
                    <a href="?page=barang" class="btn btn-outline" style="padding:8px 14px; color:#ef4444; border-color:#fca5a5;" title="Reset filter">
                        <i class="fa-solid fa-rotate-left"></i> Reset
                    </a>
                    <?php endif; ?>
                </div>

                <!-- Action Buttons (Toggle Harga Beli & Tambah Barang) -->
                <div style="display:flex; align-items:center; gap:10px;">
                    <button type="button" class="btn btn-outline" id="btnToggleHargaBeli" onclick="toggleAllHargaBeli()" style="padding:8px 14px; display:inline-flex; align-items:center; gap:8px;">
                        <i class="fa-solid fa-eye" id="iconToggleHargaBeli"></i>
                        <span id="labelToggleHargaBeli">Tampilkan Harga Beli</span>
                    </button>
                    <button type="button" class="btn btn-primary" onclick="openModal('modalTambah')" style="padding:8px 16px;">
                        <i class="fa-solid fa-plus"></i> Tambah Barang
                    </button>
                </div>
            </div>

            <?php if ($cari !== '' || ($filterKat !== '' && $filterKat !== 'all')): ?>
            <div style="margin-top:12px; font-size:12.5px; color:var(--text-muted); display:flex; align-items:center; gap:6px;">
                <span>Menampilkan <strong style="color:var(--text-main);"><?= $query ? $query->num_rows : 0 ?></strong> barang<?= ($filterKat !== '') ? ' kategori <strong style="color:var(--primary-color);">' . htmlspecialchars($filterKat) . '</strong>' : '' ?><?= ($cari !== '') ? ' dengan kata kunci <strong style="color:var(--primary-color);">"' . htmlspecialchars($cari) . '"</strong>' : '' ?></span>
            </div>
            <?php endif; ?>
        </form>

        <!-- Tabel -->
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th>
                            Harga Beli
                            <button type="button" onclick="toggleAllHargaBeli()" title="Sembunyikan / Tampilkan Semua Harga Beli" style="background:none; border:none; cursor:pointer; color:inherit; opacity:0.6; margin-left:4px;">
                                <i class="fa-solid fa-eye-slash" id="thIconHargaBeli"></i>
                            </button>
                        </th>
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
                        <td>
                            <span class="harga-beli-wrapper" data-real="Rp <?= number_format($row['harga_beli'] ?? 0, 0, ',', '.') ?>" onclick="toggleSingleHargaBeli(this)" title="Klik untuk mengintip / menyembunyikan nominal ini" style="cursor:pointer; display:inline-flex; align-items:center; gap:6px; user-select:none; padding:2px 6px; border-radius:4px; transition:background 0.2s;">
                                <span class="harga-beli-text" style="letter-spacing:1px; font-weight:600; color:var(--text-muted);">••••••</span>
                                <i class="fa-solid fa-eye-slash eye-mini" style="font-size:10px; opacity:0.4;"></i>
                            </span>
                        </td>
                        <td>Rp <?= number_format($row['harga_jual'] ?? 0, 0, ',', '.') ?></td>
                        <td><span class="status <?= $statusStok ?>"><?= $row['stok_aktual'] ?></span></td>
                        <td>
                            <button class="btn-action-icon btn-edit" onclick='editBarang(<?= htmlspecialchars(json_encode($row), ENT_QUOTES) ?>)' title="Edit">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <form action="action_barang.php" method="POST" style="display:inline;"
                                  onsubmit="return confirmDeleteForm(event, this, 'Hapus barang <?= htmlspecialchars($row['nama_produk'], ENT_QUOTES) ?>?');">
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
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                            <label class="form-label" style="margin:0;">Harga Beli (Rp) <span style="color:red">*</span></label>
                            <button type="button" onclick="toggleInputVisibility('tambah_harga_beli', this)" style="background:none; border:none; cursor:pointer; font-size:11px; color:var(--text-muted); display:inline-flex; align-items:center; gap:4px;">
                                <i class="fa-solid fa-eye-slash"></i> <span>Sensor</span>
                            </button>
                        </div>
                        <input type="number" name="harga_beli" id="tambah_harga_beli" class="form-control" required placeholder="0" min="0">
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
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                        <label class="form-label" style="margin:0;">Harga Beli (Rp) <span style="color:red">*</span></label>
                        <button type="button" id="btnToggleEditHarga" onclick="toggleInputVisibility('edit_harga_beli', this)" style="background:none; border:none; cursor:pointer; font-size:11px; color:var(--text-muted); display:inline-flex; align-items:center; gap:4px;">
                            <i class="fa-solid fa-eye"></i> <span>Lihat</span>
                        </button>
                    </div>
                    <input type="password" name="harga_beli" id="edit_harga_beli" class="form-control" required style="font-family:inherit;">
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
// Pengaturan sensor harga beli (Default: disembunyikan / sensor demi privasi)
let isHargaBeliHidden = localStorage.getItem('hideHargaBeli') !== 'false'; // default true

function updateHargaBeliDisplay() {
    const wrappers = document.querySelectorAll('.harga-beli-wrapper');
    const btnIcon = document.getElementById('iconToggleHargaBeli');
    const btnLabel = document.getElementById('labelToggleHargaBeli');
    const thIcon = document.getElementById('thIconHargaBeli');

    wrappers.forEach(w => {
        const txt = w.querySelector('.harga-beli-text');
        const eye = w.querySelector('.eye-mini');
        if (isHargaBeliHidden) {
            txt.textContent = '••••••';
            txt.style.letterSpacing = '1px';
            txt.style.color = 'var(--text-muted)';
            if (eye) {
                eye.className = 'fa-solid fa-eye-slash eye-mini';
                eye.style.opacity = '0.4';
            }
            w.style.background = 'transparent';
        } else {
            txt.textContent = w.getAttribute('data-real');
            txt.style.letterSpacing = 'normal';
            txt.style.color = 'var(--text-main)';
            if (eye) {
                eye.className = 'fa-solid fa-eye eye-mini';
                eye.style.opacity = '0.7';
            }
        }
    });

    if (btnIcon && btnLabel) {
        if (isHargaBeliHidden) {
            btnIcon.className = 'fa-solid fa-eye';
            btnLabel.textContent = 'Tampilkan Harga Beli';
            if (thIcon) thIcon.className = 'fa-solid fa-eye-slash';
        } else {
            btnIcon.className = 'fa-solid fa-eye-slash';
            btnLabel.textContent = 'Sembunyikan Harga Beli';
            if (thIcon) thIcon.className = 'fa-solid fa-eye';
        }
    }
}

function toggleAllHargaBeli() {
    isHargaBeliHidden = !isHargaBeliHidden;
    localStorage.setItem('hideHargaBeli', isHargaBeliHidden ? 'true' : 'false');
    updateHargaBeliDisplay();
}

function toggleSingleHargaBeli(el) {
    const txt = el.querySelector('.harga-beli-text');
    const eye = el.querySelector('.eye-mini');
    const realVal = el.getAttribute('data-real');
    
    if (txt.textContent === '••••••') {
        txt.textContent = realVal;
        txt.style.letterSpacing = 'normal';
        txt.style.color = 'var(--text-main)';
        if (eye) {
            eye.className = 'fa-solid fa-eye eye-mini';
            eye.style.opacity = '0.7';
        }
        el.style.background = 'rgba(0,0,0,0.04)';
    } else {
        txt.textContent = '••••••';
        txt.style.letterSpacing = '1px';
        txt.style.color = 'var(--text-muted)';
        if (eye) {
            eye.className = 'fa-solid fa-eye-slash eye-mini';
            eye.style.opacity = '0.4';
        }
        el.style.background = 'transparent';
    }
}

function toggleInputVisibility(inputId, btn) {
    const inp = document.getElementById(inputId);
    if (inp.type === 'password') {
        inp.type = 'number';
        btn.innerHTML = '<i class="fa-solid fa-eye-slash"></i> <span>Sensor</span>';
    } else {
        inp.type = 'password';
        btn.innerHTML = '<i class="fa-solid fa-eye"></i> <span>Lihat</span>';
    }
}

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

    // Pastikan harga beli di modal edit default-nya tersensor
    const inpBeli = document.getElementById('edit_harga_beli');
    const btnBeli = document.getElementById('btnToggleEditHarga');
    if (inpBeli) inpBeli.type = 'password';
    if (btnBeli) btnBeli.innerHTML = '<i class="fa-solid fa-eye"></i> <span>Lihat</span>';

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

// Jalankan saat halaman selesai dimuat
document.addEventListener('DOMContentLoaded', function() {
    updateHargaBeliDisplay();

    // Filter instan saat mengetik di input pencarian
    const inputCari = document.getElementById('inputCariBarang');
    if (inputCari) {
        inputCari.addEventListener('input', function() {
            const val = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('.data-table tbody tr');
            rows.forEach(row => {
                if (row.cells.length <= 1) return; // lewati row kosong
                const text = row.innerText.toLowerCase();
                const match = (!val || text.includes(val));
                row.style.display = match ? '' : 'none';
            });
        });
    }
});
</script>
