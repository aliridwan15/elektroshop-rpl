<?php
session_start();
include 'koneksi.php';

header('Content-Type: application/json');

$favoriteItemCount = 0;
if (isset($_SESSION['id'])) {
    $userId = $_SESSION['id'];
    $stmt = $conn->prepare("SELECT COUNT(*) FROM favorite WHERE id_user = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    $favoriteItemCount = $count;
}

echo json_encode(['count' => $favoriteItemCount]);
$conn->close();
?>