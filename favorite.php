<?php
include 'koneksi.php'; // Your database connection file

session_start(); // Start the session if not already started
include 'resource/header.php'; // Your header file

// Ensure user is logged in
if (!isset($_SESSION['id'])) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Anda harus login untuk melihat daftar favorit Anda.</div></div>";
    exit;
}

$userId = $_SESSION['id'];

// Fetch favorite items for the current user from the database
$favoriteItems = [];
$stmt = $conn->prepare("
    SELECT
        p.id,
        p.nama_produk,
        p.harga,
        p.gambar,
        p.stok,
        COALESCE(AVG(r.bintang), 0) AS avg_rating,
        COUNT(r.id) AS total_ulasan,
        MAX(f.created_at) AS favorite_created_at -- Get the latest created_at for ordering
    FROM
        favorite f
    JOIN
        produk p ON f.id_produk = p.id
    LEFT JOIN
        rating r ON p.id = r.id_produk
    WHERE
        f.id_user = ?
    GROUP BY
        p.id, p.nama_produk, p.harga, p.gambar, p.stok -- Include all non-aggregated columns from SELECT
    ORDER BY
        favorite_created_at DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $favoriteItems[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favorit Saya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            background-color: #ffe3f1 !important;
        }

        .container-fluid {
            background-color: #ffe3f1 !important;
            padding-left: 15px;
            padding-right: 15px;
        }

        .btn-delete {
            background-color: deeppink;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-delete:hover {
            background-color: #c71585; /* darker shade for hover effect */
        }

        .link-similar {
            background-color: hotpink;
            color: white;
            padding: 8px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .link-similar:hover {
            background-color: #c71585;
        }

        /* Modified cart-link to be a button for AJAX */
        .cart-link-btn {
            margin-left: auto;
            border: 1px solid #999;
            padding: 6px 10px;
            border-radius: 6px;
            background: transparent;
            transition: all 0.3s;
            cursor: pointer; /* Ensure it looks clickable */
        }

        .cart-link-btn:hover {
            border-color: #666;
        }

        .cart-link-btn i {
            color: #333;
            transition: color 0.3s;
        }

        .cart-link-btn:hover i {
            color: #ff69b4;
        }

        .cart-link-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* NEW STYLE FOR BUY NOW BUTTON - REMOVED */


        /* Container spacing */
        .container {
            padding-left: 15px;
            padding-right: 15px;
        }

        /* Favorite item card specific styling */
        .favorite-item-card-wrapper {
            max-width: 750px;
            margin: 0 auto 25px auto;
            padding: 20px;
            border-radius: 10px;
            display: flex;
            align-items: flex-start;
            background-color: #fff;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        .favorite-item-card-wrapper img {
            width: 140px;
            height: 140px; /* Fixed height for favorite item images */
            border-radius: 8px;
            object-fit: cover;
        }

        /* --- Related Item Styling (Copied from cart.php) --- */
        .card {
            border-radius: 8px !important; /* Consistent with cart.php's visual */
            background-color: #fff;
            transition: box-shadow 0.3s ease-in-out;
            padding: 0; /* Remove padding from card itself to apply to inner div if needed */
        }

        .card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .card .position-relative {
            padding: 0; /* Ensure no extra padding around image */
        }

        .card img {
            width: 100%;
            height: 180px; /* Set a fixed height for consistency */
            object-fit: cover;
            border-radius: 8px 8px 0 0; /* Rounded top corners */
        }

        .card .position-absolute {
            top: 0.5rem; /* Adjust positioning as needed */
            right: 0.5rem;
            padding: 0;
            display: flex; /* Use flex for icon spacing */
            gap: 0.5rem; /* Space between icons */
        }

        .card .position-absolute a {
            text-decoration: none;
        }

        .card .position-absolute i {
            font-size: 1.1rem; /* Slightly larger icons */
            color: #333; /* Darker color for icons */
            text-shadow: 0 0 3px rgba(255,255,255,0.7); /* Subtle shadow for visibility */
        }

        .card .position-absolute a:hover i {
            color: #ff0099; /* Pink hover effect for icons */
        }

        .card-body {
            padding: 1rem; /* Padding for the card body */
        }

        .card-body h6 {
            font-size: 1rem;
            font-weight: bold;
            margin-bottom: 0.25rem;
        }

        .card-body .text-danger {
            font-size: 1.1rem;
            font-weight: bold;
        }

        .card .fa-star {
            font-size: 0.85rem; /* Matches cart.php */
        }
        /* --- End Related Item Styling --- */

        /* Style for notification modal header */
        .modal-header.bg-success {
            background-color: #28a745 !important;
        }
        .modal-header.bg-danger {
            background-color: #dc3545 !important;
        }
    </style>
</head>
<body>

<div class="container" style="padding: 60px 20px; background-color: #ffe3f1;">

    <nav style="font-size: 14px; margin-bottom: 20px;">
        <a href="index.php">Home</a> / <span>Favorite</span>
    </nav>

    <h3 class="mb-4 text-dark">Your Favorite Items</h3>
    <div class="favorite-items-container">
        <?php if (empty($favoriteItems)): ?>
            <div class="col-12">
                <div class="alert alert-info text-center" role="alert">
                    Anda belum memiliki produk favorit. Jelajahi produk kami dan tambahkan favorit!
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($favoriteItems as $item): ?>
                <div class="favorite-item-card-wrapper">
                    <div style="position: relative; flex-shrink: 0;">
                        <img src="admin/uploads/<?= htmlspecialchars(explode(',', $item['gambar'])[0]) ?>" alt="<?= htmlspecialchars($item['nama_produk']) ?>">
                    </div>

                    <div style="flex-grow: 1; margin-left: 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: baseline;">
                            <h5 style="margin: 0; font-weight: bold;"><?= htmlspecialchars($item['nama_produk']) ?></h5>
                        </div>
                        <p style="margin: 5px 0; font-size: 16px;">
                            <span style="font-weight: bold;" class="text-danger">Rp <?= number_format($item['harga'], 0, ',', '.') ?></span>
                        </p>
                        <p style="margin: 5px 0; color: #555;">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star" style="color:<?= $i <= $item['avg_rating'] ? '#f5c518' : '#ccc' ?>;"></i>
                            <?php endfor; ?>
                            <small class="text-muted">(<?= number_format($item['total_ulasan']) ?> ulasan)</small>
                        </p>
                        <div style="margin-top: 15px; display: flex; gap: 10px; align-items: center;">
                            <a href="product_details.php?id=<?= $item['id'] ?>" class="link-similar">Lihat Produk</a>
                            <button class="btn-delete remove-favorite-item" data-product-id="<?= $item['id'] ?>">
                                Hapus Favorit
                            </button>
                            <button class="cart-link-btn add-to-cart-from-favorite-btn"
                                    data-product-id="<?= $item['id'] ?>"
                                    data-product-name="<?= htmlspecialchars($item['nama_produk']) ?>"
                                    <?= ($item['stok'] == 0) ? 'disabled' : '' ?>
                                    title="Tambahkan ke Keranjang">
                                <i class="fas fa-shopping-cart"></i>
                            </button>
                            </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="container mt-5">
        <h4 class="mb-4 text-danger"><i class="fas fa-stream"></i> Related Item</h4>
        <div class="row">
            <?php
            // Fetch 4 random related products from the database
            $relatedProducts = [];
            $stmtRelated = $conn->prepare("SELECT id, nama_produk, harga, gambar, stok FROM produk ORDER BY RAND() LIMIT 4");
            $stmtRelated->execute();
            $resultRelated = $stmtRelated->get_result();
            while ($rowRelated = $resultRelated->fetch_assoc()) {
                $relatedProducts[] = $rowRelated;
            }
            $stmtRelated->close();

            foreach ($relatedProducts as $relatedItem): ?>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card text-center border-0 shadow-sm p-2">
                    <div class="position-relative">
                        <img src="admin/uploads/<?= htmlspecialchars(explode(',', $relatedItem['gambar'])[0]) ?>" class="img-fluid rounded" alt="<?= htmlspecialchars($relatedItem['nama_produk']) ?>">
                        <div class="position-absolute">
                            <a href="product_details.php?id=<?= $relatedItem['id'] ?>" class="ms-2"><i class="fas fa-eye text-dark"></i></a>
                        </div>
                    </div>
                    <div class="card-body">
                        <h6 class="fw-bold mb-1"><?= htmlspecialchars($relatedItem['nama_produk']) ?></h6>
                        <div class="text-danger fw-bold">Rp <?= number_format($relatedItem['harga'], 0, ',', '.') ?></div>
                        <div class="mt-1">
                            <?php for ($s = 1; $s <= 5; $s++): ?>
                                <i class="fas fa-star" style="color:<?= $s <= rand(3,5) ? '#f5c518' : '#ccc' ?>;"></i>
                            <?php endfor; ?>
                            <small class="text-muted">(<?= rand(5, 50) ?>)</small>
                        </div>
                        <button class="cart-link-btn add-to-cart-from-favorite-btn mt-2"
                                data-product-id="<?= $relatedItem['id'] ?>"
                                data-product-name="<?= htmlspecialchars($relatedItem['nama_produk']) ?>"
                                <?= ($relatedItem['stok'] == 0) ? 'disabled' : '' ?>
                                title="Tambahkan ke Keranjang">
                            <i class="fas fa-shopping-cart"></i> Tambah ke Keranjang
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmModalLabel">Konfirmasi Penghapusan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Apakah Anda yakin ingin menghapus produk <span id="deleteModalProductName" class="fw-bold"></span> dari favorit?
                <input type="hidden" id="modalProductId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteFavoriteBtn">Hapus</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="notificationModalLabel">Notifikasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="notificationModalBody">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addToCartSuccessModal" tabindex="-1" aria-labelledby="addToCartSuccessModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="addToCartSuccessModalLabel"><i class="fas fa-check-circle"></i> Berhasil!</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <p class="lead" id="addToCartModalMessage">Produk berhasil ditambahkan ke keranjang Anda.</p>
                <img src="img/th.jpeg" alt="Success icon" class="img-fluid my-3" style="max-width: 80px;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Lanjutkan Belanja</button>
                <a href="cart.php" class="btn btn-primary">Lihat Keranjang</a>
            </div>
        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    // Show confirmation modal when 'Hapus Favorit' button is clicked
    $('.remove-favorite-item').on('click', function() {
        const productId = $(this).data('product-id');
        // Find the product name within the same card
        const productName = $(this).closest('.favorite-item-card-wrapper').find('h5').text();

        $('#deleteModalProductName').text(`${productName}`);
        $('#modalProductId').val(productId);

        const deleteConfirmModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
        deleteConfirmModal.show();
    });

    // Handle deletion when 'Hapus' button in modal is clicked
    $('#confirmDeleteFavoriteBtn').on('click', function() {
        const productId = $('#modalProductId').val();

        // Hide the modal
        const deleteConfirmModalInstance = bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal'));
        if (deleteConfirmModalInstance) {
            deleteConfirmModalInstance.hide();
        }

        $.ajax({
            url: 'remove_favorite_ajax.php', // Use the dedicated AJAX endpoint for favorite removal
            type: 'POST',
            data: { product_id: productId },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    // Remove the item card from the DOM
                    $(`.remove-favorite-item[data-product-id="${productId}"]`).closest('.favorite-item-card-wrapper').remove();
                    // Show a success notification
                    showNotification('Produk berhasil dihapus dari favorit.', true);

                    // Update favorite badge in header (assuming updateFavoriteBadge is defined in header.php)
                    if (typeof updateFavoriteBadge === 'function') {
                        updateFavoriteBadge();
                    }

                    // Check if there are no favorite items left
                    if ($('.favorite-item-card-wrapper').length === 0) {
                        $('.favorite-items-container').html('<div class="col-12"><div class="alert alert-info text-center" role="alert">Anda belum memiliki produk favorit. Jelajahi produk kami dan tambahkan favorit!</div></div>');
                    }

                } else {
                    // Show an error notification
                    showNotification('Gagal menghapus produk dari favorit: ' + response.message, false);
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", status, error);
                showNotification('Terjadi kesalahan saat berkomunikasi dengan server. Silakan coba lagi.', false);
            }
        });
    });

    // Handle Add to Cart button click (AJAX)
    $('.add-to-cart-from-favorite-btn').on('click', function() {
        const productId = $(this).data('product-id');
        const productName = $(this).data('product-name');
        const quantity = 1; // Assuming adding 1 item from favorites page

        // Check stock before proceeding (though backend should also validate)
        if ($(this).is(':disabled')) {
            showNotification(`Produk "${productName}" sedang tidak tersedia (stok kosong).`, false);
            return;
        }

        $.ajax({
            url: 'add_to_cart_ajax.php', // This is the recommended separate AJAX file
            type: 'POST',
            data: {
                product_id: productId,
                quantity: quantity
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    // Update cart badge in header (assuming updateCartBadge is defined in header.php)
                    if (typeof updateCartBadge === 'function') {
                        updateCartBadge();
                    }

                    // If a redirect URL is provided (e.g., for "Buy Now" scenario from other pages), use it
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    } else {
                        // Otherwise, show the success modal for regular add to cart
                        $('#addToCartModalMessage').text(`Produk "${response.product_name || productName}" berhasil ditambahkan ke keranjang Anda.`);
                        const addToCartSuccessModal = new bootstrap.Modal(document.getElementById('addToCartSuccessModal'));
                        addToCartSuccessModal.show();
                    }
                } else {
                    showNotification('Gagal menambahkan produk ke keranjang: ' + response.message, false);
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", status, error);
                showNotification('Terjadi kesalahan saat menambahkan produk ke keranjang. Silakan coba lagi.', false);
            }
        });
    });

    // REMOVED: Handle Buy Now button click (AJAX)
    // The entire block for $('.buy-now-btn').on('click', function() { ... }); has been removed.

    // Function to show notifications
    function showNotification(message, isSuccess) {
        const notificationModalElement = document.getElementById('notificationModal');
        if (!notificationModalElement) {
            console.error("Notification modal element not found!");
            alert(message); // Fallback alert if modal HTML is missing
            return;
        }

        const modalTitle = notificationModalElement.querySelector('.modal-title');
        const modalBody = notificationModalElement.querySelector('#notificationModalBody');
        const modalHeader = notificationModalElement.querySelector('.modal-header');

        modalTitle.textContent = isSuccess ? 'Berhasil!' : 'Terjadi Kesalahan!';
        modalBody.innerHTML = `<p>${message}</p>`;

        modalHeader.classList.remove('bg-success', 'bg-danger'); // Remove previous states
        modalHeader.classList.add(isSuccess ? 'bg-success' : 'bg-danger', 'text-white');

        const notificationModal = new bootstrap.Modal(notificationModalElement);
        notificationModal.show();
    }
});
</script>

<?php include 'resource/footer.php'; ?>
</body>
</html>