<?php
session_start();
session_unset();
session_destroy();
header("Location: login.php?msg=" . urlencode("Anda telah berhasil keluar dari sistem."));
exit;
?>
