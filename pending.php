<?php
session_start();
include 'koneksi.php'; // Pastikan file koneksi Anda sudah benar dan `$conn` tersedia

// Pastikan user sudah login
if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit();
}

$loggedInUserId = (int)$_SESSION['id'];

// --- Display session messages (added for consistency) ---
if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success text-center" role="alert">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger text-center" role="alert">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
    unset($_SESSION['error_message']);
}
// --------------------------------------------------------


// Ambil data pesanan pending dari database
$pendingOrders = [];
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
    JOIN
        pengiriman pg ON t.id = pg.id_transaksi  -- JOIN with pengiriman table
    WHERE
        t.id_user = ? AND pg.status_pengiriman = 'diproses' -- Changed condition to shipping status
    ORDER BY
        t.id DESC, p.nama_produk ASC
");
$stmt->bind_param("i", $loggedInUserId);
$stmt->execute();
$result = $stmt->get_result();

// Mengorganisir hasil berdasarkan ID Transaksi
while ($row = $result->fetch_assoc()) {
    $transaksiId = $row['id_transaksi'];
    if (!isset($pendingOrders[$transaksiId])) {
        $pendingOrders[$transaksiId] = [
            'id_transaksi' => $transaksiId,
            'total_transaksi' => $row['total_transaksi'], // Total keseluruhan transaksi
            'items' => []
        ];
    }
    $pendingOrders[$transaksiId]['items'][] = [
        'name' => $row['nama_produk'],
        'img' => $row['gambar'],
        'price' => $row['harga_satuan'],
        'qty' => $row['jumlah'],
    ];
}
$stmt->close();


// Ambil beberapa item terkait (misalnya, 4 produk acak)
$relatedItems = [];
$stmt = $conn->prepare("SELECT id, nama_produk, harga, gambar FROM produk ORDER BY RAND() LIMIT 4");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    // Ambil rating count (jika ada) - Anda mungkin perlu JOIN ke tabel rating
    // Untuk saat ini, saya akan menggunakan angka acak atau 0 sebagai placeholder
    $ratingCount = rand(10, 100); // Placeholder
    $relatedItems[] = [
        'id' => $row['id'],
        'img' => $row['gambar'],
        'name' => $row['nama_produk'],
        'price' => $row['harga'],
        'rating' => $ratingCount
    ];
}
$stmt->close();
// If you uncommented $conn->close() before, make sure it's after ALL database interactions.
// For a script that includes a footer (which might use the connection), it's generally better
// to let the script end naturally, or close it right before `exit()`.
// $conn->close();
?>

<?php include 'resource/header.php'; ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

<style>
    /* Umum: Gunakan class yang lebih spesifik untuk mencegah bentrok */

    .my-sidebar-order { /* Mengganti .sidebar-order agar lebih spesifik */
        background-color: #ffe3f1;
        padding: 20px;
        border-radius: 10px;
        /* Hapus atau komentari baris ini. display: inline-block; bisa mengganggu layout kolom Bootstrap. */
        /* display: inline-block; */
        min-width: 200px;
    }

    .my-sidebar-order a { /* Mengganti .sidebar-order a */
        display: flex;
        align-items: center;
        color: #000;
        padding: 10px;
        text-decoration: none;
        font-weight: bold;
        border-radius: 6px;
        margin-bottom: 10px;
    }

    .my-sidebar-order a:hover { /* Mengganti .sidebar-order a:hover */
        background-color: #ffb4d4;
        color: #000;
    }

    .my-sidebar-order i { /* Mengganti .sidebar-order i */
        margin-right: 10px;
    }

    /* CSS for active link in sidebar */
    .my-sidebar-order a.active { /* Mengganti .sidebar-order a.active */
        background-color: hotpink; /* Warna latar belakang untuk link aktif */
        color: white; /* Warna teks untuk link aktif */
    }

    @media (max-width: 768px) {
        .my-sidebar-order { /* Mengganti .sidebar-order */
            width: 100%;
        }
    }

    .my-order-card { /* Mengganti .order-card agar lebih spesifik */
        background-color: transparent;
        border-radius: 10px;
        padding: 20px;
        margin: 0 auto 20px auto;
        max-width: 800px;
        display: flex;
        position: relative;
        border: 1px solid #ffb6d9;
        min-height: 22px;
        flex-wrap: wrap;
    }
    .my-order-card-header { /* Mengganti .order-card-header */
        width: 100%;
        font-weight: bold;
        margin-bottom: 10px;
        border-bottom: 1px dashed #ffb6d9;
        padding-bottom: 10px;
    }

    .my-order-item-container { /* Mengganti .order-item-container */
        display: flex;
        align-items: center;
        width: 100%;
        margin-bottom: 15px;
    }

    .my-product-image { /* Mengganti .product-image agar lebih spesifik */
        max-width: 100px;
        max-height: 100px;
        border-radius: 10px;
        background-color: white;
        padding: 5px;
        object-fit: contain;
    }

    .my-product-title { /* Mengganti .product-title */
        font-size: 16px;
        font-weight: bold;
        margin-bottom: 3px;
        margin-left: 15px;
    }

    .my-product-price { /* Mengganti .product-price */
        font-size: 14px;
        font-weight: bold;
        margin-left: 15px;
    }

    .my-product-qty { /* Mengganti .product-qty */
        font-size: 13px;
        margin-left: 10px;
        color: #555;
    }

    .my-product-divider { /* Mengganti .product-divider */
        border-top: 1px solid #eee;
        margin: 8px 0;
        width: 100%;
    }

    .my-product-meta { /* Mengganti .product-meta */
        font-size: 13px;
    }

    /* Tombol Bayar tidak ada lagi, jadi btn-pay dihapus */
    /* .btn-pay {
        background-color: #ff2e8c;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        font-weight: bold;
        text-decoration: none;
    }

    .btn-pay:hover {
        background-color: #e6006d;
        text-decoration: none;
    } */

    .my-delete-icon { /* Mengganti .delete-icon */
        font-size: 20px;
        color: #333;
        cursor: pointer;
    }

    .my-delete-icon:hover { /* Mengganti .delete-icon:hover */
        color: red;
    }

    .my-bottom-right-controls { /* Mengganti .bottom-right-controls */
        position: absolute;
        bottom: 20px;
        right: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .my-size-box { /* Mengganti .size-box */
        display: inline-block;
        border: 1px solid #ccc;
        padding: 2px 8px;
        border-radius: 4px;
        font-weight: bold;
        margin-left: 5px;
        font-size: 13px;
        background-color: white;
    }

    .my-dot-color { /* Mengganti .dot-color */
        height: 12px;
        width: 12px;
        background-color: #1da1f2;
        border-radius: 50%;
        display: inline-block;
        margin-right: 5px;
        border: 1px solid #ccc;
    }

    .my-related-items-container { /* Mengganti .related-items-container */
        background-color: transparent;
        padding: 0;
        margin-top: 40px;
    }

    .my-related-items-title { /* Mengganti .related-items-title */
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 0;
        color: #333;
    }

    .my-related-items-grid { /* Mengganti .related-items-grid */
        display: flex;
        gap: 20px;
        overflow-x: auto;
        padding-bottom: 10px;
        scroll-snap-type: x mandatory;
        max-width: 100%;
        padding: 0 20px;
        transition: transform 0.5s ease-in-out;
    }

    .my-product-card { /* Mengganti .product-card */
        min-width: 250px;
        flex: 0 0 250px;
        scroll-snap-align: start;
        background-color: white;
        border-radius: 10px;
        padding: 15px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
    }

    .my-product-card:hover { /* Mengganti .product-card:hover */
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
    }

    .my-product-image.related { /* Menambahkan .my- ke .product-image */
        width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 8px;
        margin-bottom: 10px;
        background-color: #f5f5f5;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #999;
    }

    .my-product-actions { /* Mengganti .product-actions */
        position: absolute;
        top: 8px;
        right: 8px;
        display: flex;
        gap: 8px;
    }

    .my-product-actions a { /* Mengganti .product-actions a */
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
    }

    .my-product-actions a:hover { /* Mengganti .product-actions a:hover */
        color: #ff4d4d;
    }

    /* Ini sudah spesifik, tidak perlu perubahan */
    .product-card {
        min-width: 250px;
        max-width: 250px;
        flex: 0 0 auto;
        scroll-snap-align: start;
    }

    .rating-count {
        color: #666;
        font-size: 12px;
    }

    @media (min-width: 768px) {
        .my-order-row { /* Mengganti .order-row */
            display: flex;
        }
    }

    @media (max-width: 768px) {
        .my-product-card { /* Mengganti .product-card */
            width: 160px;
        }
    }
</style>

<div class="container-fluid" style="padding: 40px 20px; background-color: #ffe3f1;">
    <div class="row my-order-row">
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
            <?php if (empty($pendingOrders)): ?>
                <div class="alert alert-info text-center" role="alert">
                    Tidak ada pesanan pending saat ini.
                </div>
            <?php else: ?>
                <?php foreach ($pendingOrders as $order): ?>
                    <div class="my-order-card">
                        <div class="my-order-card-header">
                            Order ID: #<?= htmlspecialchars($order['id_transaksi']) ?> - Total: Rp<?= number_format($order['total_transaksi'], 0, ',', '.') ?>
                        </div>
                        <?php foreach ($order['items'] as $item): ?>
                            <div class="my-order-item-container">
                                <div class="text-center">
                                    <img src="admin/uploads/<?= htmlspecialchars($item['img']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="my-product-image img-fluid">
                                </div>

                                <div class="ms-3" style="flex: 1;">
                                    <div class="my-product-title"><?= htmlspecialchars($item['name']) ?></div>
                                    <div>
                                        <span class="my-product-price">Rp <?= number_format($item['price'], 0, ',', '.') ?></span>
                                        <span class="my-product-qty">x<?= htmlspecialchars($item['qty']) ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <div class="my-bottom-right-controls">
                            <a href="#" class="delete-icon-trigger" data-bs-toggle="modal" data-bs-target="#cancelConfirmationModal" data-transaction-id="<?= htmlspecialchars($order['id_transaksi']) ?>" onclick="event.preventDefault()">
                                <i class="fas fa-trash my-delete-icon"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <div class="my-related-items-container px-2">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h5 class="my-related-items-title mb-0">Related Item</h5>
                </div>

                <div id="sliderWrapper" style="overflow: hidden; width: 100%;">
                    <div class="my-related-items-grid px-2" id="productSlider">
                        <?php foreach ($relatedItems as $item): ?>
                            <div class="my-product-card">
                                <div class="my-product-image-wrapper">
                                    <img src="admin/uploads/<?= htmlspecialchars($item['img']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="my-product-image related">
                                    <div class="my-product-actions">
                                        <a href="favorite.php?product_id=<?= htmlspecialchars($item['id']) ?>"><i class="far fa-heart"></i></a>
                                        <a href="product_details.php?id=<?= htmlspecialchars($item['id']) ?>"><i class="far fa-eye"></i></a>
                                    </div>
                                </div>
                                <div class="my-product-name"><?= htmlspecialchars($item['name']) ?></div>
                                <div class="my-product-price">Rp <?= number_format($item['price'], 0, ',', '.') ?></div>
                                <div class="product-rating">★★★★★ <span class="rating-count">(<?= htmlspecialchars($item['rating']) ?>)</span></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="cancelConfirmationModal" tabindex="-1" aria-labelledby="cancelConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelConfirmationModalLabel">Konfirmasi Pembatalan Pesanan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Apakah Anda yakin ingin membatalkan pesanan ini? Aksi ini tidak dapat dibatalkan dan stok produk akan dikembalikan.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tidak</button>
                <a href="#" id="confirmCancelButton" class="btn btn-danger">Ya, Batalkan Pesanan</a>
            </div>
        </div>
    </div>
</div>

<?php include 'resource/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<script>
    // Script for Cancel Confirmation Modal
    var cancelConfirmationModal = document.getElementById('cancelConfirmationModal');
    if (cancelConfirmationModal) {
        cancelConfirmationModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var transactionId = button.getAttribute('data-transaction-id');
            var confirmButton = cancelConfirmationModal.querySelector('#confirmCancelButton');
            confirmButton.href = 'cancel_transaction.php?transaction_id=' + transactionId;
        });
    }
</script>

</body>
</html>