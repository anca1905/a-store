<?php
require 'config.php';
$conn->query("ALTER TABLE tbl_kategori ADD COLUMN foto_kategori VARCHAR(255) DEFAULT NULL");
echo "Migration done";
?>
