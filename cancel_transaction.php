<?php
session_start();
include 'koneksi.php'; // Pastikan path ini benar dan $conn tersedia

if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit();
}

if (isset($_GET['transaction_id'])) {
    $transactionId = (int)$_GET['transaction_id'];
    $loggedInUserId = (int)$_SESSION['id'];

    $conn->begin_transaction();

    try {
        // Step 1: Get product details from the transaction detail for stock update and for modal message
        $stmt_select_items = $conn->prepare("SELECT td.id_produk, td.jumlah, p.nama_produk FROM transaksi_detail td JOIN produk p ON td.id_produk = p.id WHERE id_transaksi = ?");
        $stmt_select_items->bind_param("i", $transactionId);
        $stmt_select_items->execute();
        $result_items = $stmt_select_items->get_result();
        $items_to_return_stock = [];
        $product_names_for_message = []; // To collect product names for the modal
        while ($row = $result_items->fetch_assoc()) {
            $items_to_return_stock[] = $row;
            $product_names_for_message[] = htmlspecialchars($row['nama_produk']);
        }
        $stmt_select_items->close();

        // Step 2: Update the status of the main transaction to 'cancel'
        // Add status_pengiriman update to 'dibatalkan' in the 'pengiriman' table
        $stmt_update_transaksi = $conn->prepare("UPDATE transaksi SET status_transaksi = 'cancel' WHERE id = ? AND id_user = ? AND status_transaksi = 'pending'");
        $stmt_update_transaksi->bind_param("ii", $transactionId, $loggedInUserId);
        $stmt_update_transaksi->execute();

        if ($stmt_update_transaksi->affected_rows > 0) {
            // Update the delivery status as well
            $stmt_update_pengiriman = $conn->prepare("UPDATE pengiriman SET status_pengiriman = 'dibatalkan' WHERE id_transaksi = ? AND id_user = ?");
            $stmt_update_pengiriman->bind_param("ii", $transactionId, $loggedInUserId);
            $stmt_update_pengiriman->execute();
            // No need to check affected_rows for pengiriman update, as it's part of the same cancellation logic
            $stmt_update_pengiriman->close();

            // Step 3: Return stock to products for each item in the cancelled order
            foreach ($items_to_return_stock as $item) {
                $stmt_update_stock = $conn->prepare("UPDATE produk SET stok = stok + ? WHERE id = ?");
                $stmt_update_stock->bind_param("ii", $item['jumlah'], $item['id_produk']);
                $stmt_update_stock->execute();
                if ($stmt_update_stock->affected_rows === 0) {
                    throw new Exception("Gagal mengembalikan stok untuk produk ID: " . $item['id_produk']);
                }
                $stmt_update_stock->close();
            }

            // Commit the transaction if all updates were successful
            $conn->commit();
            // Set success message including product names
            $_SESSION['success_message'] = "Pesanan #" . htmlspecialchars($transactionId) . " dengan produk: " . implode(', ', $product_names_for_message) . " berhasil dibatalkan dan stok produk dikembalikan.";
        } else {
            // If affected_rows is 0, it means the transaction wasn't found or wasn't pending
            // or the user ID didn't match.
            throw new Exception("Pembatalan pesanan gagal. Pesanan sudah dibayar / sudah dibatalkan, atau Anda tidak memiliki izin.");
        }
    } catch (Exception $e) {
        // Rollback the transaction if any error occurs
        $conn->rollback();
        $_SESSION['error_message'] = "Error saat membatalkan pesanan: " . $e->getMessage();
    } finally {
        // Ensure statement is closed if it was opened
        if (isset($stmt_update_transaksi) && $stmt_update_transaksi) {
            $stmt_update_transaksi->close();
        }
        // Ensure connection is closed
        $conn->close();
    }
} else {
    $_SESSION['error_message'] = "ID Transaksi tidak ditemukan.";
}

// Redirect back to the cancel page
header('Location: cancel.php');
exit();
?>