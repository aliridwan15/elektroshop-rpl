<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'koneksi.php'; // Ensure koneksi.php is included

$cartItemCount = 0;
$favoriteItemCount = 0;

if (isset($_SESSION['id'])) {
    $userId = $_SESSION['id'];

    // Get cart item count
    $stmtCart = $conn->prepare("SELECT SUM(quantity) FROM cart WHERE id_user = ?");
    $stmtCart->bind_param("i", $userId);
    $stmtCart->execute();
    $stmtCart->bind_result($count);
    $stmtCart->fetch();
    $stmtCart->close();
    $cartItemCount = $count > 0 ? $count : 0;

    // Get favorite item count
    $stmtFavorite = $conn->prepare("SELECT COUNT(*) FROM favorite WHERE id_user = ?");
    $stmtFavorite->bind_param("i", $userId);
    $stmtFavorite->execute();
    $stmtFavorite->bind_result($favCount);
    $stmtFavorite->fetch();
    $stmtFavorite->close();
    $favoriteItemCount = $favCount > 0 ? $favCount : 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Electroshop</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <style>
        /* Your existing CSS */
        * {
            margin: 0; padding: 0; box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background: #ffe3f1;
            color: #111;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .topbar {
            background: white;
            color: #000;
            font-size: 14px;
            padding: 8px 20px;
            text-align: center;
            border-bottom: 1px solid #f48fb1;
        }

        header {
            background: #ffcce5;
            padding: 10px 20px;
            border-bottom: 2px solid #f48fb1;
            position: relative;
        }

        nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
        }

        .logo img {
            height: 70px;
        }

        .nav-center {
            display: flex;
            gap: 20px;
            list-style: none;
            flex: 1;
            justify-content: center;
        }

        .nav-center li a {
            text-decoration: none;
            color: #111;
            font-weight: 600;
        }

        .search-icons {
            display: flex;
            align-items: center;
            gap: 15px;
            position: relative;
        }

        .search-box {
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            width: 220px;
        }

        .icon-group a {
            color: #111;
            font-size: 18px;
            text-decoration: none;
            margin-left: 10px;
            position: relative; /* Important for badge positioning */
        }

        .icon-group a:hover {
            color: #e91e63;
        }

        /* --- CSS for Cart Badge --- */
        .cart-badge {
            position: absolute;
            top: -8px; /* Adjust vertical position */
            right: -8px; /* Adjust horizontal position */
            background-color: #ff0000; /* Red */
            color: white;
            font-size: 0.7em; /* Smaller font size */
            padding: 2px 6px; /* Padding for rounded shape */
            border-radius: 50%; /* Make it circular */
            min-width: 20px; /* Minimum width for single digits */
            text-align: center;
            line-height: 1; /* Keep text centered */
            display: <?= $cartItemCount > 0 ? 'block' : 'none'; ?>; /* Hide by default if 0 items */
        }

        /* --- CSS for Favorite Badge (NEW) --- */
        .favorite-badge {
            position: absolute;
            top: -8px; /* Adjust vertical position */
            right: -8px; /* Adjust horizontal position */
            background-color: #ff0000; /* Red */
            color: white;
            font-size: 0.7em; /* Smaller font size */
            padding: 2px 6px; /* Padding for rounded shape */
            border-radius: 50%; /* Make it circular */
            min-width: 20px; /* Minimum width for single digits */
            text-align: center;
            line-height: 1; /* Keep text centered */
            display: <?= $favoriteItemCount > 0 ? 'block' : 'none'; ?>; /* Hide by default if 0 items */
        }
        /* --- End NEW CSS --- */


        .account-popup {
            display: none;
            position: absolute;
            right: 0;
            top: 50px;
            background: linear-gradient(to bottom right, #a26d91, #000000);
            color: white;
            border-radius: 10px;
            padding: 10px 0;
            width: 220px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            z-index: 1000;
        }

        .account-popup a {
            display: flex;
            align-items: center;
            padding: 10px 20px;
            color: white;
            text-decoration: none;
            font-size: 14px;
        }

        .account-popup a i {
            margin-right: 12px;
        }

        .account-popup a:hover {
            background-color: #4a2f47;
            border-left: 4px solid #e91e63;
        }
    </style>
</head>
<body>
    <div class="topbar">
        Summer Sale For All Electronic And Free Express Delivery – OFF 50%! <strong>ShopNow</strong>
    </div>

    <header>
        <nav>
            <div class="logo">
                <img src="img/logoelektroshop.png" alt="Electroshop Logo">
            </div>

            <ul class="nav-center">
                <li><a href="index.php">Home</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="register.php">Sign Up</a></li>
            </ul>

            <form action="search.php" method="GET" style="display: flex; align-items: center; gap: 10px;">
                <input type="text" class="search-box" name="q" placeholder="What are you looking for?">
                <button type="submit" style="background: none; border: none; cursor: pointer;">
                    <i class="fas fa-search"></i>
                </button>
            </form>

            <div class="icon-group">
                <?php if (isset($_SESSION['id'])): ?>
                    <a href="favorite.php">
                        <i class="far fa-heart"></i>
                        <span class="favorite-badge"><?= $favoriteItemCount ?></span>
                    </a>
                    <a href="cart.php">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-badge"><?= $cartItemCount ?></span>
                    </a>
                    <a href="#" id="accountIcon"><i class="far fa-user"></i></a>
                    <div class="account-popup" id="accountPopup">
                        <a href="myaccount.php"><i class="far fa-user"></i> Manage My Account</a>
                        <a href="pending.php"><i class="fas fa-bag-shopping"></i> My Order</a>
                        <a href="faq.php"><i class="fas fa-comment-dots"></i> FAQ</a>
                        <a href="my_reviews.php"><i class="far fa-star"></i> My Reviews</a>
                        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                <?php else: ?>
                    <a href="login.php"><i class="far fa-user"></i></a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <script>
        // === Fungsi untuk memperbarui badge keranjang di header ===
        function updateCartBadge() {
            $.ajax({
                url: 'get_cart_count.php', // Ensure this path is correct
                type: 'GET',
                dataType: 'json', // Expect JSON response
                success: function(response) {
                    const count = response.count; // Access the count from the JSON response
                    $('.cart-badge').text(count);
                    if (count === 0) {
                        $('.cart-badge').hide(); // Hide if 0
                    } else {
                        $('.cart-badge').show(); // Show if items exist
                    }
                },
                error: function() {
                    console.error('Failed to update cart badge.');
                    $('.cart-badge').hide(); // Hide on error
                }
            });
        }

        // === Fungsi untuk memperbarui badge favorit di header (NEW) ===
        function updateFavoriteBadge() {
            $.ajax({
                url: 'get_favorite_count.php', // Ensure this path is correct
                type: 'GET',
                dataType: 'json', // Expect JSON response
                success: function(response) {
                    const count = response.count; // Access the count from the JSON response
                    $('.favorite-badge').text(count);
                    if (count === 0) {
                        $('.favorite-badge').hide(); // Hide if 0
                    } else {
                        $('.favorite-badge').show(); // Show if items exist
                    }
                },
                error: function() {
                    console.error('Failed to update favorite badge.');
                    $('.favorite-badge').hide(); // Hide on error
                }
            });
        }

        // Call functions on page load
        $(document).ready(function() {
            updateCartBadge(); // Initialize cart badge
            updateFavoriteBadge(); // Initialize favorite badge
        });

        // Existing account popup logic
        const accountIcon = document.getElementById('accountIcon');
        const accountPopup = document.getElementById('accountPopup');

        if (accountIcon) {
            accountIcon.addEventListener('click', function(event) {
                event.preventDefault();
                accountPopup.style.display = accountPopup.style.display === 'block' ? 'none' : 'block';
            });

            document.addEventListener('click', function(event) {
                if (!accountIcon.contains(event.target) && !accountPopup.contains(event.target)) {
                    accountPopup.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>