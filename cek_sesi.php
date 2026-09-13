<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=" . urlencode("Silakan login terlebih dahulu untuk mengakses sistem."));
    exit;
}

// Helper: cek apakah user memiliki role tertentu
function isRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// Helper: cek apakah user adalah Pimpinan
function isPimpinan() {
    return isRole('Pimpinan');
}

// Helper: cek apakah user adalah Kasir
function isKasir() {
    return isRole('Kasir');
}
?>
