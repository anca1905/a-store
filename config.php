<?php
// config.php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'db_astore';

// Connect using MySQLi
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    // If database doesn't exist, provide a helper link
    if($conn->connect_errno == 1049) {
        die("<h3>Database '$db' belum ada!</h3><p>Silakan rancang database-nya terlebih dahulu dengan menjalankan <a href='setup.php' style='color:blue; text-decoration:underline;'>setup.php</a></p>");
    } else {
        die("Koneksi Database Gagal: " . $conn->connect_error);
    }
}

// Function to safely execute queries
function get_query($conn, $query) {
    $result = $conn->query($query);
    if (!$result) {
        die("Query Error: " . $conn->error);
    }
    return $result;
}
?>
