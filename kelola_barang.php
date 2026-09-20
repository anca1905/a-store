<?php
include 'config.php';
include 'cek_sesi.php';
$pageTitle = "Kelola Data Barang";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - A STORE</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .action-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px; }
        .action-buttons { display: flex; gap: 12px; }
        .btn-outline { background: transparent; border: 1px solid var(--border-color); color: var(--text-main); }
        .btn-action-icon { width: 32px; height: 32px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; border: none; cursor: pointer; color: white; margin-right: 4px; font-size:14px; }
        .btn-edit { background: var(--warning-color); }
        .btn-delete { background: var(--danger-color); }
        
        /* Modal Styles Native CSS */
        .modal {
            display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%;
            background-color: rgba(15, 23, 42, 0.4); backdrop-filter: blur(4px);
            align-items: center; justify-content: center;
        }
        .modal.active { display: flex; }
        .modal-content {
            background-color: var(--surface-card); border-radius: var(--border-radius-lg);
            width: 100%; max-width: 500px; padding: 24px; box-shadow: var(--shadow-card);
            animation: slideUp 0.3s ease;
        }
        @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px; }
        .modal-header h3 { font-size: 18px; }
        .close-btn { background: none; border: none; font-size: 20px; cursor: pointer; color: var(--text-muted); }
        
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600; color: var(--text-muted); }
        .form-control { width: 100%; padding: 10px 14px; border: 1px solid var(--border-color); border-radius: 6px; font-family: inherit; font-size: 14px; outline: none; }
    </style>
</head>
<body>
    <div class="layout-wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <div class="main-content">
            <?php include 'includes/topbar.php'; ?>

            <main class="dashboard-content">
                <div class="page-header">
                    <h2>Master Data Barang</h2>
                    <p>Kelola persediaan barang riil yang terhubung dengan Database MySQL.</p>
                </div>

                <!-- Flash Message Error/Success -->
                <?php if(isset($_GET['msg'])): ?>
                <div style="padding:12px 16px; background:var(--success-light); color:var(--success-color); border-radius:8px; margin-bottom:20px; font-weight:500;">
                    <i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($_GET['msg']); ?>
                </div>
                <?php endif; ?>

                <div class="chart-card">
                    <div class="action-bar">
                        <form action="" method="GET" style="display:flex;">
                            <div class="search-bar" style="background:#fff; border:1px solid var(--border-color); width:280px; margin-bottom:0;">
                                <i class="fa-solid fa-search"></i>
                                <input type="text" name="cari" placeholder="Cari kode/nama..." value="<?= isset($_GET['cari']) ? htmlspecialchars($_GET['cari']) : '' ?>">
                            </div>
                            <button type="submit" class="btn btn-outline" style="margin-left:8px;"><i class="fa-solid fa-filter"></i> Cari</button>
                        </form>
                        <div class="action-buttons">
                            <button class="btn btn-primary" onclick="openModal('modalTambah')"><i class="fa-solid fa-plus"></i> Tambah Barang Baru</button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Foto</th>
                                    <th>Kode Barang</th>
                                    <th>Nama Produk</th>
                                    <th>Kategori</th>
                                    <th>Harga Jual</th>
                                    <th>Min. Stok</th>
                                    <th>Stok Aktual</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $cari = isset($_GET['cari']) ? $_GET['cari'] : '';
                                $sql = "SELECT * FROM tbl_barang";
                                if($cari != '') {
                                    $sql .= " WHERE kode_barang LIKE '%$cari%' OR nama_produk LIKE '%$cari%' OR kategori LIKE '%$cari%'";
                                }
                                $sql .= " ORDER BY id_barang DESC";
                                $query = get_query($conn, $sql);
                                
                                if($query->num_rows > 0):
                                    while($row = $query->fetch_assoc()):
                                        $statusStok = ($row['stok_aktual'] <= $row['stok_min']) ? 'status-danger' : 'status-good';
                                ?>
                                <tr>
                                    <td>
                                        <?php if($row['foto_barang'] && file_exists('assets/img/barang/'.$row['foto_barang'])): ?>
                                            <img src="assets/img/barang/<?= $row['foto_barang'] ?>" alt="Foto" style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px; border: 1px solid var(--border-color);">
                                        <?php else: ?>
                                            <div style="width: 50px; height: 50px; background: var(--bg-body); border-radius: 6px; display: flex; align-items: center; justify-content: center; color: var(--text-light); border: 1px solid var(--border-color);">
                                                <i class="fa-solid fa-image"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?= $row['kode_barang'] ?></strong></td>
                                    <td><?= $row['nama_produk'] ?></td>
                                    <td><?= $row['kategori'] ?></td>
                                    <td>Rp <?= number_format($row['harga_jual'],0,',','.') ?></td>
                                    <td><?= $row['stok_min'] ?></td>
                                    <td><span class="status <?= $statusStok ?>"><?= $row['stok_aktual'] ?></span></td>
                                    <td>
                                        <!-- Edit Modal Trigger via JS param -->
                                        <button class="btn-action-icon btn-edit" onclick="editData(<?= htmlspecialchars(json_encode($row)) ?>)" title="Edit Data"><i class="fa-solid fa-pen"></i></button>
                                        
                                        <!-- Delete Form -->
                                        <form action="action_barang.php" method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus barang <?= $row['nama_produk'] ?>?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id_barang" value="<?= $row['id_barang'] ?>">
                                            <button type="submit" class="btn-action-icon btn-delete" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                <?php 
                                    endwhile;
                                else: 
                                ?>
                                <tr><td colspan="8" style="text-align:center; padding:30px; color:var(--text-muted)">Tidak ada data barang ditemukan.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- MODAL TAMBAH BARANG -->
    <div id="modalTambah" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Tambah Barang Baru</h3>
                <button class="close-btn" onclick="closeModal('modalTambah')"><i class="fa-solid fa-times"></i></button>
            </div>
            <!-- Form Insert -->
            <form action="action_barang.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="insert">
                
                <div style="display:flex; gap:16px;">
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Kode Barang</label>
                        <input type="text" name="kode_barang" class="form-control" required placeholder="Cth: BRG-007">
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Harga Jual (Rp)</label>
                        <input type="number" name="harga_jual" class="form-control" required placeholder="Cth: 15000">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Nama Produk</label>
                    <input type="text" name="nama_produk" class="form-control" required placeholder="Cth: Kopi Spesial">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Kategori</label>
                    <input type="text" name="kategori" class="form-control" required placeholder="Cth: Minuman">
                </div>
                
                <div style="display:flex; gap:16px;">
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Stok Aktual Awal</label>
                        <input type="number" name="stok_aktual" class="form-control" required placeholder="0">
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Batas Stok Minimal</label>
                        <input type="number" name="stok_min" class="form-control" required placeholder="Cth: 10">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Foto Barang (Opsional)</label>
                    <input type="file" name="foto_barang" class="form-control" accept="image/*">
                </div>
                
                <div style="margin-top:24px; text-align:right;">
                    <button type="button" class="btn btn-outline" style="margin-right:8px;" onclick="closeModal('modalTambah')">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Simpan Data</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT BARANG -->
    <div id="modalEdit" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Ubah Data Barang</h3>
                <button class="close-btn" onclick="closeModal('modalEdit')"><i class="fa-solid fa-times"></i></button>
            </div>
            <!-- Form Update -->
            <form action="action_barang.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id_barang" id="edit_id">
                
                <div style="display:flex; gap:16px;">
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Kode Barang</label>
                        <input type="text" name="kode_barang" id="edit_kode" class="form-control" required readonly style="background:#f1f5f9;">
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Harga Jual (Rp)</label>
                        <input type="number" name="harga_jual" id="edit_harga_jual" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Nama Produk</label>
                    <input type="text" name="nama_produk" id="edit_nama" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Kategori</label>
                    <input type="text" name="kategori" id="edit_kategori" class="form-control" required>
                </div>
                
                <div style="display:flex; gap:16px;">
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Stok Aktual</label>
                        <input type="number" name="stok_aktual" id="edit_stok_aktual" class="form-control" required>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Batas Stok Minimal</label>
                        <input type="number" name="stok_min" id="edit_stok_min" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Ganti Foto Barang (Biarkan kosong jika tidak diganti)</label>
                    <div style="margin-bottom: 8px;" id="current_foto_container"></div>
                    <input type="file" name="foto_barang" class="form-control" accept="image/*">
                </div>
                
                <div style="margin-top:24px; text-align:right;">
                    <button type="button" class="btn btn-outline" style="margin-right:8px;" onclick="closeModal('modalEdit')">Batal</button>
                    <button type="submit" class="btn btn-primary" style="background:var(--warning-color)"><i class="fa-solid fa-save"></i> Perbarui Data</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) {
            document.getElementById(id).classList.add('active');
        }
        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
        }
        
        // Fungsi untuk menangkap data dari tombol Edit dan memasukkannya ke Modal Edit
        function editData(data) {
            document.getElementById('edit_id').value = data.id_barang;
            document.getElementById('edit_kode').value = data.kode_barang;
            document.getElementById('edit_nama').value = data.nama_produk;
            document.getElementById('edit_harga_jual').value = data.harga_jual;
            document.getElementById('edit_kategori').value = data.kategori;
            document.getElementById('edit_stok_aktual').value = data.stok_aktual;
            document.getElementById('edit_stok_min').value = data.stok_min;
            
            let fotoHtml = '';
            if(data.foto_barang) {
                fotoHtml = `<img src="assets/img/barang/${data.foto_barang}" style="height:60px; border-radius:6px; border:1px solid #e2e8f0;">`;
            } else {
                fotoHtml = `<span style="font-size:12px; color:var(--text-muted);"><i class="fa-solid fa-image"></i> Belum ada foto</span>`;
            }
            document.getElementById('current_foto_container').innerHTML = fotoHtml;
            
            openModal('modalEdit');
        }
    </script>
</body>
</html>
