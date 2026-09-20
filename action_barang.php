<?php
session_start();
include 'config.php';
include 'cek_sesi.php';

// Fungsi upload foto
function uploadFoto($file) {
    $targetDir = __DIR__ . "/assets/img/barang/";
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

// Guard: hanya Pimpinan
if (!isPimpinan()) {
    header("Location: index.php?error=" . urlencode("Akses ditolak."));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    // ─── INSERT ──────────────────────────────────────────────────────
    if ($action == 'insert') {
        $kode_barang = $conn->real_escape_string(trim($_POST['kode_barang']));
        $nama        = $conn->real_escape_string(trim($_POST['nama_produk']));
        $kategori    = $conn->real_escape_string(trim($_POST['kategori']));
        $satuan      = $conn->real_escape_string(trim($_POST['satuan'] ?? 'Pcs'));
        $harga_beli  = (int)($_POST['harga_beli'] ?? 0);
        $harga_jual  = (int)($_POST['harga_jual'] ?? 0);
        $stok_aktual = (int)($_POST['stok_aktual'] ?? 0);
        $stok_min    = (int)($_POST['stok_min'] ?? 0);
        $deskripsi   = trim($_POST['deskripsi'] ?? '');
        $merk        = trim($_POST['merk'] ?? '');
        $warna       = trim($_POST['warna'] ?? '');
        
        $tambahan = [];
        if ($merk !== '') $tambahan[] = "Merk: $merk";
        if ($warna !== '') $tambahan[] = "Warna: $warna";
        if (!empty($tambahan)) {
            $deskripsi .= ($deskripsi !== '' ? "\n" : "") . implode(", ", $tambahan);
        }
        $deskripsi = $conn->real_escape_string($deskripsi);

        $foto_sql = 'NULL';
        if (!empty($_FILES['foto_barang']['name'])) {
            $up = uploadFoto($_FILES['foto_barang']);
            if ($up) $foto_sql = "'" . $conn->real_escape_string($up) . "'";
        }

        $cek = get_query($conn, "SELECT id_barang FROM tbl_barang WHERE kode_barang='$kode_barang'");
        if ($cek->num_rows > 0) {
            header("Location: index.php?page=barang&error=" . urlencode("Kode '$kode_barang' sudah terdaftar."));
            exit;
        }

        $sql = "INSERT INTO tbl_barang
                    (kode_barang, nama_produk, kategori, satuan, harga_beli, harga_jual, stok_min, stok_aktual, deskripsi, foto_barang)
                VALUES
                    ('$kode_barang','$nama','$kategori','$satuan',$harga_beli,$harga_jual,$stok_min,$stok_aktual,'$deskripsi',$foto_sql)";
        get_query($conn, $sql);
        header("Location: index.php?page=barang&msg=" . urlencode("Barang berhasil ditambahkan."));
        exit;

    // ─── UPDATE ──────────────────────────────────────────────────────
    } elseif ($action == 'update') {
        $id          = (int)$_POST['id_barang'];
        $nama        = $conn->real_escape_string(trim($_POST['nama_produk']));
        $kategori    = $conn->real_escape_string(trim($_POST['kategori']));
        $satuan      = $conn->real_escape_string(trim($_POST['satuan'] ?? 'Pcs'));
        $harga_beli  = (int)($_POST['harga_beli'] ?? 0);
        $harga_jual  = (int)($_POST['harga_jual'] ?? 0);
        $stok_aktual = (int)($_POST['stok_aktual'] ?? 0);
        $stok_min    = (int)($_POST['stok_min'] ?? 0);
        $deskripsi   = trim($_POST['deskripsi'] ?? '');
        $merk        = trim($_POST['merk'] ?? '');
        $warna       = trim($_POST['warna'] ?? '');
        
        $tambahan = [];
        if ($merk !== '') $tambahan[] = "Merk: $merk";
        if ($warna !== '') $tambahan[] = "Warna: $warna";
        if (!empty($tambahan)) {
            $deskripsi .= ($deskripsi !== '' ? "\n" : "") . implode(", ", $tambahan);
        }
        $deskripsi = $conn->real_escape_string($deskripsi);

        $fotoSql = '';
        if (!empty($_FILES['foto_barang']['name'])) {
            $up = uploadFoto($_FILES['foto_barang']);
            if ($up) {
                // Hapus foto lama
                $qOld = get_query($conn, "SELECT foto_barang FROM tbl_barang WHERE id_barang=$id");
                $rOld = $qOld->fetch_assoc();
                if ($rOld && $rOld['foto_barang']) {
                    $oldPath = __DIR__ . "/assets/img/barang/" . $rOld['foto_barang'];
                    if (file_exists($oldPath)) unlink($oldPath);
                }
                $fotoSql = ", foto_barang='" . $conn->real_escape_string($up) . "'";
            }
        }

        $sql = "UPDATE tbl_barang
                SET nama_produk='$nama', kategori='$kategori', satuan='$satuan',
                    harga_beli=$harga_beli, harga_jual=$harga_jual,
                    stok_min=$stok_min, stok_aktual=$stok_aktual, deskripsi='$deskripsi' $fotoSql
                WHERE id_barang=$id";
        get_query($conn, $sql);
        header("Location: index.php?page=barang&msg=" . urlencode("Barang berhasil diperbarui."));
        exit;

    // ─── DELETE ──────────────────────────────────────────────────────
    } elseif ($action == 'delete') {
        $id = (int)$_POST['id_barang'];

        $qOld = get_query($conn, "SELECT foto_barang FROM tbl_barang WHERE id_barang=$id");
        if ($qOld->num_rows > 0) {
            $rOld = $qOld->fetch_assoc();
            if ($rOld['foto_barang']) {
                $oldPath = __DIR__ . "/assets/img/barang/" . $rOld['foto_barang'];
                if (file_exists($oldPath)) unlink($oldPath);
            }
        }

        get_query($conn, "DELETE FROM tbl_barang WHERE id_barang=$id");
        header("Location: index.php?page=barang&msg=" . urlencode("Barang berhasil dihapus."));
        exit;
    }
}

header("Location: index.php?page=barang");
exit;
?>
