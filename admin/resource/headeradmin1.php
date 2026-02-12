<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_nama'])) {
    header("Location: loginadmin.php");
    exit;
}
$nama_admin = $_SESSION['admin_nama'] ?? 'Admin';

$current_page = basename($_SERVER['PHP_SELF']);


?>

<style>
    .header-admin {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: linear-gradient(to right, #ffb6c1 25%, #ff0080 75%);
        padding: 10px 20px;
        height: 40px;
        color: black;
        font-family: 'Segoe UI', sans-serif;
        font-weight: bold;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 999;
    }

    .logo-section {
        display: flex;
        align-items: center;
        gap: 10px;
        color: deeppink;
        transform: translateX(30px);
    }

    .logo-section img {
        height: 60px;
    }

    .menu-icon {
        margin-left: 30px;
        cursor: pointer;
        transform: translateX(50px);
    }

    .menu-icon div {
        width: 25px;
        height: 3px;
        background-color: black;
        margin: 4px 0;
        border-radius: 2px;
    }

    .admin-info {
        position: relative;
        display: flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
    }

    .admin-info span {
        font-size: 14px;
        font-weight: bold;
    }

    .dropdown-menu {
        display: none;
        position: absolute;
        top: 40px;
        right: 0;
        background: rgba(255, 255, 255, 0.6);
        backdrop-filter: blur(10px);
        border-radius: 10px;
        padding: 15px;
        min-width: 180px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.15);
    }

    .dropdown-menu a {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        color: black;
        text-decoration: none;
        font-size: 15px;
        border-radius: 6px;
    }

    .dropdown-menu a:hover {
        background-color:rgb(251, 118, 204);
    }

    .sidebar {
        transform: translateY(10px);
        width: 250px;
        background-color: #fdf0f5;
        height: 100vh;
        position: fixed;
        top: 60px;
        left: -270px;
        padding: 20px;
        box-sizing: border-box;
        border-right: 1px solid #ddd;
        transition: left 0.3s ease;
        z-index: 998;
    }

    .sidebar.active {
        left: 0;
    }

    .sidebar a {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px;
        color: black;
        text-decoration: none;
        margin-bottom: 5px;
        border-radius: 5px;
        font-size: 15px;
    }

    .sidebar a.active,
    .sidebar a:hover {
        background-color: #f9d1dd;
        color: deeppink;
        font-weight: bold;
    }

    .sidebar hr {
        border: none;
        border-top: 1px solid #e2d5db;
        margin: 5px 0 10px;
    }
</style>

<div class="header-admin">
    <div style="display: flex; align-items: center;">
        <div class="logo-section">
            <img src="img/logoelektroshop.png" alt="Logo">
        </div>
        <div class="menu-icon" onclick="toggleSidebar()">
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>

    <div class="admin-info" onclick="toggleDropdown()">
        <i class="bi bi-person-circle"></i>
        <span><?= strtoupper($nama_admin) ?></span>
        <i class="bi bi-caret-down-fill"></i>
        <div class="dropdown-menu" id="dropdownMenu">
            <a href="manageakun.php"><i class="bi bi-gear-fill"></i> Manage Akun</a>
            <a href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </div>
    </div>
</div>

<div class="sidebar" id="sidebar">
    <a href="./dashboardadmin.php" class="<?= $current_page == 'dashboardadmin.php' ? 'active' : '' ?>"><i class="bi bi-columns-gap"></i> Dashboard</a><hr>
    <a href="./kelola_pengguna.php" class="<?= $current_page == 'kelola_pengguna.php' ? 'active' : '' ?>"><i class="bi bi-people-fill"></i> Kelola Pengguna</a><hr>
    <a href="./kelola_produk.php" class="<?= $current_page == 'kelola_produk.php' ? 'active' : '' ?>"><i class="bi bi-box-seam"></i> Kelola Produk</a><hr>
    <a href="./kelola_kategori.php" class="<?= $current_page == 'kelola_kategori.php' ? 'active' : '' ?>"><i class="bi bi-tags-fill"></i> Kelola Kategori</a><hr>
    <a href="./kelola_transaksi.php" class="<?= $current_page == 'kelola_transaksi.php' ? 'active' : '' ?>"><i class="bi bi-cash-coin"></i> Kelola Transaksi</a><hr>
    <a href="./kelola_faq.php" class="<?= $current_page == 'kelola_faq.php' ? 'active' : '' ?>"><i class="bi bi-question-circle-fill"></i> Kelola FAQ</a><hr>
    <a href="./kelola_pengiriman.php" class="<?= $current_page == 'kelola_pengiriman.php' ? 'active' : '' ?>"><i class="bi bi-truck"></i> Kelola Pengiriman</a><hr>
    <a href="./kelola_diskon.php" class="<?= $current_page == 'kelola_diskon.php' ? 'active' : '' ?>"><i class="bi bi-ticket-detailed-fill"></i> Kupon Diskon</a><hr>
    <a href="./kelola_rating.php" class="<?= $current_page == 'kelola_rating.php' ? 'active' : '' ?>"><i class="bi bi-star-fill"></i> Kelola Rating</a><hr>
    <a href="./kelola_penjualan.php" class="<?= $current_page == 'kelola_penjualan.php' ? 'active' : '' ?>"><i class="bi bi-bar-chart-fill"></i> Kelola Penjualan</a><hr>
    <a href="./kelola_garansi.php" class="<?= $current_page == 'kelola_garansi.php' ? 'active' : '' ?>"><i class="bi bi-shield-check"></i> Kelola Garansi</a><hr>
    <a href="./kelola_favorite.php" class="<?= $current_page == 'kelola_favorite.php' ? 'active' : '' ?>"><i class="bi bi-heart-fill"></i> Kelola Favorite</a>
</div>


<script>
    function toggleDropdown() {
        const menu = document.getElementById('dropdownMenu');
        menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
    }

    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('active');
    }

    window.addEventListener('click', function(e) {
        const dropdown = document.getElementById('dropdownMenu');
        if (!e.target.closest('.admin-info')) {
            dropdown.style.display = 'none';
        }
    });
</script>

<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
