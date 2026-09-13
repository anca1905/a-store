<?php
session_start();
include 'config.php';
include 'cek_sesi.php';
include 'cek_role_pimpinan.php';

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
        get_query($conn, "INSERT INTO tbl_kategori (nama_kategori) VALUES ('$nama')");
        header("Location: index.php?page=kategori&msg=" . urlencode('Kategori berhasil ditambahkan.'));
        exit;

    } elseif ($action == 'update') {
        $id   = (int)$_POST['id_kategori'];
        $nama = $conn->real_escape_string(trim($_POST['nama_kategori']));
        get_query($conn, "UPDATE tbl_kategori SET nama_kategori='$nama' WHERE id_kategori=$id");
        header("Location: index.php?page=kategori&msg=" . urlencode('Kategori berhasil diperbarui.'));
        exit;

    } elseif ($action == 'delete') {
        $id = (int)$_POST['id_kategori'];
        get_query($conn, "DELETE FROM tbl_kategori WHERE id_kategori=$id");
        header("Location: index.php?page=kategori&msg=" . urlencode('Kategori berhasil dihapus.'));
        exit;
    }
}
header("Location: index.php?page=kategori");
exit;
?>
