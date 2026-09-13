<?php
if (!isPimpinan()) {
    header("Location: index.php?error=" . urlencode("Akses ditolak. Halaman ini hanya untuk Pimpinan."));
    exit;
}
?>
