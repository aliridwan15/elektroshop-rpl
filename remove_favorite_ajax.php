<?php
session_start();
include 'koneksi.php'; // Pastikan file koneksi database Anda benar

header('Content-Type: application/json'); // Memberi tahu browser bahwa respons adalah JSON

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Anda harus login untuk melakukan tindakan ini.']);
    exit;
}

$userId = $_SESSION['id'];

// Pastikan request method adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Metode permintaan tidak diizinkan. Hanya metode POST yang diperbolehkan.']);
    exit;
}

// Ambil product_id dari data POST
$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

if ($productId <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'ID produk tidak valid.']);
    exit;
}

// Siapkan pernyataan untuk menghapus dari tabel favorit
$stmt = $conn->prepare("DELETE FROM favorite WHERE id_user = ? AND id_produk = ?");
if ($stmt === false) {
    echo json_encode(['status' => 'error', 'message' => 'Gagal menyiapkan pernyataan SQL: ' . $conn->error]);
    exit;
}

$stmt->bind_param("ii", $userId, $productId);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        // Berhasil dihapus
        echo json_encode(['status' => 'success', 'message' => 'Produk berhasil dihapus dari favorit.', 'action' => 'removed']);
    } else {
        // Produk tidak ditemukan di favorit pengguna ini
        echo json_encode(['status' => 'error', 'message' => 'Produk tidak ditemukan di daftar favorit Anda atau sudah dihapus.']);
    }
} else {
    // Gagal eksekusi query
    echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus produk dari favorit: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>