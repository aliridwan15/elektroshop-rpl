<?php
session_start();
include 'koneksi.php'; // Ensure your koneksi.php file exists and works correctly
include 'resource/header.php'; // Ensure your header.php file exists and works correctly

// Ensure user is logged in
if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit();
}

$loggedInUserId = (int)$_SESSION['id'];
$checkoutItems = [];
$totalCheckoutHarga = 0;
$totalBerat = 0; // Tambahkan inisialisasi untuk total berat

// --- START: KONDISI UNTUK BUY NOW ATAU DARI KERANJANG ---
if (isset($_SESSION['buy_now_product_id']) && isset($_SESSION['buy_now_quantity'])) {
    // Ini adalah skenario "Buy Now"
    $buyNowProductId = (int)$_SESSION['buy_now_product_id'];
    $buyNowQuantity = (int)$_SESSION['buy_now_quantity'];

    // Ambil detail produk langsung dari tabel 'produk'
    $stmt = $conn->prepare("SELECT id, nama_produk, harga, gambar, stok, berat FROM produk WHERE id = ?");
    $stmt->bind_param("i", $buyNowProductId);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();

    // Hapus variabel sesi 'buy_now' setelah digunakan, terlepas dari hasil
    // Ini penting agar 'buy now' tidak terus-menerus berlaku pada refresh
    unset($_SESSION['buy_now_product_id']);
    unset($_SESSION['buy_now_quantity']);

    if ($product) {
        // Pastikan kuantitas yang diminta tidak melebihi stok yang tersedia
        $actualQuantity = min($buyNowQuantity, $product['stok']);

        if ($actualQuantity > 0) {
            $subtotal = $product['harga'] * $actualQuantity;
            $totalCheckoutHarga += $subtotal;
            $totalBerat += $product['berat'] * $actualQuantity; // Hitung berat untuk "Buy Now"

            $checkoutItems[] = [
                'cart_item_id' => null, // Ini bukan dari tabel cart, jadi bisa null atau 0
                'id_produk' => $product['id'],
                'nama_produk' => $product['nama_produk'],
                'harga' => $product['harga'],
                'gambar' => explode(',', $product['gambar'])[0],
                'stok' => $product['stok'],
                'quantity' => $actualQuantity,
                'subtotal_item' => $subtotal
            ];
        } else {
            // Stok habis atau kuantitas tidak valid untuk Buy Now
            echo '<div class="container mt-5"><div class="alert alert-warning text-center" role="alert">Produk yang Anda pilih tidak tersedia atau kuantitas tidak valid untuk pembelian langsung. Silakan kembali ke <a href="index.php" class="alert-link">Halaman Utama</a>.</div></div>';
            include 'resource/footer.php';
            exit();
        }
    } else {
        // Produk tidak ditemukan untuk Buy Now
        echo '<div class="container mt-5"><div class="alert alert-warning text-center" role="alert">Produk untuk pembelian langsung tidak ditemukan. Silakan kembali ke <a href="index.php" class="alert-link">Halaman Utama</a>.</div></div>';
        include 'resource/footer.php';
        exit();
    }

} else {
    // Ini adalah skenario checkout dari keranjang (menggunakan POST selected_cart_ids)
    if (isset($_POST['selected_cart_ids']) && is_array($_POST['selected_cart_ids'])) {
        $selectedCartIds = [];
        foreach ($_POST['selected_cart_ids'] as $id) {
            $selectedCartIds[] = (int)$id;
        }

        if (empty($selectedCartIds)) {
            echo '<div class="container mt-5"><div class="alert alert-warning text-center" role="alert">Tidak ada item yang dipilih untuk checkout. Silakan kembali ke <a href="cart.php" class="alert-link">Keranjang Belanja</a>.</div></div>';
            include 'resource/footer.php';
            exit();
        }

        $placeholders = implode(',', array_fill(0, count($selectedCartIds), '?'));
        $types = str_repeat('i', count($selectedCartIds));

        $stmt = $conn->prepare("
            SELECT
                c.id AS cart_item_id,
                c.id_produk,
                c.quantity,
                p.nama_produk,
                p.harga,
                p.gambar,
                p.stok,
                p.berat -- Tambahkan berat di sini
            FROM cart c
            JOIN produk p ON c.id_produk = p.id
            WHERE c.id_user = ? AND c.id IN ($placeholders)
        ");

        $bindParams = array_merge([$loggedInUserId], $selectedCartIds);
        $stmt->bind_param("i" . $types, ...$bindParams);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $productId = $row['id_produk'];
            $quantity = $row['quantity'];
            $productStock = $row['stok'];
            $productBerat = $row['berat']; // Ambil berat produk

            $actualQuantity = min($quantity, $productStock);

            if ($productStock == 0) {
                $actualQuantity = 0; // Jika stok 0, quantity checkout juga 0
            }

            if ($actualQuantity > 0) {
                $subtotal = $row['harga'] * $actualQuantity;
                $totalCheckoutHarga += $subtotal;
                $totalBerat += $productBerat * $actualQuantity; // Tambahkan ke total berat

                $checkoutItems[] = [
                    'cart_item_id' => $row['cart_item_id'],
                    'id_produk' => $productId,
                    'nama_produk' => $row['nama_produk'],
                    'harga' => $row['harga'],
                    'gambar' => explode(',', $row['gambar'])[0],
                    'stok' => $productStock,
                    'quantity' => $actualQuantity,
                    'subtotal_item' => $subtotal
                ];
            }
        }
        $stmt->close();
    } else {
        // Jika tidak ada selected_cart_ids dan bukan 'Buy Now'
        echo '<div class="container mt-5"><div class="alert alert-warning text-center" role="alert">Tidak ada item yang dipilih untuk checkout. Silakan kembali ke <a href="cart.php" class="alert-link">Keranjang Belanja</a>.</div></div>';
        include 'resource/footer.php';
        exit();
    }
}
// --- END: KONDISI UNTUK BUY NOW ATAU DARI KERANJANG ---


// Fetch user addresses (LOGIKA INI TETAP SAMA)
$user_addresses = [];
$selected_address = [
    'id_alamat' => '',
    'nama_penerima' => '',
    'no_telepon' => '',
    'alamat_lengkap' => '',
    'desa' => '',
    'distrik' => '',
    'kota' => '',
    'kode_pos' => '',
    'provinsi' => ''
];

if ($loggedInUserId) {
    $stmt = $conn->prepare("SELECT * FROM Alamat_Pengiriman WHERE id_pengguna = ?");
    $stmt->bind_param("i", $loggedInUserId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $user_addresses[] = $row;
    }
    $stmt->close();

    if (!empty($user_addresses)) {
        $selected_address = $user_addresses[0]; // Set the first address as default
    }
}

// Inisialisasi diskon dari sesi jika ada
$initialDiscount = isset($_SESSION['applied_discount_amount']) ? (float)$_SESSION['applied_discount_amount'] : 0;
$initialCouponCode = isset($_SESSION['applied_coupon_code']) ? $_SESSION['applied_coupon_code'] : '';

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #ffe3f1;
            font-family: Arial, sans-serif;
        }
        .btn-pink {
            background-color: #ff1493;
            color: #fff;
            border: none;
        }
        .btn-pink:hover {
            background-color: #e01383;
            color: #fff; /* Ensure text color remains white on hover */
        }
        .btn-pink-outline {
            background-color: #ff1493;
            color: white;
            border: none;
        }
        .product-info {
            display: flex;
            align-items: center;
            flex-grow: 1;
        }
        .product-info img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 6px;
            margin-right: 10px;
        }
        .product-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }
        .product-price {
            font-weight: bold;
        }
        .payment-icons img {
            height: 25px;
            margin-left: 8px;
        }
        .fas.fa-camera {
            font-size: 1.2rem;
        }
        /* Custom dropdown styles */
        .custom-dropdown {
            position: relative;
            width: 100%;
        }
        .selected-option {
            background-color: transparent;
            border: 1px solid #ced4da;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .selected-option img {
            height: 30px;
        }
        .options {
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            background-color: #fff;
            border: 1px solid #ced4da;
            border-radius: 5px;
            z-index: 1000;
            display: none;
        }
        .option {
            padding: 10px;
            display: flex;
            align-items: center;
            cursor: pointer;
        }
        .option:hover {
            background-color: #f0f0f0;
        }
        .option img {
            height: 30px;
            margin-right: 10px;
        }
    </style>
</head>
<body>

<div class="container my-5">
    <form action="process_checkout.php" method="POST" enctype="multipart/form-data">
        <?php foreach ($checkoutItems as $item): ?>
            <input type="hidden" name="checkout_products[<?= $item['id_produk'] ?>][quantity]" value="<?= htmlspecialchars($item['quantity']) ?>">
            <input type="hidden" name="checkout_products[<?= $item['id_produk'] ?>][price]" value="<?= htmlspecialchars($item['harga']) ?>">
            <input type="hidden" name="checkout_products[<?= $item['id_produk'] ?>][cart_item_id]" value="<?= htmlspecialchars($item['cart_item_id']) ?>">
            <input type="hidden" name="checkout_products[<?= $item['id_produk'] ?>][name]" value="<?= htmlspecialchars($item['nama_produk']) ?>">
            <input type="hidden" name="checkout_products[<?= $item['id_produk'] ?>][image]" value="<?= htmlspecialchars($item['gambar']) ?>">
        <?php endforeach; ?>
        <input type="hidden" name="total_berat_produk" value="<?= $totalBerat ?>"> 
        <input type="hidden" name="applied_discount_amount" id="appliedDiscountAmount" value="<?= $initialDiscount ?>">
        <input type="hidden" name="applied_coupon_code_hidden" id="appliedCouponCodeHidden" value="<?= htmlspecialchars($initialCouponCode) ?>">


        <div class="row g-4">
            <div class="col-md-6">
                <h4 class="mb-4">Detail Penagihan</h4>

                <?php if (!empty($user_addresses)): ?>
                <div class="mb-3">
                    <label for="saved_address" class="form-label">Pilih Alamat Pengiriman</label>
                    <select class="form-select" id="saved_address">
                        <?php foreach ($user_addresses as $address): ?>
                            <option value="<?= htmlspecialchars($address['id_alamat']) ?>"
                                data-nama_penerima="<?= htmlspecialchars($address['nama_penerima']) ?>"
                                data-no_telepon="<?= htmlspecialchars($address['no_telepon']) ?>"
                                data-alamat_lengkap="<?= htmlspecialchars($address['alamat_lengkap']) ?>"
                                data-desa="<?= htmlspecialchars($address['desa']) ?>"
                                data-distrik="<?= htmlspecialchars($address['distrik']) ?>"
                                data-kota="<?= htmlspecialchars($address['kota']) ?>"
                                data-kode_pos="<?= htmlspecialchars($address['kode_pos']) ?>"
                                data-provinsi="<?= htmlspecialchars($address['provinsi']) ?>">
                                <?= htmlspecialchars($address['alamat_lengkap'] . ', ' . $address['kota']) ?>
                            </option>
                        <?php endforeach; ?>
                        <option value="new_address">Tambah Alamat Baru</option>
                    </select>
                </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label for="nama_penerima" class="form-label">Nama Penerima</label>
                    <input type="text" class="form-control" id="nama_penerima" name="nama_penerima" placeholder="Nama Lengkap Penerima" value="<?= htmlspecialchars($selected_address['nama_penerima']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="no_telepon" class="form-label">Nomor Telepon</label>
                    <input type="text" class="form-control" id="no_telepon" name="no_telepon" placeholder="Nomor Telepon" value="<?= htmlspecialchars($selected_address['no_telepon']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="alamat_lengkap" class="form-label">Alamat Lengkap</label>
                    <textarea class="form-control" id="alamat_lengkap" name="alamat_lengkap" rows="3" placeholder="Contoh: Jl. Merdeka No. 10, RT 05 RW 03" required><?= htmlspecialchars($selected_address['alamat_lengkap']) ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="desa" class="form-label">Desa/Kelurahan</label>
                    <input type="text" class="form-control" id="desa" name="desa" placeholder="Desa/Kelurahan" value="<?= htmlspecialchars($selected_address['desa']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="distrik" class="form-label">Kecamatan</label>
                    <input type="text" class="form-control" id="distrik" name="distrik" placeholder="Kecamatan" value="<?= htmlspecialchars($selected_address['distrik']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="kota" class="form-label">Kota/Kabupaten</label>
                    <input type="text" class="form-control" id="kota" name="kota" placeholder="Kota/Kabupaten" value="<?= htmlspecialchars($selected_address['kota']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="provinsi" class="form-label">Provinsi</label>
                    <input type="text" class="form-control" id="provinsi" name="provinsi" placeholder="Provinsi" value="<?= htmlspecialchars($selected_address['provinsi']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="kodepos" class="form-label">Kode Pos</label>
                    <input type="text" class="form-control" id="kodepos" name="kode_pos" placeholder="Masukkan Kode Pos" value="<?= htmlspecialchars($selected_address['kode_pos']) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="expedition" class="form-label">Ekspedisi</label>
                    <select class="form-select" id="expedition" name="expedition">
                        <option value="JNE EXPRESS" data-cost="15000">JNE EXPRESS (Rp 15.000)</option>
                        <option value="J&T" data-cost="14000">J&T (Rp 14.000)</option>
                        <option value="SiCepat" data-cost="16000">SiCepat (Rp 16.000)</option>
                        <option value="AnterAja" data-cost="13000">AnterAja (Rp 13.000)</option>
                    </select>
                </div>

                <div class="mb-3" id="paymentProofSection" style="display:none;">
                    <label for="payment-proof" class="form-label">Bukti Pembayaran</label>
                    <div class="position-relative">
                        <input type="file" id="payment-proof" name="payment_proof" class="form-control opacity-0 position-absolute w-100 h-100 top-0 start-0" style="z-index: 2; cursor: pointer;">
                        <div class="form-control d-flex justify-content-between align-items-center" style="z-index: 1;">
                            <span class="text-muted" id="fileName">Unggah file</span>
                            <i class="fas fa-camera text-muted"></i>
                        </div>
                    </div>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="save-info" name="save_info">
                    <label class="form-check-label" for="save-info">
                        Simpan informasi ini untuk checkout lebih cepat di lain waktu
                    </label>
                </div>

                <div class="input-group mb-3">
                    <input type="text" class="form-control" placeholder="Kode Kupon" id="couponCode" name="coupon_code" value="<?= htmlspecialchars($initialCouponCode) ?>">
                    <button type="button" class="btn btn-pink-outline" id="applyCoupon">Terapkan Kupon</button>
                </div>

                <div id="couponMessage" class="mb-3"></div>

                <button type="submit" class="btn btn-pink w-100">Buat Pesanan</button>
            </div>

            <div class="col-md-6">
                <?php if (!empty($checkoutItems)): ?>
                    <h4>Ringkasan Pesanan</h4>
                    <hr>
                    <?php foreach ($checkoutItems as $item): ?>
                        <div class="product-item">
                            <div class="product-info">
                                <img src="admin/uploads/<?= htmlspecialchars(trim($item['gambar'])) ?>" alt="<?= htmlspecialchars($item['nama_produk']) ?>">
                                <span><?= htmlspecialchars($item['nama_produk']) ?> (x<?= $item['quantity'] ?>)</span>
                            </div>
                            <div class="product-price" data-price="<?= $item['subtotal_item'] ?>">Rp<?= number_format($item['subtotal_item'], 0, ',', '.') ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Tidak ada produk yang dipilih untuk checkout.</p>
                <?php endif; ?>

                <hr>

                <div class="d-flex justify-content-between">
                    <span>Subtotal:</span>
                    <span id="subtotalDisplay">Rp<?= number_format($totalCheckoutHarga, 0, ',', '.') ?></span>
                    <input type="hidden" id="subtotalValue" value="<?= $totalCheckoutHarga ?>">
                </div>
                <div class="d-flex justify-content-between">
                    <span>Pengiriman:</span>
                    <span id="shippingCostDisplay">Gratis</span>
                    <input type="hidden" id="shippingCostValue" value="0">
                </div>
                <div class="d-flex justify-content-between" id="adminFeeRow" style="display:none;">
                    <span>Biaya Admin:</span>
                    <span id="adminFeeDisplay">Rp0</span>
                    <input type="hidden" id="adminFeeValue" value="0">
                </div>
                <div class="d-flex justify-content-between" id="discountRow" style="display:<?= $initialDiscount > 0 ? 'flex' : 'none' ?>;">
                    <span>Diskon:</span>
                    <span id="discountDisplay">-Rp<?= number_format($initialDiscount, 0, ',', '.') ?></span>
                    <input type="hidden" id="discountValue" value="<?= $initialDiscount ?>">
                </div>
                <div class="d-flex justify-content-between fw-bold mt-2">
                    <span>Total:</span>
                    <span id="totalDisplay">Rp<?= number_format($totalCheckoutHarga - $initialDiscount, 0, ',', '.') ?></span>
                    <input type="hidden" id="totalValue" name="total_transaksi" value="<?= $totalCheckoutHarga - $initialDiscount ?>">
                </div>

                <hr>

                <div class="mb-3">
                    <div class="form-check mb-2 d-flex align-items-center">
                        <input type="radio" name="payment_method" id="e_wallet" class="form-check-input me-2" value="e_wallet" required>
                        <label for="e_wallet" class="form-check-label me-3">E-Wallet</label>

                        <div class="custom-dropdown flex-grow-1" id="eWalletDropdown">
                            <div class="selected-option" id="selectedEWalletOption">
                                <span>Pilih E-Wallet</span>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="options" id="eWalletOptions">
                                <div class="option" data-value="ELPAY" data-admin-fee="0">
                                    <img src="img/elpay.png" alt="ELPAY"> ELPAY
                                </div>
                                <div class="option" data-value="OVO" data-admin-fee="1000">
                                    <img src="img/ovo.png" alt="OVO"> OVO
                                </div>
                                <div class="option" data-value="DANA" data-admin-fee="1200">
                                    <img src="img/dana.png" alt="DANA"> DANA
                                </div>
                            </div>
                            <input type="hidden" name="selected_e_wallet" id="selectedEWallet">
                        </div>
                    </div>
                </div>

                <div>
                    <input type="radio" name="payment_method" id="cod" class="form-check-input me-2" value="COD">
                    <label for="cod" class="form-check-label">Cash on Delivery (COD)</label>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Pastikan semua variabel DOM elemen didefinisikan dengan benar
    const eWalletDropdown = document.getElementById('eWalletDropdown');
    const selectedEWalletOption = document.getElementById('selectedEWalletOption');
    const eWalletOptions = document.getElementById('eWalletOptions');
    const selectedEWalletHiddenInput = document.getElementById('selectedEWallet');
    const paymentProofSection = document.getElementById('paymentProofSection');
    const paymentMethodRadios = document.querySelectorAll('input[name="payment_method"]');
    const fileNameDisplay = document.getElementById('fileName');
    const paymentProofInput = document.getElementById('payment-proof'); // Added this line for clarity
    const subtotalDisplay = document.getElementById('subtotalDisplay');
    const subtotalValue = document.getElementById('subtotalValue');
    const shippingCostDisplay = document.getElementById('shippingCostDisplay');
    const shippingCostValue = document.getElementById('shippingCostValue');
    const adminFeeDisplay = document.getElementById('adminFeeDisplay');
    const adminFeeValue = document.getElementById('adminFeeValue');
    const adminFeeRow = document.getElementById('adminFeeRow');
    const discountDisplay = document.getElementById('discountDisplay');
    const discountValue = document.getElementById('discountValue');
    const discountRow = document.getElementById('discountRow');
    const totalDisplay = document.getElementById('totalDisplay');
    const totalValue = document.getElementById('totalValue');
    const expeditionSelect = document.getElementById('expedition');
    const couponCodeInput = document.getElementById('couponCode');
    const applyCouponButton = document.getElementById('applyCoupon');
    const couponMessage = document.getElementById('couponMessage');

    // Address fields
    const savedAddressSelect = document.getElementById('saved_address');
    const namaPenerimaInput = document.getElementById('nama_penerima');
    const noTeleponInput = document.getElementById('no_telepon');
    const alamatLengkapInput = document.getElementById('alamat_lengkap');
    const desaInput = document.getElementById('desa');
    const distrikInput = document.getElementById('distrik');
    const kotaInput = document.getElementById('kota');
    const kodePosInput = document.getElementById('kodepos');
    const provinsiInput = document.getElementById('provinsi');

    // Inisialisasi nilai diskon saat halaman dimuat
    let currentAdminFee = 0;
    let currentShippingCost = 0;
    let currentDiscount = parseFloat(document.getElementById('appliedDiscountAmount').value) || 0;


    // Calculate initial subtotal from product items displayed on the page
    function calculateSubtotal() {
        let sum = 0;
        document.querySelectorAll('.product-price').forEach(item => {
            const priceText = item.getAttribute('data-price');
            sum += parseFloat(priceText);
        });
        subtotalValue.value = sum;
        subtotalDisplay.textContent = `Rp${sum.toLocaleString('id-ID')}`;
        return sum;
    }

    function updateTotal() {
        let sub = parseFloat(subtotalValue.value);
        let total = sub + currentShippingCost + currentAdminFee - currentDiscount;
        totalValue.value = total;
        totalDisplay.textContent = `Rp${total.toLocaleString('id-ID')}`;
    }

    // Handle payment method change
    paymentMethodRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'e_wallet') {
                eWalletDropdown.style.display = 'block';
                paymentProofSection.style.display = 'block';
                // Make payment proof required when E-Wallet is selected
                paymentProofInput.setAttribute('required', 'required'); // ADDED THIS LINE

                // Set default selected E-Wallet option and admin fee if none is selected
                let selectedEWalletOptionData = eWalletOptions.querySelector('.option[data-value="' + selectedEWalletHiddenInput.value + '"]');
                if (!selectedEWalletOptionData) {
                    selectedEWalletOptionData = eWalletOptions.querySelector('.option'); // Fallback to first option
                    if (selectedEWalletOptionData) {
                        selectedEWalletHiddenInput.value = selectedEWalletOptionData.dataset.value;
                        selectedEWalletOption.innerHTML = `<img src="img/${selectedEWalletOptionData.dataset.value.toLowerCase()}.png" alt="${selectedEWalletOptionData.dataset.value}"> ${selectedEWalletOptionData.dataset.value} <i class="fas fa-chevron-down"></i>`;
                    }
                }
                currentAdminFee = parseFloat(selectedEWalletOptionData ? selectedEWalletOptionData.dataset.adminFee : 0);
                adminFeeRow.style.display = 'flex';
                adminFeeDisplay.textContent = `Rp${currentAdminFee.toLocaleString('id-ID')}`;
            } else { // Handles COD and any other non-e_wallet method
                eWalletDropdown.style.display = 'none';
                eWalletOptions.style.display = 'none'; // Hide options if opened
                paymentProofSection.style.display = 'none';
                // Remove required attribute for non-E-Wallet methods (like COD)
                paymentProofInput.removeAttribute('required'); // ADDED THIS LINE
                // Clear the file input's value and display text
                paymentProofInput.value = '';
                fileNameDisplay.textContent = 'Unggah file';

                currentAdminFee = 0;
                adminFeeRow.style.display = 'none';
            }
            updateTotal();
        });
    });

    // Initial check on page load to set correct required state for paymentProofInput
    document.addEventListener('DOMContentLoaded', function() {
        const initialSelectedMethod = document.querySelector('input[name="payment_method"]:checked');
        if (initialSelectedMethod && initialSelectedMethod.value === 'e_wallet') {
            paymentProofInput.setAttribute('required', 'required');
            eWalletDropdown.style.display = 'block';
            paymentProofSection.style.display = 'block';

            // Ensure initial E-Wallet state is correct
            let selectedEWalletOptionData = eWalletOptions.querySelector('.option[data-value="' + selectedEWalletHiddenInput.value + '"]');
            if (!selectedEWalletOptionData) { // If no E-Wallet is selected or default is needed
                selectedEWalletOptionData = eWalletOptions.querySelector('.option');
                if (selectedEWalletOptionData) {
                    selectedEWalletHiddenInput.value = selectedEWalletOptionData.dataset.value;
                    selectedEWalletOption.innerHTML = `<img src="img/${selectedEWalletOptionData.dataset.value.toLowerCase()}.png" alt="${selectedEWalletOptionData.dataset.value}"> ${selectedEWalletOptionData.dataset.value} <i class="fas fa-chevron-down"></i>`;
                }
            }
            currentAdminFee = parseFloat(selectedEWalletOptionData ? selectedEWalletOptionData.dataset.adminFee : 0);
            adminFeeRow.style.display = 'flex';
            adminFeeDisplay.textContent = `Rp${currentAdminFee.toLocaleString('id-ID')}`;

        } else {
            paymentProofInput.removeAttribute('required');
            paymentProofInput.value = '';
            fileNameDisplay.textContent = 'Unggah file';
            eWalletDropdown.style.display = 'none';
            paymentProofSection.style.display = 'none';
            currentAdminFee = 0;
            adminFeeRow.style.display = 'none';
        }
        updateTotal();
    });


    // Handle E-Wallet dropdown
    selectedEWalletOption.addEventListener('click', () => {
        eWalletOptions.style.display = eWalletOptions.style.display === 'block' ? 'none' : 'block';
    });

    eWalletOptions.querySelectorAll('.option').forEach(option => {
        option.addEventListener('click', function() {
            const value = this.dataset.value;
            const adminFee = parseFloat(this.dataset.adminFee);
            selectedEWalletOption.innerHTML = `<img src="img/${value.toLowerCase()}.png" alt="${value}"> ${value} <i class="fas fa-chevron-down"></i>`; // Perbaikan path gambar
            selectedEWalletHiddenInput.value = value;
            eWalletOptions.style.display = 'none';

            // Update admin fee based on selected E-Wallet
            currentAdminFee = adminFee;
            adminFeeDisplay.textContent = `Rp${currentAdminFee.toLocaleString('id-ID')}`;
            adminFeeRow.style.display = 'flex';
            updateTotal();
        });
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', (event) => {
        if (!eWalletDropdown.contains(event.target)) {
            eWalletOptions.style.display = 'none';
        }
    });

    // Handle file input display
    paymentProofInput.addEventListener('change', function() {
        if (this.files && this.files.length > 0) {
            fileNameDisplay.textContent = this.files[0].name;
        } else {
            fileNameDisplay.textContent = 'Unggah file';
        }
    });

    // Handle expedition selection
    expeditionSelect.addEventListener('change', function() {
        currentShippingCost = parseFloat(this.options[this.selectedIndex].dataset.cost || 0);
        shippingCostValue.value = currentShippingCost;
        shippingCostDisplay.textContent = `Rp${currentShippingCost.toLocaleString('id-ID')}`;
        updateTotal();
    });

    // Handle coupon application (AJAX call to a backend script)
    applyCouponButton.addEventListener('click', function() {
        const couponCode = couponCodeInput.value.trim();
        if (couponCode === '') {
            couponMessage.innerHTML = '<div class="alert alert-warning">Please enter a coupon code.</div>';
            return;
        }

        // Pastikan total_amount yang dikirim adalah nilai setelah dihitung subtotal, ongkir, dll.
        // Untuk penerapan kupon, umumnya diskon dihitung dari subtotal produk
        const amountForCoupon = parseFloat(subtotalValue.value);

        fetch('apply_coupon.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `coupon_code=${encodeURIComponent(couponCode)}&total_amount=${encodeURIComponent(amountForCoupon)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentDiscount = parseFloat(data.discount_amount);
                discountValue.value = currentDiscount;
                document.getElementById('appliedDiscountAmount').value = currentDiscount; // Update hidden input
                document.getElementById('appliedCouponCodeHidden').value = couponCode; // Update hidden input
                discountDisplay.textContent = `-Rp${currentDiscount.toLocaleString('id-ID')}`;
                discountRow.style.display = 'flex';
                couponMessage.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
            } else {
                currentDiscount = 0;
                discountValue.value = 0;
                document.getElementById('appliedDiscountAmount').value = 0; // Update hidden input
                document.getElementById('appliedCouponCodeHidden').value = ''; // Clear hidden input
                discountRow.style.display = 'none';
                couponMessage.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
            }
            updateTotal();
        })
        .catch(error => {
            console.error('Error applying coupon:', error); // COMPLETED THIS LINE
            couponMessage.innerHTML = `<div class="alert alert-danger">Terjadi kesalahan saat menerapkan kupon. Silakan coba lagi.</div>`;
        });
    });

    // Handle saved address selection
    if (savedAddressSelect) {
        savedAddressSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value === 'new_address') {
                // Clear all fields for new address entry
                namaPenerimaInput.value = '';
                noTeleponInput.value = '';
                alamatLengkapInput.value = '';
                desaInput.value = '';
                distrikInput.value = '';
                kotaInput.value = '';
                kodePosInput.value = '';
                provinsiInput.value = '';
                // Set focus to the first new address field
                namaPenerimaInput.focus();
            } else {
                // Populate fields with selected address data
                namaPenerimaInput.value = selectedOption.dataset.namaPenerima;
                noTeleponInput.value = selectedOption.dataset.noTelepon;
                alamatLengkapInput.value = selectedOption.dataset.alamatLengkap;
                desaInput.value = selectedOption.dataset.desa;
                distrikInput.value = selectedOption.dataset.distrik;
                kotaInput.value = selectedOption.dataset.kota;
                kodePosInput.value = selectedOption.dataset.kodePos;
                provinsiInput.value = selectedOption.dataset.provinsi;
            }
        });
    }

    // Initial calculations on page load
    calculateSubtotal();
    updateTotal();
</script>
</body>
</html>