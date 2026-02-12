<?php
session_start();
include 'koneksi.php'; // Pastikan jalur ini benar dan $conn tersedia

// Pastikan pengguna sudah login
if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit();
}

$loggedInUserId = (int)$_SESSION['id'];

// --- Mengambil pesanan berstatus 'dikemas' dari Database ---
$packagedOrders = [];
$stmt = $conn->prepare("
    SELECT
        p.id AS id_pengiriman,
        p.id_transaksi,
        p.ekspedisi,
        p.no_resi,
        p.tanggal_dikirim,   /* Menambahkan kolom tanggal_dikirim */
        p.tanggal_diterima,  /* Menambahkan kolom tanggal_diterima */
        t.total_transaksi,
        td.jumlah,
        td.harga_satuan,
        prod.nama_produk,
        prod.gambar
    FROM
        pengiriman p
    JOIN
        transaksi t ON p.id_transaksi = t.id
    JOIN
        transaksi_detail td ON t.id = td.id_transaksi
    JOIN
        produk prod ON td.id_produk = prod.id
    WHERE
        p.id_user = ? AND p.status_pengiriman = 'dikemas'
    ORDER BY
        p.created_at DESC, prod.nama_produk ASC
");
$stmt->bind_param("i", $loggedInUserId);
$stmt->execute();
$result = $stmt->get_result();

// Mengatur hasil berdasarkan ID pengiriman
while ($row = $result->fetch_assoc()) {
    $pengirimanId = $row['id_pengiriman'];
    if (!isset($packagedOrders[$pengirimanId])) {
        $packagedOrders[$pengirimanId] = [
            'id_pengiriman' => $pengirimanId,
            'id_transaksi' => $row['id_transaksi'],
            'ekspedisi' => $row['ekspedisi'],
            'no_resi' => $row['no_resi'],
            'total_transaksi' => $row['total_transaksi'],
            'tanggal_dikirim' => $row['tanggal_dikirim'],   // Menyimpan tanggal dikirim
            'tanggal_diterima' => $row['tanggal_diterima'], // Menyimpan tanggal diterima
            'items' => []
        ];
    }
    $packagedOrders[$pengirimanId]['items'][] = [
        'name' => $row['nama_produk'],
        'img' => $row['gambar'],
        'price' => $row['harga_satuan'],
        'qty' => $row['jumlah'],
    ];
}
$stmt->close();

// --- Mengambil item "Just For You" (serupa dengan cancel.php Anda) ---
$justForYouItems = [];
$stmt = $conn->prepare("SELECT id, nama_produk, harga, gambar FROM produk ORDER BY RAND() LIMIT 4");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
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

?>

<?php include 'resource/header.php'; ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">


<style>
    /* General Styles (keep these for consistency) */
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

    /* Sidebar specific styles */
    /* Updated sidebar styles to match pending.php */
    .my-sidebar-order {
        background-color: #ffe3f1;
        padding: 20px;
        border-radius: 10px;
        width: 100%; /* Memastikan ini mengambil lebar penuh kolomnya */
        box-sizing: border-box;
    }

    .my-sidebar-order a {
        display: flex;
        align-items: center;
        color: #000;
        padding: 10px;
        text-decoration: none;
        font-weight: bold;
        border-radius: 6px;
        margin-bottom: 10px;
        transition: background-color 0.3s ease;
    }

    .my-sidebar-order a:hover {
        background-color: #ffb4d4;
        color: #000;
    }

    .my-sidebar-order a.active { /* Menambahkan kelas .active untuk halaman saat ini */
        background-color: hotpink; /* Warna latar belakang untuk link aktif */
        color: white; /* Warna teks untuk link aktif */
    }

    .my-sidebar-order i {
        margin-right: 10px;
    }

    /* Order Card styles */
    .order-card {
        background-color: transparent;
        border-radius: 10px;
        padding: 20px;
        margin: 0 auto 20px auto;
        max-width: 800px;
        display: flex;
        flex-direction: column; /* Diubah menjadi kolom untuk tumpukan yang lebih baik di layar kecil */
        align-items: flex-start;
        position: relative;
        border: 1px solid #ffb6d9;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }

    .order-card .order-details-header {
        width: 100%;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #ffb6d9;
    }

    .order-card .item-list {
        width: 100%;
    }

    .order-card .item-details {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
    }

    .product-image {
        max-width: 100px; /* Ukuran yang lebih kecil untuk item individual */
        border-radius: 8px;
        background-color: white;
        padding: 5px;
        object-fit: cover;
        height: 100px; /* tinggi tetap untuk konsistensi */
        margin-right: 15px;
    }

    .product-title {
        font-size: 16px;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .product-price {
        font-size: 14px;
        font-weight: bold;
        color: #d6008f;
    }

    .product-qty {
        font-size: 13px;
        color: #555;
    }

    .product-divider {
        border-top: 1px solid #ffb6d9;
        margin: 10px 0;
    }

    .product-meta {
        font-size: 14px;
    }

    .btn-contact {
        background-color: #ff2e8c;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        font-weight: bold;
        text-decoration: none;
        display: inline-block;
        margin-top: 10px;
        transition: background-color 0.3s ease;
    }

    .btn-contact:hover {
        background-color: #e6006d;
    }

    /* Related items styles (sesuaikan sesuai kebutuhan untuk responsivitas) */
    .related-items-container {
        background-color: transparent;
        padding: 0;
        margin-top: 40px;
    }

    .related-items-title {
        font-size: 1.6rem;
        font-weight: bold;
        margin-bottom: 0;
        color: #333;
    }

    .related-items-grid {
        display: flex;
        flex-wrap: nowrap;
        gap: 20px;
        overflow-x: auto;
        padding-bottom: 10px;
        scroll-snap-type: x mandatory;
        padding: 0 20px;
        transition: transform 0.5s ease-in-out;
    }

    .product-card {
        min-width: 200px;
        max-width: 200px;
        flex: 0 0 auto;
        scroll-snap-align: start;
        background-color: white;
        padding: 15px;
        border-radius: 10px;
        position: relative;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .product-image-wrapper {
        position: relative;
        width: 100%;
        height: 150px;
        border-radius: 8px;
        background-color: #f5f5f5;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 10px;
        overflow: hidden;
    }

    .product-image.related {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 8px;
    }

    .product-actions {
        position: absolute;
        top: 8px;
        right: 8px;
        display: flex;
        gap: 8px;
        z-index: 10;
    }

    .product-actions a {
        color: #666;
        background: white;
        padding: 4px;
        border-radius: 50%;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        transition: color 0.3s, background-color 0.3s;
    }

    .product-actions a:hover {
        color: #ff4d4d;
        background-color: #f8f8f8;
    }

    .product-name {
        font-weight: bold;
        margin-bottom: 5px;
        color: #333;
        min-height: 40px;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .product-price {
        color: #ff4d4d;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .product-rating {
        color: #ffc107;
        font-size: 14px;
    }

    .rating-count {
        color: #666;
        font-size: 12px;
    }

    @media (min-width: 768px) {
        .order-row {
            display: flex;
        }
    }

    @media (max-width: 767.98px) {
        .my-sidebar-order { /* Updated class name */
            width: 100%;
            margin-bottom: 20px;
        }
        .main-container {
            margin-left: 10px;
            margin-right: 10px;
        }
        .related-items-grid {
            padding: 0 10px;
        }
        .product-card {
            min-width: 160px;
            max-width: 160px;
        }
    }
</style>

<div class="container-fluid" style="padding: 40px 20px; background-color: #ffe3f1;">
    <div class="row order-row">
        <div class="col-md-3 mb-4">
            <div class="my-sidebar-order">
                <h5 class="mb-4"><i class="fas fa-stream text-danger"></i> My Orders</h5>
                <a href="pending.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'pending.php') ? 'active' : '' ?>"><i class="fas fa-exclamation-circle text-danger"></i> Pending</a>
                <a href="packaged.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'packaged.php') ? 'active' : '' ?>"><i class="fas fa-box text-dark"></i> Packaged</a>
                <a href="sent.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'sent.php') ? 'active' : '' ?>"><i class="fas fa-truck text-dark"></i> Sent</a>
                <a href="finished.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'finished.php') ? 'active' : '' ?>"><i class="fas fa-check-circle text-success"></i> Finished</a>
                <a href="cancel.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'cancel.php') ? 'active' : '' ?>"><i class="fas fa-times-circle text-secondary"></i> Cancelled</a>
            </div>
        </div>

        <div class="col-md-9">
            <h2>My Packaged Orders</h2>
            <?php if (empty($packagedOrders)): ?>
                <div class="alert alert-info text-center mt-3" role="alert">
                    Tidak ada pesanan yang sedang dalam status 'dikemas' saat ini.
                </div>
            <?php else: ?>
                <?php foreach ($packagedOrders as $order): ?>
                    <div class="order-card">
                        <div class="order-details-header">
                            <h5>Products in this package:</h5>
                        </div>
                        <div class="item-list">
                            <?php foreach ($order['items'] as $item): ?>
                                <div class="item-details">
                                    <img src="admin/uploads/<?= htmlspecialchars($item['img']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="product-image">
                                    <div>
                                        <div class="product-title"><?= htmlspecialchars($item['name']) ?></div>
                                        <div class="product-price">Rp<?= number_format($item['price'], 0, ',', '.') ?></div>
                                        <div class="product-qty">Quantity: x<?= htmlspecialchars($item['qty']) ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="mb-3 mt-3">
                            <strong>Ekspedisi:</strong> <?= htmlspecialchars($order['ekspedisi']) ?><br>
                            <strong>No. Resi:</strong> <?= htmlspecialchars($order['no_resi'] ?: 'N/A') ?><br>
                            <strong>Estimasi Dikirim:</strong>
                            <?php if ($order['tanggal_diterima']): ?>
                                <?= htmlspecialchars(date('d F Y', strtotime($order['tanggal_dikirim']))) ?> 
                            <?php elseif ($order['tanggal_dikirim']): ?>
                                <?php
                                $estimatedDeliveryStart = date('d F Y', strtotime($order['tanggal_dikirim'] . ' +3 days'));
                                $estimatedDeliveryEnd = date('d F Y', strtotime($order['tanggal_dikirim'] . ' +5 days'));
                                ?>
                                <?= htmlspecialchars($estimatedDeliveryStart) ?> - <?= htmlspecialchars($estimatedDeliveryEnd) ?>
                            <?php else: ?>
                                Belum ada informasi pengiriman.
                            <?php endif; ?>
                            <br>
                            <strong>Total Transaksi:</strong> Rp<?= number_format($order['total_transaksi'], 0, ',', '.') ?><br>
                        </div>
                        <a href="contact.php" class="btn-contact mt-3">Contact Seller</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <div class="related-items-container px-2">
                <div class="d-flex justify-content-center mb-3">
                    <h5 class="related-items-title mb-0">Just For You</h5>
                </div>

                <div class="related-items-wrapper">
                    <div class="related-items-grid" id="productSlider">
                        <?php foreach ($justForYouItems as $item): ?>
                            <div class="product-card">
                                <div class="product-image-wrapper">
                                    <img src="admin/uploads/<?= htmlspecialchars($item['img']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="product-image related">
                                    <div class="product-actions">
                                        <a href="favorite.php?product_id=<?= $item['id'] ?>"><i class="far fa-heart"></i></a>
                                        <a href="product_details.php?id=<?= $item['id'] ?>"><i class="far fa-eye"></i></a>
                                    </div>
                                </div>
                                <div class="product-name"><?= htmlspecialchars($item['name']) ?></div>
                                <div class="product-price">Rp <?= number_format($item['price'], 0, ',', '.') ?></div>
                                <div class="product-rating">★★★★★ <span class="rating-count">(<?= htmlspecialchars($item['rating']) ?>)</span></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Your existing JavaScript for slider (if needed)
    let currentIndex = 0;
    const items = document.querySelectorAll('#productSlider .product-card'); // Corrected ID

    function navigateRelated(direction) {
        const container = document.getElementById('productSlider');
        const scrollAmount = 250;
        container.scrollBy({
            left: direction === 'left' ? -scrollAmount : scrollAmount,
            behavior: 'smooth'
        });
    }
</script>

<?php include 'resource/footer.php'; ?>

<?php
// Tutup koneksi database HANYA setelah semua pemrosesan PHP dan semua file yang disertakan telah menyelesaikan operasi database mereka.
if (isset($conn) && $conn) {
    $conn->close();
}
?>