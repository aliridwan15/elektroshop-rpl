<?php
session_start();
require_once 'koneksi.php'; // Pastikan file ini mendefinisikan $koneksi atau $conn

// Termasuk header - PENTING: Pastikan file ini SENDIRI TIDAK mengeluarkan tag <html>, <head>, <body>,
// karena struktur HTML utama dan CSS sekarang ada di sidebars.php.
// resource/header.php Anda seharusnya hanya berisi konten yang masuk KE DALAM <body> (misalnya, logo sederhana atau navigasi tambahan).
include 'resource/header.php';

// Dapatkan jenis produk dari parameter URL
// Sanitasi input untuk mencegah injeksi SQL
$filter_jenis_produk = '';
if (isset($_GET['jenis_produk']) && !empty($_GET['jenis_produk'])) {
    // Asumsi koneksi.php Anda mengatur $conn, jika itu $koneksi, ubah $conn menjadi $koneksi di sini
    $filter_jenis_produk = mysqli_real_escape_string($conn, $_GET['jenis_produk']);
}

// Siapkan query SQL
$sql = "SELECT produk.*, kategori.nama_kategori
        FROM produk
        JOIN kategori ON produk.id_kategori = kategori.id";

// Jika jenis produk ditentukan, tambahkan klausa WHERE
if (!empty($filter_jenis_produk)) {
    $sql .= " WHERE produk.jenis_produk = '$filter_jenis_produk'";
}

$sql .= " ORDER BY produk.nama_produk ASC"; // Urutkan produk secara alfabetis

// Asumsi koneksi.php Anda mengatur $conn, jika itu $koneksi, ubah $conn menjadi $koneksi di sini
$result = mysqli_query($conn, $sql);

$page_title = "Semua Produk"; // Default title
if (!empty($filter_jenis_produk)) {
    // Format judul dengan rapi, misalnya, 'smart_home' menjadi 'Produk Rumah Pintar'
    $page_title = ucwords(str_replace('_', ' ', $filter_jenis_produk)) . " Produk";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #ffc0cb; /* Background pink */
            margin: 0;
            padding-top: 20px; /* Sesuaikan padding karena nav fixed dihapus */
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: #ffe6f0; /* Pink lebih terang untuk konten */
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        h1 {
            color: deeppink;
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.5em;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.05);
        }
        .product-grid {
            display: grid;
            /* Lebih kompak: Target 5-6 kolom pada layar besar */
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 15px; /* Kurangi jarak untuk tata letak lebih padat */
            justify-content: center;
            padding: 0 10px;
        }
        .product-card {
            background-color: #fff;
            border: 1px solid #ffccd9;
            border-radius: 8px; /* Radius sedikit lebih kecil */
            box-shadow: 0 3px 10px rgba(0,0,0,0.07); /* Bayangan lebih ringan */
            padding: 10px; /* Kurangi padding */
            text-align: center;
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .product-card:hover {
            transform: translateY(-3px); /* Angkatan kurang dramatis */
            box-shadow: 0 6px 15px rgba(0,0,0,0.12); /* Sedikit lebih menonjol saat hover */
        }
        .product-card img {
            max-width: 100%;
            height: 120px; /* Tinggi sangat berkurang untuk tampilan ringkas */
            object-fit: contain; /* Gunakan 'contain' untuk memastikan gambar penuh terlihat */
            border-radius: 4px; /* Radius lebih kecil untuk gambar */
            margin-bottom: 10px; /* Kurangi margin */
            border: 1px solid #eee;
        }
        .product-card h3 {
            font-size: 1.1em; /* Font lebih kecil */
            color: #444;
            margin-bottom: 5px; /* Kurangi margin */
            line-height: 1.2;
            overflow: hidden; /* Sembunyikan overflow untuk judul panjang */
            white-space: nowrap; /* Cegah pembungkus teks */
            text-overflow: ellipsis; /* Tambahkan elipsis untuk teks terpotong */
        }
        .product-card p {
            font-size: 0.8em; /* Font lebih kecil */
            color: #666;
            margin-bottom: 4px; /* Kurangi margin */
            line-height: 1.3;
        }
        .product-card .price {
            font-size: 1em; /* Font lebih kecil */
            font-weight: bold;
            color: deeppink;
            margin-top: 8px; /* Kurangi margin */
            margin-bottom: 8px; /* Kurangi margin */
        }
        .product-card small {
            font-size: 0.75em; /* Font lebih kecil lagi */
            color: #888;
            line-height: 1.2;
        }
        .no-products {
            text-align: center;
            color: #888;
            padding: 50px;
            font-size: 1.2em;
        }

        /* Styling Tombol Detail Produk */
        .product-card .btn-detail {
            background-color: deeppink; /* Warna tombol */
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.9em;
            margin-top: 10px; /* Beri sedikit jarak dari konten di atasnya */
            display: inline-block; /* Agar padding bekerja dan rata tengah */
            transition: background-color 0.2s ease;
        }
        .product-card .btn-detail:hover {
            background-color: #c71585; /* Warna lebih gelap saat hover */
            color: white; /* Pastikan teks tetap putih saat hover */
        }

        /* Breadcrumb Styling */
        .breadcrumb-nav {
            background-color: #ff90cd; /* Cocok dengan header tabel */
            padding: 10px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            font-size: 1em;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .breadcrumb-nav a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.2s ease;
        }
        .breadcrumb-nav a:hover {
            color: #ffd4e8;
        }
        .breadcrumb-nav span {
            color: #333;
            margin: 0 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="breadcrumb-nav">
            <a href="index.php">Home</a>
            <span>/</span>
            <a href="sidebars.php">Produk</a>
            <?php if (!empty($filter_jenis_produk)): ?>
                <span>/</span>
                <span><?= ucwords(str_replace('_', ' ', $filter_jenis_produk)) ?></span>
            <?php endif; ?>
        </div>

        <h1><?= $page_title ?></h1>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="product-grid">
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <div class="product-card">
                        <img src="admin/uploads/<?= htmlspecialchars($row['gambar']) ?>" alt="<?= htmlspecialchars($row['nama_produk']) ?>">
                        <h3><?= htmlspecialchars($row['nama_produk']) ?></h3>
                        <strong>Kategori: <?= htmlspecialchars($row['nama_kategori']) ?></strong>
                        <p class="price">Rp<?= number_format($row['harga'], 0, ',', '.') ?></p>
                        <p><small>Berat: <?= $row['berat'] ?> kg | Warna: <?= htmlspecialchars($row['warna']) ?></small></p>
                        <p><small>Deskripsi: <?= nl2br(htmlspecialchars(substr($row['deskripsi'], 0, 50))) ?><?php if (strlen($row['deskripsi']) > 50) echo '...' ?></small></p>
                        <a href="product_details.php?id=<?= $row['id'] ?>" class="btn-detail">Lihat Detail</a>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="no-products">Tidak ada produk ditemukan untuk kategori ini.</p>
        <?php endif; ?>
    </div>

    <?php
    // Tutup koneksi database
    // Asumsi koneksi.php Anda mengatur $conn, jika itu $koneksi, ubah $conn menjadi $koneksi di sini
    mysqli_close($conn);

    // Termasuk footer
    include 'resource/footer.php'; // Pastikan file ini menutup </body> dan </html>
    ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>