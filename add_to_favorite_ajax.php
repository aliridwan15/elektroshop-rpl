<?php
// add_to_favorite_ajax.php
session_start();
include 'koneksi.php'; // Pastikan file koneksi.php ada dan benar

header('Content-Type: application/json');

$response = [
    'status' => 'error',
    'message' => 'Terjadi kesalahan tidak dikenal.',
    'action' => '',
    'product_name' => 'Produk Ini', // Default product name
    'total_favorites' => 0
];

// 1. Check if user is logged in
if (!isset($_SESSION['id'])) {
    $response['message'] = 'Anda harus login untuk menambahkan produk ke favorit.';
    echo json_encode($response);
    exit;
}

$userId = $_SESSION['id'];

// 2. Handle POST request for adding/removing favorite
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $productId = intval($_POST['product_id']);
    $tanggalTambah = date('Y-m-d H:i:s'); // Use full timestamp for better tracking

    if ($productId <= 0) {
        $response['message'] = 'ID Produk tidak valid.';
        echo json_encode($response);
        exit;
    }

    // Ambil nama produk untuk pesan respons (moved here to always get it if product_id is valid)
    $stmtProductName = $conn->prepare("SELECT nama_produk FROM produk WHERE id = ?");
    if ($stmtProductName) {
        $stmtProductName->bind_param("i", $productId);
        $stmtProductName->execute();
        $resultProductName = $stmtProductName->get_result();
        if ($row = $resultProductName->fetch_assoc()) {
            $response['product_name'] = htmlspecialchars($row['nama_produk']);
        }
        $stmtProductName->close();
    } else {
        // Handle error if product name query fails
        error_log("Error preparing product name statement: " . $conn->error);
    }


    // Cek apakah produk sudah ada di favorit user
    $checkFavoriteStmt = $conn->prepare("SELECT id FROM favorite WHERE id_user = ? AND id_produk = ?");
    if ($checkFavoriteStmt) {
        $checkFavoriteStmt->bind_param("ii", $userId, $productId);
        $checkFavoriteStmt->execute();
        $checkFavoriteResult = $checkFavoriteStmt->get_result();

        if ($checkFavoriteResult->num_rows > 0) {
            // Produk sudah ada di favorit, hapus
            $deleteStmt = $conn->prepare("DELETE FROM favorite WHERE id_user = ? AND id_produk = ?");
            if ($deleteStmt) {
                $deleteStmt->bind_param("ii", $userId, $productId);
                if ($deleteStmt->execute()) {
                    $response['status'] = 'success';
                    $response['message'] = '<b>' . $response['product_name'] . '</b> berhasil dihapus dari favorit Anda.';
                    $response['action'] = 'removed';
                } else {
                    $response['message'] = 'Gagal menghapus produk dari favorit: ' . $deleteStmt->error;
                }
                $deleteStmt->close();
            } else {
                $response['message'] = 'Kesalahan database (delete): ' . $conn->error;
            }
        } else {
            // Produk belum ada di favorit, tambahkan
            $insertStmt = $conn->prepare("INSERT INTO favorite (id_user, id_produk, tanggal_tambah) VALUES (?, ?, ?)");
            if ($insertStmt) {
                $insertStmt->bind_param("iis", $userId, $productId, $tanggalTambah);
                if ($insertStmt->execute()) {
                    $response['status'] = 'success';
                    $response['message'] = '<b>' . $response['product_name'] . '</b> berhasil ditambahkan ke favorit Anda.';
                    $response['action'] = 'added';
                } else {
                    $response['message'] = 'Gagal menambahkan produk ke favorit: ' . $insertStmt->error;
                }
                $insertStmt->close();
            } else {
                $response['message'] = 'Kesalahan database (insert): ' . $conn->error;
            }
        }
        $checkFavoriteStmt->close();
    } else {
        $response['message'] = 'Kesalahan database (cek favorit): ' . $conn->error;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // If it's a GET request, just return the favorite count
    $response['status'] = 'success'; // A GET request for count is usually a success if processed
    $response['message'] = 'Jumlah favorit berhasil diambil.'; // Optional message for clarity
} else {
    // Invalid request method or missing product_id for POST
    $response['message'] = 'Permintaan tidak valid.';
}

// Ambil jumlah favorit terbaru setelah operasi (or if it's a GET request)
// This ensures total_favorites is always in the response
$stmtFavoriteCount = $conn->prepare("SELECT COUNT(*) FROM favorite WHERE id_user = ?");
if ($stmtFavoriteCount) {
    $stmtFavoriteCount->bind_param("i", $userId);
    $stmtFavoriteCount->execute();
    $stmtFavoriteCount->bind_result($favCount);
    $stmtFavoriteCount->fetch();
    $stmtFavoriteCount->close();
    $response['total_favorites'] = $favCount;
} else {
    // Log error if favorite count query fails
    error_log("Error preparing favorite count statement: " . $conn->error);
}


echo json_encode($response);
$conn->close();
?>