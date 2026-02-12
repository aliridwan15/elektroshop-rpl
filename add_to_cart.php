<?php
session_start();
include 'koneksi.php';

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = (int)($_POST['product_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 1); // Default to 1 if not specified
    $loggedInUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

    if ($loggedInUserId === 0) {
        $response['message'] = 'Anda harus login untuk menambahkan produk ke keranjang.';
        echo json_encode($response);
        exit();
    }

    if ($productId <= 0 || $quantity <= 0) {
        $response['message'] = 'ID produk atau kuantitas tidak valid.';
        echo json_encode($response);
        exit();
    }

    // Check if product exists and get its stock
    $stmtProduct = $conn->prepare("SELECT stok FROM produk WHERE id = ?");
    $stmtProduct->bind_param("i", $productId);
    $stmtProduct->execute();
    $resultProduct = $stmtProduct->get_result();
    $productData = $resultProduct->fetch_assoc();
    $stmtProduct->close();

    if (!$productData) {
        $response['message'] = 'Produk tidak ditemukan.';
        echo json_encode($response);
        exit();
    }

    $availableStock = $productData['stok'];

    // Check if item already exists in the user's cart
    $stmtCheck = $conn->prepare("SELECT id, quantity FROM cart WHERE id_user = ? AND id_produk = ?");
    $stmtCheck->bind_param("ii", $loggedInUserId, $productId);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();
    $existingCartItem = $resultCheck->fetch_assoc();
    $stmtCheck->close();

    if ($existingCartItem) {
        // Item exists, update quantity
        $newQuantity = $existingCartItem['quantity'] + $quantity;
        // Ensure new quantity does not exceed stock
        if ($newQuantity > $availableStock) {
            $newQuantity = $availableStock; // Cap at max stock
            $response['message'] = 'Kuantitas melebihi stok yang tersedia. Menambahkan hingga stok maksimal.';
        }

        $stmtUpdate = $conn->prepare("UPDATE cart SET quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmtUpdate->bind_param("ii", $newQuantity, $existingCartItem['id']);
        if ($stmtUpdate->execute()) {
            $response = ['status' => 'success', 'message' => 'Kuantitas produk di keranjang berhasil diperbarui.'];
        } else {
            $response['message'] = 'Gagal memperbarui kuantitas di database: ' . $conn->error;
        }
        $stmtUpdate->close();

    } else {
        // Item does not exist, insert new
        if ($quantity > $availableStock) {
            $quantity = $availableStock; // Cap at max stock
            $response['message'] = 'Kuantitas melebihi stok yang tersedia. Menambahkan hingga stok maksimal.';
        }

        $stmtInsert = $conn->prepare("INSERT INTO cart (id_user, id_produk, quantity, tanggal_tambah) VALUES (?, ?, ?, CURDATE())");
        $stmtInsert->bind_param("iii", $loggedInUserId, $productId, $quantity);
        if ($stmtInsert->execute()) {
            $response = ['status' => 'success', 'message' => 'Produk berhasil ditambahkan ke keranjang.'];
        } else {
            $response['message'] = 'Gagal menambahkan produk ke keranjang: ' . $conn->error;
        }
        $stmtInsert->close();
    }

} else {
    $response['message'] = 'Metode permintaan tidak diizinkan.';
}

echo json_encode($response);
$conn->close();
?>