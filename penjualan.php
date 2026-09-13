<?php
include 'config.php';
include 'cek_sesi.php';
$pageTitle = "Transaksi Penjualan (Kasir)";

// Tarik data semua barang untuk diletakkan di referensi JS (Pencarian Kasir Cepat)
$qBarang = get_query($conn, "SELECT id_barang, kode_barang, nama_produk, harga, stok_aktual, foto_barang FROM tbl_barang WHERE stok_aktual > 0");
$barang_array = [];
while($row = $qBarang->fetch_assoc()) {
    $barang_array[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - A STORE</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .pos-grid { display: flex; gap: 24px; flex-wrap: wrap; align-items:flex-start; }
        .pos-left { flex: 7; display: flex; flex-direction: column; gap: 24px; min-width: 400px; }
        .pos-right { flex: 3; display: flex; flex-direction: column; gap: 24px; min-width: 300px; position:sticky; top:100px; }
        
        .search-product { display: flex; gap: 12px; margin-bottom: 20px; position: relative;}
        .form-control { flex: 1; padding: 12px 16px; border: 1px solid var(--border-color); border-radius: var(--border-radius-md); font-family: inherit; font-size: 14px; outline: none; }
        
        /* Auto complet box */
        .autocomplete-dropdown { position: absolute; top: 100%; left: 0; right: 0; background: #fff; box-shadow: var(--shadow-md); border-radius: 8px; max-height: 200px; overflow-y: auto; z-index: 100; border: 1px solid var(--border-color); display: none; }
        .autocomplete-item { padding: 10px 16px; cursor: pointer; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; }
        .autocomplete-item:hover { background: var(--primary-light); }
        
        .cart-qty { width: 60px; padding: 6px; text-align: center; border: 1px solid var(--border-color); border-radius: 6px; }
        .cart-action { padding: 6px 10px; border-radius: 6px; color: var(--danger-color); background: var(--danger-light); border: none; cursor: pointer; }
        
        .summary-box { background: var(--bg-body); padding: 20px; border-radius: var(--border-radius-md); margin-bottom: 20px; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 15px; color: var(--text-muted); }
        .summary-total { display: flex; justify-content: space-between; margin-top: 16px; padding-top: 16px; border-top: 2px dashed var(--border-color); font-size: 20px; font-weight: 700; color: var(--text-main); }
        
        .btn-large { width: 100%; padding: 16px; font-size: 16px; font-weight: 600; justify-content: center; border-radius:8px;}
        .btn-success { background-color: var(--success-color); color: white; border:none; cursor:pointer;}
        .btn-success:hover { background-color: #059669; }
    </style>
</head>
<body>
    <div class="layout-wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <div class="main-content">
            <?php include 'includes/topbar.php'; ?>

            <main class="dashboard-content">
                <div class="page-header">
                    <h2>Transaksi Penjualan (Kasir)</h2>
                    <p>Catat transaksi penjualan harian. Data ini otomatis mengurangi stok database.</p>
                </div>

                <?php if(isset($_GET['msg'])): ?>
                <div style="padding:12px 16px; background:var(--success-light); color:var(--success-color); border-radius:8px; margin-bottom:20px; font-weight:500;">
                    <i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($_GET['msg']); ?>
                </div>
                <?php endif; ?>

                <div class="pos-grid">
                    <!-- Kiri: Pencarian & Tabel Keranjang -->
                    <div class="pos-left">
                        <div class="chart-card">
                            <div class="search-product">
                                <input type="text" id="searchInput" class="form-control" placeholder="Ketik nama atau kode barang..." autocomplete="off">
                                <button class="btn btn-primary"><i class="fa-solid fa-search"></i></button>
                                <div id="dropdownResult" class="autocomplete-dropdown"></div>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="data-table" id="cartTable">
                                    <thead>
                                        <tr>
                                            <th>Kode</th>
                                            <th>Nama Produk</th>
                                            <th>Harga</th>
                                            <th>Qty</th>
                                            <th>Subtotal</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="cartBody">
                                        <tr id="emptyRow">
                                            <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 30px;">Keranjang masih kosong. Cari barang di atas untuk menambahkan.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Kanan: Form Data Bayar & Summary -->
                    <div class="pos-right chart-card">
                        <h3 style="margin-bottom:20px; font-size:18px;">Ringkasan Pembayaran</h3>
                        
                        <div class="summary-box">
                            <div class="summary-row">
                                <span>Subtotal (<span id="txtItems">0</span> item)</span>
                                <span id="txtSubtotal">Rp 0</span>
                            </div>
                            <div class="summary-row">
                                <span>Pajak (0%)</span>
                                <span>Rp 0</span>
                            </div>
                            <div class="summary-total">
                                <span>Total Pembayaran</span>
                                <span id="txtTotal">Rp 0</span>
                            </div>
                        </div>

                        <!-- Form Pembayaran ke Backend PHP -->
                        <form action="action_penjualan.php" method="POST" id="formPenjualan">
                            <!-- Array Keranjang Akan diisi by JS kemari -->
                            <input type="hidden" name="cart_data" id="cartDataJson">
                            <input type="hidden" name="grand_total" id="inputGrandTotal" value="0">
                            <input type="hidden" name="total_item" id="inputTotalItem" value="0">
                            
                            <div style="margin-bottom: 20px;">
                                <label style="display:block; margin-bottom:8px; font-size:14px; font-weight:500; color:var(--text-muted)">Uang Diterima (Rp)</label>
                                <input type="number" id="inputDibayar" name="nominal_bayar" class="form-control" placeholder="0" style="font-size:18px; font-weight:bold;" required>
                            </div>
                            
                            <div style="margin-bottom: 24px;">
                                <label style="display:block; margin-bottom:8px; font-size:14px; font-weight:500; color:var(--text-muted)">Kembalian (Rp)</label>
                                <input type="text" id="inputKembalianText" class="form-control" value="0" disabled style="background:var(--bg-body); font-weight:bold; color:var(--success-color);">
                                <input type="hidden" name="kembalian" id="inputKembalianValue" value="0">
                            </div>

                            <button type="submit" id="btnSubmit" class="btn btn-large btn-success" disabled><i class="fa-solid fa-check-circle"></i> Selesaikan Transaksi</button>
                            <button type="button" class="btn btn-large" style="margin-top:12px; border:1px solid var(--border-color); background:transparent; color:var(--text-main)" onclick="location.reload();"><i class="fa-solid fa-redo"></i> Reset Keranjang</button>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- JS Khusus POS Kasir -->
    <script>
        const dbBarang = <?= json_encode($barang_array); ?>;
        const searchInput = document.getElementById('searchInput');
        const dropdownResult = document.getElementById('dropdownResult');
        let cart = [];

        // 1. Logika Pencarian AutoComplete
        searchInput.addEventListener('input', function() {
            const keyword = this.value.toLowerCase();
            dropdownResult.innerHTML = '';
            
            if(keyword.length < 1) {
                dropdownResult.style.display = 'none';
                return;
            }
            
            const results = dbBarang.filter(b => 
                b.kode_barang.toLowerCase().includes(keyword) || 
                b.nama_produk.toLowerCase().includes(keyword)
            );

            if(results.length > 0) {
                dropdownResult.style.display = 'block';
                results.forEach(item => {
                    const div = document.createElement('div');
                    div.className = 'autocomplete-item';
                    const foto = item.foto_barang ? `assets/img/barang/${item.foto_barang}` : null;
                    const imgHtml = foto 
                        ? `<img src="${foto}" style="width:40px; height:40px; object-fit:cover; border-radius:6px; margin-right:12px; border:1px solid var(--border-color);">`
                        : `<div style="width:40px; height:40px; background:var(--bg-body); border-radius:6px; margin-right:12px; display:flex; align-items:center; justify-content:center; color:var(--text-light); border:1px solid var(--border-color);"><i class="fa-solid fa-image"></i></div>`;
                        
                    div.innerHTML = `
                        <div style="display:flex; align-items:center;">
                            ${imgHtml}
                            <div>
                                <span style="font-weight:600">${item.kode_barang}</span> - ${item.nama_produk}
                                <div style="font-size:12px; color:#64748b">Stok Tersedia: ${item.stok_aktual}</div>
                            </div>
                        </div>
                        <div style="font-weight:700; color:#0284C7; display:flex; align-items:center;">Rp ${formatRupiah(item.harga)}</div>
                    `;
                    div.onclick = () => {
                        addToCart(item);
                        searchInput.value = '';
                        dropdownResult.style.display = 'none';
                        searchInput.focus();
                    };
                    dropdownResult.appendChild(div);
                });
            } else {
                dropdownResult.style.display = 'none';
            }
        });

        // Hide dropdown outside click
        document.addEventListener('click', function(e) {
            if (e.target !== searchInput && e.target !== dropdownResult) {
                dropdownResult.style.display = 'none';
            }
        });

        // 2. Logika Keranjang (Add)
        function addToCart(item) {
            const existingIndex = cart.findIndex(c => c.kode_barang === item.kode_barang);
            if(existingIndex >= 0) {
                if(cart[existingIndex].qty < item.stok_aktual) {
                    cart[existingIndex].qty += 1;
                    cart[existingIndex].subtotal = cart[existingIndex].qty * cart[existingIndex].harga;
                } else {
                    alert('Oops! Stok tidak mencukupi.');
                }
            } else {
                cart.push({
                    kode_barang: item.kode_barang,
                    nama_produk: item.nama_produk,
                    harga: parseInt(item.harga),
                    qty: 1,
                    subtotal: parseInt(item.harga)
                });
            }
            renderCart();
        }

        // 3. Render Tabel Keranjang
        function renderCart() {
            const tbody = document.getElementById('cartBody');
            tbody.innerHTML = '';
            
            if(cart.length === 0) {
                tbody.innerHTML = '<tr id="emptyRow"><td colspan="6" style="text-align: center; color: var(--text-muted); padding: 30px;">Keranjang kosong.</td></tr>';
                calculateSummary();
                return;
            }

            cart.forEach((item, index) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td><strong>${item.kode_barang}</strong></td>
                    <td>${item.nama_produk}</td>
                    <td>Rp ${formatRupiah(item.harga)}</td>
                    <td>
                        <input type="number" class="cart-qty" value="${item.qty}" min="1" onchange="updateQty(${index}, this.value)">
                    </td>
                    <td style="font-weight:700; color:var(--primary-color)">Rp ${formatRupiah(item.subtotal)}</td>
                    <td>
                        <button type="button" class="cart-action" onclick="removeFromCart(${index})"><i class="fa-solid fa-trash"></i></button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
            calculateSummary();
        }

        function updateQty(index, newQty) {
            newQty = parseInt(newQty);
            if(newQty < 1 || isNaN(newQty)) {
                newQty = 1;
            }
            const dataItem = dbBarang.find(i => i.kode_barang === cart[index].kode_barang);
            if(newQty > dataItem.stok_aktual) {
                alert('Stok tidak mencukupi! Maksimal: ' + dataItem.stok_aktual);
                newQty = dataItem.stok_aktual;
            }
            cart[index].qty = newQty;
            cart[index].subtotal = newQty * cart[index].harga;
            renderCart();
        }

        function removeFromCart(index) {
            cart.splice(index, 1);
            renderCart();
        }

        // 4. Kalkulasi Panel Kanan
        function calculateSummary() {
            let totalItem = 0;
            let grandTotal = 0;

            cart.forEach(c => {
                totalItem += c.qty;
                grandTotal += c.subtotal;
            });

            document.getElementById('txtItems').innerText = totalItem;
            document.getElementById('txtSubtotal').innerText = 'Rp ' + formatRupiah(grandTotal);
            document.getElementById('txtTotal').innerText = 'Rp ' + formatRupiah(grandTotal);
            
            // Set Hidden inputs for Backend (PHP)
            document.getElementById('cartDataJson').value = JSON.stringify(cart);
            document.getElementById('inputGrandTotal').value = grandTotal;
            document.getElementById('inputTotalItem').value = totalItem;

            calculateKembalian(); // Hitung ulang kembalian jika qty berubah
            validateSubmit(grandTotal);
        }

        // 5. Kalkulator Kembalian
        const inputDibayar = document.getElementById('inputDibayar');
        inputDibayar.addEventListener('input', calculateKembalian);

        function calculateKembalian() {
            const tagihan = parseInt(document.getElementById('inputGrandTotal').value) || 0;
            const dibayar = parseInt(inputDibayar.value) || 0;
            const btnSubmit = document.getElementById('btnSubmit');

            if(tagihan > 0 && dibayar >= tagihan) {
                const kembalian = dibayar - tagihan;
                document.getElementById('inputKembalianText').value = "Rp " + formatRupiah(kembalian);
                document.getElementById('inputKembalianValue').value = kembalian;
                btnSubmit.disabled = false;
            } else {
                document.getElementById('inputKembalianText').value = "Uang Kurang!";
                document.getElementById('inputKembalianValue').value = 0;
                btnSubmit.disabled = true;
            }
        }

        function validateSubmit(grandTotal) {
            if(grandTotal === 0) {
                document.getElementById('btnSubmit').disabled = true;
            } else {
                calculateKembalian();
            }
        }

        function formatRupiah(number) {
            return new Intl.NumberFormat('id-ID').format(number);
        }
    </script>
</body>
</html>
