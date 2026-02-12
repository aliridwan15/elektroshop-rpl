<?php
require_once 'koneksi.php';
session_start();

// Pastikan pengguna sudah login
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id'];
$transaksi_id = isset($_GET['transaksi_id']) ? intval($_GET['transaksi_id']) : 0;

$transaction = null;
$error = '';

if ($transaksi_id > 0) {
    // Mengambil data transaksi spesifik dari database
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
            pengiriman pg ON t.id = pg.id_transaksi
        LEFT JOIN
            alamat_pengiriman ap ON pg.id_alamat = ap.id_alamat
        WHERE
            t.id = ? AND t.id_user = ?
        GROUP BY
            t.id
        LIMIT 1
    ");

    if ($stmt) {
        $stmt->bind_param("ii", $transaksi_id, $id_user);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $transaction = $result->fetch_assoc();
        } else {
            $error = "Transaksi tidak ditemukan atau Anda tidak memiliki akses ke transaksi ini.";
        }
        $stmt->close();
    } else {
        $error = "Gagal menyiapkan query transaksi: " . $conn->error;
    }
} else {
    $error = "ID Transaksi tidak valid.";
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Transaksi #<?= htmlspecialchars($transaksi_id) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2, h3, h5 {
            color: #e91e63;
            text-align: center;
            margin-bottom: 20px;
        }
        .section-title {
            color: #e91e63;
            margin-top: 25px;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px dashed #ddd;
        }
        p {
            margin-bottom: 5px;
            line-height: 1.6;
        }
        strong {
            color: #555;
        }
        .product-list {
            list-style: none;
            padding: 0;
            margin-top: 10px;
        }
        .product-list li {
            background: #f0f0f0;
            padding: 8px 12px;
            border-radius: 5px;
            margin-bottom: 5px;
            font-size: 0.9em;
        }
        .total-price {
            font-size: 1.2em;
            font-weight: bold;
            color: #e91e63;
            text-align: right;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #eee;
        }

        /* Print-specific styles */
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .receipt-container {
                box-shadow: none;
                border: none;
                margin: 0;
                padding: 0;
            }
            /* Hide elements not needed for printing */
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>

<div class="receipt-container">
    <h2>Struk Pembelian</h2>
    <h3>ELEKTROSHOP</h3>
    <p class="text-center no-print">Terima kasih telah berbelanja bersama kami!</p>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger" role="alert">
            <?= $error ?>
        </div>
    <?php elseif ($transaction): ?>
        <h5 class="section-title">Detail Transaksi</h5>
        <p><strong>ID Transaksi:</strong> #<?= htmlspecialchars($transaction['transaksi_id']) ?></p>
        <p><strong>Tanggal Transaksi:</strong> <?= date('d F Y H:i', strtotime(htmlspecialchars($transaction['tanggal_transaksi']))) ?></p>
        <p><strong>Status Transaksi:</strong> <?= htmlspecialchars($transaction['status_transaksi_utama']) ?></p>
        <p><strong>Metode Pembayaran:</strong> <?= htmlspecialchars($transaction['metode_pembayaran'] ?? 'N/A') ?></p>

        <h5 class="section-title">Produk Dipesan</h5>
        <ul class="product-list">
            <?php
            $products_list = explode('; ', $transaction['produk_dibeli']);
            foreach ($products_list as $product_item) {
                echo '<li>' . htmlspecialchars($product_item) . '</li>';
            }
            ?>
        </ul>

        <p class="total-price"><strong>Total Harga:</strong> Rp<?= number_format(htmlspecialchars($transaction['total_transaksi']), 0, ',', '.') ?></p>

        <h5 class="section-title">Detail Pengiriman</h5>
        <p><strong>Penerima:</strong> <?= htmlspecialchars($transaction['nama_penerima'] ?? 'N/A') ?></p>
        <p><strong>Telepon:</strong> <?= htmlspecialchars($transaction['no_telepon_penerima'] ?? 'N/A') ?></p>
        <p><strong>Alamat:</strong> <?= htmlspecialchars($transaction['alamat_penerima'] ?? 'N/A') ?>, Desa <?= htmlspecialchars($transaction['desa_penerima'] ?? 'N/A') ?>, Kec. <?= htmlspecialchars($transaction['distrik_penerima'] ?? 'N/A') ?>, Kota <?= htmlspecialchars($transaction['kota_penerima'] ?? 'N/A') ?>, Prov. <?= htmlspecialchars($transaction['provinsi_penerima'] ?? 'N/A') ?>, Kode Pos <?= htmlspecialchars($transaction['kode_pos_penerima'] ?? 'N/A') ?></p>

        <p><strong>Status Pengiriman:</strong> <?= htmlspecialchars($transaction['status_pengiriman'] ?? 'N/A') ?></p>
        <p><strong>Nomor Resi:</strong> <?= htmlspecialchars($transaction['no_resi'] ?? 'N/A') ?></p>
        <p><strong>Kurir:</strong> <?= htmlspecialchars($transaction['ekspedisi'] ?? 'N/A') ?></p>
        <p><strong>Tanggal Kirim:</strong> <?= ($transaction['tanggal_dikirim'] ? date('d F Y', strtotime(htmlspecialchars($transaction['tanggal_dikirim']))) : 'N/A') ?></p>
        <p><strong>Tanggal Terima:</strong> <?= ($transaction['tanggal_diterima'] ? date('d F Y', strtotime(htmlspecialchars($transaction['tanggal_diterima']))) : 'N/A') ?></p>

        <div class="text-center mt-4 no-print">
            <button onclick="window.print()" class="btn btn-primary" style="background-color: #e91e63; border-color: #e91e63;">
                Cetak Struk Ini
            </button>
            <a href="my_history.php" class="btn btn-secondary" style="margin-left: 10px;">Kembali ke Riwayat Transaksi</a>
        </div>
    <?php endif; ?>
</div>

<script>
    // Memulai fungsi cetak secara otomatis setelah halaman dimuat (opsional, bisa dihapus jika ingin tombol cetak manual)
    // window.onload = function() {
    //     window.print();
    // };
</script>
</body>
</html>