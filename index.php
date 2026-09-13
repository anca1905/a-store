<?php
session_start();
include 'config.php';
include 'cek_sesi.php';

$role = $_SESSION['role'] ?? 'Kasir';

// ─── Definisi halaman per role ───────────────────────────────────────
$pagesForPimpinan = ['dashboard', 'barang', 'kategori', 'kelola_kasir', 'stok', 'riwayat_stok', 'laporan', 'des', 'pengaturan', 'historis'];
$pagesForKasir    = ['penjualan', 'riwayat_transaksi', 'data_barang_kasir', 'stok_barang'];

$allValidPages = array_merge($pagesForPimpinan, $pagesForKasir);

// Default page per role
$defaultPage = ($role === 'Pimpinan') ? 'dashboard' : 'penjualan';
$page = isset($_GET['page']) ? $_GET['page'] : $defaultPage;

// Validasi akses halaman sesuai role
if ($role === 'Pimpinan' && !in_array($page, $pagesForPimpinan)) {
    $page = 'dashboard';
} elseif ($role === 'Kasir' && !in_array($page, $pagesForKasir)) {
    $page = 'penjualan';
} elseif (!in_array($page, $allValidPages)) {
    $page = $defaultPage;
}

// ─── Label halaman ────────────────────────────────────────────────────
$pageLabels = [
    'dashboard'         => ['icon' => 'fa-house',             'title' => 'Dasbor'],
    'barang'            => ['icon' => 'fa-box-open',           'title' => 'Data Barang'],
    'kategori'          => ['icon' => 'fa-tags',               'title' => 'Kategori Barang'],
    'kelola_kasir'      => ['icon' => 'fa-users',              'title' => 'Data Kasir'],
    'stok'              => ['icon' => 'fa-warehouse',          'title' => 'Manajemen Stok'],
    'riwayat_stok'      => ['icon' => 'fa-clock-rotate-left',  'title' => 'Riwayat Stok'],
    'historis'          => ['icon' => 'fa-clock-rotate-left',  'title' => 'Data Penjualan'],
    'laporan'           => ['icon' => 'fa-file-invoice',       'title' => 'Laporan Penjualan'],
    'des'               => ['icon' => 'fa-chart-line',         'title' => 'Peramalan Stok'],
    'pengaturan'        => ['icon' => 'fa-gear',               'title' => 'Pengaturan'],
    'penjualan'         => ['icon' => 'fa-cash-register',      'title' => 'Penjualan'],
    'riwayat_transaksi' => ['icon' => 'fa-clock-rotate-left',  'title' => 'Riwayat Transaksi'],
    'data_barang_kasir' => ['icon' => 'fa-boxes-stacked',      'title' => 'Data Barang'],
    'stok_barang'       => ['icon' => 'fa-warehouse',          'title' => 'Stok Barang'],
];

$pageTitle  = $pageLabels[$page]['title'] ?? 'Dashboard';
$namaUser   = $_SESSION['nama_lengkap'] ?? 'User';
$roleUser   = $_SESSION['role'] ?? '';

// ─── Notifikasi stok kritis (hanya Pimpinan) ─────────────────────────
$jumlahKritis = 0;
if ($role === 'Pimpinan') {
    $cekKritis    = get_query($conn, "SELECT COUNT(*) as jml FROM tbl_barang WHERE stok_aktual <= stok_min");
    $jumlahKritis = $cekKritis->fetch_assoc()['jml'];
}

// ─── AJAX Handler Detail Transaksi (Mode Struk) ────────────────────────
if (isset($_GET['ajax_detail'])) {
    $val = $conn->real_escape_string($_GET['ajax_detail']);
    $qHdr = get_query($conn, "SELECT id_penjualan, no_faktur, tanggal_waktu, total_item, grand_total, nominal_bayar, kembalian FROM tbl_penjualan WHERE id_penjualan = '$val' OR no_faktur = '$val'");
    if ($qHdr && $qHdr->num_rows > 0) {
        $hdr = $qHdr->fetch_assoc();
        $idTrx = $hdr['id_penjualan'];
        
        $qDet = get_query($conn, "
            SELECT d.kode_barang, b.nama_produk, d.harga_satuan, d.qty, d.subtotal
            FROM tbl_detail_penjualan d
            LEFT JOIN tbl_barang b ON d.kode_barang = b.kode_barang
            WHERE d.id_penjualan = $idTrx
        ");

        echo '<div id="strukPrintArea" style="max-width:320px; margin:0 auto; font-family:\'Courier New\', Courier, monospace; font-size:12px; color:#000; background:#fff; padding:20px 10px;">';
        
        // Header Struk
        echo '<div style="text-align:center; margin-bottom:16px;">';
        echo '<div style="display:flex; justify-content:center; align-items:center; gap:8px; margin-bottom:4px;">';
        echo '<h2 style="font-size:24px; font-weight:900; margin:0; letter-spacing:-1px;">A</h2>';
        echo '<h2 style="font-size:18px; font-weight:800; margin:0;">A STORE</h2>';
        echo '</div>';
        echo '<div style="font-size:10px;">Desa Anaiwoi, Kec. Tanggetada<br>Kab. Kolaka</div>';
        echo '</div>';
        
        echo '<div style="border-bottom:1px dashed #000; margin-bottom:8px;"></div>';
        
        // Info Transaksi
        echo '<table style="width:100%; font-size:11px; margin-bottom:8px; border-collapse:collapse;">';
        echo '<tr><td style="width:90px; padding:2px 0;">No. Transaksi</td><td style="width:10px;">:</td><td>' . htmlspecialchars($hdr['no_faktur']) . '</td></tr>';
        echo '<tr><td style="padding:2px 0;">Tanggal</td><td>:</td><td>' . date('d/m/Y H.i', strtotime($hdr['tanggal_waktu'])) . '</td></tr>';
        echo '<tr><td style="padding:2px 0;">Kasir</td><td>:</td><td>Kasir</td></tr>';
        echo '</table>';
        
        echo '<div style="border-bottom:1px dashed #000; margin-bottom:8px;"></div>';
        
        // Item List
        echo '<table style="width:100%; font-size:11px; border-collapse:collapse; margin-bottom:8px;">';
        echo '<thead><tr>';
        echo '<th style="text-align:left; padding:4px 0; width:20px;">No</th>';
        echo '<th style="text-align:left; padding:4px 0;">Barang</th>';
        echo '<th style="text-align:center; padding:4px 0; width:25px;">Qty</th>';
        echo '<th style="text-align:right; padding:4px 0; width:65px;">Harga</th>';
        echo '<th style="text-align:right; padding:4px 0; width:65px;">Subtotal</th>';
        echo '</tr></thead><tbody>';
        
        $no = 1;
        while ($d = $qDet->fetch_assoc()) {
            echo '<tr>';
            echo '<td style="padding:4px 0; vertical-align:top;">' . $no++ . '</td>';
            echo '<td style="padding:4px 0; padding-right:4px;">' . htmlspecialchars($d['nama_produk'] ?? $d['kode_barang']) . '</td>';
            echo '<td style="padding:4px 0; text-align:center; vertical-align:top;">' . $d['qty'] . '</td>';
            echo '<td style="padding:4px 0; text-align:right; vertical-align:top;">Rp' . number_format($d['harga_satuan'], 0, '', '.') . '</td>';
            echo '<td style="padding:4px 0; text-align:right; vertical-align:top;">Rp' . number_format($d['subtotal'], 0, '', '.') . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
        
        echo '<div style="border-bottom:1px dashed #000; margin-bottom:8px;"></div>';
        
        // Total
        echo '<table style="width:100%; font-size:11px; border-collapse:collapse; margin-bottom:16px;">';
        echo '<tr><td style="text-align:right; padding:2px 10px 2px 0;">Total Item</td><td style="width:10px;">:</td><td style="width:65px; text-align:right;">' . $hdr['total_item'] . '</td></tr>';
        echo '<tr><td style="text-align:right; padding:2px 10px 2px 0;">Total Belanja</td><td>:</td><td style="text-align:right;">Rp' . number_format($hdr['grand_total'], 0, '', '.') . '</td></tr>';
        echo '<tr><td style="text-align:right; padding:2px 10px 2px 0;">Bayar</td><td>:</td><td style="text-align:right;">Rp' . number_format($hdr['nominal_bayar'], 0, '', '.') . '</td></tr>';
        echo '<tr><td style="text-align:right; padding:2px 10px 2px 0; font-weight:bold;">Kembalian</td><td style="font-weight:bold;">:</td><td style="text-align:right; font-weight:bold;">Rp' . number_format($hdr['kembalian'], 0, '', '.') . '</td></tr>';
        echo '</table>';
        
        // Footer
        echo '<div style="text-align:center; font-size:11px; font-weight:bold;">Terima kasih atas kunjungan Anda!</div>';
        echo '</div>';
        
        // Print Button
        echo '<div style="text-align:center; margin-top:20px; border-top:1px solid #e2e8f0; padding-top:16px;">';
        echo '<button onclick="printStruk()" style="background:#111; color:#fff; border:none; padding:10px 20px; border-radius:6px; font-weight:600; cursor:pointer; font-family:\'Outfit\', sans-serif;"><i class="fa-solid fa-print"></i> Cetak Struk</button>';
        echo '</div>';
        
        echo '<script>
        function printStruk() {
            var printContents = document.getElementById("strukPrintArea").innerHTML;
            var originalContents = document.body.innerHTML;
            document.body.innerHTML = "<div style=\'padding:20px; display:flex; justify-content:center;\'>" + printContents + "</div>";
            window.print();
            document.body.innerHTML = originalContents;
            location.reload();
        }
        </script>';
        
    } else {
        echo '<div style="text-align:center; padding:20px; font-family:\'Outfit\', sans-serif;">Data tidak ditemukan.</div>';
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> — A STORE</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* ── Sidebar nav group label ─────────────────── */
        .nav-group-label {
            padding: 18px 20px 6px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: rgba(255,255,255,0.45);
        }

        /* ── Role badge di header ────────────────────── */
        .role-badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 600;
            padding: 2px 10px;
            border-radius: 20px;
            background: var(--primary-light);
            color: var(--primary-color);
        }

        /* ── Kritis badge di nav ─────────────────────── */
        .kritis-badge {
            background: var(--danger-color);
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            padding: 1px 7px;
            border-radius: 20px;
            margin-left: auto;
        }

        /* ── Error flash message ─────────────────────── */
        .flash-error {
            padding: 12px 16px;
            background: var(--danger-light, #fee2e2);
            color: var(--danger-color, #dc2626);
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* ── Form controls global ────────────────────── */
        .form-control {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-family: inherit;
            font-size: 14px;
            outline: none;
            box-sizing: border-box;
        }
        .form-group { margin-bottom: 16px; }
        .form-label {
            display: block;
            margin-bottom: 6px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
        }
        .btn-outline {
            background: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-main);
        }
        .btn-action-icon {
            width: 32px; height: 32px;
            border-radius: 6px;
            display: inline-flex; align-items: center; justify-content: center;
            border: none; cursor: pointer; color: white;
            margin-right: 4px; font-size: 14px;
        }
        .btn-edit   { background: var(--warning-color); }
        .btn-delete { background: var(--danger-color);  }

        /* ── Modal ───────────────────────────────────── */
        .modal {
            display: none; position: fixed; z-index: 1000;
            left: 0; top: 0; width: 100%; height: 100%;
            background: rgba(15,23,42,0.45); backdrop-filter: blur(4px);
            align-items: center; justify-content: center;
        }
        .modal.active { display: flex; }
        .modal-content {
            background: var(--surface-card);
            border-radius: var(--border-radius-lg);
            width: 100%; max-width: 520px;
            padding: 28px; box-shadow: var(--shadow-card);
            animation: slideUp 0.25s ease;
            max-height: 90vh; overflow-y: auto;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px) }
            to   { opacity: 1; transform: translateY(0) }
        }
        .modal-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 14px;
        }
        .modal-header h3 { font-size: 18px; font-weight: 700; }
        .close-btn { background: none; border: none; font-size: 20px; cursor: pointer; color: var(--text-muted); }
        .close-btn:hover { color: var(--danger-color); }

        /* ── POS Styles (penjualan) ──────────────────── */
        .pos-grid { display: flex; gap: 24px; flex-wrap: wrap; align-items: flex-start; }
        .pos-left  { flex: 7; display: flex; flex-direction: column; gap: 24px; min-width: 400px; }
        .pos-right { flex: 3; display: flex; flex-direction: column; gap: 0; min-width: 300px; position: sticky; top: 20px; }
        .search-product { display: flex; gap: 12px; margin-bottom: 20px; position: relative; }
        .autocomplete-dropdown {
            position: absolute; top: 100%; left: 0; right: 0;
            background: #fff; box-shadow: var(--shadow-md); border-radius: 8px;
            max-height: 240px; overflow-y: auto; z-index: 200;
            border: 1px solid var(--border-color); display: none;
        }
        .autocomplete-item {
            padding: 10px 16px; cursor: pointer; border-bottom: 1px solid var(--border-color);
            display: flex; justify-content: space-between; align-items: center;
        }
        .autocomplete-item:hover { background: var(--primary-light); }
        .cart-qty { width: 60px; padding: 6px; text-align: center; border: 1px solid var(--border-color); border-radius: 6px; }
        .cart-action { padding: 6px 10px; border-radius: 6px; color: var(--danger-color); background: var(--danger-light); border: none; cursor: pointer; }
        .summary-box { background: var(--surface-card); padding: 20px; border-radius: var(--border-radius-md); margin-bottom: 20px; border: 1px solid var(--border-color); }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 15px; color: var(--text-muted); }
        .summary-total { display: flex; justify-content: space-between; margin-top: 16px; padding-top: 16px; border-top: 2px dashed var(--border-color); font-size: 20px; font-weight: 700; }
        .btn-large { width: 100%; padding: 16px; font-size: 16px; font-weight: 600; justify-content: center; border-radius: 8px; display: flex; align-items: center; gap: 8px; font-family: inherit; cursor: pointer; transition: var(--transition-fast); }
        .btn-success { background-color: var(--success-color); color: white; border: none; }
        .btn-success:hover { background-color: #059669; }
    </style>
</head>

<body>
    <div class="app-container">
        <!-- ═══════════════ SIDEBAR ═══════════════ -->
        <aside class="app-sidebar">
            <div class="sidebar-header" style="align-items:center; gap:12px; border-bottom: 1px solid #334155; margin-bottom: 8px;">
                <img src="assets/img/logo.jpeg" alt="Logo" style="width: 36px; height: 36px; object-fit: contain; border-radius: 8px; background: #fff; padding: 3px;">
                <div>
                    <h1 class="sidebar-brand" style="line-height:1;">A STORE</h1>
                    <div style="font-size:10px; color:#9CA3AF; letter-spacing:0.5px; margin-top:4px; font-weight:600;"><?= htmlspecialchars($roleUser) ?></div>
                </div>
            </div>

            <nav class="sidebar-nav">
                <?php if ($role === 'Pimpinan'): ?>
                    <!-- ── PIMPINAN NAV ── -->
                    <a href="?page=dashboard" class="nav-item <?= $page === 'dashboard' ? 'active' : '' ?>">
                        <i class="fa-solid fa-table-columns"></i>
                        <span>Dasbor</span>
                    </a>

                    <div class="nav-group-label">Data Master</div>
                    <a href="?page=barang" class="nav-item <?= $page === 'barang' ? 'active' : '' ?>">
                        <i class="fa-solid fa-boxes-stacked"></i>
                        <span>Data Barang</span>
                    </a>
                    <a href="?page=kategori" class="nav-item <?= $page === 'kategori' ? 'active' : '' ?>">
                        <i class="fa-solid fa-tags"></i>
                        <span>Kategori Barang</span>
                    </a>
                    <a href="?page=kelola_kasir" class="nav-item <?= $page === 'kelola_kasir' ? 'active' : '' ?>">
                        <i class="fa-solid fa-user-tie"></i>
                        <span>Data Kasir</span>
                    </a>

                    <div class="nav-group-label">Stok</div>
                    <a href="?page=stok" class="nav-item <?= $page === 'stok' ? 'active' : '' ?>">
                        <i class="fa-solid fa-warehouse"></i>
                        <span>Manajemen Stok</span>
                        <?php if ($jumlahKritis > 0): ?><span class="kritis-badge"><?= $jumlahKritis ?></span><?php endif; ?>
                    </a>
                    <a href="?page=riwayat_stok" class="nav-item <?= $page === 'riwayat_stok' ? 'active' : '' ?>">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        <span>Riwayat Stok</span>
                    </a>

                    <div class="nav-group-label">Transaksi</div>
                    <a href="?page=historis" class="nav-item <?= $page === 'historis' ? 'active' : '' ?>">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <span>Penjualan</span>
                    </a>
                    <a href="?page=laporan" class="nav-item <?= $page === 'laporan' ? 'active' : '' ?>">
                        <i class="fa-solid fa-file-invoice"></i>
                        <span>Laporan Penjualan</span>
                    </a>
                    <a href="?page=des" class="nav-item <?= $page === 'des' ? 'active' : '' ?>">
                        <i class="fa-solid fa-chart-line"></i>
                        <span>Peramalan Stok</span>
                    </a>
                    <a href="?page=pengaturan" class="nav-item <?= $page === 'pengaturan' ? 'active' : '' ?>">
                        <i class="fa-solid fa-gear"></i>
                        <span>Pengaturan</span>
                    </a>

                <?php else: ?>
                    <!-- ── KASIR NAV ── -->
                    <div class="nav-group-label">Transaksi</div>
                    <a href="?page=penjualan" class="nav-item <?= $page === 'penjualan' ? 'active' : '' ?>">
                        <i class="fa-solid fa-cash-register"></i>
                        <span>Penjualan</span>
                    </a>
                    <a href="?page=riwayat_transaksi" class="nav-item <?= $page === 'riwayat_transaksi' ? 'active' : '' ?>">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        <span>Riwayat Transaksi</span>
                    </a>
                    <a href="?page=data_barang_kasir" class="nav-item <?= $page === 'data_barang_kasir' ? 'active' : '' ?>">
                        <i class="fa-solid fa-boxes-stacked"></i>
                        <span>Data Barang</span>
                    </a>
                    <a href="?page=stok_barang" class="nav-item <?= $page === 'stok_barang' ? 'active' : '' ?>">
                        <i class="fa-solid fa-warehouse"></i>
                        <span>Stok Barang</span>
                    </a>
                <?php endif; ?>
            </nav>

            <div class="sidebar-footer">
                <a href="logout.php" class="nav-item logout-item">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Keluar</span>
                </a>
            </div>
        </aside>

        <!-- ═══════════════ MAIN CONTENT ═══════════════ -->
        <div class="main-wrapper">
            <header class="main-header" style="background:#fff; border-bottom:1px solid var(--border-color); margin-bottom:24px; padding:0 32px;">
                <h2 class="page-title" style="font-size:20px;"><?= htmlspecialchars($pageTitle) ?></h2>
                <div class="user-controls" style="display:flex; align-items:center; gap:20px;">
                    <div class="notification-trigger" style="position:relative; width:36px; height:36px; display:flex; align-items:center; justify-content:center; background:var(--bg-body); border-radius:50%; cursor:pointer;">
                        <i class="fa-solid fa-bell" style="color:var(--text-muted); font-size:16px;"></i>
                        <span class="badge" style="position:absolute; top:-2px; right:-2px; width:16px; height:16px; background:var(--primary-dark); color:#fff; font-size:9px; border-radius:50%; display:flex; align-items:center; justify-content:center; border:2px solid #fff;">3</span>
                    </div>
                    <div class="user-profile" style="display:flex; align-items:center; gap:12px; padding:4px 8px; cursor:pointer;">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($namaUser) ?>&background=111111&color=fff&rounded=true" alt="Avatar" class="avatar" style="width:36px; height:36px; border-radius:50%;">
                        <div class="user-info" style="display:flex; flex-direction:column; text-align:left;">
                            <span class="user-name" style="font-size:13px; font-weight:700; color:var(--text-main); line-height:1.2;"><?= htmlspecialchars($namaUser) ?></span>
                            <span class="user-role" style="font-size:11px; color:var(--text-muted);"><?= htmlspecialchars($roleUser) ?></span>
                        </div>
                        <i class="fa-solid fa-chevron-down dropdown-icon" style="font-size:10px; color:var(--text-muted); margin-left:4px;"></i>
                    </div>
                </div>
            </header>

            <main class="content-area" style="padding:0 32px 40px 32px;">
                <?php if (isset($_GET['error'])): ?>
                    <div class="flash-error">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <?= htmlspecialchars($_GET['error']) ?>
                    </div>
                <?php endif; ?>

                <?php
                $pageFile = "includes/pages/{$page}.php";
                if (file_exists($pageFile)) {
                    include $pageFile;
                } else {
                    echo "<div style='padding:40px; text-align:center; color:var(--text-muted);'><i class='fa-solid fa-triangle-exclamation' style='font-size:40px; margin-bottom:16px; display:block;'></i>Halaman <strong>{$page}</strong> belum tersedia.</div>";
                }
                ?>
            </main>
        </div>
    </div>

    <script>
        function openModal(id) { document.getElementById(id).classList.add('active'); }
        function closeModal(id) { document.getElementById(id).classList.remove('active'); }
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal')) {
                e.target.classList.remove('active');
            }
        });
    </script>
</body>
</html>