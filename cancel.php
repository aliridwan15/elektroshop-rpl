<?php
session_start();
include 'koneksi.php'; // Pastikan file koneksi Anda sudah benar dan `$conn` tersedia

// Pastikan user sudah login
if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit();
}

$loggedInUserId = (int)$_SESSION['id'];

// Ambil data pesanan yang dibatalkan dari database
$cancelledOrders = [];
$stmt = $conn->prepare("
    SELECT
        t.id AS id_transaksi,
        t.total_transaksi,
        td.jumlah,
        td.harga_satuan,
        p.nama_produk,
        p.gambar
    FROM
        transaksi t
    JOIN
        transaksi_detail td ON t.id = td.id_transaksi
    JOIN
        produk p ON td.id_produk = p.id
    WHERE
        t.id_user = ? AND t.status_transaksi = 'cancel'
    ORDER BY
        t.id DESC, p.nama_produk ASC
");
$stmt->bind_param("i", $loggedInUserId);
$stmt->execute();
$result = $stmt->get_result();

// Mengorganisir hasil berdasarkan ID Transaksi
while ($row = $result->fetch_assoc()) {
    $transaksiId = $row['id_transaksi'];
    if (!isset($cancelledOrders[$transaksiId])) {
        $cancelledOrders[$transaksiId] = [
            'id_transaksi' => $transaksiId,
            'total_transaksi' => $row['total_transaksi'],
            'items' => []
        ];
    }
    $cancelledOrders[$transaksiId]['items'][] = [
        'name' => $row['nama_produk'],
        'img' => $row['gambar'],
        'price' => $row['harga_satuan'],
        'qty' => $row['jumlah'],
    ];
}
$stmt->close();

// Ambil beberapa item terkait (misalnya, 4 produk acak) untuk "Just For You"
$justForYouItems = [];
$stmt = $conn->prepare("SELECT id, nama_produk, harga, gambar FROM produk ORDER BY RAND() LIMIT 4");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    // Placeholder untuk rating count. Anda bisa mengambil ini dari tabel 'rating' jika ada.
    $ratingCount = rand(10, 100);
    $justForYouItems[] = [
        'id' => $row['id'],
        'img' => $row['gambar'],
        'name' => $row['nama_produk'],
        'price' => $row['harga'],
        'rating' => $ratingCount
    ];
}
$stmt->close();
// REMOVE $conn->close(); from here

?>

<?php include 'resource/header.php'; ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

<style>
    body {
        background-color: #ffc0de;
        font-family: Arial, sans-serif;
    }

    .main-container {
        margin-left: 40px;
        margin-right: 40px;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 30px 0 10px;
    }

    .section-header h2 {
        font-size: 1.6rem;
        font-weight: bold;
    }

    .section-header .btn-view {
        background-color: hotpink;
        color: white;
        border: none;
        padding: 8px 20px;
        border-radius: 5px;
        font-weight: bold;
        transition: background-color 0.3s ease;
    }

    .section-header .btn-view:hover {
        background-color: deeppink;
    }

    .product-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        padding-bottom: 40px;
    }

    .product-card {
        background-color: white;
        width: 180px;
        padding: 15px;
        border-radius: 10px;
        position: relative;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        transition: transform 0.3s ease;
    }

    .product-card:hover {
        transform: scale(1.02);
    }

    .product-card img {
        width: 100%;
        border-radius: 10px;
        height: 150px; /* Tambahkan tinggi tetap untuk gambar */
        object-fit: cover; /* Pastikan gambar mengisi kotak */
    }

    .trash-icon {
        position: absolute;
        top: 10px;
        right: 10px;
        color: gray;
        cursor: default; /* Ubah cursor karena ini hanya ikon tampilan, bukan tindakan */
    }

    .product-name {
        font-size: 0.95rem;
        font-weight: bold;
        margin-top: 10px;
        min-height: 40px; /* Berikan tinggi minimum agar tidak geser */
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2; /* Batasi 2 baris */
        -webkit-box-orient: vertical;
    }

    .product-price {
        font-size: 0.9rem;
        color: #d6008f;
        font-weight: bold;
    }

    .old-price {
        text-decoration: line-through;
        color: gray;
        font-size: 0.8rem;
        margin-left: 5px;
    }

    .rating {
        color: gold;
        font-size: 0.8rem;
        margin-top: 5px;
    }

    .rating span {
        color: #555;
        margin-left: 5px;
    }
</style>

<div class="main-container">

    <div class="section-header">
        <h2>My Cancellation</h2>
        <button class="btn-view">View All</button>
    </div>
    <div class="product-grid">
        <?php if (empty($cancelledOrders)): ?>
            <div class="alert alert-info text-center w-100" role="alert">
                Tidak ada pesanan yang dibatalkan saat ini.
            </div>
        <?php else: ?>
            <?php foreach ($cancelledOrders as $order): ?>
                <?php foreach ($order['items'] as $item): ?>
                    <div class="product-card">
                        <i class="fas fa-trash trash-icon"></i>
                        <img src="admin/uploads/<?= htmlspecialchars($item['img']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                        <div class="product-name"><?= htmlspecialchars($item['name']) ?></div>
                        <div class="product-price">Rp<?= number_format($item['price'], 0, ',', '.') ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="section-header">
        <h2>Just For You</h2>
        <button class="btn-view">See All</button>
    </div>
    <div class="product-grid">
        <?php foreach ($justForYouItems as $item): ?>
            <div class="product-card">
                <img src="admin/uploads/<?= htmlspecialchars($item['img']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                <div class="product-name"><?= htmlspecialchars($item['name']) ?></div>
                <div class="product-price">Rp<?= number_format($item['price'], 0, ',', '.') ?></div>
                <div class="rating">★★★★★ <span>(<?= htmlspecialchars($item['rating']) ?>)</span></div>
            </div>
        <?php endforeach; ?>
    </div>

</div>

<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusModalLabel">Pemberitahuan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="statusModalBody">
                </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var statusModal = new bootstrap.Modal(document.getElementById('statusModal'));
        var statusModalBody = document.getElementById('statusModalBody');

        <?php
        // These messages are echoed directly into the JavaScript block
        // after being set by the cancel_transaction.php
        if (isset($_SESSION['success_message'])): ?>
            statusModalBody.innerHTML = '<div class="alert alert-success" role="alert"><?= htmlspecialchars($_SESSION['success_message']); ?></div>';
            statusModal.show();
            <?php unset($_SESSION['success_message']); // Clear the message after displaying ?>
        <?php elseif (isset($_SESSION['error_message'])): ?>
            statusModalBody.innerHTML = '<div class="alert alert-danger" role="alert"><?= htmlspecialchars($_SESSION['error_message']); ?></div>';
            statusModal.show();
            <?php unset($_SESSION['error_message']); // Clear the message after displaying ?>
        <?php endif; ?>
    });
</script>

<?php include 'resource/footer.php'; ?>

<?php
// Close the database connection ONLY after all PHP processing and all included files have finished their database operations.
if (isset($conn) && $conn) {
    $conn->close();
}
?>