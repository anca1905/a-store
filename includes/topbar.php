<?php
// includes/topbar.php
?>
<header class="topbar">
    <div class="search-bar">
        <i class="fa-solid fa-desktop"></i>
        <input type="text" value="<?= isset($pageTitle) ? $pageTitle : 'Sistem Business Intelligence A STORE'; ?>" disabled style="background:transparent; color:var(--text-main); font-weight:500;">
    </div>

    <div class="user-controls">
        <div class="notification-trigger">
            <i class="fa-solid fa-bell"></i>
            <?php
            // Hitung notifikasi stok kritis dinamis
            include_once 'config.php';
            $cekKritis = get_query($conn, "SELECT COUNT(*) as jml FROM tbl_barang WHERE stok_aktual <= stok_min");
            $dataKritis = $cekKritis->fetch_assoc();
            if ($dataKritis['jml'] > 0):
            ?>
                <span class="badge pulse-animation"><?= $dataKritis['jml'] ?></span>
            <?php endif; ?>
        </div>

        <div class="user-profile">
            <?php
            $namaUser = isset($_SESSION['nama_lengkap']) ? $_SESSION['nama_lengkap'] : 'Admin Store';
            $roleUser = isset($_SESSION['role']) ? $_SESSION['role'] : 'Administrator BI';
            $avatarUrl = "https://ui-avatars.com/api/?name=" . urlencode($namaUser) . "&background=0284C7&color=fff&rounded=true";
            ?>
            <img src="<?= $avatarUrl ?>" alt="Profile" class="avatar">
            <div class="user-info">
                <span class="user-name"><?= htmlspecialchars($namaUser) ?></span>
                <span class="user-role"><?= htmlspecialchars($roleUser) ?></span>
            </div>
            <a href="logout.php" style="margin-left: 12px; color: var(--danger-color); padding: 8px; border-radius: 50%; background: var(--danger-light); display: flex; align-items: center; justify-content: center; width: 32px; height: 32px;" title="Logout">
                <i class="fa-solid fa-power-off" style="font-size: 14px;"></i>
            </a>
        </div>
    </div>
</header>