<?php
session_start();
include 'koneksi.php'; // Pastikan file koneksi.php Anda ada dan berfungsi dengan benar
include 'resource/header.php'; // Pastikan header.php ada dan berfungsi dengan benar

// Ambil ID user dari session login
$loggedInUserId = isset($_SESSION['id']) ? (int)$_SESSION['id'] : 0;

$cartItems = [];
$totalHargaKeranjang = 0;

if ($loggedInUserId > 0) {
    $stmt = $conn->prepare("
        SELECT
            c.id AS cart_item_id,
            c.id_produk,
            c.quantity,
            p.nama_produk,
            p.harga,
            p.gambar,
            p.stok
        FROM cart c
        JOIN produk p ON c.id_produk = p.id
        WHERE c.id_user = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->bind_param("i", $loggedInUserId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $productId = $row['id_produk'];
        $quantity = $row['quantity'];
        $productStock = $row['stok'];
        $actualQuantity = min($quantity, $productStock);

        // Perbarui kuantitas di database jika melebihi stok
        if ($quantity > $productStock) {
            $updateStmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $updateStmt->bind_param("ii", $actualQuantity, $row['cart_item_id']);
            $updateStmt->execute();
            $updateStmt->close();
        }

        // Jika stok produk 0, pastikan kuantitas di keranjang juga 0
        if ($productStock == 0) {
            $actualQuantity = 0;
        }

        $subtotal = $row['harga'] * $actualQuantity;
        $totalHargaKeranjang += $subtotal;

        $cartItems[] = [
            'db_cart_id' => $row['cart_item_id'],
            'id' => $productId,
            'nama_produk' => $row['nama_produk'],
            'harga' => $row['harga'],
            'gambar' => explode(',', $row['gambar'])[0], // Ambil gambar pertama jika ada beberapa
            'stok' => $productStock,
            'quantity' => $actualQuantity,
            'subtotal' => $subtotal
        ];
    }

    $stmt->close();

} else {
    echo '
    <div class="container mt-5">
        <div class="alert alert-warning text-center" role="alert">
            Anda harus <a href="login.php" class="alert-link">login terlebih dahulu</a> untuk melihat dan mengelola keranjang belanja.
        </div>
    </div>';
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #ffb3da;
            font-family: Arial, sans-serif;
        }

        .cart-header h2 {
            font-size: 20px;
        }

        .btn-edit {
            background-color: #ff0099;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: bold;
        }

        .cart-item {
            display: flex;
            border-radius: 8px;
            margin-bottom: 20px;
            padding: 15px 0;
            align-items: center;
            gap: 15px;
            background: transparent;
            border-bottom: 1px solid #fff;
        }

        .cart-item img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 6px;
        }

        .item-details {
            flex: 1;
        }

        .item-details h5 {
            margin-bottom: 5px;
            font-weight: bold;
        }

        .item-details p {
            margin: 0;
        }

        .item-details .line {
            border-top: 1px solid #000;
            margin: 10px 0;
        }

        .item-subinfo {
            display: flex;
            gap: 20px;
            font-size: 14px;
        }

        .checkbox-align {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 30px;
        }

        .footer-cart {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .footer-left {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .footer-right {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn-delete, .btn-checkout {
            border: none;
            padding: 10px 18px;
            border-radius: 6px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-delete {
            background-color: #ff4d4d;
            color: #fff;
        }

        .btn-checkout {
            background-color: #ff0099;
            color: #fff;
        }

        .btn-checkout:disabled {
            background-color: #cccccc; /* Abu-abu */
            cursor: not-allowed;
            color: #666666;
        }

        @media (max-width: 768px) {
            .footer-cart {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            .footer-right {
                justify-content: flex-end;
                width: 100%;
            }
        }

        .mb-footer {
            margin-bottom: 100px;
        }

        /* Related Item CSS */
        .card .fa-star {
            font-size: 0.85rem;
        }

        /* Styles for quantity controls */
        .quantity-control {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .quantity-control button {
            background-color: #eee;
            border: 1px solid #ccc;
            padding: 2px 8px;
            border-radius: 4px;
            cursor: pointer;
        }
        .quantity-control input {
            width: 40px;
            text-align: center;
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 2px 0;
        }
        .item-actions {
            margin-left: auto;
            display: none;
        }
        .item-actions .btn {
            padding: 5px 10px;
            font-size: 0.85rem;
        }

        .modal-header.bg-success {
            background-color: #28a745 !important;
        }
        .modal-header.bg-danger {
            background-color: #dc3545 !important;
        }
    </style>
</head>
<body>

<div class="container mb-footer">

    <div class="d-flex justify-content-between align-items-start mt-4 mb-3 px-3 cart-header">
        <h2><a href="index.php" class="text-dark text-decoration-none">Home</a> / Cart</h2>
        <button class="btn-edit">EDIT</button>
    </div>

    <div class="px-3" id="cart-items-container">
        <?php if ($loggedInUserId === 0): ?>
            <div class="alert alert-warning text-center" role="alert">
                Anda belum login. <a href="login.php" class="alert-link">Login</a> untuk menyimpan keranjang Anda!
            </div>
        <?php elseif (empty($cartItems)): ?>
            <div class="alert alert-info text-center" role="alert">
                Keranjang Anda kosong. Yuk, <a href="index.php" class="alert-link">mulai belanja</a>!
            </div>
        <?php else: ?>
            <?php foreach ($cartItems as $item): ?>
                <div class="cart-item" data-db-cart-id="<?= $item['db_cart_id'] ?>" data-product-id="<?= $item['id'] ?>">
                    <div class="checkbox-align">
                        <input type="checkbox"
                            class="form-check-input cart-item-checkbox"
                            data-harga="<?= $item['harga']; ?>"
                            data-qty="<?= $item['quantity']; ?>"
                            onchange="updateCount(); updateTotalHarga();">
                    </div>
                    <img src="admin/uploads/<?= htmlspecialchars(trim($item['gambar'])) ?>" alt="<?= htmlspecialchars($item['nama_produk']) ?>">
                    <div class="item-details">
                        <h5><?= htmlspecialchars($item['nama_produk']) ?></h5>
                        <p>Rp <span class="item-price"><?= number_format($item['harga'], 0, ',', '.') ?></span>
                        <span style="float: right;">x<span class="item-quantity-display"><?= $item['quantity'] ?></span></span></p>
                        <div class="line"></div>
                        <div class="item-subinfo">
                            <div>Stok: <strong><?= $item['stok'] ?></strong></div>
                        </div>
                    </div>
                    <div class="item-actions">
                        <div class="quantity-control">
                            <button class="decrease-quantity"
                                    data-db-cart-id="<?= $item['db_cart_id'] ?>"
                                    data-product-id="<?= $item['id'] ?>">-</button>
                            <input type="text"
                                class="quantity-input"
                                value="<?= $item['quantity'] ?>"
                                data-db-cart-id="<?= $item['db_cart_id'] ?>"
                                data-product-id="<?= $item['id'] ?>"
                                data-max-stock="<?= $item['stok'] ?>"
                                readonly>
                            <button class="increase-quantity"
                                    data-db-cart-id="<?= $item['db_cart_id'] ?>"
                                    data-product-id="<?= $item['id'] ?>"
                                    data-max-stock="<?= $item['stok'] ?>">+</button>
                        </div>
                        <button class="btn btn-danger btn-sm remove-item" data-db-cart-id="<?= $item['db_cart_id'] ?>">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="footer-cart px-3" <?php if (empty($cartItems) || $loggedInUserId === 0) echo 'style="display: none;"'; ?>>
        <div class="footer-left">
            <?php if (!empty($cartItems) && $loggedInUserId > 0): ?>
                <input type="checkbox" class="form-check-input" id="checkAll" onchange="toggleAll(this)">
                <label for="checkAll" class="ms-2"><strong>Checklist All</strong></label>
            <?php endif; ?>
        </div>
        <div class="footer-right ms-auto d-flex align-items-center gap-3">
            <span><strong>Total: Rp <span id="totalHarga">0</span></strong></span>
            <button class="btn-delete" style="display: none;"><i class="fa fa-trash"></i> Delete Items</button>
            <button class="btn-checkout" id="checkoutButton">Checkout (<span id="checkoutCount">0</span>)</button>
        </div>
    </div>

</div>

<div class="container mt-5">
    <h4 class="mb-4 text-danger"><i class="fas fa-stream"></i> Related Item</h4>
    <div class="row">
        <?php
        // Fetch 4 random related products from the database
        $relatedProducts = [];
        $stmtRelated = $conn->prepare("SELECT id, nama_produk, harga, gambar FROM produk ORDER BY RAND() LIMIT 4");
        $stmtRelated->execute();
        $resultRelated = $stmtRelated->get_result();
        while ($rowRelated = $resultRelated->fetch_assoc()) {
            $relatedProducts[] = $rowRelated;
        }
        $stmtRelated->close();

        foreach ($relatedProducts as $relatedItem): ?>
        <div class="col-md-3 mb-4">
            <div class="card text-center border-0 shadow-sm p-2" style="background-color: #ffff;">
                <div class="position-relative">
                    <img src="admin/uploads/<?= htmlspecialchars(explode(',', $relatedItem['gambar'])[0]) ?>" class="img-fluid rounded" alt="<?= htmlspecialchars($relatedItem['nama_produk']) ?>">
                    <div class="position-absolute top-0 end-0 p-2">
                        <a href="favorite.php"><i class="fas fa-heart text-dark"></i></a>
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
                </div>
            </div>
        </div>
        <?php endforeach; ?>
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
                Apakah Anda yakin ingin menghapus <span id="deleteModalItemName" class="fw-bold">item ini</span> dari keranjang?
                <input type="hidden" id="modalDbCartId">
                <input type="hidden" id="modalDeleteActionType">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Hapus</button>
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

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Fungsi format Rupiah
    function formatRupiah(number) {
        return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function updateTotalHarga() {
        let total = 0;
        let count = 0;

        document.querySelectorAll('.cart-item-checkbox:checked').forEach(cb => {
            const itemElement = cb.closest('.cart-item');
            const hargaText = itemElement.querySelector('.item-price').textContent;

            const harga = parseFloat(hargaText.replace(/\./g, '')) || 0;
            const qty = parseInt(itemElement.querySelector('.quantity-input').value) || 0;

            total += harga * qty;
            count++;
        });

        const totalHargaEl = document.getElementById('totalHarga');
        if (totalHargaEl) totalHargaEl.textContent = formatRupiah(total);

        const checkoutCountEl = document.getElementById('checkoutCount');
        if (checkoutCountEl) checkoutCountEl.textContent = count;
    }

    function showNotification(message, isSuccess) {
        const modalTitle = document.getElementById('notificationModalLabel');
        const modalBody = document.getElementById('notificationModalBody');
        const notificationModal = new bootstrap.Modal(document.getElementById('notificationModal'));

        modalTitle.textContent = isSuccess ? 'Berhasil!' : 'Terjadi Kesalahan!';
        modalBody.innerHTML = `<p>${message}</p>`;

        const modalHeader = document.querySelector('#notificationModal .modal-header');
        modalHeader.classList.remove('bg-success', 'bg-danger', 'text-white'); // Bersihkan kelas sebelumnya
        if (isSuccess) {
            modalHeader.classList.add('bg-success', 'text-white');
        } else {
            modalHeader.classList.add('bg-danger', 'text-white');
        }

        notificationModal.show();
    }

    // Function untuk memperbarui badge keranjang di header (diasumsikan ada di header.php)
    function updateCartBadge() {
        $.ajax({
            url: 'get_cart_count.php', // Pastikan file ini ada dan mengembalikan jumlah item di keranjang
            type: 'GET',
            success: function(response) {
                const count = parseInt(response);
                $('.cart-badge').text(count);
                if (count === 0) {
                    $('.cart-badge').hide();
                } else {
                    $('.cart-badge').show();
                }
            },
            error: function() {
                console.error('Gagal memperbarui badge keranjang.');
            }
        });
    }

    // Function untuk memperbarui item keranjang (kuantitas atau hapus) via AJAX
    function updateCartItem(action, dbCartId, productId = null, quantity = null, selectedDbCartIds = []) {
        $.ajax({
            url: 'update_cart_ajax.php', // File PHP untuk operasi update/delete
            type: 'POST',
            data: {
                action: action,
                db_cart_id: dbCartId,
                product_id: productId,
                quantity: quantity,
                db_cart_ids: selectedDbCartIds // Dikirim jika action adalah 'remove_multiple_items'
            },
            dataType: 'json',
            success: function (data) {
                if (data.status === 'success') {
                    if (action === 'remove_item') {
                        $(`.cart-item[data-db-cart-id="${dbCartId}"]`).remove();
                        showNotification('Item berhasil dihapus.', true);
                    } else if (action === 'remove_multiple_items') {
                        showNotification('Item-item terpilih berhasil dihapus.', true);
                        location.reload(); // Reload untuk memastikan semua item yang dihapus hilang dari DOM
                    } else if (action === 'update_quantity') {
                        const itemElement = $(`.cart-item[data-db-cart-id="${dbCartId}"]`);
                        itemElement.find('.quantity-input').val(data.new_quantity);
                        itemElement.find('.item-quantity-display').text(data.new_quantity);
                        // showNotification('Kuantitas berhasil diperbarui.', true); // Bisa terlalu sering jika diaktifkan
                    }

                    updateCartBadge();
                    updateTotalHarga();
                    updateCount(); // Call updateCount to handle button state after item changes

                    // Periksa jika keranjang kosong setelah aksi
                    if (data.total_items_in_cart === 0) {
                        $('#cart-items-container').html('<div class="alert alert-info text-center" role="alert">Keranjang Anda kosong. Yuk, <a href="index.php" class="alert-link">mulai belanja</a>!</div>');
                        $('.footer-cart').hide();
                    } else {
                        $('.footer-cart').show();
                    }

                } else {
                    showNotification('Gagal memperbarui keranjang: ' + data.message, false);
                    // Jika terjadi kesalahan di server, pertimbangkan untuk reload halaman
                    // location.reload();
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error("AJAX Error:", textStatus, errorThrown, jqXHR.responseText);
                showNotification('Terjadi kesalahan saat berkomunikasi dengan server. Silakan coba lagi.', false);
                // location.reload(); // Pertimbangkan untuk reload jika ada masalah koneksi
            }
        });
    }

    // === Logika Kontrol Kuantitas ===
    $(document).on('click', '.decrease-quantity', function () {
        const dbCartId = $(this).data('db-cart-id');
        const productId = $(this).data('product-id');
        const quantityInput = $(this).siblings('.quantity-input');
        let currentQuantity = parseInt(quantityInput.val());

        if (currentQuantity > 1) {
            currentQuantity--;
            updateCartItem('update_quantity', dbCartId, productId, currentQuantity);
        }
    });

    $(document).on('click', '.increase-quantity', function () {
        const dbCartId = $(this).data('db-cart-id');
        const productId = $(this).data('product-id');
        const quantityInput = $(this).siblings('.quantity-input');
        let currentQuantity = parseInt(quantityInput.val());
        const maxStock = parseInt(quantityInput.data('max-stock'));

        if (currentQuantity < maxStock) {
            currentQuantity++;
            updateCartItem('update_quantity', dbCartId, productId, currentQuantity);
        } else {
            showNotification('Maaf, kuantitas melebihi stok yang tersedia (' + maxStock + ').', false);
        }
    });

    $(document).on('change', '.cart-item-checkbox', function () {
        updateTotalHarga();
        updateCount();
    });

    // === Logika Hapus Item (untuk tombol hapus item individual dalam mode edit) ===
    $(document).on('click', '.remove-item', function () {
        const dbCartId = $(this).data('db-cart-id');
        const itemName = $(this).closest('.cart-item').find('h5').text(); // Ambil nama produk

        $('#deleteModalItemName').text(`produk "${itemName}"`);
        $('#modalDbCartId').val(dbCartId);
        $('#modalDeleteActionType').val('remove_item'); // Menandai ini adalah hapus item tunggal

        const deleteConfirmModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
        deleteConfirmModal.show();
    });

    // Event listener untuk tombol konfirmasi hapus di modal
    $(document).on('click', '#confirmDeleteBtn', function() {
        const actionType = $('#modalDeleteActionType').val();
        const deleteConfirmModal = bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal'));
        deleteConfirmModal.hide(); // Sembunyikan modal konfirmasi

        if (actionType === 'remove_item') {
            const dbCartId = $('#modalDbCartId').val();
            updateCartItem('remove_item', dbCartId);
        } else if (actionType === 'remove_multiple_items') {
            const selectedDbCartIds = [];
            $('.cart-item-checkbox:checked').each(function() {
                const itemElement = $(this).closest('.cart-item');
                selectedDbCartIds.push(itemElement.data('db-cart-id'));
            });
            updateCartItem('remove_multiple_items', null, null, null, selectedDbCartIds);
        }
    });

    // === Logika Mode Edit ===
    let isEditMode = false;

    document.querySelector('.btn-edit').addEventListener('click', function () {
        isEditMode = !isEditMode;
        const deleteButton = document.querySelector('.btn-delete');
        const itemActions = document.querySelectorAll('.cart-item .item-actions');

        if (isEditMode) {
            deleteButton.style.display = 'inline-flex';
            itemActions.forEach(el => el.style.display = 'flex');
            this.textContent = 'DONE';
        } else {
            deleteButton.style.display = 'none';
            itemActions.forEach(el => el.style.display = 'none');
            this.textContent = 'EDIT';
        }
    });

    // Status awal saat halaman dimuat
    window.addEventListener('DOMContentLoaded', () => {
        // Sembunyikan footer dan item actions jika keranjang kosong atau belum login
        if (<?php echo empty($cartItems) || $loggedInUserId === 0 ? 'true' : 'false'; ?>) {
            $('.footer-cart').hide();
        } else {
            $('.footer-cart').show();
            // Sembunyikan tombol delete dan kontrol individual secara default
            document.querySelector('.btn-delete').style.display = 'none';
            document.querySelectorAll('.cart-item .item-actions').forEach(el => el.style.display = 'none');
        }
        updateCartBadge();
        updateTotalHarga();
        updateCount();
    });

    // Fungsi checkbox master untuk toggle all
    function toggleAll(source) {
        const checkboxes = document.querySelectorAll('.cart-item-checkbox');
        checkboxes.forEach(cb => cb.checked = source.checked);
        updateTotalHarga();
        updateCount();
    }

    // Fungsi update jumlah item ter-check DAN mengontrol tombol checkout
    function updateCount() {
        const count = document.querySelectorAll('.cart-item-checkbox:checked').length;
        document.getElementById('checkoutCount').textContent = count;

        const checkoutButton = document.getElementById('checkoutButton');

        if (count > 0) {
            checkoutButton.disabled = false;
            checkoutButton.style.backgroundColor = '#ff0099'; // Warna aktif
            checkoutButton.style.color = '#fff';
        } else {
            checkoutButton.disabled = true;
            checkoutButton.style.backgroundColor = '#cccccc'; // Warna abu-abu
            checkoutButton.style.color = '#666666';
        }
    }

    // === Hapus Banyak Item (untuk tombol "Delete Items") ===
    $(document).on('click', '.btn-delete', function() {
        const selectedDbCartIds = [];
        $('.cart-item-checkbox:checked').each(function() {
            const itemElement = $(this).closest('.cart-item');
            selectedDbCartIds.push(itemElement.data('db-cart-id'));
        });

        if (selectedDbCartIds.length === 0) {
            showNotification('Tidak ada item yang dipilih untuk dihapus.', false);
            return;
        }

        $('#deleteModalItemName').text(`${selectedDbCartIds.length} item yang dipilih`);
        $('#modalDeleteActionType').val('remove_multiple_items');

        const deleteConfirmModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
        deleteConfirmModal.show();
    });

    // New JavaScript for Checkout Button
    $(document).on('click', '#checkoutButton', function(e) {
        e.preventDefault(); // Prevent default form submission if any

        const selectedCartItemIds = [];
        $('.cart-item-checkbox:checked').each(function() {
            const itemElement = $(this).closest('.cart-item');
            selectedCartItemIds.push(itemElement.data('db-cart-id'));
        });

        if (selectedCartItemIds.length === 0) {
            showNotification('Pilih setidaknya satu item untuk checkout.', false);
            return;
        }

        // Create a dynamic form to send the selected IDs as POST data
        const form = $('<form>', {
            'action': 'checkout.php',
            'method': 'POST',
            'style': 'display:none;'
        });

        // Add each selected cart item ID as a hidden input
        $.each(selectedCartItemIds, function(index, id) {
            $('<input>').attr({
                'type': 'hidden',
                'name': 'selected_cart_ids[]', // Use array notation for multiple values
                'value': id
            }).appendTo(form);
        });

        // Append the form to the body and submit it
        $('body').append(form);
        form.submit();
    });
</script>

<?php include 'resource/footer.php'; ?>
</body>
</html>