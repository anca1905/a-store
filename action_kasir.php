<?php
session_start();
include 'config.php';
include 'cek_sesi.php';
include 'cek_role_pimpinan.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action == 'insert') {
        $username     = $conn->real_escape_string(trim($_POST['username']));
        $nama_lengkap = $conn->real_escape_string(trim($_POST['nama_lengkap']));
        $password_raw = trim($_POST['password']);

        if (empty($username) || empty($nama_lengkap) || empty($password_raw)) {
            header("Location: index.php?page=kelola_kasir&error=" . urlencode('Semua field wajib diisi.'));
            exit;
        }

        $cek = get_query($conn, "SELECT id_user FROM tbl_users WHERE username='$username'");
        if ($cek->num_rows > 0) {
            header("Location: index.php?page=kelola_kasir&error=" . urlencode("Username '$username' sudah digunakan."));
            exit;
        }

        $password = password_hash($password_raw, PASSWORD_BCRYPT);
        get_query($conn, "INSERT INTO tbl_users (username, password, nama_lengkap, role) VALUES ('$username', '$password', '$nama_lengkap', 'Kasir')");
        header("Location: index.php?page=kelola_kasir&msg=" . urlencode('Akun Kasir berhasil ditambahkan.'));
        exit;

    } elseif ($action == 'update') {
        $id           = (int)$_POST['id_user'];
        $nama_lengkap = $conn->real_escape_string(trim($_POST['nama_lengkap']));
        $password_raw = trim($_POST['password'] ?? '');

        if (!empty($password_raw)) {
            $password = password_hash($password_raw, PASSWORD_BCRYPT);
            get_query($conn, "UPDATE tbl_users SET nama_lengkap='$nama_lengkap', password='$password' WHERE id_user=$id AND role='Kasir'");
        } else {
            get_query($conn, "UPDATE tbl_users SET nama_lengkap='$nama_lengkap' WHERE id_user=$id AND role='Kasir'");
        }
        header("Location: index.php?page=kelola_kasir&msg=" . urlencode('Data Kasir berhasil diperbarui.'));
        exit;

    } elseif ($action == 'delete') {
        $id = (int)$_POST['id_user'];
        if ($id == $_SESSION['user_id']) {
            header("Location: index.php?page=kelola_kasir&error=" . urlencode('Tidak dapat menghapus akun sendiri.'));
            exit;
        }
        get_query($conn, "DELETE FROM tbl_users WHERE id_user=$id AND role='Kasir'");
        header("Location: index.php?page=kelola_kasir&msg=" . urlencode('Akun Kasir berhasil dihapus.'));
        exit;
    }
}
header("Location: index.php?page=kelola_kasir");
exit;
?>
