<?php
session_start();
include 'config.php';

// Fungsi upload foto
function uploadFoto($file) {
    $targetDir = __DIR__ . "/assets/img/barang/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

    $fileName = basename($file["name"]);
    $fileName = preg_replace('/[^a-zA-Z0-9\.\-_]/', '_', $fileName);
    $fileName = time() . '_' . $fileName;
    $targetPath = $targetDir . $fileName;
    $ext = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));

    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
        if (move_uploaded_file($file["tmp_name"], $targetPath)) return $fileName;
    }
    return null;
}

// Helper: Penentuan prefix kode barang otomatis mengikuti model yang diinput (mengambil kata depan produk)
function determinePrefixBarang($nama, $kategori) {
    $namaUpper = strtoupper(trim((string)$nama));
    $katUpper  = strtoupper(trim((string)$kategori));
    
    // 1. Ekstrak kata depan / token pertama dari nama barang
    $cleanTokens = preg_split('/[\s\/\-_]+/', $namaUpper);
    $rawFirstWord = !empty($cleanTokens[0]) ? preg_replace('/[^A-Z0-9]/', '', $cleanTokens[0]) : '';
    
    // Pemetaan brand / kata kunci agar kode konsisten dengan database
    // Menggunakan kata depan penuh yang konsisten (tanpa typo atau singkatan membingungkan)
    $brandAliases = [
        'CULTULAR'     => 'CULTULAR',
        'CULUTULAR'    => 'CULTULAR',
        'CULTURAL'     => 'CULTULAR',
        'CULTURE'      => 'CULTULAR',
        'MIDELHIGH'    => 'MIDELHIGH',
        'MIDEL'        => 'MIDELHIGH',
        'MIDLDLEHIGHT' => 'MIDELHIGH',
        'MIDDLEHIGH'   => 'MIDELHIGH',
        'AFD'          => 'AFD',
        'METAFORA'     => 'METAFORA',
        'METAFORE'     => 'METAFORA',
        'MSA'          => 'MSA',
        'MSACO'        => 'MSA',
        'KEMEJA'       => 'KEMEJA',
        'KMJA'         => 'KEMEJA',
        'KMEJA'        => 'KEMEJA',
        'HPND'         => 'HPND',
        'HPNDHODIEE'   => 'HPND',
        'WISH'         => 'WISH',
        'THREEWORLD'   => 'THREEWORLD',
        'FREAKY'       => 'FREAKY',
        'POLO'         => 'POLO',
        'JERSEY'       => 'JERSEY',
        'CINCIN'       => 'CINCIN',
        'CNCN'         => 'CINCIN',
        'KALUNG'       => 'KALUNG',
        'KLG'          => 'KALUNG',
        'SLINGBAG'     => 'SLINGBAG',
        'SLING'        => 'SLINGBAG',
        'SLGBAG'       => 'SLINGBAG',
        'KENSI'        => 'KENSI',
        'KNSI'         => 'KENSI',
        'RANSEL'       => 'RNSL',
        'SELEMPANG'    => 'SLMPNG',
        'PARFUM'       => 'PRFM',
        'SENDAL'       => 'SNDL',
        'HELM'         => 'HLM',
        'SEPATU'       => 'SPT',
        'TOPI'         => 'TPI',
        'CURDUROY'     => 'CRDUROY',
        'RAJUT'        => 'RAJUT',
        'KNITWEAR'     => 'RAJUT',
    ];

    // Cek jika kata pertama cocok dengan alias yang sudah dikenal
    if (!empty($rawFirstWord) && isset($brandAliases[$rawFirstWord])) {
        return $brandAliases[$rawFirstWord];
    }

    // Cek frasa multi-kata di bagian depan nama produk
    if (strpos($namaUpper, 'SLING BAG') === 0 || strpos($namaUpper, 'SLINGBAG') === 0) {
        return 'SLINGBAG';
    }
    if (strpos($namaUpper, 'LONG SLEEVE') === 0 || strpos($namaUpper, 'LONGSLEVE') === 0 || strpos($namaUpper, 'LNGSLEEVE') === 0) {
        return 'LONGSLEVE';
    }
    if (strpos($namaUpper, 'SHORT PANTS') === 0) {
        return 'SP';
    }
    if (strpos($namaUpper, 'LONG PANTS') === 0) {
        return 'LP';
    }

    // Jika kata depan adalah nama merk/model (panjang >= 2, bukan kata umum seperti TSHIRT, BAJU, CELANA)
    $ignoreWords = ['TSHIRT', 'SHIRT', 'BAJU', 'CELANA', 'OVERSIZE', 'BOXY', 'REGULER', 'ORIGINAL', 'CREWNECK'];
    if (!empty($rawFirstWord) && strlen($rawFirstWord) >= 2 && !in_array($rawFirstWord, $ignoreWords)) {
        return $rawFirstWord;
    }

    // Jika kata pertama adalah kata yang diabaikan (misal "TSHIRT CULTULAR ..."), cek kata kedua
    if (!empty($cleanTokens[1])) {
        $secondWord = preg_replace('/[^A-Z0-9]/', '', $cleanTokens[1]);
        if (isset($brandAliases[$secondWord])) {
            return $brandAliases[$secondWord];
        }
        if (strlen($secondWord) >= 2 && !in_array($secondWord, $ignoreWords)) {
            return $secondWord;
        }
    }
    
    // Cek kecocokan jika kata kunci ada di dalam nama (fallback)
    if (strpos($namaUpper, 'CULTULAR') !== false || strpos($namaUpper, 'CULUTULAR') !== false || strpos($namaUpper, 'CULTURAL') !== false) {
        return 'CULTULAR';
    }
    if (strpos($namaUpper, 'MIDELHIGH') !== false || strpos($namaUpper, 'MIDLDLEHIGHT') !== false) {
        return 'MIDELHIGH';
    }
    if (strpos($namaUpper, 'WISH') !== false) {
        return 'WISH';
    }

    // 2. Fallback: Standar prefix kategori
    $categoryDefaults = [
        'ATASAN'        => 'ATS',
        'SHORT PANTS'   => 'SP',
        'LONG PANTS'    => 'LP',
        'KEMEJA'        => 'KEMEJA',
        'HODIEE'        => 'HDE',
        'JERSEY'        => 'JERSEY',
        'SEPATU'        => 'SPT',
        'TOPI'          => 'TPI',
        'HELM'          => 'HLM',
        'SENDAL'        => 'SNDL',
        'PARFUM'        => 'PRFM',
        'RANSEL'        => 'RNSL',
        'TAS SELEMPANG' => 'SLMPNG',
        'SLING BAG'     => 'SLINGBAG',
        'KALUNG'        => 'KALUNG',
        'CINCIN'        => 'CINCIN',
        'BANDANA'       => 'BDN',
        'BLIND BOX'     => 'BLB',
        'DOMPET'        => 'DMP',
        'GANTUNGAN'     => 'GTG',
        'JAM TANGAN'    => 'JTG',
        'KACAMATA'      => 'KCM',
        'KOS KAKI'      => 'KSK',
        'STAINLESS'     => 'STL',
        'LONG SLEEVE'   => 'LONGSLEVE',
        'ACCESSORIES'   => 'ACC',
        'KERTAS'        => 'KTS',
    ];
    
    if (isset($categoryDefaults[$katUpper])) {
        return $categoryDefaults[$katUpper];
    }
    
    // 3. Default aman jika kategori belum terdaftar
    $cleanKat = preg_replace('/[^A-Z]/', '', $katUpper);
    return substr($cleanKat, 0, 4) ?: 'BRG';
}

function generateKodeBarangOtomatis($conn, $nama = '', $kategori = '', $customPrefix = '') {
    $prefix = '';
    if (!empty($customPrefix)) {
        // Hapus angka di akhir jika ada, dan bersihkan karakter
        $prefix = preg_replace('/[0-9]+$/', '', strtoupper(trim((string)$customPrefix)));
        $prefix = preg_replace('/[^A-Z0-9_]/', '', $prefix);
    }
    
    if (empty($prefix)) {
        $prefix = determinePrefixBarang($nama, $kategori);
    }
    
    $prefix = strtoupper(trim($prefix));
    $esc = $conn->real_escape_string($prefix);
    
    // Cari semua kode dengan prefix yang sama diikuti angka
    $res = $conn->query("SELECT kode_barang FROM tbl_barang WHERE kode_barang REGEXP '^" . $esc . "[0-9]+$'");
    $maxNum = 0;
    $digits = 2; // Format 2 digit (01, 02, 03) sesuai instruksi user
    
    if ($res && $res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            $code = $row['kode_barang'];
            if (preg_match('/^' . preg_quote($prefix, '/') . '(\\d+)$/', $code, $m)) {
                $num = (int)$m[1];
                if ($num > $maxNum) {
                    $maxNum = $num;
                    $digits = max(2, strlen($m[1]));
                }
            }
        }
    }
    
    $nextNum = $maxNum + 1;
    $padded = str_pad($nextNum, max(2, $digits), '0', STR_PAD_LEFT);
    $fullKode = $prefix . $padded;
    
    return [
        'kode_barang' => $fullKode,
        'prefix'      => $prefix,
        'next_num'    => $nextNum,
        'digits'      => $digits
    ];
}

// ─── AJAX ENDPOINT: GENERATE KODE OTOMATIS ─────────────────────────────
if (isset($_GET['ajax']) && $_GET['ajax'] == 'get_kode') {
    header('Content-Type: application/json; charset=utf-8');
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }
    $nama     = $_GET['nama'] ?? '';
    $kategori = $_GET['kategori'] ?? '';
    $prefix   = $_GET['prefix'] ?? '';
    
    $res = generateKodeBarangOtomatis($conn, $nama, $kategori, $prefix);
    echo json_encode([
        'status'      => 'success',
        'kode_barang' => $res['kode_barang'],
        'prefix'      => $res['prefix'],
        'next_num'    => $res['next_num']
    ]);
    exit;
}

include 'cek_sesi.php';

// Guard: hanya Pimpinan
if (!isPimpinan()) {
    header("Location: index.php?error=" . urlencode("Akses ditolak."));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    // ─── INSERT ──────────────────────────────────────────────────────
    if ($action == 'insert') {
        $kode_barang = $conn->real_escape_string(trim($_POST['kode_barang']));
        $nama        = $conn->real_escape_string(trim($_POST['nama_produk']));
        $kategori    = $conn->real_escape_string(trim($_POST['kategori']));
        $satuan      = $conn->real_escape_string(trim($_POST['satuan'] ?? 'Pcs'));
        $harga_beli  = (int)($_POST['harga_beli'] ?? 0);
        $harga_jual  = (int)($_POST['harga_jual'] ?? 0);
        $stok_aktual = (int)($_POST['stok_aktual'] ?? 0);
        $stok_min    = (int)($_POST['stok_min'] ?? 0);
        $deskripsi   = trim($_POST['deskripsi'] ?? '');
        $merk        = trim($_POST['merk'] ?? '');
        $warna       = trim($_POST['warna'] ?? '');
        
        $tambahan = [];
        if ($merk !== '') $tambahan[] = "Merk: $merk";
        if ($warna !== '') $tambahan[] = "Warna: $warna";
        if (!empty($tambahan)) {
            $deskripsi .= ($deskripsi !== '' ? "\n" : "") . implode(", ", $tambahan);
        }
        $deskripsi = $conn->real_escape_string($deskripsi);

        $foto_sql = 'NULL';
        if (!empty($_FILES['foto_barang']['name'])) {
            $up = uploadFoto($_FILES['foto_barang']);
            if ($up) $foto_sql = "'" . $conn->real_escape_string($up) . "'";
        }

        // Jika kode_barang kosong atau sudah terdaftar, generate otomatis berurutan
        if (empty($kode_barang)) {
            $gen = generateKodeBarangOtomatis($conn, $nama, $kategori);
            $kode_barang = $gen['kode_barang'];
        } else {
            $cek = get_query($conn, "SELECT id_barang FROM tbl_barang WHERE kode_barang='$kode_barang'");
            if ($cek->num_rows > 0) {
                // Generate nomor berikutnya jika kode duplikat
                $gen = generateKodeBarangOtomatis($conn, $nama, $kategori, $kode_barang);
                $kode_barang = $gen['kode_barang'];
            }
        }

        $sql = "INSERT INTO tbl_barang
                    (kode_barang, nama_produk, kategori, satuan, harga_beli, harga_jual, stok_min, stok_aktual, deskripsi, foto_barang)
                VALUES
                    ('$kode_barang','$nama','$kategori','$satuan',$harga_beli,$harga_jual,$stok_min,$stok_aktual,'$deskripsi',$foto_sql)";
        get_query($conn, $sql);
        header("Location: index.php?page=barang&msg=" . urlencode("Barang '$nama' ($kode_barang) berhasil ditambahkan."));
        exit;

    // ─── UPDATE ──────────────────────────────────────────────────────
    } elseif ($action == 'update') {
        $id          = (int)$_POST['id_barang'];
        $nama        = $conn->real_escape_string(trim($_POST['nama_produk']));
        $kategori    = $conn->real_escape_string(trim($_POST['kategori']));
        $satuan      = $conn->real_escape_string(trim($_POST['satuan'] ?? 'Pcs'));
        $harga_beli  = (int)($_POST['harga_beli'] ?? 0);
        $harga_jual  = (int)($_POST['harga_jual'] ?? 0);
        $stok_aktual = (int)($_POST['stok_aktual'] ?? 0);
        $stok_min    = (int)($_POST['stok_min'] ?? 0);
        $deskripsi   = trim($_POST['deskripsi'] ?? '');
        $merk        = trim($_POST['merk'] ?? '');
        $warna       = trim($_POST['warna'] ?? '');
        
        $tambahan = [];
        if ($merk !== '') $tambahan[] = "Merk: $merk";
        if ($warna !== '') $tambahan[] = "Warna: $warna";
        if (!empty($tambahan)) {
            $deskripsi .= ($deskripsi !== '' ? "\n" : "") . implode(", ", $tambahan);
        }
        $deskripsi = $conn->real_escape_string($deskripsi);

        $fotoSql = '';
        if (!empty($_FILES['foto_barang']['name'])) {
            $up = uploadFoto($_FILES['foto_barang']);
            if ($up) {
                // Hapus foto lama
                $qOld = get_query($conn, "SELECT foto_barang FROM tbl_barang WHERE id_barang=$id");
                $rOld = $qOld->fetch_assoc();
                if ($rOld && $rOld['foto_barang']) {
                    $oldPath = __DIR__ . "/assets/img/barang/" . $rOld['foto_barang'];
                    if (file_exists($oldPath)) unlink($oldPath);
                }
                $fotoSql = ", foto_barang='" . $conn->real_escape_string($up) . "'";
            }
        }

        $sql = "UPDATE tbl_barang
                SET nama_produk='$nama', kategori='$kategori', satuan='$satuan',
                    harga_beli=$harga_beli, harga_jual=$harga_jual,
                    stok_min=$stok_min, stok_aktual=$stok_aktual, deskripsi='$deskripsi' $fotoSql
                WHERE id_barang=$id";
        get_query($conn, $sql);
        header("Location: index.php?page=barang&msg=" . urlencode("Barang berhasil diperbarui."));
        exit;

    // ─── DELETE ──────────────────────────────────────────────────────
    } elseif ($action == 'delete') {
        $id = (int)$_POST['id_barang'];

        $qOld = get_query($conn, "SELECT foto_barang FROM tbl_barang WHERE id_barang=$id");
        if ($qOld->num_rows > 0) {
            $rOld = $qOld->fetch_assoc();
            if ($rOld['foto_barang']) {
                $oldPath = __DIR__ . "/assets/img/barang/" . $rOld['foto_barang'];
                if (file_exists($oldPath)) unlink($oldPath);
            }
        }

        get_query($conn, "DELETE FROM tbl_barang WHERE id_barang=$id");
        header("Location: index.php?page=barang&msg=" . urlencode("Barang berhasil dihapus."));
        exit;
    }
}

header("Location: index.php?page=barang");
exit;
?>
