<?php
require_once 'koneksi.php';
session_start();

// Pastikan pengguna sudah login
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id'];

// Mengambil data transaksi dari database
$transactions = [];
$stmt = $conn->prepare("
    SELECT
        t.id AS transaksi_id,
        t.created_at AS tanggal_transaksi,
        t.total_transaksi,
        t.status_transaksi AS status_transaksi_utama,
        GROUP_CONCAT(CONCAT(td.jumlah, 'x ', p.nama_produk, ' (@', td.harga_satuan, ')') SEPARATOR '; ') AS produk_dibeli,
        t.metode_pembayaran,
        ap.nama_penerima,
        ap.no_telepon AS no_telepon_penerima,
        ap.alamat_lengkap AS alamat_penerima,
        ap.desa AS desa_penerima,
        ap.distrik AS distrik_penerima,
        ap.kota AS kota_penerima,
        ap.provinsi AS provinsi_penerima,
        ap.kode_pos AS kode_pos_penerima,
        pg.status_pengiriman,
        pg.no_resi,
        pg.ekspedisi,
        pg.tanggal_dikirim,
        pg.tanggal_diterima
    FROM
        transaksi t
    JOIN
        transaksi_detail td ON t.id = td.id_transaksi
    JOIN
        produk p ON td.id_produk = p.id
    LEFT JOIN
        pengiriman pg ON t.id = pg.id_transaksi -- Join ke tabel pengiriman
    LEFT JOIN
        alamat_pengiriman ap ON pg.id_alamat = ap.id_alamat -- Kemudian join ke alamat_pengiriman melalui pengiriman
    WHERE
        t.id_user = ?
    GROUP BY
        t.id
    ORDER BY
        t.created_at DESC
");

if ($stmt) {
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }
    $stmt->close();
} else {
    // Handle error if statement preparation fails
    $error = "Gagal menyiapkan query transaksi: " . $conn->error;
}

include 'resource/header.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi Saya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Styling dari myaccount.php agar konsisten */
        .modal-bg {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.5); display: none; justify-content: center; align-items: center;
            z-index: 999;
        }
        .modal-box {
            background: #fff; padding: 30px; border-radius: 10px; width: 60%;
        }
        .alert {
            position: fixed; top: 20px; right: 20px; padding: 15px 20px; border-radius: 5px;
            color: white; font-weight: bold; z-index: 1000;
            opacity: 0; transform: translateY(-20px); transition: opacity 0.5s ease, transform 0.5s ease;
        }
        .alert.show {
            opacity: 1; transform: translateY(0);
        }
        .alert-success { background: green; }
        .alert-error { background: red; }
        button {
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        button:hover {
            filter: brightness(0.9);
            cursor: pointer;
        }
        .modal-box .close-btn {
            position: absolute; top: 15px; right: 20px; font-size: 24px; cursor: pointer; color: #aaa; text-decoration: none;
        }
        .modal-box .close-btn:hover {
            color: #555;
        }

        /* Specific styling for history page */
        .transaction-card {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 20px;
            padding: 20px;
        }
        .transaction-card h5 {
            color: #e91e63;
            margin-bottom: 15px;
            font-weight: bold;
        }
        .transaction-card p {
            margin-bottom: 5px;
        }
        .transaction-card .btn {
            margin-top: 10px;
        }
        .product-list {
            list-style: none;
            padding: 0;
            margin-top: 10px;
        }
        .product-list li {
            background: #eee;
            padding: 8px 12px;
            border-radius: 5px;
            margin-bottom: 5px;
            font-size: 0.9em;
        }
    </style>
</head>
<body>

<main style="display: flex; padding: 40px 20px; background: #ffc0cb; min-height: 80vh;">
    <aside style="width: 25%; padding-right: 20px;">
        <h3>Manage My Account</h3>
        <ul style="list-style: none; margin-top: 10px; padding:0;">
            <li><a href="myaccount.php" style="text-decoration:none; color:#000;">My Profile</a></li>
            <li><a href="my_address_ship.php" style="text-decoration:none; color:#000;">My Address Ship</a></li>
        </ul>
        <h3 style="margin-top: 20px;">My Orders</h3>
        <ul style="list-style: none; margin-top: 10px; padding:0;">
            <li><a href="cancel.php" style="text-decoration:none; color:#000;">My Cancellations</a></li>
            <li><a href="my_reviews.php" style="text-decoration:none; color:#000;">My Rating</a></li>
            <li style="color:#e91e63; font-weight:bold;"><a href="my_history.php" style="text-decoration:none; color:#e91e63;">My History</a></li>
        </ul>
        <h3 style="margin-top: 20px;">My Wishlist</h3>
        <ul style="list-style: none; margin-top: 10px; padding:0;">
            <li><a href="guarantee.php" style="text-decoration:none; color:#000;">Guarantee</a></li>
        </ul>
    </aside>

    <section style="width: 75%; background: white; padding: 30px; border-radius: 10px;">
        <h2 style="color:#e91e63; margin-bottom: 30px;">Riwayat Transaksi Saya</h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error show">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <?php if (empty($transactions)): ?>
            <div class="alert alert-info" role="alert">
                Anda belum memiliki riwayat transaksi.
            </div>
        <?php else: ?>
            <?php foreach ($transactions as $transaction): ?>
                <div class="transaction-card">
                    <h5>ID Transaksi: #<?= htmlspecialchars($transaction['transaksi_id']) ?></h5>
                    <p><strong>Tanggal Transaksi:</strong> <?= date('d F Y H:i', strtotime(htmlspecialchars($transaction['tanggal_transaksi']))) ?></p>
                    <p><strong>Status Transaksi:</strong> <?= htmlspecialchars($transaction['status_transaksi_utama']) ?></p>
                    <p><strong>Metode Pembayaran:</strong> <?= htmlspecialchars($transaction['metode_pembayaran'] ?? 'N/A') ?></p>
                    <p><strong>Produk Dipesan:</strong></p>
                    <ul class="product-list">
                        <?php
                        $products_list = explode('; ', $transaction['produk_dibeli']);
                        foreach ($products_list as $product_item) {
                            echo '<li>' . htmlspecialchars($product_item) . '</li>';
                        }
                        ?>
                    </ul>

                    <p><strong>Total Harga:</strong> <strong style="color: #e91e63;">Rp<?= number_format(htmlspecialchars($transaction['total_transaksi']), 0, ',', '.') ?></strong></p>

                    <h6>Detail Pengiriman:</h6>
                    <p><strong>Penerima:</strong> <?= htmlspecialchars($transaction['nama_penerima'] ?? 'N/A') ?></p>
                    <p><strong>Telepon:</strong> <?= htmlspecialchars($transaction['no_telepon_penerima'] ?? 'N/A') ?></p>
                    <p><strong>Alamat:</strong> <?= htmlspecialchars($transaction['alamat_penerima'] ?? 'N/A') ?>, Desa <?= htmlspecialchars($transaction['desa_penerima'] ?? 'N/A') ?>, Kec. <?= htmlspecialchars($transaction['distrik_penerima'] ?? 'N/A') ?>, Kota <?= htmlspecialchars($transaction['kota_penerima'] ?? 'N/A') ?>, Prov. <?= htmlspecialchars($transaction['provinsi_penerima'] ?? 'N/A') ?>, Kode Pos <?= htmlspecialchars($transaction['kode_pos_penerima'] ?? 'N/A') ?></p>

                    <p><strong>Status Pengiriman:</strong> <?= htmlspecialchars($transaction['status_pengiriman'] ?? 'N/A') ?></p>
                    <p><strong>Nomor Resi:</strong> <?= htmlspecialchars($transaction['no_resi'] ?? 'N/A') ?></p>
                    <p><strong>Kurir:</strong> <?= htmlspecialchars($transaction['ekspedisi'] ?? 'N/A') ?></p>
                    <p><strong>Tanggal Kirim:</strong> <?= ($transaction['tanggal_dikirim'] ? date('d F Y', strtotime(htmlspecialchars($transaction['tanggal_dikirim']))) : 'N/A') ?></p>
                    <p><strong>Tanggal Terima:</strong> <?= ($transaction['tanggal_diterima'] ? date('d F Y', strtotime(htmlspecialchars($transaction['tanggal_diterima']))) : 'N/A') ?></p>

                    <a href="print_receipt.php?transaksi_id=<?= htmlspecialchars($transaction['transaksi_id']) ?>" target="_blank" class="btn btn-primary" style="background-color: #e91e63; border-color: #e91e63;">
                        <i class="fas fa-print"></i> Cetak Struk
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>

<?php include 'resource/footer.php'; ?>

<script>
    // Script untuk menampilkan dan menyembunyikan alert
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => { alert.classList.add('show'); }, 10);
            setTimeout(() => {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 500);
            }, 5000);
        });
    });
</script>
</body>
</html>