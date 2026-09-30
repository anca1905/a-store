<?php
// Halaman: Pengaturan Akun
$successMsg = '';
$errorMsg   = '';

// Auto-migrate kolom foto_profil jika belum ada
$checkFoto = $conn->query("SHOW COLUMNS FROM tbl_users LIKE 'foto_profil'");
if ($checkFoto && $checkFoto->num_rows == 0) {
    $conn->query("ALTER TABLE tbl_users ADD COLUMN foto_profil VARCHAR(255) DEFAULT NULL AFTER nama_lengkap");
}

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
        $pw_lama    = $_POST['password_lama']   ?? '';
        $pw_baru    = $_POST['password_baru']   ?? '';
        $pw_konfirm = $_POST['password_konfirm'] ?? '';
        $uid = (int)$_SESSION['user_id'];

        $qPw   = get_query($conn, "SELECT password FROM tbl_users WHERE id_user=$uid");
        $stored = $qPw->fetch_assoc()['password'];
        $valid  = password_verify($pw_lama, $stored) || md5($pw_lama) === $stored;

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

// Flash messages dari redirect (action_profil.php)
if (!$successMsg && isset($_GET['msg']))   $successMsg = htmlspecialchars($_GET['msg']);
if (!$errorMsg   && isset($_GET['error'])) $errorMsg   = htmlspecialchars($_GET['error']);

// Ambil data user terkini
$uid = (int)$_SESSION['user_id'];
$qMe = get_query($conn, "SELECT username, nama_lengkap, role, foto_profil FROM tbl_users WHERE id_user=$uid");
$me  = $qMe->fetch_assoc();

// Sinkronisasi foto_profil ke session
$_SESSION['foto_profil'] = $me['foto_profil'] ?? null;

// Tentukan URL avatar
$fotoProfil = $me['foto_profil'] ?? null;
if ($fotoProfil && file_exists(__DIR__ . '/../../assets/img/profil/' . $fotoProfil)) {
    $avatarSrc = 'assets/img/profil/' . htmlspecialchars($fotoProfil);
} else {
    $avatarSrc = 'https://ui-avatars.com/api/?name=' . urlencode($me['nama_lengkap']) . '&background=0284C7&color=fff&size=120&rounded=true';
}
?>

<div class="dashboard-content-wrapper">

    <?php if ($successMsg): ?>
    <div style="padding:12px 16px; background:var(--success-light); color:var(--success-color); border-radius:8px; margin-bottom:20px; font-weight:500; display:flex; align-items:center; gap:8px;">
        <i class="fa-solid fa-check-circle"></i> <?= $successMsg ?>
    </div>
    <?php endif; ?>
    <?php if ($errorMsg): ?>
    <div class="flash-error"><i class="fa-solid fa-circle-exclamation"></i> <?= $errorMsg ?></div>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px;">

        <!-- ═══ Card Profil ═══ -->
        <div class="chart-card">
            <h3 style="font-size:15px; font-weight:700; margin-bottom:20px;">
                <i class="fa-solid fa-user-circle" style="color:var(--primary-color); margin-right:8px;"></i>Informasi Akun
            </h3>

            <!-- Avatar + Upload Foto -->
            <div style="text-align:center; margin-bottom:24px;">
                <div style="position:relative; display:inline-block;">
                    <img id="previewAvatar" src="<?= $avatarSrc ?>" alt="Avatar"
                         style="width:100px; height:100px; border-radius:50%; object-fit:cover; border:3px solid var(--primary-color); box-shadow:0 2px 12px rgba(2,132,199,.25);">
                    <!-- Tombol kamera overlay -->
                    <label for="inputFotoProfil" title="Ganti Foto"
                           style="position:absolute; bottom:2px; right:2px; background:var(--primary-color); color:#fff; width:28px; height:28px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; box-shadow:0 2px 6px rgba(0,0,0,.25); transition:background .2s;"
                           onmouseover="this.style.background='#0369a1'" onmouseout="this.style.background='var(--primary-color)'">
                        <i class="fa-solid fa-camera" style="font-size:12px;"></i>
                    </label>
                </div>

                <div style="font-size:16px; font-weight:700; margin-top:10px;"><?= htmlspecialchars($me['nama_lengkap']) ?></div>
                <div style="font-size:13px; color:var(--text-muted);"><?= htmlspecialchars($me['username']) ?></div>
                <span style="display:inline-block; margin-top:6px; font-size:12px; font-weight:600; padding:3px 12px; border-radius:20px; background:var(--primary-light); color:var(--primary-color);">
                    <?= htmlspecialchars($me['role']) ?>
                </span>

                <?php if ($fotoProfil): ?>
                <div style="margin-top:10px;">
                    <form action="action_profil.php" method="POST"
                          onsubmit="return confirm('Hapus foto profil?');">
                        <input type="hidden" name="action" value="hapus_foto_profil">
                        <button type="submit" style="background:none; border:none; color:var(--danger-color); font-size:12px; cursor:pointer; display:inline-flex; align-items:center; gap:4px;">
                            <i class="fa-solid fa-trash-can" style="font-size:11px;"></i> Hapus Foto
                        </button>
                    </form>
                </div>
                <?php endif; ?>
            </div>

            <!-- Form Upload Foto (hidden, submit otomatis saat file dipilih) -->
            <form action="action_profil.php" method="POST" enctype="multipart/form-data" id="formUploadFoto">
                <input type="hidden" name="action" value="upload_foto_profil">
                <input type="file" name="foto_profil" id="inputFotoProfil" accept="image/*"
                       style="display:none;" onchange="previewAndUpload(this)">
            </form>

            <!-- Form Edit Nama -->
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

        <!-- ═══ Card Ubah Password ═══ -->
        <div class="chart-card">
            <h3 style="font-size:15px; font-weight:700; margin-bottom:20px;">
                <i class="fa-solid fa-lock" style="color:var(--warning-color); margin-right:8px;"></i>Ubah Password
            </h3>

            <form method="POST">
                <input type="hidden" name="action" value="ubah_password">
                <div class="form-group">
                    <label class="form-label">Password Saat Ini <span style="color:red">*</span></label>
                    <div style="position:relative;">
                        <input type="password" name="password_lama" id="pwLama" class="form-control" required placeholder="Masukkan password lama" style="padding-right:42px;">
                        <button type="button" onclick="togglePw('pwLama','eyeLama')"
                                style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--text-muted); cursor:pointer;">
                            <i class="fa-solid fa-eye" id="eyeLama"></i>
                        </button>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Password Baru <span style="color:red">*</span></label>
                    <div style="position:relative;">
                        <input type="password" name="password_baru" id="pwBaru" class="form-control" required placeholder="Min. 6 karakter" style="padding-right:42px;">
                        <button type="button" onclick="togglePw('pwBaru','eyeBaru')"
                                style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--text-muted); cursor:pointer;">
                            <i class="fa-solid fa-eye" id="eyeBaru"></i>
                        </button>
                    </div>
                    <!-- Strength bar -->
                    <div id="strengthBar" style="margin-top:6px; height:4px; border-radius:4px; background:#e2e8f0; overflow:hidden;">
                        <div id="strengthFill" style="height:100%; width:0; border-radius:4px; transition:width .3s,background .3s;"></div>
                    </div>
                    <small id="strengthText" style="font-size:11px; color:var(--text-muted);"></small>
                </div>
                <div class="form-group">
                    <label class="form-label">Konfirmasi Password Baru <span style="color:red">*</span></label>
                    <div style="position:relative;">
                        <input type="password" name="password_konfirm" id="pwKonfirm" class="form-control" required placeholder="Ulangi password baru" style="padding-right:42px;">
                        <button type="button" onclick="togglePw('pwKonfirm','eyeKonfirm')"
                                style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--text-muted); cursor:pointer;">
                            <i class="fa-solid fa-eye" id="eyeKonfirm"></i>
                        </button>
                    </div>
                    <small id="matchText" style="font-size:11px;"></small>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%; background:var(--warning-color); border-color:var(--warning-color);">
                    <i class="fa-solid fa-key"></i> Perbarui Password
                </button>
            </form>
        </div>
    </div>
</div>

<script>
// ── Preview & upload foto langsung saat file dipilih ──
function previewAndUpload(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    // Preview lokal sebelum upload
    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('previewAvatar').src = e.target.result;
    };
    reader.readAsDataURL(file);
    // Submit form upload
    document.getElementById('formUploadFoto').submit();
}

// ── Toggle show/hide password ──
function togglePw(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fa-solid fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fa-solid fa-eye';
    }
}

// ── Password strength meter ──
document.getElementById('pwBaru').addEventListener('input', function() {
    const val = this.value;
    let score = 0;
    if (val.length >= 6)  score++;
    if (val.length >= 10) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const fill   = document.getElementById('strengthFill');
    const text   = document.getElementById('strengthText');
    const levels = [
        { label: '',         color: '#e2e8f0', pct: '0%'  },
        { label: 'Sangat Lemah', color: '#ef4444', pct: '20%' },
        { label: 'Lemah',    color: '#f97316', pct: '40%' },
        { label: 'Cukup',   color: '#eab308', pct: '60%' },
        { label: 'Kuat',     color: '#22c55e', pct: '80%' },
        { label: 'Sangat Kuat', color: '#15803d', pct: '100%'},
    ];
    const lv = levels[score] || levels[0];
    fill.style.width      = lv.pct;
    fill.style.background = lv.color;
    text.textContent      = lv.label;
    text.style.color      = lv.color;
});

// ── Konfirmasi password match ──
document.getElementById('pwKonfirm').addEventListener('input', function() {
    const match = document.getElementById('matchText');
    if (this.value === document.getElementById('pwBaru').value) {
        match.textContent = '✓ Password cocok';
        match.style.color = 'var(--success-color)';
    } else {
        match.textContent = '✗ Password tidak cocok';
        match.style.color = 'var(--danger-color)';
    }
});
</script>
