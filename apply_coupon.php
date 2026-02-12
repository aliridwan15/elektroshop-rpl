<?php
session_start();
include 'koneksi.php'; // Pastikan path ke koneksi database Anda benar

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $couponCode = isset($_POST['coupon_code']) ? trim($_POST['coupon_code']) : '';
    $totalAmount = isset($_POST['total_amount']) ? (float)$_POST['total_amount'] : 0;

    if (empty($couponCode)) {
        $response['message'] = 'Kode kupon tidak boleh kosong.';
        echo json_encode($response);
        exit();
    }

    if ($totalAmount <= 0) {
        $response['message'] = 'Jumlah total pesanan tidak valid untuk diskon.';
        echo json_encode($response);
        exit();
    }

    // Ambil detail kupon dari database
    $stmt = $conn->prepare("SELECT diskon, start_date, end_date, status FROM kupon_diskon WHERE code = ?");
    $stmt->bind_param("s", $couponCode);
    $stmt->execute();
    $result = $stmt->get_result();
    $coupon = $result->fetch_assoc();
    $stmt->close();

    if ($coupon) {
        // Validasi status kupon
        if ($coupon['status'] === 'expired') {
            $response['message'] = 'Kupon ini sudah kadaluarsa.';
            echo json_encode($response);
            exit();
        }

        // Validasi tanggal kupon
        $currentDate = date('Y-m-d');
        if ($currentDate < $coupon['start_date']) {
            $response['message'] = 'Kupon ini belum aktif.';
            echo json_encode($response);
            exit();
        }
        if ($currentDate > $coupon['end_date']) {
            $response['message'] = 'Kupon ini sudah tidak berlaku.';
            // Opsional: Perbarui status kupon menjadi 'expired' di database
            $updateStmt = $conn->prepare("UPDATE kupon_diskon SET status = 'expired' WHERE code = ?");
            $updateStmt->bind_param("s", $couponCode);
            $updateStmt->execute();
            $updateStmt->close();
            echo json_encode($response);
            exit();
        }

        $discountPercentage = $coupon['diskon']; // Ini adalah persentase, misal 10.50
        $discountAmount = ($totalAmount * $discountPercentage) / 100;

        // Simpan diskon ke sesi agar bisa diakses saat proses checkout final
        $_SESSION['applied_coupon_code'] = $couponCode;
        $_SESSION['applied_discount_amount'] = $discountAmount;

        $response['success'] = true;
        $response['message'] = 'Kupon berhasil diterapkan! Anda mendapatkan diskon ' . number_format($discountPercentage, 0, ',', '.') . '%.';
        $response['discount_amount'] = $discountAmount;
    } else {
        $response['message'] = 'Kode kupon tidak valid.';
    }
} else {
    $response['message'] = 'Metode permintaan tidak valid.';
}

echo json_encode($response);
?>