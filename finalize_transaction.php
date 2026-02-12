<?php
session_start();
include 'koneksi.php'; // Pastikan file koneksi Anda sudah benar dan `$conn` tersedia

// --- WAJIB DIAKTIFKAN UNTUK DEBUGGING ---
error_reporting(E_ALL);
ini_set('display_errors', 1);
// --------------------------------------------------

echo "DEBUG: Script finalize_transaction.php dimulai.<br>"; // Pesan awal

// Pastikan ini diakses setelah konfirmasi dari modal
if (!isset($_GET['confirmed']) || $_GET['confirmed'] !== 'true' || !isset($_SESSION['checkout_data'])) {
    echo "DEBUG: Gagal pada pengecekan awal. Mengarahkan kembali ke checkout.php.<br>";
    header('Location: checkout.php?error=invalid_access');
    exit();
}

echo "DEBUG: Pengecekan awal BERHASIL. Memuat data sesi.<br>";

$checkoutData = $_SESSION['checkout_data'];

echo "DEBUG: Isi dari \$_SESSION['checkout_data']:<br>";
echo "<pre>";
var_dump($checkoutData); // Lihat isi lengkap data checkout
echo "</pre><hr>";

// Ambil semua data checkout dari sesi
$loggedInUserId = $checkoutData['loggedInUserId'];
$checkoutProducts = $checkoutData['checkout_products'];
$appliedDiscountAmount = $checkoutData['applied_discount_amount'];
$appliedCouponCode = $checkoutData['applied_coupon_code'];
$namaPenerima = $checkoutData['nama_penerima'];
$noTelepon = $checkoutData['no_telepon'];
$alamatLengkap = $checkoutData['alamat_lengkap'];
$desa = $checkoutData['desa'];
$distrik = $checkoutData['distrik'];
$kota = $checkoutData['kota'];
$kodePos = $checkoutData['kode_pos'];
$provinsi = $checkoutData['provinsi'];
$expedition = $checkoutData['expedition'];
$paymentMethod = $checkoutData['payment_method']; // Bisa 'e_wallet' atau 'COD'
$selectedEWallet = $checkoutData['selected_e_wallet'] ?? null; // 'ELPAY', 'OVO', 'DANA'
$finalTotal = $checkoutData['final_total'];

// --- PENTING: Mengambil jalur file sementara yang sudah dipersist dari sesi ---
// Asumsi: Skrip yang menerima form submit awal (misalnya checkout.php atau confirm_checkout.php)
// telah memindahkan file yang diunggah ke 'uploads/temp_proofs/'
// dan menyimpan jalurnya di $_SESSION['checkout_data']['payment_proof_persisted_temp_path']
$paymentProofPersistedTempPath = $checkoutData['payment_proof_persisted_temp_path'] ?? null;
$paymentProofName = $checkoutData['payment_proof_name'] ?? null;
$paymentProofErrorCode = $checkoutData['payment_proof_error_code'] ?? null;


// --- LOGIKA PENENTUAN METODE PEMBAYARAN UNTUK DATABASE ---
$paymentMethodForDB = 'COD'; // Default fallback jika ada masalah

echo "DEBUG: Metode pembayaran yang dipilih dari sesi: {$paymentMethod}<br>";
echo "DEBUG: E-Wallet spesifik yang dipilih dari sesi: {$selectedEWallet}<br>";

if ($paymentMethod === 'e_wallet') {
    switch ($selectedEWallet) {
        case 'ELPAY':
            $paymentMethodForDB = 'ELPAY';
            break;
        case 'OVO':
            $paymentMethodForDB = 'OVO';
            break;
        case 'DANA':
            $paymentMethodForDB = 'DANA';
            break;
        default:
            echo "DEBUG: E-Wallet tidak dikenal ({$selectedEWallet}). Fallback ke OVO.<br>";
            $paymentMethodForDB = 'OVO'; // Fallback E-Wallet jika tidak dikenal
            break;
    }
} else if ($paymentMethod === 'COD') {
    $paymentMethodForDB = 'COD';
} else {
    echo "DEBUG: Metode pembayaran tidak valid: {$paymentMethod}. Mengarahkan kembali ke checkout.php.<br>";
    $_SESSION['transaction_error'] = "Metode pembayaran tidak valid.";
    header('Location: checkout.php?error=invalid_payment_method');
    exit();
}
echo "DEBUG: paymentMethodForDB setelah penyesuaian: {$paymentMethodForDB}<br>";


// --- Handle Payment Proof Upload (pindahkan file sekarang) ---
$finalPaymentProofPath = null;
echo "DEBUG: Memulai penanganan bukti pembayaran.<br>";
echo "DEBUG: paymentProofPersistedTempPath: " . ($paymentProofPersistedTempPath ? $paymentProofPersistedTempPath : "NULL") . "<br>";
echo "DEBUG: paymentProofName: " . ($paymentProofName ? $paymentProofName : "NULL") . "<br>";
echo "DEBUG: paymentProofErrorCode: " . ($paymentProofErrorCode !== null ? $paymentProofErrorCode : "NULL") . "<br>";

// Validasi wajib bukti pembayaran untuk metode selain COD
if ($paymentMethodForDB !== 'COD') {
    if (!$paymentProofPersistedTempPath || $paymentProofErrorCode !== UPLOAD_ERR_OK) {
        echo "DEBUG: Metode pembayaran non-COD dipilih, tetapi bukti pembayaran tidak valid atau tidak ditemukan.<br>";
        $_SESSION['transaction_error'] = "Bukti pembayaran wajib untuk metode " . htmlspecialchars($paymentMethodForDB) . ". Silakan unggah bukti pembayaran yang valid.";
        header('Location: checkout.php?error=payment_proof_required');
        exit();
    }
}


if ($paymentProofPersistedTempPath && $paymentProofErrorCode == UPLOAD_ERR_OK) {
    $targetDir = "uploads/payment_proofs/"; // Lokasi penyimpanan akhir
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
        echo "DEBUG: Direktori target dibuat: {$targetDir}<br>";
    }
    // Buat nama file unik untuk penyimpanan akhir
    $fileName = uniqid() . '_' . basename($paymentProofName); // Pastikan $paymentProofName ada dari sesi
    $targetFilePath = $targetDir . $fileName;
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

    echo "DEBUG: Nama file target: {$targetFilePath}<br>";
    echo "DEBUG: Tipe file: {$fileType}<br>";

    $allowTypes = array('jpg', 'png', 'jpeg', 'gif', 'pdf'); // Tambahkan 'gif' jika perlu
    if (in_array($fileType, $allowTypes)) {
        // Pindahkan file dari lokasi sementara yang persisten ke lokasi akhir
        if (file_exists($paymentProofPersistedTempPath) && rename($paymentProofPersistedTempPath, $targetFilePath)) {
            $finalPaymentProofPath = $targetFilePath;
            echo "DEBUG: File bukti pembayaran berhasil dipindahkan dari temp ke final.<br>";
            // Bersihkan jalur file sementara dari sesi setelah berhasil dipindahkan
            unset($_SESSION['checkout_data']['payment_proof_persisted_temp_path']);
            // Opsional: Hapus file fisik di temp_proofs jika `rename` tidak menghapusnya (biasanya sudah terhapus)
            // if (file_exists($paymentProofPersistedTempPath)) {
            //      unlink($paymentProofPersistedTempPath);
            // }
        } else {
            echo "DEBUG: Gagal memindahkan file bukti pembayaran dari temp ke final. Mengarahkan kembali.<br>";
            $_SESSION['transaction_error'] = "Gagal memindahkan file bukti pembayaran (DEBUG: " . $paymentProofPersistedTempPath . " -> " . $targetFilePath . "). Pastikan file sudah diproses di halaman sebelumnya.";
            header('Location: checkout.php?error=upload_failed_final');
            exit();
        }
    } else {
        echo "DEBUG: Tipe file tidak diizinkan. Mengarahkan kembali.<br>";
        $_SESSION['transaction_error'] = "Maaf, hanya format JPG, JPEG, PNG, GIF, & PDF yang diizinkan untuk bukti pembayaran.";
        header('Location: checkout.php?error=invalid_file_type_final');
        exit();
    }
} else {
    echo "DEBUG: Tidak ada bukti pembayaran untuk diunggah (COD atau error awal).<br>";
    // Jika ini adalah E-Wallet dan sampai sini, berarti ada masalah yang sudah ditangani di atas.
}
echo "DEBUG: finalPaymentProofPath: " . ($finalPaymentProofPath ? $finalPaymentProofPath : "NULL") . "<br>";


// --- LOGIKA PROSES TRANSAKSI ---
echo "DEBUG: Memulai blok TRY untuk transaksi database.<br>";
try {
    $conn->begin_transaction();
    echo "DEBUG: Transaksi database dimulai.<br>";

    // 0. Cek atau simpan alamat pengiriman baru
    echo "DEBUG: Memulai pengecekan/penyimpanan alamat pengiriman.<br>";
    $idAlamatPengiriman = null;
    $stmt = $conn->prepare("SELECT id_alamat FROM alamat_pengiriman WHERE id_pengguna = ? AND nama_penerima = ? AND no_telepon = ? AND alamat_lengkap = ? AND desa = ? AND distrik = ? AND kota = ? AND kode_pos = ? AND provinsi = ?");
    $stmt->bind_param("issssssss",
        $loggedInUserId, $namaPenerima, $noTelepon, $alamatLengkap, $desa, $distrik, $kota, $kodePos, $provinsi
    );
    $stmt->execute();
    if ($stmt->error) { throw new Exception("SQL Error (Alamat SELECT): " . $stmt->error); }
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $idAlamatPengiriman = $row['id_alamat'];
        echo "DEBUG: Alamat pengiriman ditemukan: ID " . $idAlamatPengiriman . "<br>";
    }
    $stmt->close();

    // Jika alamat tidak ditemukan, masukkan sebagai alamat baru
    if ($idAlamatPengiriman === null) {
        echo "DEBUG: Alamat tidak ditemukan, memasukkan alamat baru.<br>";
        $stmt = $conn->prepare("INSERT INTO alamat_pengiriman (id_pengguna, nama_penerima, no_telepon, alamat_lengkap, desa, distrik, kota, kode_pos, provinsi) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssssss",
            $loggedInUserId, $namaPenerima, $noTelepon, $alamatLengkap, $desa, $distrik, $kota, $kodePos, $provinsi
        );
        $stmt->execute();
        if ($stmt->error) { throw new Exception("SQL Error (Alamat INSERT): " . $stmt->error); }
        $idAlamatPengiriman = $conn->insert_id;
        $stmt->close();
        echo "DEBUG: Alamat pengiriman baru berhasil ditambahkan: ID " . $idAlamatPengiriman . "<br>";
    }


    // 1. Dapatkan ID Kupon jika ada
    echo "DEBUG: Mencari ID Kupon.<br>";
    $idKupon = null; // Default null
    if (!empty($appliedCouponCode)) {
        $stmt = $conn->prepare("SELECT id FROM kupon_diskon WHERE code = ? AND status = 'aktif' AND end_date >= CURDATE()");
        $stmt->bind_param("s", $appliedCouponCode);
        $stmt->execute();
        if ($stmt->error) { throw new Exception("SQL Error (Kupon SELECT): " . $stmt->error); }
        $result = $stmt->get_result();
        if ($kupon = $result->fetch_assoc()) {
            $idKupon = $kupon['id'];
            echo "DEBUG: Kupon ditemukan: ID " . $idKupon . "<br>";
        } else {
            echo "DEBUG: Kupon tidak valid atau tidak ditemukan.<br>";
        }
        $stmt->close();
    } else {
        echo "DEBUG: Tidak ada kode kupon diterapkan.<br>";
    }

    // Tentukan status transaksi awal berdasarkan metode pembayaran - MODIFIED LOGIC HERE
    $statusTransaksiDB = '';
    if ($paymentMethodForDB === 'COD') {
        $statusTransaksiDB = 'pending';
        echo "DEBUG: Metode pembayaran COD. Status transaksi awal: pending.<br>";
    } else if (in_array($paymentMethodForDB, ['ELPAY', 'OVO', 'DANA'])) {
        $statusTransaksiDB = 'dibayar'; // Set to 'dibayar' for e-wallet payments
        echo "DEBUG: Metode pembayaran E-Wallet ({$paymentMethodForDB}). Status transaksi awal: dibayar.<br>";
    } else {
        // Fallback for any other unexpected paymentMethodForDB value (should ideally be caught earlier)
        $statusTransaksiDB = 'pending';
        echo "DEBUG: Metode pembayaran tidak terduga. Status transaksi awal: pending.<br>";
    }

    echo "DEBUG: Final status transaksi untuk DB: {$statusTransaksiDB}<br>";

    // Hitung tanggal pengiriman dan penerimaan
    $tanggalDikirim = date('Y-m-d', strtotime('+1 day')); // 1 hari setelah checkout
    $deliveryDays = rand(4, 5); // Acak 4 atau 5 hari
    $tanggalDiterima = date('Y-m-d', strtotime($tanggalDikirim . ' + ' . $deliveryDays . ' days'));

    // Buat nomor resi random
    // Awalan "ELSP" diikuti 12 karakter alphanumeric random
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    $randomString = '';
    for ($i = 0; $i < 12; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    $noResi = 'ELSP' . $randomString;

    echo "DEBUG: Tanggal Dikirim: {$tanggalDikirim}<br>";
    echo "DEBUG: Tanggal Diterima: {$tanggalDiterima}<br>";
    echo "DEBUG: Nomor Resi: {$noResi}<br>";

    // 2. Masukkan data ke tabel 'transaksi'
    echo "DEBUG: Memasukkan data ke tabel Transaksi.<br>";
    $stmt = $conn->prepare("INSERT INTO Transaksi (
        id_user, total_transaksi, metode_pembayaran, status_transaksi, id_kupon, bukti_pembayaran
    ) VALUES (?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("idssis",
        $loggedInUserId,
        $finalTotal,
        $paymentMethodForDB, // Sudah disesuaikan agar hanya COD, OVO, DANA, ELPAY
        $statusTransaksiDB,
        $idKupon,
        $finalPaymentProofPath
    );
    $stmt->execute();
    if ($stmt->error) { throw new Exception("SQL Error (Transaksi INSERT): " . $stmt->error . " | Data: " . $loggedInUserId . ", " . $finalTotal . ", " . $paymentMethodForDB . ", " . $statusTransaksiDB . ", " . $idKupon . ", " . $finalPaymentProofPath); }
    $idTransaksi = $conn->insert_id;
    $stmt->close();
    echo "DEBUG: Data Transaksi berhasil dimasukkan. ID Transaksi: " . $idTransaksi . "<br>";

    // 3. Masukkan data ke tabel 'pengiriman'
    echo "DEBUG: Memasukkan data ke tabel pengiriman.<br>";
    $stmt = $conn->prepare("INSERT INTO pengiriman (
        id_transaksi, id_user, id_alamat, ekspedisi, status_pengiriman, tanggal_dikirim, tanggal_diterima, no_resi
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)"); // Tambahkan no_resi di sini

    // Buat variabel untuk nilai literal 'diproses'
    $statusPengirimanAwal = 'diproses';

    $stmt->bind_param("iiisssss", // 2 'i', 1 'i', 1 's', 1 's', 2 's' for dates, 1 's' for no_resi
        $idTransaksi,
        $loggedInUserId,
        $idAlamatPengiriman,
        $expedition,
        $statusPengirimanAwal,
        $tanggalDikirim,
        $tanggalDiterima,
        $noResi // Tambahkan no_resi di sini
    );
    $stmt->execute();
    if ($stmt->error) { throw new Exception("SQL Error (Pengiriman INSERT): " . $stmt->error . " | Data: " . $idTransaksi . ", " . $loggedInUserId . ", " . $idAlamatPengiriman . ", " . $expedition . ", " . $statusPengirimanAwal . ", " . $tanggalDikirim . ", " . $tanggalDiterima . ", " . $noResi); }
    $stmt->close();
    echo "DEBUG: Data Pengiriman berhasil dimasukkan.<br>";

    // Inisialisasi cartItemsToRemove (penting jika buy now)
    $cartItemsToRemove = [];

    // 4. Masukkan detail produk ke tabel 'transaksi_detail' dan update stok produk
    echo "DEBUG: Memasukkan detail produk ke tabel transaksi_detail dan update stok.<br>";
    if (empty($checkoutProducts)) {
        echo "DEBUG: checkoutProducts kosong. Tidak ada detail transaksi yang ditambahkan.<br>";
    }
    foreach ($checkoutProducts as $productId => $details) {
        $quantity = (int)$details['quantity'];
        $price = (float)$details['price'];
        $cartItemId = $details['cart_item_id'] ?? null; // This will be null for 'buy now'

        // Insert into transaksi_detail
        $stmt = $conn->prepare("INSERT INTO transaksi_detail (id_transaksi, id_produk, jumlah, harga_satuan, subtotal) VALUES (?, ?, ?, ?, ?)");
        $subtotalItem = $quantity * $price;
        $stmt->bind_param("iiidd",
            $idTransaksi,
            $productId,
            $quantity,
            $price,
            $subtotalItem
        );
        $stmt->execute();
        if ($stmt->error) { throw new Exception("SQL Error (Detail Transaksi INSERT): " . $stmt->error . " | Produk ID: " . $productId . ", Jumlah: " . $quantity); }
        $stmt->close();
        echo "DEBUG: Detail transaksi untuk Produk ID {$productId} berhasil dimasukkan.<br>";

        // Update product stock
        $stmt = $conn->prepare("UPDATE produk SET stok = stok - ? WHERE id = ?");
        $stmt->bind_param("ii", $quantity, $productId);
        $stmt->execute();
        if ($stmt->error) { throw new Exception("SQL Error (Stok UPDATE): " . $stmt->error . " | Produk ID: " . $productId); }
        $stmt->close();
        echo "DEBUG: Stok produk ID {$productId} berhasil diupdate.<br>";

        // Add to list of cart items to remove, only if it originated from cart
        if ($cartItemId !== null) {
            $cartItemsToRemove[] = $cartItemId;
        }
    }

    // 5. Hapus item dari keranjang jika berasal dari keranjang
    echo "DEBUG: Memulai penghapusan item dari keranjang.<br>";
    if (!empty($cartItemsToRemove)) {
        $placeholders = implode(',', array_fill(0, count($cartItemsToRemove), '?'));

        // Tentukan string tipe data: 'i' untuk id_user, diikuti 'i' untuk setiap ID item keranjang
        $types = 'i' . str_repeat('i', count($cartItemsToRemove));

        $stmt = $conn->prepare("DELETE FROM cart WHERE id_user = ? AND id IN ($placeholders)");
        if ($stmt === false) { // Tambahkan pengecekan jika prepare gagal
            throw new Exception("Prepare statement failed for cart DELETE: " . $conn->error);
        }

        // Siapkan array argumen untuk bind_param, pastikan semua berupa referensi
        $bindParams = [];
        $bindParams[] = $types; // String tipe data ditambahkan pertama (secara nilai)
        $bindParams[] = &$loggedInUserId; // id_user diteruskan sebagai referensi

        // Tambahkan setiap ID item keranjang sebagai referensi
        foreach ($cartItemsToRemove as &$item) { // Penting: Gunakan '&' untuk referensi di sini
            $bindParams[] = &$item;
        }
        // Catatan: Setelah loop ini, $item akan tetap menjadi referensi ke elemen terakhir.
        // Untuk kasus ini, tidak masalah karena $cartItemsToRemove tidak digunakan lagi setelah ini.

        // Panggil bind_param secara dinamis
        call_user_func_array([$stmt, 'bind_param'], $bindParams);

        $stmt->execute();
        if ($stmt->error) {
            throw new Exception("SQL Error (Cart DELETE): " . $stmt->error . " | User ID: " . $loggedInUserId . ", Cart Items: " . implode(',', $cartItemsToRemove));
        }
        $stmt->close();
        echo "DEBUG: Item keranjang berhasil dihapus.<br>";
    } else {
        echo "DEBUG: Tidak ada item keranjang untuk dihapus.<br>";
    }

    // 6. Hapus variabel sesi yang terkait dengan checkout/kupon/buy_now
    echo "DEBUG: Menghapus variabel sesi checkout.<br>";
    unset($_SESSION['applied_discount_amount']);
    unset($_SESSION['applied_coupon_code']);
    unset($_SESSION['buy_now_product_id']);
    unset($_SESSION['buy_now_quantity']);
    unset($_SESSION['checkout_data']); // Hapus semua data checkout setelah selesai

    $conn->commit();
    echo "DEBUG: Transaksi database BERHASIL di-commit.<br>";

    // Set success message in session to be displayed on pending.php
    $_SESSION['transaction_success'] = true;
    $_SESSION['new_transaction_id'] = $idTransaksi; // Untuk menampilkan ID transaksi di halaman pending

    echo "DEBUG: Mengarahkan ke pending.php.<br>";
    header('Location: pending.php');
    exit();

} catch (Exception $e) {
    $conn->rollback();
    echo "DEBUG: Terjadi kesalahan dalam transaksi. Rollback dilakukan.<br>";
    // Log the error for debugging
    error_log("Error during transaction for user " . $loggedInUserId . ": " . $e->getMessage());
    // Set error message in session
    $_SESSION['transaction_error'] = "Terjadi kesalahan saat memproses pesanan Anda. Silakan coba lagi. Detail: " . htmlspecialchars($e->getMessage());

    // --- Tampilkan error di browser saat debugging ---
    echo "<h1>TRANSACTION ERROR!</h1>";
    echo "<p>Pesan Kesalahan: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Kode Kesalahan: " . htmlspecialchars($e->getCode()) . "</p>";
    echo "<p>Trace Penuh:</p><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    // ------------------------------------------------

    // Jangan redirect dulu saat debugging agar pesan error terlihat
    // header('Location: checkout.php?error=transaction_failed');
    exit();
}
?>