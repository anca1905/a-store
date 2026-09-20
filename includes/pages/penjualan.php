<?php
$qBarang = get_query($conn, "SELECT id_barang, kode_barang, nama_produk, harga_jual as harga, stok_aktual, foto_barang FROM tbl_barang WHERE stok_aktual > 0 ORDER BY nama_produk ASC");
$barang_array = [];
while($row = $qBarang->fetch_assoc()) $barang_array[] = $row;
?>

<div class="dashboard-content-wrapper" style="padding:20px;">
    
    <?php if(isset($_GET['msg'])): ?>
    <div style="padding:12px 16px; background:var(--success-light); color:var(--success-color); border-radius:8px; font-weight:500; display:flex; align-items:center; gap:8px; margin-bottom:20px;">
        <i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($_GET['msg']) ?>
    </div>
    <?php endif; ?>

    <div style="display:flex; gap:24px; align-items:flex-start; flex-wrap:wrap;">
        
        <!-- ── KIRI: GRID PRODUK ─────────────────────────────────── -->
        <div style="flex:7; min-width:400px; display:flex; flex-direction:column; gap:20px;">
            <div style="display:flex; justify-content:space-between; align-items:flex-end;">
                <div>
                    <h2 style="font-size:20px; font-weight:700; margin-bottom:8px;">Transaksi Penjualan</h2>
                    <label style="font-size:12px; font-weight:600; color:var(--text-muted); display:block; margin-bottom:4px;">Scan / cari barang</label>
                    <div style="position:relative; width:340px;">
                        <i class="fa-solid fa-search" style="position:absolute; left:14px; top:12px; color:var(--text-muted);"></i>
                        <input type="text" id="searchInput" class="form-control" placeholder="Kode barang / nama barang" autocomplete="off" style="padding-left:38px; border-radius:24px; box-shadow:0 2px 4px rgba(0,0,0,0.02);">
                    </div>
                </div>
                <div style="padding:10px 16px; background:#fff; border:1px solid var(--border-color); border-radius:24px; font-size:13px; font-weight:700; color:var(--text-main);">
                    Produk (<span id="totalProdukTersedia"><?= count($barang_array) ?></span>)
                </div>
            </div>

            <!-- Grid Produk -->
            <div id="productGrid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(140px, 1fr)); gap:16px; max-height:calc(100vh - 220px); overflow-y:auto; padding-right:8px; padding-bottom:20px;">
                <!-- Digenarate via JS -->
            </div>
        </div>

        <!-- ── KANAN: KERANJANG & PEMBAYARAN ─────────────────────── -->
        <div style="flex:3; min-width:320px; background:#fff; border:1px solid var(--border-color); border-radius:16px; padding:24px; position:sticky; top:20px; box-shadow:var(--shadow-card);">
            <div style="text-align:center; font-size:16px; font-weight:700; margin-bottom:16px; padding-bottom:12px; border-bottom:2px dashed var(--border-color);">
                Total Item: <span id="txtItems" style="color:var(--primary-color);">0</span>
            </div>

            <!-- List Keranjang -->
            <div id="cartList" style="min-height:120px; max-height:240px; overflow-y:auto; margin-bottom:20px; padding-right:8px;">
                <div style="text-align:center; color:var(--text-muted); padding:40px 0; font-size:13px;">
                    <i class="fa-solid fa-basket-shopping" style="font-size:32px; opacity:0.3; display:block; margin-bottom:8px;"></i>
                    Belum ada barang dipilih
                </div>
            </div>

            <form action="action_penjualan.php" method="POST" id="formPenjualan" onsubmit="event.preventDefault(); openModal('modalConfirmTrx');">
                <input type="hidden" name="cart_data" id="cartDataJson">
                <input type="hidden" name="grand_total" id="inputGrandTotal" value="0">
                <input type="hidden" name="total_item" id="inputTotalItem" value="0">
                <input type="hidden" name="kembalian" id="inputKembalianValue" value="0">

                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; font-size:15px; font-weight:800;">
                    <span>Total Belanja</span>
                    <span id="txtTotal" style="color:var(--primary-color); font-size:18px;">Rp 0</span>
                </div>

                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                    <label style="font-size:14px; font-weight:700; color:var(--text-main);">Metode Pembayaran</label>
                    <select name="metode_pembayaran" class="form-control" style="width:160px; font-size:14px; font-weight:bold; padding:8px;" required>
                        <option value="Cash">Cash</option>
                        <option value="Transfer">Transfer</option>
                    </select>
                </div>

                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                    <label style="font-size:14px; font-weight:700; color:var(--text-main);">Bayar</label>
                    <input type="number" id="inputDibayar" name="nominal_bayar" class="form-control" placeholder="0" style="width:160px; font-size:15px; font-weight:bold; text-align:right;" required>
                </div>

                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
                    <label style="font-size:14px; font-weight:700; color:var(--text-main);">Kembalian</label>
                    <input type="text" id="inputKembalianText" class="form-control" value="Rp 0" disabled style="width:160px; background:var(--bg-body); font-weight:bold; color:var(--success-color); text-align:right;">
                </div>

                <button type="submit" id="btnSubmit" class="btn btn-primary" style="width:100%; padding:14px; font-size:15px; border-radius:8px; margin-bottom:12px; justify-content:center; background:#000; border:none;" disabled>
                    Simpan Transaksi
                </button>
                <button type="button" class="btn btn-outline" style="width:100%; padding:14px; font-size:14px; border-radius:8px; justify-content:center;" onclick="location.reload();">
                    Batal
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Modal Confirm Transaction -->
<div id="modalConfirmTrx" class="modal">
    <div class="modal-content" style="max-width:400px; text-align:center; padding:32px 24px;">
        <i class="fa-solid fa-circle-question" style="font-size:48px; color:var(--primary-color); margin-bottom:16px;"></i>
        <h3 style="font-size:20px; font-weight:700; margin-bottom:12px;">Konfirmasi Transaksi</h3>
        <p style="color:var(--text-muted); font-size:14px; margin-bottom:24px; line-height:1.5;">Apakah transaksi sudah pas/benar? Pastikan jumlah bayar dan item sudah sesuai sebelum menyimpan.</p>
        
        <div style="display:flex; justify-content:center; gap:12px;">
            <button type="button" class="btn btn-outline" style="padding:10px 24px;" onclick="closeModal('modalConfirmTrx')">Batal</button>
            <button type="button" class="btn btn-primary" style="padding:10px 24px;" onclick="document.getElementById('formPenjualan').submit();">Ya, Simpan</button>
        </div>
    </div>
</div>

<style>
/* Custom scrollbar untuk grid & cart */
#productGrid::-webkit-scrollbar, #cartList::-webkit-scrollbar { width: 6px; }
#productGrid::-webkit-scrollbar-track, #cartList::-webkit-scrollbar-track { background: transparent; }
#productGrid::-webkit-scrollbar-thumb, #cartList::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
#productGrid::-webkit-scrollbar-thumb:hover, #cartList::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

.product-card {
    background: #fff; border: 1px solid var(--border-color); border-radius: 12px; padding: 12px; 
    cursor: pointer; text-align: center; transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
}
.product-card:hover {
    border-color: var(--primary-color); transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(2, 132, 199, 0.1);
}
.qty-btn {
    width: 24px; height: 24px; border-radius: 6px; border: none; 
    background: var(--bg-body); color: var(--text-main); font-weight: 700; cursor: pointer;
    display: flex; align-items: center; justify-content: center; transition: 0.1s;
}
.qty-btn:hover { background: #e2e8f0; }
</style>

<script>
const dbBarang = <?= json_encode($barang_array) ?>;
const grid = document.getElementById('productGrid');
const searchInput = document.getElementById('searchInput');
const cartList = document.getElementById('cartList');
let cart = [];

function fmt(n) { return new Intl.NumberFormat('id-ID').format(n); }

// Render Grid Produk
function renderProducts(products) {
    grid.innerHTML = '';
    document.getElementById('totalProdukTersedia').innerText = products.length;
    
    if(products.length === 0) {
        grid.innerHTML = '<div style="grid-column: 1 / -1; text-align:center; padding:40px; color:var(--text-muted);"><i class="fa-solid fa-box-open" style="font-size:32px; opacity:0.3; margin-bottom:12px; display:block;"></i>Barang tidak ditemukan.</div>';
        return;
    }

    products.forEach(p => {
        const card = document.createElement('div');
        card.className = 'product-card';
        card.onclick = () => addToCart(p);

        const img = p.foto_barang ? `assets/img/barang/${p.foto_barang}` : '';
        const imgHtml = img 
            ? `<img src="${img}" style="width:100%; height:110px; object-fit:cover; border-radius:8px; margin-bottom:10px;">` 
            : `<div style="width:100%; height:110px; background:var(--bg-body); border-radius:8px; margin-bottom:10px; display:flex; align-items:center; justify-content:center; color:var(--text-light); border:1px dashed #cbd5e1;"><i class="fa-solid fa-image fa-2x"></i></div>`;

        card.innerHTML = `
            ${imgHtml}
            <div style="font-size:13px; font-weight:700; margin-bottom:4px; line-height:1.3; height:34px; overflow:hidden; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical;">${p.nama_produk}</div>
            <div style="font-size:14px; font-weight:800; color:var(--primary-color); margin-bottom:6px;">Rp ${fmt(p.harga)}</div>
            <div style="font-size:11px; color:var(--text-muted); background:var(--bg-body); padding:3px 6px; border-radius:10px; display:inline-block;">Stok: <b>${p.stok_aktual}</b></div>
        `;
        grid.appendChild(card);
    });
}

// Fitur Pencarian Cepat
searchInput.addEventListener('input', (e) => {
    const kw = e.target.value.toLowerCase();
    const filtered = dbBarang.filter(b => b.kode_barang.toLowerCase().includes(kw) || b.nama_produk.toLowerCase().includes(kw));
    renderProducts(filtered);
});

// Tambah ke Keranjang
function addToCart(item) {
    const idx = cart.findIndex(c => c.kode_barang === item.kode_barang);
    if(idx >= 0) {
        if(cart[idx].qty < item.stok_aktual) { 
            cart[idx].qty++; 
            cart[idx].subtotal = cart[idx].qty * cart[idx].harga; 
        } else {
            alert('Stok tidak mencukupi! Sisa stok: ' + item.stok_aktual);
        }
    } else {
        cart.push({ 
            kode_barang: item.kode_barang, 
            nama_produk: item.nama_produk, 
            harga: parseInt(item.harga), 
            qty: 1, 
            subtotal: parseInt(item.harga),
            stok_max: item.stok_aktual
        });
    }
    renderCart();
}

// Ubah Qty dari Keranjang
function updateQty(i, v) {
    if(v < 1) { 
        cart.splice(i, 1); 
    } else {
        if(v > cart[i].stok_max) { 
            alert('Stok maksimal: ' + cart[i].stok_max); 
            v = cart[i].stok_max; 
        }
        cart[i].qty = v; 
        cart[i].subtotal = v * cart[i].harga;
    }
    renderCart();
}

// Render UI Keranjang
function renderCart() {
    cartList.innerHTML = '';
    
    if(cart.length === 0) { 
        cartList.innerHTML = '<div style="text-align:center; color:var(--text-muted); padding:40px 0; font-size:13px;"><i class="fa-solid fa-basket-shopping" style="font-size:32px; opacity:0.3; display:block; margin-bottom:8px;"></i>Belum ada barang dipilih</div>'; 
        calculateSummary(); 
        return; 
    }

    cart.forEach((c, i) => {
        const row = document.createElement('div');
        row.style.cssText = 'display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #f1f5f9; padding:10px 0;';
        row.innerHTML = `
            <div style="flex:1; padding-right:12px;">
                <div style="font-size:13px; font-weight:700; line-height:1.2; margin-bottom:4px;">${c.nama_produk}</div>
                <div style="font-size:11px; color:var(--text-muted);">Rp ${fmt(c.harga)}</div>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
                <button type="button" class="qty-btn" onclick="updateQty(${i}, ${c.qty-1})"><i class="fa-solid fa-minus" style="font-size:10px;"></i></button>
                <span style="font-size:13px; font-weight:700; width:20px; text-align:center;">${c.qty}</span>
                <button type="button" class="qty-btn" onclick="updateQty(${i}, ${c.qty+1})"><i class="fa-solid fa-plus" style="font-size:10px;"></i></button>
            </div>
            <div style="font-size:14px; font-weight:800; width:90px; text-align:right; color:var(--text-main);">
                Rp ${fmt(c.subtotal)}
            </div>
        `;
        cartList.appendChild(row);
    });
    
    // Auto scroll ke bawah
    cartList.scrollTop = cartList.scrollHeight;
    calculateSummary();
}

// Kalkulasi Total
function calculateSummary() {
    let totalItem = 0, grandTotal = 0;
    cart.forEach(c => { totalItem += c.qty; grandTotal += c.subtotal; });
    
    document.getElementById('txtItems').innerText = totalItem;
    document.getElementById('txtTotal').innerText = 'Rp ' + fmt(grandTotal);
    
    document.getElementById('cartDataJson').value = JSON.stringify(cart);
    document.getElementById('inputGrandTotal').value = grandTotal;
    document.getElementById('inputTotalItem').value = totalItem;
    
    calculateKembalian(); 
}

const inputDibayar = document.getElementById('inputDibayar');
inputDibayar.addEventListener('input', calculateKembalian);

function calculateKembalian() {
    const tagihan = parseInt(document.getElementById('inputGrandTotal').value) || 0;
    const dibayar = parseInt(inputDibayar.value) || 0;
    
    if(tagihan > 0 && dibayar >= tagihan) {
        const kb = dibayar - tagihan;
        document.getElementById('inputKembalianText').value = 'Rp ' + fmt(kb);
        document.getElementById('inputKembalianText').style.color = 'var(--success-color)';
        document.getElementById('inputKembalianValue').value = kb;
        document.getElementById('btnSubmit').disabled = false;
        document.getElementById('btnSubmit').style.background = '#000';
    } else {
        document.getElementById('inputKembalianText').value = dibayar > 0 ? 'Uang Kurang' : 'Rp 0';
        document.getElementById('inputKembalianText').style.color = dibayar > 0 ? 'var(--danger-color)' : 'var(--text-muted)';
        document.getElementById('inputKembalianValue').value = 0;
        document.getElementById('btnSubmit').disabled = true;
        document.getElementById('btnSubmit').style.background = '#94a3b8'; // abu-abu
    }
}

// Init run
renderProducts(dbBarang);

<?php if (isset($_GET['print_id'])): ?>
window.onload = function() {
    lihatDetail('<?= htmlspecialchars($_GET['print_id'], ENT_QUOTES) ?>');
};
<?php endif; ?>

function lihatDetail(noFaktur) {
    document.getElementById('modalDetailTitle').textContent = 'Struk — ' + noFaktur;
    document.getElementById('modalDetailBody').innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
    openModal('modalDetail');

    fetch('index.php?page=penjualan&ajax_detail=' + encodeURIComponent(noFaktur))
        .then(r => r.text())
        .then(html => { document.getElementById('modalDetailBody').innerHTML = html; });
}
</script>

<!-- Modal Detail Transaksi -->
<div id="modalDetail" class="modal">
    <div class="modal-content" style="max-width:560px;">
        <div class="modal-header">
            <h3 id="modalDetailTitle">Cetak Struk</h3>
            <button class="close-btn" onclick="closeModal('modalDetail')"><i class="fa-solid fa-times"></i></button>
        </div>
        <div id="modalDetailBody" style="min-height:100px; display:flex; align-items:center; justify-content:center;">
            <i class="fa-solid fa-spinner fa-spin"></i>
        </div>
    </div>
</div>

