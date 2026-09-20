<?php
session_start();
include 'config.php';
include 'cek_sesi.php';
include 'cek_role_pimpinan.php';

// Fungsi upload foto
function uploadFotoKategori($file) {
    $targetDir = __DIR__ . "/assets/img/kategori/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

    $fileName = basename($file["name"]);
    $fileName = preg_replace('/[^a-zA-Z0-9\.\-_]/', '_', $fileName);
    $fileName = time() . '_' . $fileName;
    $targetPath = $targetDir . $fileName;
    $ext = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));

    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
        if (move_uploaded_file($file["tmp_name"], $targetPath)) return $fileName;
    }
    return null;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action == 'insert') {
        $nama = $conn->real_escape_string(trim($_POST['nama_kategori']));
        if (empty($nama)) {
            header("Location: index.php?page=kategori&error=" . urlencode('Nama kategori tidak boleh kosong.'));
            exit;
        }
        $cek = get_query($conn, "SELECT id_kategori FROM tbl_kategori WHERE nama_kategori='$nama'");
        if ($cek->num_rows > 0) {
            header("Location: index.php?page=kategori&error=" . urlencode("Kategori '$nama' sudah ada."));
            exit;
        }
        
        $foto_sql = 'NULL';
        if (!empty($_FILES['foto_kategori']['name'])) {
            $up = uploadFotoKategori($_FILES['foto_kategori']);
            if ($up) $foto_sql = "'" . $conn->real_escape_string($up) . "'";
        }

        get_query($conn, "INSERT INTO tbl_kategori (nama_kategori, foto_kategori) VALUES ('$nama', $foto_sql)");
        header("Location: index.php?page=kategori&msg=" . urlencode('Kategori berhasil ditambahkan.'));
        exit;

    } elseif ($action == 'update') {
        $id   = (int)$_POST['id_kategori'];
        $nama = $conn->real_escape_string(trim($_POST['nama_kategori']));
        
        $fotoSql = '';
        if (!empty($_FILES['foto_kategori']['name'])) {
            $up = uploadFotoKategori($_FILES['foto_kategori']);
            if ($up) {
                // Hapus foto lama
                $qOld = get_query($conn, "SELECT foto_kategori FROM tbl_kategori WHERE id_kategori=$id");
                $rOld = $qOld->fetch_assoc();
                if ($rOld && $rOld['foto_kategori']) {
                    $oldPath = __DIR__ . "/assets/img/kategori/" . $rOld['foto_kategori'];
                    if (file_exists($oldPath)) unlink($oldPath);
                }
                $fotoSql = ", foto_kategori='" . $conn->real_escape_string($up) . "'";
            }
        }
        
        get_query($conn, "UPDATE tbl_kategori SET nama_kategori='$nama' $fotoSql WHERE id_kategori=$id");
        header("Location: index.php?page=kategori&msg=" . urlencode('Kategori berhasil diperbarui.'));
        exit;

    } elseif ($action == 'delete') {
        $id = (int)$_POST['id_kategori'];
        $qOld = get_query($conn, "SELECT foto_kategori FROM tbl_kategori WHERE id_kategori=$id");
        if ($qOld->num_rows > 0) {
            $rOld = $qOld->fetch_assoc();
            if ($rOld['foto_kategori']) {
                $oldPath = __DIR__ . "/assets/img/kategori/" . $rOld['foto_kategori'];
                if (file_exists($oldPath)) unlink($oldPath);
            }
        }
        get_query($conn, "DELETE FROM tbl_kategori WHERE id_kategori=$id");
        header("Location: index.php?page=kategori&msg=" . urlencode('Kategori berhasil dihapus.'));
        exit;
    }
}
header("Location: index.php?page=kategori");
exit;
?>
