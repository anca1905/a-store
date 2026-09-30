<?php
// action_profil.php — Handler: Upload Foto Profil
session_start();
include 'config.php';
include 'cek_sesi.php';

$uid = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php?page=pengaturan");
    exit;
}

$action = $_POST['action'] ?? '';

// ─── Upload Foto Profil ────────────────────────────────────────────────
if ($action === 'upload_foto_profil') {
    if (empty($_FILES['foto_profil']['name'])) {
        header("Location: index.php?page=pengaturan&error=" . urlencode("Pilih file foto terlebih dahulu."));
        exit;
    }

    $file = $_FILES['foto_profil'];
    $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!in_array($ext, $allowed)) {
        header("Location: index.php?page=pengaturan&error=" . urlencode("Format file tidak didukung. Gunakan JPG, PNG, GIF, atau WEBP."));
        exit;
    }

    if ($file['size'] > 2 * 1024 * 1024) {
        header("Location: index.php?page=pengaturan&error=" . urlencode("Ukuran file maksimal 2MB."));
        exit;
    }

    $targetDir = __DIR__ . '/assets/img/profil/';
    if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

    // Hapus foto lama jika ada
    $qOld = get_query($conn, "SELECT foto_profil FROM tbl_users WHERE id_user=$uid");
    $oldData = $qOld->fetch_assoc();
    if (!empty($oldData['foto_profil'])) {
        $oldFile = $targetDir . $oldData['foto_profil'];
        if (file_exists($oldFile)) @unlink($oldFile);
    }

    $newName = 'profil_' . $uid . '_' . time() . '.' . $ext;
    $dest    = $targetDir . $newName;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        header("Location: index.php?page=pengaturan&error=" . urlencode("Gagal mengupload foto. Coba lagi."));
        exit;
    }

    $safeNew = $conn->real_escape_string($newName);
    get_query($conn, "UPDATE tbl_users SET foto_profil='$safeNew' WHERE id_user=$uid");
    $_SESSION['foto_profil'] = $newName;

    header("Location: index.php?page=pengaturan&msg=" . urlencode("Foto profil berhasil diperbarui."));
    exit;
}

// ─── Hapus Foto Profil ────────────────────────────────────────────────
if ($action === 'hapus_foto_profil') {
    $qOld = get_query($conn, "SELECT foto_profil FROM tbl_users WHERE id_user=$uid");
    $oldData = $qOld->fetch_assoc();
    if (!empty($oldData['foto_profil'])) {
        $oldFile = __DIR__ . '/assets/img/profil/' . $oldData['foto_profil'];
        if (file_exists($oldFile)) @unlink($oldFile);
    }
    get_query($conn, "UPDATE tbl_users SET foto_profil=NULL WHERE id_user=$uid");
    $_SESSION['foto_profil'] = null;

    header("Location: index.php?page=pengaturan&msg=" . urlencode("Foto profil berhasil dihapus."));
    exit;
}

// Fallback
header("Location: index.php?page=pengaturan");
exit;
