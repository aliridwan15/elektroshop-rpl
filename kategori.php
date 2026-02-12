<?php
session_start();
require_once 'koneksi.php'; // Pastikan ini mengacu pada koneksi database Anda

include 'resource/header.php'; // Termasuk header Anda (tanpa <html>, <head>, <body>)

$category_id = null;
$category_name = "Kategori Tidak Ditemukan"; // Default title

// Dapatkan ID kategori dari parameter URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $category_id = mysqli_real_escape_string($conn, $_GET['id']);

    // Ambil nama kategori berdasarkan ID
    $sql_cat_name = "SELECT nama_kategori FROM kategori WHERE id = '$category_id'";
    $result_cat_name = mysqli_query($conn, $sql_cat_name);
    if ($result_cat_name && mysqli_num_rows($result_cat_name) > 0) {
        $row_cat_name = mysqli_fetch_assoc($result_cat_name);
        $category_name = htmlspecialchars($row_cat_name['nama_kategori']);
    }

    // Query produk berdasarkan ID kategori
    $sql_products = "SELECT produk.*, kategori.nama_kategori
                     FROM produk
                     JOIN kategori ON produk.id_kategori = kategori.id
                     WHERE produk.id_kategori = '$category_id'
                     ORDER BY produk.nama_produk ASC";
    $result_products = mysqli_query($conn, $sql_products);

} else {
    // Jika tidak ada ID kategori yang diberikan atau tidak valid
    $result_products = null; // Tidak ada hasil produk
}

$page_title = $category_name . " Produk";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        /* SALIN SEMUA CSS DARI FILE SIDEBARS.PHP ANDA DI SINI
           (termasuk body, container, h1, product-grid, product-card, dll.)
           Atau, lebih baik lagi, pindahkan semua CSS umum ke file .css terpisah
           dan tautkan di sini: <link rel="stylesheet" href="path/to/your/styles.css">
        */

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #ffc0cb; /* Pink background */
            margin: 0;
            padding-top: 20px; /* Adjust padding as fixed nav is removed */
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: #ffe6f0; /* Lighter pink for content */
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
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 15px;
            justify-content: center;
            padding: 0 10px;
        }
        .product-card {
            background-color: #fff;
            border: 1px solid #ffccd9;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.07);
            padding: 10px;
            text-align: center;
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .product-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.12);
        }
        .product-card img {
            max-width: 100%;
            height: 120px;
            object-fit: contain;
            border-radius: 4px;
            margin-bottom: 10px;
            border: 1px solid #eee;
        }
        .product-card h3 {
            font-size: 1.1em;
            color: #444;
            margin-bottom: 5px;
            line-height: 1.2;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }
        .product-card p {
            font-size: 0.8em;
            color: #666;
            margin-bottom: 4px;
            line-height: 1.3;
        }
        .product-card .price {
            font-size: 1em;
            font-weight: bold;
            color: deeppink;
            margin-top: 8px;
            margin-bottom: 8px;
        }
        .product-card small {
            font-size: 0.75em;
            color: #888;
            line-height: 1.2;
        }
        .no-products {
            text-align: center;
            color: #888;
            padding: 50px;
            font-size: 1.2em;
        }
        .product-card .btn-detail {
            background-color: deeppink;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.9em;
            margin-top: 10px;
            display: inline-block;
            transition: background-color 0.2s ease;
        }
        .product-card .btn-detail:hover {
            background-color: #c71585;
            color: white;
        }
        .breadcrumb-nav {
            background-color: #ff90cd;
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
            <span>/</span>
            <span><?= $category_name ?></span>
        </div>

        <h1><?= $page_title ?></h1>

        <?php if ($result_products && mysqli_num_rows($result_products) > 0): ?>
            <div class="product-grid">
                <?php while ($row = mysqli_fetch_assoc($result_products)): ?>
                    <div class="product-card">
                        <img src="admin/uploads/<?= htmlspecialchars($row['gambar']) ?>" alt="<?= htmlspecialchars($row['nama_produk']) ?>">
                        <h3><?= htmlspecialchars($row['nama_produk']) ?></h3>
                        <strong>Kategori: <?= htmlspecialchars($row['nama_kategori']) ?></strong>
                        <p>Stok: <?= $row['stok'] ?></p>
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
    mysqli_close($conn);

    include 'resource/footer.php'; // Pastikan file ini menutup </body> dan </html>
    ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>