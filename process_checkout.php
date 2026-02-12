<?php
session_start();
include 'koneksi.php'; // Pastikan file koneksi Anda sudah benar dan `$conn` tersedia

// Pastikan user sudah login
if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit();
}

$loggedInUserId = (int)$_SESSION['id'];

// Mengambil data dari POST
$checkoutProducts = $_POST['checkout_products'] ?? [];
$totalBeratProduk = (float)($_POST['total_berat_produk'] ?? 0);
$appliedDiscountAmount = (float)($_POST['applied_discount_amount'] ?? 0);
$appliedCouponCode = $_POST['applied_coupon_code_hidden'] ?? '';

// Detail alamat pengiriman
$namaPenerima = $_POST['nama_penerima'] ?? '';
$noTelepon = $_POST['no_telepon'] ?? '';
$alamatLengkap = $_POST['alamat_lengkap'] ?? '';
$desa = $_POST['desa'] ?? '';
$distrik = $_POST['distrik'] ?? '';
$kota = $_POST['kota'] ?? '';
$kodePos = $_POST['kode_pos'] ?? '';
$provinsi = $_POST['provinsi'] ?? '';
$expedition = $_POST['expedition'] ?? '';
$paymentMethod = $_POST['payment_method'] ?? '';
$selectedEWallet = $_POST['selected_e_wallet'] ?? ''; // Untuk E-Wallet
$paymentProof = $_FILES['payment_proof'] ?? null; // Data file yang diunggah

// Total transaksi dari POST (dihitung di client-side)
$totalTransaksiClient = (float)($_POST['total_transaksi'] ?? 0);

// Mengambil biaya admin berdasarkan metode pembayaran
$adminFee = 0;
if ($paymentMethod === 'e_wallet' && $selectedEWallet) {
    switch ($selectedEWallet) {
        case 'ELPAY':
            $adminFee = 0;
            break;
        case 'OVO':
            $adminFee = 1000;
            break;
        case 'DANA':
            $adminFee = 1200;
            break;
        default:
            $adminFee = 0;
            break;
    }
}

// Ambil biaya pengiriman dari ekspedisi
$shippingCost = 0;
switch ($expedition) {
    case 'JNE EXPRESS': $shippingCost = 15000; break;
    case 'J&T': $shippingCost = 14000; break;
    case 'SiCepat': $shippingCost = 16000; break;
    case 'AnterAja': $shippingCost = 13000; break;
    default: $shippingCost = 0; break;
}

// Pastikan total transaksi dihitung ulang di sini untuk validasi
$calculatedSubtotal = 0;
foreach ($checkoutProducts as $productId => $details) {
    $calculatedSubtotal += (float)$details['price'] * (int)$details['quantity'];
}
$finalTotal = $calculatedSubtotal + $shippingCost + $adminFee - $appliedDiscountAmount;

// Inisialisasi variabel untuk tampilan modal
$productSummary = [];
$productNames = [];
$totalQuantity = 0;

foreach ($checkoutProducts as $productId => $details) {
    $productNames[] = htmlspecialchars($details['name']);
    $totalQuantity += (int)$details['quantity'];
    $productSummary[] = htmlspecialchars($details['name']) . " (x" . htmlspecialchars($details['quantity']) . ")";
}
$productSummaryText = implode(', ', $productSummary);

// --- Penanganan Upload Bukti Pembayaran ---
$paymentProofPersistedTempPath = null;
$paymentProofError = null; // Inisialisasi variabel untuk pesan error upload

if ($paymentMethod === 'e_wallet' && $paymentProof && $paymentProof['error'] == UPLOAD_ERR_OK) {
    $tempTargetDir = "uploads/temp_proofs/"; // Direktori sementara untuk menyimpan file upload
    if (!is_dir($tempTargetDir)) {
        mkdir($tempTargetDir, 0777, true); // Buat direktori jika belum ada
    }

    $fileName = uniqid() . '_' . basename($paymentProof['name']); // Buat nama file unik
    $tempFilePath = $tempTargetDir . $fileName; // Jalur lengkap ke file sementara

    $fileType = strtolower(pathinfo($tempFilePath, PATHINFO_EXTENSION));
    $allowTypes = array('jpg', 'png', 'jpeg', 'gif', 'pdf'); // Tipe file yang diizinkan

    if (in_array($fileType, $allowTypes)) {
        // Pindahkan file yang diunggah ke lokasi sementara yang persisten
        if (move_uploaded_file($paymentProof['tmp_name'], $tempFilePath)) {
            $paymentProofPersistedTempPath = $tempFilePath; // Simpan jalur file sementara
        } else {
            $paymentProofError = "Gagal memindahkan file bukti pembayaran ke lokasi sementara.";
        }
    } else {
        $paymentProofError = "Maaf, hanya format JPG, JPEG, PNG, GIF, & PDF yang diizinkan untuk bukti pembayaran.";
    }
} else if ($paymentProof && $paymentProof['error'] !== UPLOAD_ERR_OK) {
    // Tangani error upload lainnya dari PHP
    switch ($paymentProof['error']) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            $paymentProofError = "Ukuran file bukti pembayaran terlalu besar.";
            break;
        case UPLOAD_ERR_PARTIAL:
            $paymentProofError = "File bukti pembayaran hanya terunggah sebagian.";
            break;
        case UPLOAD_ERR_NO_FILE:
            // Ini akan ditangani oleh validasi di finalize_transaction.php jika e-wallet
            break;
        default:
            $paymentProofError = "Terjadi kesalahan yang tidak diketahui saat mengunggah bukti pembayaran.";
            break;
    }
}

// Simpan semua data POST dan perhitungan ke dalam sesi
$_SESSION['checkout_data'] = [
    'loggedInUserId' => $loggedInUserId,
    'checkout_products' => $checkoutProducts,
    'total_berat_produk' => $totalBeratProduk,
    'applied_discount_amount' => $appliedDiscountAmount,
    'applied_coupon_code' => $appliedCouponCode,
    'nama_penerima' => $namaPenerima,
    'no_telepon' => $noTelepon,
    'alamat_lengkap' => $alamatLengkap,
    'desa' => $desa,
    'distrik' => $distrik,
    'kota' => $kota,
    'kode_pos' => $kodePos,
    'provinsi' => $provinsi,
    'expedition' => $expedition,
    'payment_method' => $paymentMethod,
    'selected_e_wallet' => $selectedEWallet,
    'final_total' => $finalTotal,
    'admin_fee' => $adminFee,
    'shipping_cost' => $shippingCost,
    'product_names' => $productNames,
    'total_quantity' => $totalQuantity,
    'payment_proof_persisted_temp_path' => $paymentProofPersistedTempPath, // Simpan jalur file sementara yang persisten
    'payment_proof_name' => $paymentProof['name'] ?? null, // Simpan nama asli file
    'payment_proof_error_code' => $paymentProof['error'] ?? null, // Simpan kode error upload
    'payment_proof_error' => $paymentProofError // Simpan pesan error untuk ditampilkan
];

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pesanan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color:rgb(255, 75, 165);
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .btn-pink {
            background-color: #ff1493;
            color: #fff;
            border: none;
        }
        .btn-pink:hover {
            background-color: #e01383;
            color: #fff;
        }
    </style>
</head>
<body>

<div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmationModalLabel">Konfirmasi Transaksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="window.history.back();"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin melakukan transaksi ini?</p>
                <p>
                    Transaksi untuk: <?= htmlspecialchars(implode(', ', $productNames)) ?>
                    <br>Dengan jumlah total: <?= $totalQuantity ?> item
                    <br>Seharga: Rp<?= number_format($finalTotal, 0, ',', '.') ?>
                    <br>Menggunakan metode pembayaran: <?= htmlspecialchars($paymentMethod === 'e_wallet' ? 'E-Wallet (' . $selectedEWallet . ')' : $paymentMethod) ?>
                    <br>Dengan ekspedisi: <?= htmlspecialchars($expedition) ?>
                </p>
                <?php if (isset($_SESSION['checkout_data']['payment_proof_error']) && $_SESSION['checkout_data']['payment_proof_error']): ?>
                    <div class="alert alert-danger mt-3"><?= htmlspecialchars($_SESSION['checkout_data']['payment_proof_error']) ?></div>
                    <?php unset($_SESSION['checkout_data']['payment_proof_error']); // Hapus error setelah ditampilkan ?>
                <?php endif; ?>

                <div class="mb-3 mt-4">
                    <label for="konfirmasiInput" class="form-label">Untuk melanjutkan, ketik KONFIRMASI di bawah ini:</label>
                    <input type="text" class="form-control" id="konfirmasiInput" placeholder="Ketik KONFIRMASI">
                    <div class="invalid-feedback">
                        Anda harus mengetik "KONFIRMASI" dengan benar.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="window.history.back();">Kembali</button>
                <button type="button" class="btn btn-pink" id="confirmCheckoutBtn" disabled>Konfirmasi & Lanjutkan</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
        var konfirmasiInput = document.getElementById('konfirmasiInput');
        var confirmCheckoutBtn = document.getElementById('confirmCheckoutBtn');

        // Tampilkan modal saat halaman dimuat
        confirmationModal.show();

        // Event listener untuk input teks
        konfirmasiInput.addEventListener('input', function() {
            if (this.value.toUpperCase() === 'KONFIRMASI') {
                confirmCheckoutBtn.removeAttribute('disabled');
                this.classList.remove('is-invalid');
            } else {
                confirmCheckoutBtn.setAttribute('disabled', 'disabled');
                this.classList.add('is-invalid');
            }
        });

        // Event listener untuk tombol konfirmasi
        confirmCheckoutBtn.addEventListener('click', function() {
            // Pastikan input sudah benar sebelum redirect
            if (konfirmasiInput.value.toUpperCase() === 'KONFIRMASI') {
                window.location.href = 'finalize_transaction.php?confirmed=true';
            } else {
                konfirmasiInput.classList.add('is-invalid');
            }
        });

        // Pastikan modal ditutup dan redirect jika tombol close diklik
        var closeButton = document.querySelector('#confirmationModal .btn-close');
        closeButton.addEventListener('click', function() {
            window.history.back();
        });
    });
</script>
</body>
</html>