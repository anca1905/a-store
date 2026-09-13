<?php
// setup.php - Instalasi & Migrasi Database Otomatis
$host = 'localhost';
$user = 'root';
$pass = '';

$koneksi = new mysqli($host, $user, $pass);

if ($koneksi->connect_error) {
    die("<p style='color:red;'>Koneksi Gagal: " . htmlspecialchars($koneksi->connect_error) . "</p>");
}

echo "<h2>Setup Database Toko</h2>";

// =============================================
// 1. Buat Database
// =============================================
$db_name = "db_astore";
$sqlCreateDB = "CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($koneksi->query($sqlCreateDB) === TRUE) {
    echo "<p>&#10004; Database <b>" . htmlspecialchars($db_name) . "</b> berhasil dibuat atau sudah tersedia.</p>";
} else {
    die("<p style='color:red;'>Error creating database: " . htmlspecialchars($koneksi->error) . "</p>");
}

// 2. Pilih Database
$koneksi->select_db($db_name);

// =============================================
// 3. Buat Tabel tbl_users
// =============================================
$sqlUsers = "CREATE TABLE IF NOT EXISTS tbl_users (
    id_user       INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(50)  NOT NULL UNIQUE,
    password      VARCHAR(255) NOT NULL,
    nama_lengkap  VARCHAR(100) NOT NULL,
    role          VARCHAR(50)  DEFAULT 'Pimpinan'
)";
if ($koneksi->query($sqlUsers) === TRUE) {
    echo "<p>&#10004; Tabel <b>tbl_users</b> siap.</p>";

    // Seed user Pimpinan
    $cekPimpinan = $koneksi->query("SELECT id_user FROM tbl_users WHERE username = 'pimpinan'");
    if ($cekPimpinan && $cekPimpinan->num_rows == 0) {
        $passPimpinan = md5('pimpinan123');
        $koneksi->query("INSERT INTO tbl_users (username, password, nama_lengkap, role)
                         VALUES ('pimpinan', '$passPimpinan', 'Pimpinan Toko', 'Pimpinan')");
        echo "<p>&nbsp;&nbsp;&#8627; User <b>pimpinan</b> default berhasil ditambahkan.</p>";
    } else {
        echo "<p>&nbsp;&nbsp;&#8627; User <b>pimpinan</b> sudah ada, dilewati.</p>";
    }

    // Seed user Kasir
    $cekKasir = $koneksi->query("SELECT id_user FROM tbl_users WHERE username = 'kasir'");
    if ($cekKasir && $cekKasir->num_rows == 0) {
        $passKasir = md5('kasir123');
        $koneksi->query("INSERT INTO tbl_users (username, password, nama_lengkap, role)
                         VALUES ('kasir', '$passKasir', 'Kasir Toko', 'Kasir')");
        echo "<p>&nbsp;&nbsp;&#8627; User <b>kasir</b> default berhasil ditambahkan.</p>";
    } else {
        echo "<p>&nbsp;&nbsp;&#8627; User <b>kasir</b> sudah ada, dilewati.</p>";
    }
} else {
    echo "<p style='color:red;'>Error tbl_users: " . htmlspecialchars($koneksi->error) . "</p>";
}

// =============================================
// 4. Buat Tabel tbl_kategori (BARU)
// =============================================
$sqlKategori = "CREATE TABLE IF NOT EXISTS tbl_kategori (
    id_kategori   INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL UNIQUE
)";
if ($koneksi->query($sqlKategori) === TRUE) {
    echo "<p>&#10004; Tabel <b>tbl_kategori</b> siap.</p>";

    // Seed kategori default
    $kategoriDefault = ['Kebutuhan Pokok', 'Bumbu Dapur', 'Minuman', 'Makanan Instan', 'Susu'];
    foreach ($kategoriDefault as $kat) {
        $katEsc = $koneksi->real_escape_string($kat);
        $cekKat = $koneksi->query("SELECT id_kategori FROM tbl_kategori WHERE nama_kategori = '$katEsc'");
        if ($cekKat && $cekKat->num_rows == 0) {
            $koneksi->query("INSERT INTO tbl_kategori (nama_kategori) VALUES ('$katEsc')");
        }
    }
    echo "<p>&nbsp;&nbsp;&#8627; Kategori default berhasil di-seed.</p>";
} else {
    echo "<p style='color:red;'>Error tbl_kategori: " . htmlspecialchars($koneksi->error) . "</p>";
}

// =============================================
// 5. Buat Tabel tbl_barang (struktur baru)
// =============================================
$sqlBarang = "CREATE TABLE IF NOT EXISTS tbl_barang (
    id_barang    INT AUTO_INCREMENT PRIMARY KEY,
    kode_barang  VARCHAR(20)  NOT NULL UNIQUE,
    nama_produk  VARCHAR(100) NOT NULL,
    kategori     VARCHAR(50)  NOT NULL,
    harga_beli   INT          NOT NULL DEFAULT 0,
    harga_jual   INT          NOT NULL DEFAULT 0,
    stok_min     INT          NOT NULL DEFAULT 0,
    satuan       VARCHAR(20)  DEFAULT 'Pcs',
    deskripsi    TEXT         DEFAULT NULL,
    stok_aktual  INT          NOT NULL DEFAULT 0,
    foto_barang  VARCHAR(255) DEFAULT NULL
)";
if ($koneksi->query($sqlBarang) === TRUE) {
    echo "<p>&#10004; Tabel <b>tbl_barang</b> siap.</p>";
} else {
    echo "<p style='color:red;'>Error tbl_barang: " . htmlspecialchars($koneksi->error) . "</p>";
}

// =============================================
// 6. Migrasi kolom tbl_barang (safe ALTER)
// =============================================

// --- Migrasi harga_beli & harga_jual ---
$checkHargaBeli = $koneksi->query("SHOW COLUMNS FROM tbl_barang LIKE 'harga_beli'");
if ($checkHargaBeli && $checkHargaBeli->num_rows == 0) {
    $koneksi->query("ALTER TABLE tbl_barang ADD COLUMN harga_beli INT NOT NULL DEFAULT 0 AFTER kategori");
    $koneksi->query("ALTER TABLE tbl_barang ADD COLUMN harga_jual INT NOT NULL DEFAULT 0 AFTER harga_beli");

    // Migrasi data lama: jika kolom harga masih ada, copy nilainya ke harga_jual
    $checkHargaLama = $koneksi->query("SHOW COLUMNS FROM tbl_barang LIKE 'harga'");
    if ($checkHargaLama && $checkHargaLama->num_rows > 0) {
        $koneksi->query("UPDATE tbl_barang SET harga_jual = harga");
        echo "<p>&#10004; Data kolom <b>harga</b> lama berhasil dimigrasikan ke <b>harga_jual</b>.</p>";
    }
    echo "<p>&#10004; Kolom <b>harga_beli</b> &amp; <b>harga_jual</b> berhasil ditambahkan ke tbl_barang.</p>";
} else {
    echo "<p>&nbsp;&nbsp;&#8627; Kolom <b>harga_beli</b> sudah ada di tbl_barang, dilewati.</p>";
}

// --- Migrasi satuan & deskripsi ---
$checkSatuan = $koneksi->query("SHOW COLUMNS FROM tbl_barang LIKE 'satuan'");
if ($checkSatuan && $checkSatuan->num_rows == 0) {
    $koneksi->query("ALTER TABLE tbl_barang ADD COLUMN satuan VARCHAR(20) DEFAULT 'Pcs' AFTER stok_min");
    $koneksi->query("ALTER TABLE tbl_barang ADD COLUMN deskripsi TEXT DEFAULT NULL AFTER satuan");
    echo "<p>&#10004; Kolom <b>satuan</b> &amp; <b>deskripsi</b> berhasil ditambahkan ke tbl_barang.</p>";
} else {
    echo "<p>&nbsp;&nbsp;&#8627; Kolom <b>satuan</b> sudah ada di tbl_barang, dilewati.</p>";
}

// =============================================
// 7. Buat Tabel tbl_penjualan
// =============================================
$sqlPenjualan = "CREATE TABLE IF NOT EXISTS tbl_penjualan (
    id_penjualan  INT AUTO_INCREMENT PRIMARY KEY,
    no_faktur     VARCHAR(30) NOT NULL UNIQUE,
    tanggal_waktu DATETIME    NOT NULL,
    total_item    INT         NOT NULL,
    grand_total   INT         NOT NULL,
    nominal_bayar INT         NOT NULL,
    kembalian     INT         NOT NULL
)";
if ($koneksi->query($sqlPenjualan) === TRUE) {
    echo "<p>&#10004; Tabel <b>tbl_penjualan</b> siap.</p>";
} else {
    echo "<p style='color:red;'>Error tbl_penjualan: " . htmlspecialchars($koneksi->error) . "</p>";
}

// =============================================
// 8. Buat Tabel tbl_detail_penjualan
// =============================================
$sqlDetail = "CREATE TABLE IF NOT EXISTS tbl_detail_penjualan (
    id_detail    INT AUTO_INCREMENT PRIMARY KEY,
    id_penjualan INT         NOT NULL,
    kode_barang  VARCHAR(20) NOT NULL,
    harga_satuan INT         NOT NULL,
    qty          INT         NOT NULL,
    subtotal     INT         NOT NULL,
    FOREIGN KEY (id_penjualan) REFERENCES tbl_penjualan(id_penjualan) ON DELETE CASCADE
)";
if ($koneksi->query($sqlDetail) === TRUE) {
    echo "<p>&#10004; Tabel <b>tbl_detail_penjualan</b> siap.</p>";
} else {
    echo "<p style='color:red;'>Error tbl_detail_penjualan: " . htmlspecialchars($koneksi->error) . "</p>";
}

// =============================================
// 9. Seed Dummy Data tbl_barang (jika kosong)
// =============================================
$cekBarang = $koneksi->query("SELECT COUNT(*) as count FROM tbl_barang");
$rowBarang  = $cekBarang->fetch_assoc();
if ($rowBarang['count'] == 0) {
    $sqlInsertBarang = "INSERT INTO tbl_barang
        (kode_barang, nama_produk, kategori, harga_beli, harga_jual, stok_min, satuan, stok_aktual, foto_barang)
        VALUES
        ('BRG-001', 'Minyak Goreng 2L',       'Kebutuhan Pokok', 28000,  32000,  50,  'Botol',   145, NULL),
        ('BRG-002', 'Beras Premium 5kg',       'Kebutuhan Pokok', 65000,  75000,  100, 'Karung',  150, NULL),
        ('BRG-003', 'Gula Pasir 1kg',          'Bumbu Dapur',     13000,  16000,  50,  'Pcs',     200, NULL),
        ('BRG-004', 'Susu Kental Manis',       'Minuman',          9500,  12000,  20,  'Kaleng',   12, NULL),
        ('BRG-005', 'Indomie Goreng (Kardus)', 'Makanan Instan',  95000, 115000,  10,  'Kardus',    8, NULL),
        ('BRG-006', 'Dancow Fortigro 800g',    'Susu',            72000,  85000,  15,  'Pcs',      30, NULL)";

    if ($koneksi->query($sqlInsertBarang) === TRUE) {
        echo "<p>&#10004; Dummy Data untuk <b>tbl_barang</b> berhasil tersimpan.</p>";
    } else {
        echo "<p style='color:red;'>Error Insert Barang: " . htmlspecialchars($koneksi->error) . "</p>";
    }
} else {
    echo "<p>&nbsp;&nbsp;&#8627; tbl_barang sudah berisi data, seed dummy dilewati.</p>";
}

// =============================================
// 10. Seed Dummy Data Historis Penjualan (jika kosong)
// =============================================
$cekJual = $koneksi->query("SELECT COUNT(*) as count FROM tbl_penjualan");
$rowJual  = $cekJual->fetch_assoc();
if ($rowJual['count'] == 0) {
    // Penjualan Januari
    $koneksi->query("INSERT INTO tbl_penjualan (id_penjualan, no_faktur, tanggal_waktu, total_item, grand_total, nominal_bayar, kembalian)
                     VALUES (1, 'INV-20260105-01', '2026-01-05 10:00:00', 120, 3840000, 4000000, 160000)");
    $koneksi->query("INSERT INTO tbl_detail_penjualan (id_penjualan, kode_barang, harga_satuan, qty, subtotal)
                     VALUES (1, 'BRG-001', 32000, 120, 3840000)");

    // Penjualan Februari
    $koneksi->query("INSERT INTO tbl_penjualan (id_penjualan, no_faktur, tanggal_waktu, total_item, grand_total, nominal_bayar, kembalian)
                     VALUES (2, 'INV-20260210-01', '2026-02-10 10:00:00', 150, 4800000, 5000000, 200000)");
    $koneksi->query("INSERT INTO tbl_detail_penjualan (id_penjualan, kode_barang, harga_satuan, qty, subtotal)
                     VALUES (2, 'BRG-001', 32000, 150, 4800000)");

    // Penjualan Maret
    $koneksi->query("INSERT INTO tbl_penjualan (id_penjualan, no_faktur, tanggal_waktu, total_item, grand_total, nominal_bayar, kembalian)
                     VALUES (3, 'INV-20260315-01', '2026-03-15 10:00:00', 180, 5760000, 6000000, 240000)");
    $koneksi->query("INSERT INTO tbl_detail_penjualan (id_penjualan, kode_barang, harga_satuan, qty, subtotal)
                     VALUES (3, 'BRG-001', 32000, 180, 5760000)");

    // Penjualan April
    $koneksi->query("INSERT INTO tbl_penjualan (id_penjualan, no_faktur, tanggal_waktu, total_item, grand_total, nominal_bayar, kembalian)
                     VALUES (4, 'INV-20260420-01', '2026-04-20 10:00:00', 140, 4480000, 4500000, 20000)");
    $koneksi->query("INSERT INTO tbl_detail_penjualan (id_penjualan, kode_barang, harga_satuan, qty, subtotal)
                     VALUES (4, 'BRG-001', 32000, 140, 4480000)");

    // Penjualan Mei
    $koneksi->query("INSERT INTO tbl_penjualan (id_penjualan, no_faktur, tanggal_waktu, total_item, grand_total, nominal_bayar, kembalian)
                     VALUES (5, 'INV-20260505-01', '2026-05-05 10:00:00', 160, 5120000, 5200000, 80000)");
    $koneksi->query("INSERT INTO tbl_detail_penjualan (id_penjualan, kode_barang, harga_satuan, qty, subtotal)
                     VALUES (5, 'BRG-001', 32000, 160, 5120000)");

    echo "<p>&#10004; Dummy Data historis <b>tbl_penjualan</b> berhasil tersimpan.</p>";
} else {
    echo "<p>&nbsp;&nbsp;&#8627; tbl_penjualan sudah berisi data, seed historis dilewati.</p>";
}

// =============================================
// Selesai
// =============================================
echo "<hr/>";
echo "<h3>Setup &amp; Migrasi Selesai!</h3>";
echo "<a href='index.php'>
        <button style='padding:10px 24px; font-size:16px; background:#0284C7; color:#fff; border:none; border-radius:6px; cursor:pointer;'>
          Buka Aplikasi
        </button>
      </a>";

$koneksi->close();
?>
