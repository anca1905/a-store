<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $password_input = $_POST['password'];

    $sql = "SELECT id_user, nama_lengkap, role, password FROM tbl_users WHERE username='$username'";
    $result = get_query($conn, $sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verifikasi password (Bcrypt / MD5 legacy)
        if (password_verify($password_input, $user['password']) || md5($password_input) === $user['password']) {
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['role'] = $user['role'];
            
            header("Location: index.php");
            exit;
        } else {
            header("Location: login.php?error=" . urlencode("Username atau Password salah!"));
            exit;
        }
    } else {
        header("Location: login.php?error=" . urlencode("Username atau Password salah!"));
        exit;
    }
} else {
    header("Location: login.php");
}
?>
