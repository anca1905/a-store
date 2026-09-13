<?php
// includes/sidebar.php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <i class="fa-solid fa-chart-line logo-icon"></i>
        <h1 class="logo-text">A STORE</h1>
    </div>
    
    <nav class="sidebar-nav">
        <ul class="nav-links">
            <li class="nav-item <?= ($currentPage == 'index.php') ? 'active' : ''; ?>">
                <a href="index.php" class="nav-link">
                    <i class="fa-solid fa-house"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item <?= ($currentPage == 'penjualan.php') ? 'active' : ''; ?>">
                <a href="penjualan.php" class="nav-link">
                    <i class="fa-solid fa-cash-register"></i>
                    <span>Transaksi Penjualan</span>
                </a>
            </li>
            <li class="nav-item <?= ($currentPage == 'kelola_barang.php') ? 'active' : ''; ?>">
                <a href="kelola_barang.php" class="nav-link">
                    <i class="fa-solid fa-box-open"></i>
                    <span>Kelola Data Barang</span>
                </a>
            </li>
            <li class="nav-item <?= ($currentPage == 'historis_penjualan.php') ? 'active' : ''; ?>">
                <a href="historis_penjualan.php" class="nav-link">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span>Data Penjualan Historis</span>
                </a>
            </li>
            <li class="nav-item <?= ($currentPage == 'kalkulasi_sma.php') ? 'active' : ''; ?>">
                <a href="kalkulasi_sma.php" class="nav-link">
                    <i class="fa-solid fa-calculator"></i>
                    <span>Kalkulasi Peramalan (SMA)</span>
                </a>
            </li>
            <li class="nav-item <?= ($currentPage == 'laporan.php') ? 'active' : ''; ?>">
                <a href="laporan.php" class="nav-link">
                    <i class="fa-solid fa-print"></i>
                    <span>Cetak Laporan</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>
