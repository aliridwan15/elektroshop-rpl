<?php
session_start();
if (!isset($_SESSION['admin_nama'])) {
    header("Location: loginadmin.php");
    exit;
}

require 'koneksi.php';

// Fungsi hitung total data per tabel
function getTotal($koneksi, $table) {
    $result = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM $table");
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

// Ambil jumlah data dari masing-masing tabel
$jumlah_pengguna = getTotal($koneksi, "pengguna");
$jumlah_produk = getTotal($koneksi, "produk");
$jumlah_kategori = getTotal($koneksi, "kategori");
$jumlah_transaksi = getTotal($koneksi, "transaksi");
$jumlah_faq = getTotal($koneksi, "faq");
$jumlah_diskon = getTotal($koneksi, "kupon_diskon");
$jumlah_pengiriman = getTotal($koneksi, "pengiriman");
$jumlah_rating = getTotal($koneksi, "rating");
$jumlah_garansi = getTotal($koneksi, "garansi");
$jumlah_favorite = getTotal($koneksi, "favorite");
$jumlah_penjualan = getTotal($koneksi, "penjualan"); // view
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #ffc0cb;
        }

        .main-content {
            margin-left: 20px;
            padding: 100px 40px 40px 40px;
            background-color: #ffc0cb;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .main-content.sidebar-active {
            margin-left: 270px;
        }

        .welcome {
            font-style: italic;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        a.card-link {
            text-decoration: none;
            color: inherit;
        }

        .card {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 2px 2px 10px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: transform 0.2s, background-color 0.2s;
        }

        .card:hover {
            background-color: #ffe3ec;
            transform: translateY(-3px);
            cursor: pointer;
        }

        .card-title {
            font-size: 14px;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .card-value {
            font-size: 18px;
        }

        .card-icon {
    font-size: 20px;
    color: white;
    width: 40px;
    height: 40px;
    background-color: #28a745; /* Warna hijau */
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}


    </style>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const sidebar = document.getElementById("sidebar");
            const mainContent = document.querySelector(".main-content");

            if (sidebar && sidebar.classList.contains("active")) {
                mainContent.classList.add("sidebar-active");
            }

            window.toggleSidebar = function () {
                sidebar.classList.toggle("active");
                mainContent.classList.toggle("sidebar-active");
            }
        });
    </script>
</head>
<body>

<?php include 'resource/headeradmin1.php'; ?>

<div class="main-content">
    <div class="welcome">
        Welcome to Dashboard ADMIN, "<?= $_SESSION['admin_nama'] ?>"!
    </div>

    <div class="grid">
    <a href="kelola_pengguna.php" class="card-link">
        <div class="card">
            <div>
                <div class="card-title">Pengguna</div>
                <div class="card-value"><?= $jumlah_pengguna ?></div>
            </div>
            <div class="card-icon"><i class="bi bi-people-fill"></i></div>
        </div>
    </a>
    <a href="kelola_produk.php" class="card-link">
        <div class="card">
            <div>
                <div class="card-title">Produk</div>
                <div class="card-value"><?= $jumlah_produk ?></div>
            </div>
            <div class="card-icon"><i class="bi bi-box-seam"></i></div>
        </div>
    </a>
    <a href="kelola_kategori.php" class="card-link">
        <div class="card">
            <div>
                <div class="card-title">Kategori</div>
                <div class="card-value"><?= $jumlah_kategori ?></div>
            </div>
            <div class="card-icon"><i class="bi bi-tags-fill"></i></div>
        </div>
    </a>
    <a href="kelola_transaksi.php" class="card-link">
        <div class="card">
            <div>
                <div class="card-title">Transaksi</div>
                <div class="card-value"><?= $jumlah_transaksi ?></div>
            </div>
            <div class="card-icon"><i class="bi bi-credit-card-2-front-fill"></i></div>
        </div>
    </a>
    <a href="kelola_faq.php" class="card-link">
        <div class="card">
            <div>
                <div class="card-title">FAQ</div>
                <div class="card-value"><?= $jumlah_faq ?></div>
            </div>
            <div class="card-icon"><i class="bi bi-question-circle-fill"></i></div>
        </div>
    </a>
    <a href="kelola_diskon.php" class="card-link">
        <div class="card">
            <div>
                <div class="card-title">Kupon Diskon</div>
                <div class="card-value"><?= $jumlah_diskon ?></div>
            </div>
            <div class="card-icon"><i class="bi bi-ticket-perforated-fill"></i></div>
        </div>
    </a>
    <a href="kelola_pengiriman.php" class="card-link">
        <div class="card">
            <div>
                <div class="card-title">Pengiriman</div>
                <div class="card-value"><?= $jumlah_pengiriman ?></div>
            </div>
            <div class="card-icon"><i class="bi bi-truck"></i></div>
        </div>
    </a>
    <a href="kelola_rating.php" class="card-link">
        <div class="card">
            <div>
                <div class="card-title">Rating</div>
                <div class="card-value"><?= $jumlah_rating ?></div>
            </div>
            <div class="card-icon"><i class="bi bi-star-fill"></i></div>
        </div>
    </a>
    <a href="kelola_garansi.php" class="card-link">
        <div class="card">
            <div>
                <div class="card-title">Garansi</div>
                <div class="card-value"><?= $jumlah_garansi ?></div>
            </div>
            <div class="card-icon"><i class="bi bi-shield-check"></i></div>
        </div>
    </a>
    <a href="kelola_favorite.php" class="card-link">
        <div class="card">
            <div>
                <div class="card-title">Favorite</div>
                <div class="card-value"><?= $jumlah_favorite ?></div>
            </div>
            <div class="card-icon"><i class="bi bi-heart-fill"></i></div>
        </div>
    </a>
    <a href="kelola_penjualan.php" class="card-link">
        <div class="card">
            <div>
                <div class="card-title">Penjualan (dibayar)</div>
                <div class="card-value"><?= $jumlah_penjualan ?></div>
            </div>
            <div class="card-icon"><i class="bi bi-graph-up-arrow"></i></div>
        </div>
    </a>
</div>

<?php include 'resource/footeradmin.php'?>
</div>

</body>
</html>
