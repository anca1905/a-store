<?php
include 'config.php';
$dir = __DIR__ . '/assets/img/barang/';
$q = $conn->query("SELECT id_barang, foto_barang FROM tbl_barang WHERE foto_barang IS NOT NULL");
while ($row = $q->fetch_assoc()) {
    $old = $row['foto_barang'];
    if (preg_match('/[^a-zA-Z0-9\.\-_]/', $old)) {
        $new = preg_replace('/[^a-zA-Z0-9\.\-_]/', '_', $old);
        if (file_exists($dir . $old)) {
            rename($dir . $old, $dir . $new);
            $conn->query("UPDATE tbl_barang SET foto_barang='$new' WHERE id_barang={$row['id_barang']}");
            echo "Renamed $old to $new\n";
        }
    }
}
echo "Done";
