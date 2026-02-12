<?php
// 1. START THE SESSION FIRST AND FOREMOST
session_start();

// Always initialize your response array
$response = ['status' => 'error', 'message' => 'An unknown error occurred.'];

// Include your database connection
include 'koneksi.php'; // Ensure this file establishes $conn

// Check if the request is a POST and required parameters are set
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id']) && isset($_POST['quantity'])) {
    // Ensure user is logged in
    if (!isset($_SESSION['id'])) {
        $response = ['status' => 'error', 'message' => 'Anda harus login untuk melanjutkan.'];
        echo json_encode($response);
        exit(); // Always exit after sending JSON response
    }

    $productId = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    $userId = (int)$_SESSION['id'];
    $isBuyNow = isset($_POST['is_buy_now']) && $_POST['is_buy_now'] === 'true'; // Correct boolean comparison

    // Input validation
    if ($productId <= 0 || $quantity <= 0) {
        $response = ['status' => 'error', 'message' => 'ID produk atau kuantitas tidak valid.'];
        echo json_encode($response);
        exit();
    }

    // --- Fetch product details ---
    $stmt = $conn->prepare("SELECT nama_produk, harga, stok FROM produk WHERE id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();

    if (!$product) {
        $response = ['status' => 'error', 'message' => 'Produk tidak ditemukan.'];
        echo json_encode($response);
        exit();
    }

    if ($product['stok'] < $quantity) {
        $response = ['status' => 'error', 'message' => 'Stok produk tidak mencukupi. Stok tersedia: ' . $product['stok']];
        echo json_encode($response);
        exit();
    }

    // --- Handle 'Buy Now' vs. 'Add to Cart' ---
    if ($isBuyNow) {
        // For 'Buy Now', we don't add to cart, we just redirect.
        // The checkout.php script will handle fetching the product details from session.
        $_SESSION['buy_now_product_id'] = $productId;
        $_SESSION['buy_now_quantity'] = $quantity;
        
        $response = [
            'status' => 'success',
            'message' => 'Mengarahkan ke halaman checkout...',
            'redirect' => 'checkout.php'
        ];
        // Don't modify cart for 'buy now' flow, just redirect.
        // Any cart item updates would be for regular 'add to cart'.

    } else {
        // --- Regular Add to Cart logic ---
        // Check if product already in cart
        $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE id_user = ? AND id_produk = ?");
        $stmt->bind_param("ii", $userId, $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $cartItem = $result->fetch_assoc();
        $stmt->close();

        if ($cartItem) {
            // Update quantity if product exists in cart
            $newQuantity = $cartItem['quantity'] + $quantity;
            if ($product['stok'] < $newQuantity) {
                 $response = ['status' => 'error', 'message' => 'Tidak dapat menambahkan lebih banyak, stok tidak mencukupi. Stok tersedia: ' . $product['stok']];
                 echo json_encode($response);
                 exit();
            }
            $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $stmt->bind_param("ii", $newQuantity, $cartItem['id']);
            $stmt->execute();
            $stmt->close();
            $response['message'] = 'Kuantitas produk berhasil diperbarui di keranjang.';
        } else {
            // Add new product to cart
            $stmt = $conn->prepare("INSERT INTO cart (id_user, id_produk, quantity) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $userId, $productId, $quantity);
            $stmt->execute();
            $stmt->close();
            $response['message'] = 'Produk berhasil ditambahkan ke keranjang.';
        }
        $response['status'] = 'success';
    }

    // --- Get total cart items for header update (if not 'buy_now' redirect) ---
    // This part runs for both regular add to cart and just before 'buy_now' redirect,
    // as it might be needed for the header/UI update.
    $stmt = $conn->prepare("SELECT SUM(quantity) AS total_items FROM cart WHERE id_user = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $totalItems = $result->fetch_assoc()['total_items'] ?? 0;
    $stmt->close();

    $response['total_items'] = $totalItems;
    $response['product_name'] = $product['nama_produk'];

} else {
    // If not a valid POST request
    $response = ['status' => 'error', 'message' => 'Permintaan tidak valid.'];
}

// Ensure the Content-Type header is set for JSON
header('Content-Type: application/json');
echo json_encode($response);
exit(); // Always exit after sending JSON
?>