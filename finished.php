<?php
session_start();
include 'koneksi.php'; // Pastikan jalur ini benar dan $conn tersedia

// Pastikan pengguna sudah login
if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit();
}

$loggedInUserId = (int)$_SESSION['id'];

// --- Mengambil pesanan berstatus 'selesai' dari Database ---
$finishedOrders = [];
$stmt = $conn->prepare("
    SELECT
        p.id AS id_pengiriman,
        p.id_transaksi,
        p.ekspedisi,
        p.no_resi,
        p.tanggal_dikirim,
        p.tanggal_diterima,
        t.total_transaksi,
        t.metode_pembayaran, -- Tambahkan metode_pembayaran
        t.status_transaksi AS current_status_transaksi, -- Ambil status transaksi saat ini
        td.jumlah,
        td.harga_satuan,
        prod.nama_produk,
        prod.gambar,
        prod.id AS id_produk_item
    FROM
        pengiriman p
    JOIN
        transaksi t ON p.id_transaksi = t.id
    JOIN
        transaksi_detail td ON t.id = td.id_transaksi
    JOIN
        produk prod ON td.id_produk = prod.id
    WHERE
        p.id_user = ? AND p.status_pengiriman = 'selesai'
    ORDER BY
        p.created_at DESC, prod.nama_produk ASC
");
$stmt->bind_param("i", $loggedInUserId);
$stmt->execute();
$result = $stmt->get_result();

// Mengatur hasil berdasarkan ID pengiriman dan sekaligus memproses update status transaksi
$transactionsToUpdate = []; // Array untuk menyimpan ID transaksi yang perlu diupdate

while ($row = $result->fetch_assoc()) {
    $pengirimanId = $row['id_pengiriman'];
    if (!isset($finishedOrders[$pengirimanId])) {
        $finishedOrders[$pengirimanId] = [
            'id_pengiriman' => $pengirimanId,
            'id_transaksi' => $row['id_transaksi'],
            'ekspedisi' => $row['ekspedisi'],
            'no_resi' => $row['no_resi'],
            'total_transaksi' => $row['total_transaksi'],
            'tanggal_dikirim' => $row['tanggal_dikirim'],
            'tanggal_diterima' => $row['tanggal_diterima'],
            'metode_pembayaran' => $row['metode_pembayaran'], // Simpan metode pembayaran
            'current_status_transaksi' => $row['current_status_transaksi'], // Simpan status transaksi saat ini
            'items' => []
        ];
    }
    $finishedOrders[$pengirimanId]['items'][] = [
        'id_produk' => $row['id_produk_item'],
        'name' => $row['nama_produk'],
        'img' => $row['gambar'],
        'price' => $row['harga_satuan'],
        'qty' => $row['jumlah'],
    ];

    // Logika untuk menandai transaksi COD yang 'selesai' untuk diupdate
    if ($row['metode_pembayaran'] === 'COD' && $row['current_status_transaksi'] !== 'dibayar') {
        $transactionsToUpdate[] = $row['id_transaksi'];
    }
}
$stmt->close();

// Proses update status_transaksi untuk transaksi COD yang sudah selesai dan belum 'dibayar'
if (!empty($transactionsToUpdate)) {
    // Gunakan array_unique untuk menghindari update ID transaksi yang sama berkali-kali
    $uniqueTransactionIds = array_unique($transactionsToUpdate);
    $placeholders = implode(',', array_fill(0, count($uniqueTransactionIds), '?'));
    
    $stmt = $conn->prepare("UPDATE transaksi SET status_transaksi = 'dibayar' WHERE id IN ($placeholders) AND metode_pembayaran = 'COD' AND status_transaksi != 'dibayar'");
    
    // Bind parameter secara dinamis
    $types = str_repeat('i', count($uniqueTransactionIds));
    $stmt->bind_param($types, ...$uniqueTransactionIds);
    
    if ($stmt->execute()) {
        // Opsional: Set session message jika berhasil update
        // $_SESSION['success_message'] = 'Status pembayaran pesanan COD yang telah selesai berhasil diperbarui.';
    } else {
        // Opsional: Set session message jika ada error update
        // $_SESSION['error_message'] = 'Gagal memperbarui status pembayaran pesanan COD: ' . $conn->error;
    }
    $stmt->close();

    // Setelah update, mungkin perlu me-refresh data $finishedOrders
    // Untuk kesederhanaan, kita bisa skip ini karena update hanya terjadi sekali per halaman load
    // Namun, jika Anda ingin status langsung terlihat, Anda bisa melakukan query ulang
    // atau menandai di $finishedOrders array bahwa status sudah berubah.
    // Untuk saat ini, saya asumsikan refresh halaman berikutnya akan menampilkan status terbaru.
}


// --- Mengambil item "Just For You" (Related Items) ---
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

    /* Sidebar specific styles - NOW USING MY- PREFIX FOR CONSISTENCY WITH PENDING.PHP */
    .my-sidebar-order { /* Changed from .sidebar-order */
        background-color: #ffe3f1;
        padding: 20px;
        border-radius: 10px;
        width: 100%; /* Memastikan ini mengambil lebar penuh kolomnya */
        box-sizing: border-box;
    }

    .my-sidebar-order a { /* Changed from .sidebar-order a */
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

    .my-sidebar-order a:hover { /* Changed from .sidebar-order a:hover */
        background-color: #ffb4d4;
        color: #000;
    }

    .my-sidebar-order a.active { /* Changed from .sidebar-order a.active */
        background-color: hotpink; /* Warna latar belakang untuk link aktif */
        color: white; /* Warna teks untuk link aktif */
    }

    .my-sidebar-order i { /* Changed from .sidebar-order i */
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
        align-items: center; /* Menyelaraskan item secara vertikal di tengah */
        margin-bottom: 10px;
        width: 100%; /* Pastikan item-details mengisi lebar penuh */
    }

    .product-image {
        max-width: 100px; /* Ukuran yang lebih kecil untuk item individual */
        border-radius: 8px;
        background-color: white;
        padding: 5px;
        object-fit: cover;
        height: 100px; /* tinggi tetap untuk konsistensi */
        margin-right: 15px;
        flex-shrink: 0; /* Pastikan gambar tidak mengecil */
    }

    .product-info-text {
        flex-grow: 1; /* Biarkan teks info produk mengisi ruang yang tersedia */
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

    .btn-action-group {
        display: flex;
        gap: 10px; /* Jarak antara tombol */
        margin-top: 15px;
        width: 100%; /* Memastikan grup tombol mengambil lebar penuh jika perlu */
        justify-content: flex-end; /* Memindahkan tombol ke kanan */
        flex-wrap: wrap; /* Izinkan wrap jika layar kecil */
    }

    .btn-buy-again {
        background-color: hotpink; /* Warna pink untuk Buy Again */
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        font-weight: bold;
        text-decoration: none;
        display: inline-block;
        transition: background-color 0.3s ease;
    }

    .btn-buy-again:hover {
        background-color: deeppink;
        color: white; /* Tetap putih saat hover */
    }

    .btn-rate-order { /* Class baru untuk tombol rating keseluruhan pesanan */
        background-color: #FFD700; /* Warna emas untuk Rate Product */
        color: #333; /* Warna teks gelap agar terbaca */
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        font-weight: bold;
        text-decoration: none;
        display: inline-block;
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    .btn-rate-order:hover {
        background-color: #DAA520; /* Warna emas yang sedikit lebih gelap saat hover */
        color: white;
    }

    /* Tombol rate product untuk setiap item produk (jika masih ingin ada) */
    .btn-rate-product-item { /* Class baru untuk tombol rating per item */
        background-color: #FFD700;
        color: #333;
        border: none;
        padding: 5px 10px; /* Padding lebih kecil */
        border-radius: 4px;
        font-weight: bold;
        text-decoration: none;
        display: inline-block;
        transition: background-color 0.3s ease, color 0.3s ease;
        margin-left: auto; /* Mendorong tombol ini ke paling kanan di dalam item-details */
        flex-shrink: 0; /* Pastikan tombol tidak mengecil */
        font-size: 0.85em; /* Ukuran font lebih kecil */
    }

    .btn-rate-product-item:hover {
        background-color: #DAA520;
        color: white;
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
        .my-sidebar-order { /* Changed from .sidebar-order */
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
        .btn-action-group {
            flex-direction: column; /* Tumpuk tombol di layar kecil */
            align-items: flex-start;
        }
        /* Penyesuaian untuk tombol Buy Again dan Rate Order di mobile */
        .btn-buy-again, .btn-rate-order {
            width: 100%; /* Buat tombol selebar penuh di layar kecil */
            text-align: center;
            margin-left: 0 !important; /* Pastikan tidak ada margin kiri otomatis di mobile */
            margin-bottom: 10px; /* Tambahkan sedikit jarak antar tombol saat menumpuk */
        }
        .btn-rate-order:last-child {
            margin-bottom: 0; /* Hapus margin bawah untuk tombol terakhir */
        }
        /* Sembunyikan tombol rate per item di mobile jika ingin hanya ada tombol rate order */
        /* .btn-rate-product-item {
            display: none;
        } */
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
                <a href="cancel.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'cancel.php') ? 'active' : '' ?>"><i class="fas fa-times-circle text-danger"></i> Cancelled</a>
            </div>
        </div>

        <div class="col-md-9">
            <h2>My Finished Orders</h2>
            <?php if (empty($finishedOrders)): ?>
                <div class="alert alert-info text-center mt-3" role="alert">
                    Tidak ada pesanan yang sudah 'selesai' saat ini.
                </div>
            <?php else: ?>
                <?php foreach ($finishedOrders as $order): ?>
                    <div class="order-card">
                        <div class="order-details-header">
                            <h5>Products in this package:</h5>
                        </div>
                        <div class="item-list">
                            <?php foreach ($order['items'] as $item): ?>
                                <div class="item-details">
                                    <img src="admin/uploads/<?= htmlspecialchars($item['img']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="product-image">
                                    <div class="product-info-text">
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
                            <strong>Tanggal Diterima:</strong>
                            <?php if ($order['tanggal_diterima']): ?>
                                <?= htmlspecialchars(date('d F Y', strtotime($order['tanggal_diterima']))) ?>
                            <?php else: ?>
                                Belum ada informasi tanggal diterima.
                            <?php endif; ?>
                            <br>
                            <strong>Metode Pembayaran:</strong> <?= htmlspecialchars($order['metode_pembayaran']) ?><br>
                            <strong>Status Transaksi:</strong>
                            <?php if ($order['metode_pembayaran'] === 'COD' && $order['current_status_transaksi'] === 'dibayar'): ?>
                                <span class="badge bg-success">Dibayar (COD)</span>
                            <?php elseif ($order['metode_pembayaran'] === 'COD' && $order['current_status_transaksi'] !== 'dibayar'): ?>
                                <span class="badge bg-warning text-dark">Menunggu Pembayaran (COD)</span>
                            <?php else: ?>
                                <span class="badge bg-primary"><?= htmlspecialchars(ucfirst($order['current_status_transaksi'])) ?></span>
                            <?php endif; ?><br>
                            <strong>Total Transaksi:</strong> Rp<?= number_format($order['total_transaksi'], 0, ',', '.') ?><br>
                        </div>
                        <div class="btn-action-group">
                            <a href="index.php" class="btn-buy-again">
                                <i class="fas fa-shopping-cart"></i> Buy Again
                            </a>
                            <?php
                            // Cek apakah produk dalam pesanan sudah pernah di-rate oleh user ini
                            // Ini memerlukan tabel rating dan join tambahan untuk cek
                            // Untuk contoh ini, kita asumsikan bisa di-rate jika status pengiriman selesai
                            // Anda bisa menambahkan logika di sini untuk memeriksa apakah rating sudah ada
                            ?>
                            <a href="rating.php?id_transaksi=<?= htmlspecialchars($order['id_transaksi']) ?>" class="btn-rate-order">
                                <i class="fas fa-star"></i> Rate Order
                            </a>
                        </div>
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
    const items = document.querySelectorAll('#productSlider .product-card');

    function navigateRelated(direction) {
        const container = document.getElementById('productSlider');
        const scrollAmount = 250; // Adjust as needed
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