<?php
session_start();
include 'koneksi.php'; // Pastikan path ini benar

$totalItems = 0;

if (isset($_SESSION['user_id'])) {
    $loggedInUserId = (int)$_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT SUM(quantity) AS total_quantity FROM cart WHERE id_user = ?");
    $stmt->bind_param("i", $loggedInUserId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    if ($row && $row['total_quantity'] !== null) {
        $totalItems = $row['total_quantity'];
    }
    $stmt->close();
}

$conn->close();
echo $totalItems;
?>