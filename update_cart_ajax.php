<?php
session_start();
include 'koneksi.php'; // Make sure your database connection is correct

header('Content-Type: application/json'); // Ensure the response is JSON

$response = ['status' => 'error', 'message' => 'Permintaan tidak valid.'];

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $loggedInUserId = isset($_SESSION['id']) ? (int)$_SESSION['id'] : 0; // Assuming 'id' is the user ID in session

    if ($loggedInUserId === 0) {
        $response = ['status' => 'error', 'message' => 'Anda harus login untuk mengelola keranjang.'];
        echo json_encode($response);
        exit();
    }

    switch ($action) {
        case 'remove_item':
            $dbCartId = (int)($_POST['db_cart_id'] ?? 0);

            if ($dbCartId > 0) {
                $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND id_user = ?");
                $stmt->bind_param("ii", $dbCartId, $loggedInUserId);

                if ($stmt->execute()) {
                    if ($stmt->affected_rows > 0) {
                        // Recalculate total items in cart after deletion
                        $totalItemsInCart = 0;
                        $countStmt = $conn->prepare("SELECT SUM(quantity) FROM cart WHERE id_user = ?");
                        $countStmt->bind_param("i", $loggedInUserId);
                        $countStmt->execute();
                        $countStmt->bind_result($totalItemsInCart);
                        $countStmt->fetch();
                        $countStmt->close();

                        $response = [
                            'status' => 'success',
                            'message' => 'Item berhasil dihapus dari keranjang.',
                            'total_items_in_cart' => (int)$totalItemsInCart
                        ];
                    } else {
                        $response = ['status' => 'error', 'message' => 'Item tidak ditemukan atau Anda tidak memiliki izin untuk menghapusnya.'];
                    }
                } else {
                    $response = ['status' => 'error', 'message' => 'Gagal menghapus item dari database: ' . $stmt->error];
                }
                $stmt->close();
            } else {
                $response = ['status' => 'error', 'message' => 'ID item keranjang tidak valid.'];
            }
            break;

        case 'remove_multiple_items':
            $dbCartIds = $_POST['db_cart_ids'] ?? [];

            if (!empty($dbCartIds) && is_array($dbCartIds)) {
                // Sanitize and cast all IDs to integers
                $sanitizedDbCartIds = array_map('intval', $dbCartIds);
                
                // Create placeholders for the IN clause
                $placeholders = implode(',', array_fill(0, count($sanitizedDbCartIds), '?'));
                $types = str_repeat('i', count($sanitizedDbCartIds)); // All IDs are integers

                $stmt = $conn->prepare("DELETE FROM cart WHERE id IN ($placeholders) AND id_user = ?");
                
                // Merge the array of IDs with the user ID for binding
                $params = array_merge($sanitizedDbCartIds, [$loggedInUserId]);
                $stmt->bind_param($types . 'i', ...$params);

                if ($stmt->execute()) {
                    if ($stmt->affected_rows > 0) {
                        // Recalculate total items in cart after deletion
                        $totalItemsInCart = 0;
                        $countStmt = $conn->prepare("SELECT SUM(quantity) FROM cart WHERE id_user = ?");
                        $countStmt->bind_param("i", $loggedInUserId);
                        $countStmt->execute();
                        $countStmt->bind_result($totalItemsInCart);
                        $countStmt->fetch();
                        $countStmt->close();

                        $response = [
                            'status' => 'success',
                            'message' => 'Item-item berhasil dihapus dari keranjang.',
                            'total_items_in_cart' => (int)$totalItemsInCart
                        ];
                    } else {
                        $response = ['status' => 'error', 'message' => 'Tidak ada item yang ditemukan untuk dihapus atau Anda tidak memiliki izin.'];
                    }
                } else {
                    $response = ['status' => 'error', 'message' => 'Gagal menghapus item dari database: ' . $stmt->error];
                }
                $stmt->close();
            } else {
                $response = ['status' => 'error', 'message' => 'ID keranjang tidak valid atau kosong.'];
            }
            break;

        case 'update_quantity':
            $dbCartId = (int)($_POST['db_cart_id'] ?? 0);
            $newQuantity = (int)($_POST['quantity'] ?? 0);

            if ($dbCartId > 0 && $newQuantity > 0) {
                // Get product stock and price from the database for validation
                $stmtStock = $conn->prepare("SELECT p.stok, p.harga FROM produk p JOIN cart c ON p.id = c.id_produk WHERE c.id = ? AND c.id_user = ?");
                $stmtStock->bind_param("ii", $dbCartId, $loggedInUserId);
                $stmtStock->execute();
                $resultStock = $stmtStock->get_result();
                $productData = $resultStock->fetch_assoc();
                $stmtStock->close();

                if ($productData) {
                    $maxStock = $productData['stok'];
                    $productPrice = $productData['harga'];

                    // Ensure newQuantity does not exceed maxStock
                    $actualNewQuantity = min($newQuantity, $maxStock);
                    if ($maxStock === 0) { // If stock is 0, quantity should be 0
                        $actualNewQuantity = 0;
                    }

                    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND id_user = ?");
                    $stmt->bind_param("iii", $actualNewQuantity, $dbCartId, $loggedInUserId);

                    if ($stmt->execute()) {
                        // If quantity actually changed, update response
                        if ($stmt->affected_rows > 0) {
                            $message = 'Kuantitas berhasil diperbarui.';
                        } else {
                            $message = 'Kuantitas tidak berubah.'; // Still success if quantity is the same
                        }

                        // Recalculate total items in cart
                        $totalItemsInCart = 0;
                        $countStmt = $conn->prepare("SELECT SUM(quantity) FROM cart WHERE id_user = ?");
                        $countStmt->bind_param("i", $loggedInUserId);
                        $countStmt->execute();
                        $countStmt->bind_result($totalItemsInCart);
                        $countStmt->fetch();
                        $countStmt->close();

                        // Calculate new subtotal for the updated item
                        $newSubtotal = $productPrice * $actualNewQuantity;

                        // Recalculate overall cart total
                        $totalHargaKeranjang = 0;
                        $cartItemsStmt = $conn->prepare("SELECT c.quantity, p.harga FROM cart c JOIN produk p ON c.id_produk = p.id WHERE c.id_user = ?");
                        $cartItemsStmt->bind_param("i", $loggedInUserId);
                        $cartItemsStmt->execute();
                        $cartResult = $cartItemsStmt->get_result();
                        while ($item = $cartResult->fetch_assoc()) {
                            $totalHargaKeranjang += $item['quantity'] * $item['harga'];
                        }
                        $cartItemsStmt->close();

                        $response = [
                            'status' => 'success',
                            'message' => $message,
                            'new_quantity' => $actualNewQuantity,
                            'new_subtotal_formatted' => number_format($newSubtotal, 0, ',', '.'),
                            'total_items_in_cart' => (int)$totalItemsInCart,
                            'cart_total_formatted' => number_format($totalHargaKeranjang, 0, ',', '.')
                        ];
                    } else {
                        $response = ['status' => 'error', 'message' => 'Gagal memperbarui kuantitas: ' . $stmt->error];
                    }
                    $stmt->close();
                } else {
                    $response = ['status' => 'error', 'message' => 'Produk atau item keranjang tidak ditemukan untuk user ini.'];
                }
            } else {
                $response = ['status' => 'error', 'message' => 'Data tidak lengkap atau kuantitas tidak valid untuk update.'];
            }
            break;

        default:
            $response = ['status' => 'error', 'message' => 'Aksi tidak dikenal.'];
            break;
    }
} else {
    $response = ['status' => 'error', 'message' => 'Metode permintaan tidak diizinkan.'];
}

echo json_encode($response);
$conn->close();
?>