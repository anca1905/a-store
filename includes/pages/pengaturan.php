<?php
// Halaman: Pengaturan Akun (Pimpinan)
$successMsg = '';
$errorMsg   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'ubah_profil') {
        $nama_baru = $conn->real_escape_string(trim($_POST['nama_lengkap']));
        if (empty($nama_baru)) {
            $errorMsg = 'Nama lengkap tidak boleh kosong.';
        } else {
            $uid = (int)$_SESSION['user_id'];
            get_query($conn, "UPDATE tbl_users SET nama_lengkap='$nama_baru' WHERE id_user=$uid");
            $_SESSION['nama_lengkap'] = $nama_baru;
            $successMsg = 'Nama profil berhasil diperbarui.';
        }

    } elseif ($action === 'ubah_password') {
        $pw_lama = $_POST['password_lama']  ?? '';
        $pw_baru = $_POST['password_baru']  ?? '';
        $pw_konfirm = $_POST['password_konfirm'] ?? '';
        $uid = (int)$_SESSION['user_id'];

        // Ambil password saat ini
        $qPw = get_query($conn, "SELECT password FROM tbl_users WHERE id_user=$uid");
        $stored = $qPw->fetch_assoc()['password'];

        $valid = password_verify($pw_lama, $stored) || md5($pw_lama) === $stored;

        if (!$valid) {
            $errorMsg = 'Password lama tidak sesuai.';
        } elseif (strlen($pw_baru) < 6) {
            $errorMsg = 'Password baru minimal 6 karakter.';
        } elseif ($pw_baru !== $pw_konfirm) {
            $errorMsg = 'Konfirmasi password tidak cocok.';
        } else {
            $hash = password_hash($pw_baru, PASSWORD_BCRYPT);
            get_query($conn, "UPDATE tbl_users SET password='$hash' WHERE id_user=$uid");
            $successMsg = 'Password berhasil diperbarui.';
        }
    }
}

// Ambil data user terkini
$uid  = (int)$_SESSION['user_id'];
$qMe  = get_query($conn, "SELECT username, nama_lengkap, role FROM tbl_users WHERE id_user=$uid");
$me   = $qMe->fetch_assoc();
?>

<div class="dashboard-content-wrapper">

    <?php if ($successMsg): ?>
    <div style="padding:12px 16px; background:var(--success-light); color:var(--success-color); border-radius:8px; margin-bottom:20px; font-weight:500; display:flex; align-items:center; gap:8px;">
        <i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($successMsg) ?>
    </div>
    <?php endif; ?>
    <?php if ($errorMsg): ?>
    <div class="flash-error"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errorMsg) ?></div>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; flex-wrap:wrap;">

        <!-- Card Profil -->
        <div class="chart-card">
            <h3 style="font-size:15px; font-weight:700; margin-bottom:20px;">
                <i class="fa-solid fa-user-circle" style="color:var(--primary-color); margin-right:8px;"></i>Informasi Akun
            </h3>

            <div style="text-align:center; margin-bottom:24px;">
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($me['nama_lengkap']) ?>&background=0284C7&color=fff&size=80&rounded=true"
                     alt="Avatar" style="border-radius:50%; border:3px solid var(--primary-color);">
                <div style="font-size:16px; font-weight:700; margin-top:10px;"><?= htmlspecialchars($me['nama_lengkap']) ?></div>
                <div style="font-size:13px; color:var(--text-muted);"><?= htmlspecialchars($me['username']) ?></div>
                <span style="display:inline-block; margin-top:6px; font-size:12px; font-weight:600; padding:3px 12px; border-radius:20px; background:var(--primary-light); color:var(--primary-color);">
                    <?= htmlspecialchars($me['role']) ?>
                </span>
            </div>

            <form method="POST">
                <input type="hidden" name="action" value="ubah_profil">
                <div class="form-group">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" class="form-control"
                           value="<?= htmlspecialchars($me['nama_lengkap']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($me['username']) ?>"
                           readonly style="background:#f1f5f9; cursor:not-allowed;">
                    <small style="color:var(--text-muted); font-size:12px;">Username tidak dapat diubah.</small>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;">
                    <i class="fa-solid fa-save"></i> Simpan Profil
                </button>
            </form>
        </div>

        <!-- Card Ubah Password -->
        <div class="chart-card">
            <h3 style="font-size:15px; font-weight:700; margin-bottom:20px;">
                <i class="fa-solid fa-lock" style="color:var(--warning-color); margin-right:8px;"></i>Ubah Password
            </h3>

            <form method="POST">
                <input type="hidden" name="action" value="ubah_password">
                <div class="form-group">
                    <label class="form-label">Password Saat Ini <span style="color:red">*</span></label>
                    <input type="password" name="password_lama" class="form-control" required placeholder="Masukkan password lama">
                </div>
                <div class="form-group">
                    <label class="form-label">Password Baru <span style="color:red">*</span></label>
                    <input type="password" name="password_baru" class="form-control" required placeholder="Min. 6 karakter">
                </div>
                <div class="form-group">
                    <label class="form-label">Konfirmasi Password Baru <span style="color:red">*</span></label>
                    <input type="password" name="password_konfirm" class="form-control" required placeholder="Ulangi password baru">
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%; background:var(--warning-color); border-color:var(--warning-color);">
                    <i class="fa-solid fa-key"></i> Perbarui Password
                </button>
            </form>
        </div>
    </div>
</div>
