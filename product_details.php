<?php
session_start(); // Start the session at the very beginning
include 'koneksi.php'; // file koneksi ke database
include 'resource/header.php'; // Pastikan header ini berisi badge keranjang jika diperlukan

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$UserId = isset($_SESSION['id']) ? (int)$_SESSION['id'] : 0;
// Ambil data produk
$stmt = $conn->prepare("SELECT * FROM produk WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$produk = $result->fetch_assoc();

if (!$produk) {
    echo "<div class='container mt-5'><h4>Produk tidak ditemukan.</h4></div>";
    include 'resource/footer.php';
    exit;
}

// Ambil rating
$ratingStmt = $conn->prepare("SELECT AVG(bintang) as rata_rata, COUNT(*) as jumlah FROM rating WHERE id_produk = ?");
$ratingStmt->bind_param("i", $id);
$ratingStmt->execute();
$ratingResult = $ratingStmt->get_result();
$rating = $ratingResult->fetch_assoc();
$avgRating = $rating['rata_rata'] !== null ? round($rating['rata_rata'], 1) : 0;
$jumlahRating = $rating['jumlah'];

$gambarUtama = explode(',', $produk['gambar'])[0]; // Get the first image as main
$gambarList = explode(',', $produk['gambar']); // All images for thumbnails

// Check if the product is already favorited by the current user
$isFavorited = false;
if (isset($_SESSION['id'])) {
    $userId = $_SESSION['id'];
    $checkFavoriteStmt = $conn->prepare("SELECT COUNT(*) FROM favorite WHERE id_user = ? AND id_produk = ?");
    $checkFavoriteStmt->bind_param("ii", $userId, $id);
    $checkFavoriteStmt->execute();
    $checkFavoriteStmt->bind_result($count);
    $checkFavoriteStmt->fetch();
    $checkFavoriteStmt->close();
    if ($count > 0) {
        $isFavorited = true;
    }
}

// Handle Add to Cart (Existing Logic) - This part will now be handled by AJAX more cleanly.
// We'll keep the basic PHP alert for non-JS users or fallback, but the JS will show modal.
if (isset($_POST['add_to_cart_form_submit'])) { // Changed name to avoid conflict with AJAX
    if (!isset($_SESSION['id'])) {
        echo "<script>alert('Anda harus login untuk menambahkan ke keranjang.'); window.location.href='login.php';</script>";
    } else {
        $userId = $_SESSION['id'];
        $productId = $_POST['product_id'];
        $quantityToAdd = isset($_POST['quantity_to_add']) ? intval($_POST['quantity_to_add']) : 1;
        $tanggal = date('Y-m-d');

        // Check if product exists in user's cart
        $cekCart = $conn->prepare("SELECT id, quantity FROM cart WHERE id_user = ? AND id_produk = ?");
        $cekCart->bind_param("ii", $userId, $productId);
        $cekCart->execute();
        $cekResult = $cekCart->get_result();

        if ($cekResult->num_rows == 0) {
            // Product not in cart, insert new
            $insertCart = $conn->prepare("INSERT INTO cart (id_user, id_produk, quantity, tanggal_tambah, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
            $insertCart->bind_param("iiis", $userId, $productId, $quantityToAdd, $tanggal);
            if ($insertCart->execute()) {
                // Success message for non-AJAX or initial load (though AJAX is preferred)
            } else {
                // Error message
            }
        } else {
            // Product in cart, update quantity
            $existingCart = $cekResult->fetch_assoc();
            $newQty = $existingCart['quantity'] + $quantityToAdd;
            $updateCart = $conn->prepare("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?");
            $updateCart->bind_param("ii", $newQty, $existingCart['id']);
            if ($updateCart->execute()) {
                // Success message
            } else {
                // Error message
            }
        }
        // Redirect to prevent form re-submission on refresh and show updated cart count
        echo "<script>window.location.href='product_details.php?id=" . $id . "';</script>";
        exit;
    }
}

// Ambil produk terkait berdasarkan kategori
$relatedStmt = $conn->prepare("
    SELECT
        p.id,
        p.nama_produk,
        p.gambar,
        p.harga,
        k.nama_kategori,
        ROUND(AVG(r.bintang), 1) AS avg_rating,
        COUNT(r.id) AS total_ulasan
    FROM produk p
    JOIN kategori k ON p.id_kategori = k.id
    LEFT JOIN rating r ON p.id = r.id_produk
    WHERE p.id_kategori = ? AND p.id <> ?
    GROUP BY p.id
    ORDER BY RAND()
    LIMIT 4
");
$relatedStmt->bind_param("ii", $produk['id_kategori'], $produk['id']);
$relatedStmt->execute();
$relatedResult = $relatedStmt->get_result();
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
.main-image {
    border: 3px solid #007bff;
    border-radius: 8px;
}
.thumbnail-image { /* Added class for thumbnail images */
    cursor: pointer;
    border: 2px solid transparent;
    transition: border 0.3s ease;
    width: 100%; /* Ensure thumbnails take full width of their col */
    height: 100px; /* Fixed height for consistent look */
    object-fit: cover; /* Crop images to fit */
}
.thumbnail-image:hover, .thumbnail-image.active {
    border: 2px solid #007bff;
}
.btn-group .btn:hover {
    background-color: #e9ecef;
}
.btn-primary {
    background-color: #ff007f;
    border-color: #ff007f;
}
.btn-primary:hover {
    background-color: #e60073;
    border-color: #e60073;
}
.card {
    border: 1px solid #ccc;
    border-radius: 10px;
    background-color: #ffe3f1;
}
.card i {
    color: #007bff;
    margin-right: 5px;
}
.card i.fas.fa-truck,
.card i.fas.fa-undo {
    color: #000 !important;
}
body {
    background-color: #ffe3f1;
}
.bg-pink {
    background-color: #ff007f;
}
/* Style for modal */
.modal-header.bg-success {
    background-color: #28a745 !important; /* Green header for success */
}
.modal-header.bg-danger { /* Added for favorite modal error */
    background-color: #dc3545 !important; /* Red header for error */
}
.modal-header .btn-close-white {
    filter: invert(1) grayscale(100%) brightness(200%); /* Makes close button white */
}
</style>

<div class="container py-5">
    <div class="row">
        <div class="col-md-6">
            <div class="row">
                <div class="col-3">
                    <?php foreach ($gambarList as $key => $gambar): ?>
                        <div class="mb-3">
                            <img src="admin/uploads/<?= htmlspecialchars(trim($gambar)) ?>" class="img-fluid border rounded thumbnail-image <?= ($key === 0) ? 'active' : '' ?>" alt="Gambar produk" data-main-image="admin/uploads/<?= htmlspecialchars(trim($gambar)) ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="col-9">
                    <img src="admin/uploads/<?= htmlspecialchars(trim($gambarUtama)) ?>" class="img-fluid border rounded shadow main-image" id="mainProductImage" alt="Gambar utama">
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <h2><?= htmlspecialchars($produk['nama_produk']) ?></h2>
            <p>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="fas fa-star" style="color:<?= $i <= $avgRating ? '#f5c518' : '#ccc' ?>;"></i>
                <?php endfor; ?>
                <span class="text-dark fw-bold"><?= number_format($avgRating, 1) ?></span>
                (<?= $jumlahRating ?> Reviews)
                <?php if ($produk['stok'] > 0): ?>
                    | <span class="text-success">In Stock (<?= $produk['stok'] ?>)</span>
                <?php else: ?>
                    | <span class="text-danger">Out of Stock</span>
                <?php endif; ?>
            </p>
            <h4 class="text-dark">Rp. <?= number_format($produk['harga'], 0, ',', '.') ?></h4>
            <p><?= nl2br(htmlspecialchars($produk['deskripsi'])) ?></p>
            <hr>
            <p><strong>Warna:</strong> <?= htmlspecialchars($produk['warna']) ?></p>
            <p><strong>Berat:</strong> <?= $produk['berat'] ?> kg</p>

            <div class="d-flex align-items-center mb-3">
                <div class="btn-group me-2" role="group">
                    <button type="button" class="btn btn-outline-secondary" id="decreaseQuantity">-</button>
                    <button type="button" class="btn btn-outline-secondary" id="displayQuantity">1</button>
                    <button type="button" class="btn btn-outline-secondary" id="increaseQuantity">+</button>
                </div>

                <button type="button" class="btn btn-primary me-2 buy-now-btn" data-product-id="<?= $produk['id'] ?>" <?= ($produk['stok'] == 0) ? 'disabled' : '' ?>>Buy Now</button>

                <button type="button" class="btn btn-outline-dark me-2 add-to-cart-btn" data-product-id="<?= $produk['id'] ?>" <?= ($produk['stok'] == 0) ? 'disabled' : '' ?>>
                    <i class="fas fa-shopping-cart"></i> Add to Cart
                </button>

                <button type="button" class="btn btn-outline-danger favorite-btn" data-product-id="<?= $produk['id'] ?>">
                    <i class="fas fa-heart <?= $isFavorited ? 'text-danger' : '' ?>" id="favoriteIcon-<?= $produk['id'] ?>"></i>
                </button>
            </div>

            <div class="card mb-2">
                <div class="card-body">
                    <i class="fas fa-truck"></i> <strong>Free Delivery</strong><br>
                    <small>Enter your postal code for Delivery Availability</small>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <i class="fas fa-undo"></i> <strong>Return Delivery</strong><br>
                    <small>Free 30 Days Delivery Returns. <a href="#">Details</a></small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($relatedResult->num_rows > 0): ?>
    <div class="container mt-5">
        <h4 class="mb-4 text-danger"><i class="fas fa-stream"></i> Related Item</h4>
        <div class="row">
            <?php while($item = $relatedResult->fetch_assoc()):
                // Check if related product is favorited
                $isRelatedFavorited = false;
                if (isset($_SESSION['id'])) {
                    $checkRelatedFavoriteStmt = $conn->prepare("SELECT COUNT(*) FROM favorite WHERE id_user = ? AND id_produk = ?");
                    $checkRelatedFavoriteStmt->bind_param("ii", $UserId, $item['id']);
                    $checkRelatedFavoriteStmt->execute();
                    $checkRelatedFavoriteStmt->bind_result($relatedCount);
                    $checkRelatedFavoriteStmt->fetch();
                    $checkRelatedFavoriteStmt->close();
                    if ($relatedCount > 0) {
                        $isRelatedFavorited = true;
                    }
                }
            ?>
                <div class="col-md-3 mb-4">
                    <div class="card text-center border-0 shadow-sm p-2" style="background-color: #fff;">
                        <div class="position-relative">
                            <img src="admin/uploads/<?= htmlspecialchars(explode(',', $item['gambar'])[0]) ?>" class="img-fluid rounded" alt="<?= htmlspecialchars($item['nama_produk']) ?>">
                            <div class="position-absolute top-0 end-0 p-2">
                                <button type="button" class="btn btn-link p-0 favorite-btn" data-product-id="<?= $item['id'] ?>">
                                    <i class="fas fa-heart <?= $isRelatedFavorited ? 'text-danger' : '' ?>" id="favoriteIcon-<?= $item['id'] ?>"></i>
                                </button>
                                <a href="product_details.php?id=<?= $item['id'] ?>" class="ms-2"><i class="fas fa-eye text-dark"></i></a>
                            </div>
                        </div>
                        <div class="card-body">
                            <h6 class="fw-bold mb-1"><?= htmlspecialchars($item['nama_produk']) ?></h6>
                            <div class="text-danger fw-bold">Rp <?= number_format($item['harga'], 0, ',', '.') ?></div>
                            <div class="mt-1">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star" style="color:<?= $i <= $item['avg_rating'] ? '#f5c518' : '#ccc' ?>;"></i>
                                <?php endfor; ?>
                                <span class="text-dark fw-bold"><?= number_format($item['avg_rating'], 1) ?></span>
                                <small class="text-muted">(<?= $item['total_ulasan'] ?>)</small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
<?php endif; ?>

<div class="modal fade" id="addToCartSuccessModal" tabindex="-1" aria-labelledby="addToCartSuccessModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="addToCartSuccessModalLabel"><i class="fas fa-check-circle"></i> Berhasil!</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <p class="lead" id="successModalMessage">Produk berhasil ditambahkan ke keranjang Anda.</p>
                <img src="img/th.jpeg" alt="Success icon" class="img-fluid my-3" style="max-width: 80px;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Lanjutkan Belanja</button>
                <a href="cart.php" class="btn btn-primary">Lihat Keranjang</a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="favoriteModal" tabindex="-1" aria-labelledby="favoriteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" id="favoriteModalHeader">
                <h5 class="modal-title" id="favoriteModalLabel"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <p class="lead" id="favoriteModalMessage"></p>
                <img src="img/th.jpeg" alt="Icon" class="img-fluid my-3" style="max-width: 80px;" id="favoriteModalIcon">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <a href="favorite.php" class="btn btn-primary">Lihat Favorit</a>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function () {
    let quantity = 1;
    const maxStock = parseInt(<?= $produk['stok'] ?>); // Get stock from PHP
    const displayQuantityButton = $("#displayQuantity");

    // Initial quantity display
    displayQuantityButton.text(quantity);

    $("#decreaseQuantity").click(function () {
        if (quantity > 1) {
            quantity--;
            displayQuantityButton.text(quantity);
        }
    });

    $("#increaseQuantity").click(function () {
        if (quantity < maxStock) { // Check against max stock
            quantity++;
            displayQuantityButton.text(quantity);
        } else {
            alert('Maaf, kuantitas melebihi stok yang tersedia (' + maxStock + ').');
        }
    });

    // Handle thumbnail clicks to change main image
    $('.thumbnail-image').click(function() {
        // Remove active class from all thumbnails
        $('.thumbnail-image').removeClass('active');
        // Add active class to the clicked thumbnail
        $(this).addClass('active');
        // Change the main image source
        $('#mainProductImage').attr('src', $(this).data('main-image'));
    });

    // --- Add to Cart Logic (AJAX) ---
    $(".add-to-cart-btn").click(function (e) {
        e.preventDefault(); // Prevent default form submission

        if (maxStock === 0) {
            alert('Produk ini sedang tidak tersedia (stok kosong).');
            return;
        }

        const productId = $(this).data('product-id');
        const currentQuantity = parseInt($('#displayQuantity').text()); // Get the quantity from the display

        $.ajax({
            url: 'add_to_cart_ajax.php', // PHP file to process adding to cart
            type: 'POST',
            data: {
                product_id: productId,
                quantity: currentQuantity // Send the selected quantity
            },
            dataType: 'json', // Expect JSON response
            success: function (data) {
                if (data.status === 'success') {
                    // Update cart badge in header using the function from header.php
                    if (typeof updateCartBadge === 'function') {
                        updateCartBadge(); // Call the function defined in header.php
                    } else {
                        // Fallback if function not found (e.g., if header.php isn't always included with script)
                        $('.cart-badge').text(data.total_items);
                        if (data.total_items > 0) {
                            $('.cart-badge').show();
                        } else {
                            $('.cart-badge').hide();
                        }
                    }

                    const productName = data.product_name ? data.product_name : '<?= htmlspecialchars($produk['nama_produk']) ?>';
                    $('#successModalMessage').text('Produk "' + productName + '" berhasil ditambahkan ke keranjang Anda.');
                    $('#addToCartSuccessModal').modal('show');
                } else {
                    alert('Gagal menambahkan produk ke keranjang: ' + data.message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error("AJAX Error:", textStatus, errorThrown, jqXHR.responseText);
                alert('Terjadi kesalahan saat menambahkan produk ke keranjang. Silakan coba lagi.');
            }
        });
    });
$(".buy-now-btn").click(function (e) {
        e.preventDefault(); // Prevent default button action

        if (maxStock === 0) {
            alert('Produk ini sedang tidak tersedia (stok kosong).');
            return;
        }

        const productId = $(this).data('product-id');
        const currentQuantity = parseInt($('#displayQuantity').text());

        $.ajax({
            url: 'add_to_cart_ajax.php', // Use the same AJAX endpoint to add to cart
            type: 'POST',
            data: {
                product_id: productId,
                quantity: currentQuantity,
                is_buy_now: 'true' // NEW: Add this parameter
            },
            dataType: 'json',
            success: function (data) {
                if (data.status === 'success') {
                    // Successfully added to cart, now redirect to checkout
                    window.location.href = 'checkout.php';
                } else {
                    alert('Gagal menambahkan produk ke keranjang untuk pembelian langsung: ' + data.message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error("AJAX Error:", textStatus, errorThrown, jqXHR.responseText);
                alert('Terjadi kesalahan saat memproses pembelian langsung. Silakan coba lagi.');
            }
        });
    });

    // --- Favorite Logic (AJAX) ---
    $('.favorite-btn').click(function(e) {
        e.preventDefault();
        const productId = $(this).data('product-id');
        const $favoriteIcon = $('#favoriteIcon-' + productId); // Target the specific icon

        $.ajax({
            url: 'add_to_favorite_ajax.php', // New PHP file to handle favorites
            type: 'POST',
            data: {
                product_id: productId
            },
            dataType: 'json',
            success: function(response) {
                const $modalHeader = $('#favoriteModalHeader');
                const $modalTitle = $('#favoriteModalLabel');
                const $modalMessage = $('#favoriteModalMessage');
                const $modalIcon = $('#favoriteModalIcon');

                if (response.status === 'success') {
                    $modalHeader.removeClass('bg-danger').addClass('bg-success');
                    $modalTitle.html('<i class="fas fa-check-circle"></i> Berhasil!');
                    $modalIcon.attr('src', 'https://via.placeholder.com/100/28a745/FFFFFF?text=✓');

                    if (response.action === 'added') {
                        $modalMessage.text('Produk "' + response.product_name + '" berhasil ditambahkan ke favorit Anda.');
                        $favoriteIcon.addClass('text-danger'); // Fill heart
                    } else if (response.action === 'removed') {
                        $modalMessage.text('Produk "' + response.product_name + '" berhasil dihapus dari favorit Anda.');
                        $favoriteIcon.removeClass('text-danger'); // Unfill heart
                    }
                    // Update favorite badge in header
                    if (typeof updateFavoriteBadge === 'function') {
                        updateFavoriteBadge(); // Call the function defined in header.php
                    } else {
                           // Fallback if function not found
                        $('.favorite-badge').text(response.total_items);
                        if (response.total_items > 0) {
                            $('.favorite-badge').show();
                        } else {
                            $('.favorite-badge').hide();
                        }
                    }
                } else {
                    $modalHeader.removeClass('bg-success').addClass('bg-danger');
                    $modalTitle.html('<i class="fas fa-times-circle"></i> Gagal!');
                    $modalIcon.attr('src', 'https://via.placeholder.com/100/dc3545/FFFFFF?text=X');
                    $modalMessage.text(response.message);
                }
                $('#favoriteModal').modal('show');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX Error:", textStatus, errorThrown, jqXHR.responseText);
                const $modalHeader = $('#favoriteModalHeader');
                const $modalTitle = $('#favoriteModalLabel');
                const $modalMessage = $('#favoriteModalMessage');
                const $modalIcon = $('#favoriteModalIcon');

                $modalHeader.removeClass('bg-success').addClass('bg-danger');
                $modalTitle.html('<i class="fas fa-times-circle"></i> Gagal!');
                $modalIcon.attr('src', 'https://via.placeholder.com/100/dc3545/FFFFFF?text=X');
                $modalMessage.text('Terjadi kesalahan saat memperbarui favorit. Silakan coba lagi.');
                $('#favoriteModal').modal('show');
            }
        });
    });

    // Initial badge updates on page load (these functions should be defined in header.php)
    // If they aren't, this will gracefully fail but the primary AJAX calls will update them.
    if (typeof updateCartBadge === 'function') {
        updateCartBadge();
    }
    if (typeof updateFavoriteBadge === 'function') {
        updateFavoriteBadge();
    }
});
</script>

<?php include 'resource/footer.php'; ?>