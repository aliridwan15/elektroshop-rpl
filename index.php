<?php
session_start();
require_once 'koneksi.php'; // Pastikan koneksi.php sudah benar

// --- START: KONSISTENSI PENGGUNAAN SESSION ID ---
// Periksa apakah pengguna sudah login. Gunakan $_SESSION['id'] untuk konsistensi.
$user_id = isset($_SESSION['id']) ? $_SESSION['id'] : null;
// --- END: KONSISTENSI PENGGUNAAN SESSION ID ---

// Ambil ID produk favorit untuk pengguna saat ini
$favorited_product_ids = [];
if ($user_id) {
    // Pastikan nama tabel adalah 'favorite' sesuai dengan add_to_favorite_ajax.php
    $sql_favorites = "SELECT id_produk FROM favorite WHERE id_user = ?";
    $stmt_favorites = $conn->prepare($sql_favorites);
    if ($stmt_favorites) {
        $stmt_favorites->bind_param("i", $user_id);
        $stmt_favorites->execute();
        $result_favorites = $stmt_favorites->get_result();
        while ($row = $result_favorites->fetch_assoc()) {
            $favorited_product_ids[] = $row['id_produk'];
        }
        $stmt_favorites->close();
    } else {
        // Handle error if prepare fails
        error_log("Failed to prepare favorites statement in index.php: " . $conn->error);
    }
}

include 'resource/header.php'; // Pastikan header.php ada dan berisi navigasi atau elemen penting lainnya

$sql_categories = "SELECT id, nama_kategori, icon_class FROM kategori ORDER BY nama_kategori ASC";
$result_categories = mysqli_query($conn, $sql_categories);

$categories = [];
if ($result_categories && mysqli_num_rows($result_categories) > 0) {
    while ($row = mysqli_fetch_assoc($result_categories)) {
        $categories[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Electroshop Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

    <style>
        /* CSS Anda yang sudah ada */
        * {margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif;}
        body {background: #ffe3f1; color: #111;}
        nav ul {list-style: none; display: flex; gap: 20px;}
        nav ul li a {text-decoration: none; color: #111; font-weight: 600;}
        .hero, .category, .products, .explore, .arrival {padding: 40px 20px;}
        .hero img, .arrival img {width: 100%; border-radius: 12px;}
        .section-title {font-size: 24px; font-weight: bold; margin-bottom: 20px;}
        .grid {display: grid; gap: 20px;}
        .product-card, .category-card {background: #fff; padding: 15px; border-radius: 12px; text-align: center;}
        .product-card img {max-width: 100%; height: 120px; object-fit: cover; margin-bottom: 10px;}
        .product-card h4 {font-size: 16px; margin: 5px 0;}
        .price {color: #e91e63; font-weight: bold;}
        footer {text-align: center; margin-top: 40px; font-size: 14px;}
        .footer-grid {display: flex; justify-content: space-around; padding: 20px 0;}
        .btn {background: #e91e63; color: white; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer;}

        .category {
            display: flex;
            gap: 30px;
            align-items: flex-start;
            max-width: 100%;
            border-bottom: 2px solid rgb(111, 111, 111);
            margin-right: 30px;
            margin-left: 30px;
        }

        .category-left {
            flex: 2;
            max-width: 45%;
            text-align: left;
            border-right: 2px solid rgb(111, 111, 111);
        }

        .category-left .grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 6px;
        }

        .category-card {
            margin-bottom: 6px;
            font-size: 18px;
            cursor: pointer;
            color: #111;
            font-weight: 600;
            background: none;
            text-align: left;
            padding-left: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            border-radius: 12px;
            padding: 12px 15px;
        }

        .carousel {
            flex: 3;
            max-width: 70%;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .carousel-inner img {
            height: 400px;
            object-fit: cover;
        }

        .carousel-control-prev,
        .carousel-control-next {
            top: 50%;
            transform: translateY(-50%);
        }

        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            filter: brightness(0.5);
        }

        .carousel-control-next-icon:hover,
        .carousel-control-prev-icon:hover {
            filter: brightness(0.5); /* abu-abu tanpa background */
        }

        .arrival-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            grid-template-rows: repeat(3, 1fr);
            gap: 20px;
            height: 600px;
        }

        .big-image, .top-right, .middle-right, .bottom-right {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 12px;
        }

        .sidebar {
            width: 500px;
            background-color: transparent;
            height: 40vh;
            padding: 30px 20px;
            position: relative;
        }

        .sidebar a {
            display: block;
            padding: 12px 10px;
            margin-bottom: 4px;
            color: #111;
            font-size: 15px;
            font-weight: 500;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.2s ease;
        }

        .sidebar a:hover {
            background-color: #f9c8dd;
            color: #e91e63;
            font-weight: 600;
        }

        .sidebar a::after {
            content: '›';
            float: right;
            color: #555;
        }

        /* Info Banner Section */
        .info-banner {
            padding: 40px 20px;
            text-align: center;
            margin-top: 40px;
        }

        .info-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .info-item {
            flex: 1 1 200px;
            max-width: 250px;
            text-align: center;
        }

        .info-item i {
            font-size: 32px;
            color: #e91e63;
            margin-bottom: 10px;
        }

        .info-item h4 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .info-item p {
            font-size: 14px;
            color: #333;
        }
        .cat-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-width: 150px;
            height: 150px;
            background: transparent;
            border: 2px solid black;
            border-radius: 12px;
            text-align: center;
            font-weight: 600;
            cursor: pointer;
            color: #000;
            transition: all 0.3s ease;
        }

        .cat-card i {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .cat-card:hover,
        .cat-card.active {
            background: #ff0a78;
            color: white;
            border-color: #ff0a78;
        }
        button {
            background-color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }

        button:hover {
            background-color: #ffe3f1;
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }

        button:hover i {
            color: #e91e63;
            transform: scale(1.2);
        }

        .product-section {
            padding: 40px 20px;
            text-align: center;
        }

        /* Best Selling Products Slider Styles */
        #best-selling-slider-container {
            display: flex; /* Use flexbox for horizontal layout */
            overflow-x: auto; /* Enable horizontal scrolling */
            white-space: nowrap; /* Keep items in a single line */
            scroll-behavior: smooth;
            gap: 15px; /* Spacing between products */
            padding-bottom: 10px; /* Space for scrollbar if present */
            -webkit-overflow-scrolling: touch; /* Smooth scrolling on iOS */
        }

        #best-selling-slider-container::-webkit-scrollbar {
            display: none; /* Hide scrollbar for a cleaner look */
        }

        .product-card-wrapper {
            flex: 0 0 auto; /* Prevent items from growing/shrinking */
            width: calc((100% / 4) - 15px); /* Adjust for 4 items per view, considering gap */
            min-width: 200px; /* Minimum width to prevent cards from getting too small */
            display: inline-block; /* To ensure they stay in a single line with white-space: nowrap */
            vertical-align: top; /* Align items at the top */
        }
        /* End Best Selling Products Slider Styles */


        .product-card {
            height: 240px; /* Fixed height for product cards */
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: #fff;
            padding: 15px;
            border-radius: 12px;
            text-align: center;
            position: relative;
        }

        .product-card .icons {
            position: absolute;
            top: 10px;
            right: 10px;
            display: flex;
            gap: 5px;
            z-index: 2; /* Ensure icons are above image */
        }
        .product-card .icons .favorite-btn { /* Use a class for the heart icon */
            background-color: transparent; /* Make button background transparent */
            box-shadow: none; /* Remove shadow */
            padding: 0; /* Remove padding */
            width: auto; /* Auto width */
            height: auto; /* Auto height */
            color: #333;
            cursor: pointer;
            transition: color 0.3s ease;
        }
        .product-card .icons .favorite-btn i {
            font-size: 1em; /* Ensure icon size is standard */
        }
        .product-card .icons .favorite-btn.is-favorited {
            color: #e91e63; /* Color when favorited (pink) */
        }
        .product-card .icons .favorite-btn:hover i {
            color: #d81b60; /* Darker pink on hover */
            transform: none; /* Remove transform on hover specific to this button */
        }


        /* Ensure the eye icon retains its default color and behavior */
        .product-card .icons .view-product-link { /* Using a class for the eye icon link */
            background-color: transparent; /* Make link background transparent */
            box-shadow: none; /* Remove shadow */
            padding: 0; /* Remove padding */
            width: auto; /* Auto width */
            height: auto; /* Auto height */
            color: #333;
            transition: color 0.3s ease;
        }
        .product-card .icons .view-product-link i {
            font-size: 1em; /* Ensure icon size is standard */
        }
        .product-card .icons .view-product-link:hover i {
            color: #e91e63; /* Change color on hover for eye icon */
            transform: none; /* Remove transform on hover specific to this link */
        }


        .explore-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .explore-nav h3 {
            font-weight: bold;
        }
        .explore-nav .arrows {
            display: flex;
            gap: 10px;
        }
        .explore-nav button {
            background: #fff;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .btn-view-all {
            background-color: #e91e63;
            color: white;
            padding: 12px 20px;
            margin-top: 20px;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            text-align: center;
            width: auto;
            min-width: 200px;
            transition: all 0.3s ease;
        }
        .btn-view-all:hover {
            background-color: #d81b60;
        }

        .product-slider-wrapper {
            overflow: hidden;
            width: 100%;
            position: relative;
        }

        .product-slider {
            display: flex;
            transition: transform 0.4s ease-in-out;
        }

        .product-slide {
            min-width: 100%;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            grid-template-rows: repeat(2, auto);
            gap: 20px 15px;
            padding: 20px 0;
            flex-shrink: 0;
            flex-grow: 0;
        }

        .object-fit-cover {
            object-fit: cover;
            height: 100%;
        }

        .section-title {
            font-size: 2rem;
            font-weight: 600;
        }

        /* Arrows for Best Selling Products */
        .products .arrow-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
        }

        .products .arrow-btn.left {
            left: 0px;
        }

        .products .arrow-btn.right {
            right: 0px;
        }

        /* Styling for favorite count in header (assuming you have a header.php) */
        .favorite-count {
            background-color: #e91e63;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.7em;
            position: absolute;
            top: -5px;
            right: -5px;
            display: none; /* Sembunyikan secara default, tampilkan dengan JS jika count > 0 */
        }
    </style>
</head>
<body>

<?php
// Example placement of favorite icon in header.php (if any)
// You can place this within your navigation element:
/*
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Electroshop</a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a href="favorite.php" class="nav-link position-relative">
                        <i class="fas fa-heart"></i>
                        <span class="favorite-count" id="headerFavoriteCount">0</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Another Link</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
*/
?>

<section class="category">
    <div class="category-left">
        <div class="sidebar">
            <h4>
                <a href="sidebars.php?jenis_produk=wearable">Wearable Technology</a>
                <a href="sidebars.php?jenis_produk=pc">PC Components</a>
                <a href="sidebars.php?jenis_produk=smart_home">Smart Home Devices</a>
                <a href="sidebars.php?jenis_produk=camera">Camera & Photography</a>
                <a href="sidebars.php?jenis_produk=storage">Storage Devices</a>
                <a href="sidebars.php?jenis_produk=networking">Networking Devices</a>
                <a href="sidebars.php?jenis_produk=gaming">Gaming Accessories</a>
                <a href="sidebars.php?jenis_produk=entertainment">Entertainment Systems</a>
                <a href="sidebars.php?jenis_produk=office">Office Peripherals</a>
            </h4>
        </div>
    </div>

    <div id="carouselExample" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="img/iphone.jpg" class="d-block w-100" alt="Slide 1">
            </div>
            <div class="carousel-item">
                <img src="img/apple.jpeg" class="d-block w-100" alt="Slide 2">
            </div>
            <div class="carousel-item">
                <img src="img/asus-rilis.jpg" class="d-block w-100" alt="Slide 3">
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExample" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>
</section>

<section style="padding: 60px 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h3 style="color: #e91e63; font-weight: bold;">Categories</h3>
            <h4 style="font-weight: 700;">Browse By Category</h4>
        </div>
        <div style="display: flex; gap: 10px;">
            <button onclick="scrollCategory(-1)" style="background-color: white; border: none; width: 40px; height: 40px; border-radius: 50%; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
                <i class="fas fa-arrow-left"></i>
            </button>
            <button onclick="scrollCategory(1)" style="background-color: white; border: none; width: 40px; height: 40px; border-radius: 50%; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
                <i class="fas fa-arrow-right"></i>
            </button>
        </div>
    </div>

    <div id="category-scroll" style="margin-top: 30px; overflow-x: auto; white-space: nowrap; scroll-behavior: smooth;">
        <div style="display: flex; justify-content: center; gap: 20px; flex-wrap: nowrap; min-width: 100%;">
                <?php if (!empty($categories)): ?>
                    <?php foreach ($categories as $category): ?>
                        <a href="kategori.php?id=<?= htmlspecialchars($category['id']) ?>" class="cat-card">
                            <i class="<?= htmlspecialchars($category['icon_class']) ?>"></i>
                            <div><?= htmlspecialchars($category['nama_kategori']) ?></div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No categories found.</p>
                <?php endif; ?>
        </div>
</div>

</section>

<section style="padding: 20px 15px;">
    <div style="
        max-width: 700px;
        margin: 0 auto;
        aspect-ratio: 16 / 9;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    ">
        <img src="img/promotion.jpg" alt="Promotional Banner" style="
            width: 100%;
            height: 100%;
            object-fit: cover;
        ">
    </div>
</section>

<section class="product-section">
  <div class="explore-nav">
    <h3 style="color: #e91e63; font-weight: bold;">Best Selling Products</h3>
    <div class="arrows">
      <button onclick="prevBestSelling()"><i class="fas fa-arrow-left"></i></button>
      <button onclick="nextBestSelling()"><i class="fas fa-arrow-right"></i></button>
    </div>
  </div>
  <h4>Check Out Our Best Sellers</h4>

  <div class="product-slider-wrapper">
    <div class="product-slider" id="bestSellingSlider">
      <?php
      $sql = "SELECT
                    p.id,
                    p.nama_produk,
                    p.gambar,
                    p.harga,
                    SUM(td.jumlah) AS total_terjual
                  FROM transaksi_detail td
                  JOIN transaksi t ON td.id_transaksi = t.id
                  JOIN produk p ON td.id_produk = p.id
                  WHERE t.status_transaksi = 'dibayar'
                  GROUP BY td.id_produk
                  ORDER BY total_terjual DESC";
      $result_best = $conn->query($sql);
      if ($result_best->num_rows > 0) {
            $count = 0;
            while($row = $result_best->fetch_assoc()) {
                if ($count % 8 === 0) {
                    if ($count > 0) echo '</div>'; // Tutup slide sebelumnya
                    echo '<div class="product-slide">'; // Buka slide baru
                }
                $is_favorited = in_array($row['id'], $favorited_product_ids) ? 'is-favorited' : '';
                echo '<div class="product-card">';
                echo '<div class="icons">';
                // Icon hati sebagai tombol dengan data-product-id dan data-product-name
                echo '<button class="favorite-btn ' . $is_favorited . '" data-product-id="' . htmlspecialchars($row['id']) . '" data-product-name="' . htmlspecialchars($row['nama_produk']) . '">';
                echo '<i class="fa-heart ' . ($is_favorited ? 'fas text-danger' : 'far') . '"></i>'; // Removed ID from here, using parent data-product-id to identify
                echo '</button>';
                // Icon mata tetap sebagai link
                echo '<a href="product_details.php?id=' . urlencode($row['id']) . '" class="view-product-link"><i class="fas fa-eye"></i></a>';
                echo '</div>';
                echo '<img src="admin/uploads/' . htmlspecialchars($row['gambar']) . '" alt="" class="img-fluid">';
                echo '<h5>' . htmlspecialchars($row['nama_produk']) . '</h5>';
                echo '<div class="price">Rp. ' . number_format($row['harga'], 0, ',', '.') . '</div>';
                echo '</div>';

                $count++;
            }
            if ($count > 0) echo '</div>'; // Tutup slide terakhir
      }
      ?>
    </div>
  </div>
</section>


<section class="product-section">
    <div class="explore-nav">
        <h3 style="color: #e91e63; font-weight: bold;">Our Products</h3>
        <div class="arrows">
            <button onclick="prevSlide()"><i class="fas fa-arrow-left"></i></button>
            <button onclick="nextSlide()"><i class="fas fa-arrow-right"></i></button>
        </div>
    </div>
    <h4>Explore Our Products</h4>

    <div class="product-slider-wrapper">
        <div class="product-slider" id="productSlider">
                <?php
                $sql = "SELECT id, nama_produk, gambar, harga FROM produk";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    $count = 0;
                    while($row = $result->fetch_assoc()) {
                        if ($count % 8 === 0) { // Mulai slide baru untuk setiap 8 produk
                            if ($count > 0) echo '</div>'; // Tutup slide sebelumnya jika bukan yang pertama
                            echo '<div class="product-slide">'; // Buka slide baru
                        }
                        $is_favorited = in_array($row['id'], $favorited_product_ids) ? 'is-favorited' : '';
                        echo '<div class="product-card">';
                        echo '<div class="icons">';
                        // Icon hati sebagai tombol dengan data-product-id dan data-product-name
                        echo '<button class="favorite-btn ' . $is_favorited . '" data-product-id="' . htmlspecialchars($row['id']) . '" data-product-name="' . htmlspecialchars($row['nama_produk']) . '">';
                        echo '<i class="fa-heart ' . ($is_favorited ? 'fas text-danger' : 'far') . '"></i>'; // Removed ID from here, using parent data-product-id to identify
                        echo '</button>';
                        // Icon mata tetap sebagai link
                        echo '<a href="product_details.php?id=' . urlencode($row['id']) . '" class="view-product-link"><i class="fas fa-eye"></i></a>';
                        echo '</div>';
                        echo '<img src="admin/uploads/' . htmlspecialchars($row['gambar']) . '" alt="" class="img-fluid">';
                        echo '<h5>' . htmlspecialchars($row['nama_produk']) . '</h5>';
                        echo '<div class="price">Rp. ' . number_format($row['harga'], 0, ',', '.') . '</div>';
                        echo '</div>';

                        $count++;
                    }
                    if ($count > 0) echo '</div>'; // Tutup slide terakhir
                }
                ?>
        </div>
    </div>

    <button class="btn-view-all" onclick="location.href='sidebars.php'">View All Products</button>
</section>


<section class="arrival py-5">
    <div class="container">
        <h3 style="color: #e91e63; font-weight: bold;">Featured </h3>
        <h4 class="section-title mb-4">New Arrival</h4>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="position-relative h-100">
                    <img src="img/ps5.jpeg" alt="Playstation 5" class="img-fluid rounded w-100 h-100 object-fit-cover">
                    <div class="position-absolute top-0 start-0 p-3 text-white">
                        <h5 class="fw-bold">PlayStation 5</h5>
                        <p class="mb-2 small">Experience lightning-fast loading with an ultra-high speed SSD, deeper immersion with support for haptic feedback, adaptive triggers, and 3D Audio, and an all-new generation of incredible PlayStation games.</p>
                        <a href="sidebars.php" class="btn btn-dark btn-sm">Shop Now</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="mb-3 position-relative">
                    <img src="img/stikps.jpeg" alt="Women Collections" class="img-fluid rounded w-100 object-fit-cover">
                    <div class="position-absolute top-0 start-0 p-3 text-dark">
                        <h6 class="fw-bold">Gaming Accessories Collection</h6>
                        <p class="small mb-2">Discover the latest in gaming accessories designed to enhance your play. From precision controllers to immersive headsets, level up your setup with our new arrivals.</p>
                        <a href="sidebars.php?jenis_produk=gaming" class="btn btn-light btn-sm">Shop Now</a>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-6 position-relative">
                        <img src="img/speakers.jpeg" alt="Speakers" class="img-fluid rounded w-100 object-fit-cover">
                        <div class="position-absolute top-0 start-0 p-3 text-white">
                            <h6 class="fw-bold">Premium Audio Speakers</h6>
                            <p class="small mb-2">Immerse yourself in rich, dynamic sound with our new range of high-fidelity speakers. Perfect for gaming, music, and movies.</p>
                            <a href="sidebars.php?jenis_produk=entertainment" class="btn btn-light btn-sm">Shop Now</a>
                        </div>
                    </div>
                    <div class="col-6 position-relative">
                        <img src="img/smartphone.jpeg" alt="Smartphones" class="img-fluid rounded w-100 object-fit-cover">
                        <div class="position-absolute top-0 start-0 p-3 text-white bg-dark bg-opacity-50 rounded h-100 overflow-auto">
                            <h6 class="fw-bold">Next-Gen Smartphones</h6>
                            <p class="small mb-2">Stay connected and capture every moment with our cutting-edge smartphones. Featuring advanced cameras, powerful processors, and sleek designs, they're built for your dynamic lifestyle.</p>
                            <a href="kategori.php?id=1" class="btn btn-light btn-sm">Shop Now</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<section class="info-banner">
    <div class="info-container">
        <div class="info-item">
            <i class="fas fa-truck"></i>
            <h4>Free Shipping</h4>
            <p>Free delivery for orders over Rp1.000.000.000</p>
        </div>
        <div class="info-item">
            <i class="fas fa-headset"></i>
            <h4>24/7 Support</h4>
            <p>Friendly support anytime, anywhere</p>
        </div>
        <div class="info-item">
            <i class="fas fa-shield-alt"></i>
            <h4>Secure Payment</h4>
            <p>Your payment is safe with us</p>
        </div>
    </div>
</section>

<?php include 'resource/footer.php'; ?>

<div class="modal fade" id="favoriteModal" tabindex="-1" aria-labelledby="favoriteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" id="favoriteModalHeader">
        <h5 class="modal-title" id="favoriteModalLabel"><i class="" id="favoriteModalTitleIcon"></i> <span id="favoriteModalTitle"></span></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <img src="img/th.jpeg" alt="Status Icon" id="favoriteModalIcon" class="mb-3" style="width: 70px; height: 70px;">
        <p class="lead" id="favoriteModalMessage"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">X</button>
      </div>
    </div>
  </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Scroll kategori
    function scrollCategory(direction) {
        const container = document.getElementById('category-scroll');
        const scrollAmount = container.clientWidth / 2;
        container.scrollLeft += direction * scrollAmount;
    }

    // Slider Produk Terlaris
    const bestSellingSlider = document.getElementById('bestSellingSlider');
    const bestSellingSlides = bestSellingSlider ? bestSellingSlider.querySelectorAll('.product-slide') : [];
    let currentBestSellingSlide = 0;
    const totalBestSellingSlides = bestSellingSlides.length;

    function showBestSellingSlide(index) {
        if (totalBestSellingSlides === 0) return;

        if (index < 0) {
            currentBestSellingSlide = totalBestSellingSlides - 1;
        } else if (index >= totalBestSellingSlides) {
            currentBestSellingSlide = 0;
        } else {
            currentBestSellingSlide = index;
        }

        bestSellingSlider.style.transform = `translateX(-${currentBestSellingSlide * 100}%)`;
    }

    function nextBestSelling() {
        showBestSellingSlide(currentBestSellingSlide + 1);
    }

    function prevBestSelling() {
        showBestSellingSlide(currentBestSellingSlide - 1);
    }

    // Slider Produk Kami
    const productSlider = document.getElementById('productSlider');
    const slides = productSlider ? productSlider.querySelectorAll('.product-slide') : []; // Added null check
    let currentSlide = 0;
    const totalSlides = slides.length;

    function showSlide(index) {
        if (totalSlides === 0) return; // Handle case where no slides exist

        if (index < 0) {
            currentSlide = totalSlides - 1;
        } else if (index >= totalSlides) {
            currentSlide = 0;
        } else {
            currentSlide = index;
        }

        productSlider.style.transform = `translateX(-${currentSlide * 100}%)`;
    }

    function nextSlide() {
        showSlide(currentSlide + 1);
    }

    function prevSlide() {
        showSlide(currentSlide - 1);
    }

    // Initialize sliders on page load
    document.addEventListener('DOMContentLoaded', () => {
        showBestSellingSlide(0);
        showSlide(0);
    });

    // AJAX for Add/Remove Favorite
    $(document).ready(function() {
        // Function to update the favorite count in the header
        function updateFavoriteCount(count) {
            const headerFavoriteCountSpan = $('#headerFavoriteCount');
            if (headerFavoriteCountSpan.length) {
                headerFavoriteCountSpan.text(count);
                if (count > 0) {
                    headerFavoriteCountSpan.show();
                } else {
                    headerFavoriteCountSpan.hide();
                }
            }
        }

        // Function to show the favorite modal with dynamic content
        function showFavoriteModal(title, message, headerClass, iconClass, imageSrc) {
            $('#favoriteModalHeader').removeClass().addClass('modal-header ' + headerClass);
            $('#favoriteModalTitleIcon').removeClass().addClass('fas ' + iconClass);
            $('#favoriteModalTitle').text(title);
            $('#favoriteModalIcon').attr('src', imageSrc);
            $('#favoriteModalMessage').html(message); // Use .html() to allow bold tags from PHP
            const favoriteModal = new bootstrap.Modal(document.getElementById('favoriteModal'));
            favoriteModal.show();
        }

        // Initial fetch of favorite count when page loads
        // Sends a GET request to add_to_favorite_ajax.php to get the count
        $.ajax({
            url: 'add_to_favorite_ajax.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.total_favorites !== undefined) {
                    updateFavoriteCount(response.total_favorites);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error fetching initial favorite count:", error);
            }
        });

        $(document).on('click', '.favorite-btn', function() {
            const button = $(this);
            const productId = button.data('product-id');
            // No need to pass product_name from here if PHP fetches it
            // const productName = button.data('product-name');

            $.ajax({
                url: 'add_to_favorite_ajax.php',
                method: 'POST',
                data: {
                    product_id: productId
                    // product_name: productName // Removed, PHP will fetch
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        // Update the icon state for ALL buttons with this product ID
                        $(`.favorite-btn[data-product-id="${productId}"]`).each(function() {
                            const currentButton = $(this);
                            const currentIcon = currentButton.find('i.fa-heart'); // Target the heart icon specifically
                            if (response.action === 'added') {
                                currentButton.addClass('is-favorited');
                                currentIcon.removeClass('far').addClass('fas text-danger');
                            } else if (response.action === 'removed') {
                                currentButton.removeClass('is-favorited');
                                currentIcon.removeClass('fas text-danger').addClass('far');
                            }
                        });

                        // Use product_name from the PHP response for the modal message
                        const modalMessage = response.message; // PHP already formats the message with bold product name
                        showFavoriteModal('Berhasil!', modalMessage, 'bg-success text-white', 'fa-check-circle', 'img/th.jpeg'); // Use your success image
                    } else {
                        // This happens if the server returns status: 'error' (e.g., not logged in)
                        // Use product_name from the PHP response, or fallback if not available
                        const modalMessage = response.message || 'Terjadi kesalahan. Silakan coba lagi.';
                        showFavoriteModal('Error!', modalMessage, 'bg-danger text-white', 'fa-times-circle', 'img/error.jpeg'); // Use your error image
                    }

                    // Always update the header favorite count based on the latest response
                    if (response.total_favorites !== undefined) {
                        updateFavoriteCount(response.total_favorites);
                    }
                },
                error: function(xhr, status, error) {
                    // This block will be executed if the AJAX call itself fails (e.g., network error, PHP syntax error)
                    console.error("AJAX Error:", status, error, xhr.responseText);
                    showFavoriteModal('Error!', 'Terjadi kesalahan pada server. Silakan coba lagi nanti.', 'bg-danger text-white', 'fa-times-circle', 'img/error.jpeg'); // Generic error
                }
            });
        });
    });
</script>

</body>
</html>