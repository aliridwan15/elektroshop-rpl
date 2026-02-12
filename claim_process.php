<?php
session_start(); // Start the session at the very beginning
include 'koneksi.php'; // Ensure this path is correct

// Ensure the user is logged in
if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit();
}

$loggedInUserId = (int)$_SESSION['id'];

$product_to_claim = null;
$guarantee_end_date = null;
$masa_garansi_produk_string = null;
$id_transaksi = null; // Initialize id_transaksi

// Handle the POST request from guarantee.php (when selecting a product to claim)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_produk'])) {
    $product_id = (int)$_POST['id_produk'];
    $tanggal_diterima = $_POST['tanggal_diterima'];
    $masa_garansi_produk_string = $_POST['masa_garansi_produk'];
    $id_transaksi = isset($_POST['id_transaksi']) ? (int)$_POST['id_transaksi'] : null;

    // Fetch product details
    $stmt = $conn->prepare("SELECT id, nama_produk, harga, gambar, warna, berat FROM produk WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($product_details = $result->fetch_assoc()) {
        $product_to_claim = $product_details;
    }
    $stmt->close();

    // Calculate guarantee end date for display
    if ($product_to_claim) {
        $tanggal_diterima_timestamp = strtotime($tanggal_diterima);
        $guarantee_end_date = date('d F Y', strtotime("+$masa_garansi_produk_string", $tanggal_diterima_timestamp));
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_claim'])) {
    // This block handles the submission of the claim form itself
    $product_id = (int)$_POST['product_id_claim'];
    $claim_description = $_POST['guarantee_description'] ?? '';
    $masa_garansi_produk_string = $_POST['masa_garansi_produk_hidden'] ?? ''; // Get from hidden field
    $tanggal_diterima = $_POST['tanggal_diterima_hidden'] ?? ''; // Get from hidden field
    $id_transaksi = isset($_POST['id_transaksi_hidden']) ? (int)$_POST['id_transaksi_hidden'] : null; // Get from new hidden field

    // --- Calculate tanggal_berakhir based on masa_garansi_produk_string and tanggal_diterima ---
    $tanggal_berakhir = null;
    if (!empty($tanggal_diterima) && !empty($masa_garansi_produk_string)) {
        $tanggal_diterima_timestamp = strtotime($tanggal_diterima);
        $tanggal_berakhir = date('Y-m-d', strtotime("+$masa_garansi_produk_string", $tanggal_diterima_timestamp));
    }
    // --- End calculation ---

    $bukti_claim = null; // Matches 'bukti_claim' column name
    $upload_dir = 'uploads/claims/'; // Directory for claim images
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Handle image upload for the claim
    if (isset($_FILES['claim_photo']) && $_FILES['claim_photo']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['claim_photo']['tmp_name'];
        $file_name = uniqid('claim_') . '_' . basename($_FILES['claim_photo']['name']);
        $target_file = $upload_dir . $file_name;
        $imageMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileMimeType = mime_content_type($file_tmp);

        if (in_array($fileMimeType, $imageMimeTypes) && move_uploaded_file($file_tmp, $target_file)) {
            $bukti_claim = $file_name; // Assign to 'bukti_claim'
        } else {
            $_SESSION['claim_status'] = 'error';
            $_SESSION['claim_message'] = 'Gagal mengunggah gambar klaim. Pastikan format file adalah gambar.';
            // Do NOT redirect here. Let the page render and the popup show.
            // header('Location: guarantee.php');
            // exit();
        }
    }

    // Insert claim data into the 'garansi' table
    $stmt = $conn->prepare("INSERT INTO garansi (id_user, id_produk, id_transaksi, masa_garansi, tanggal_berakhir, status_garansi, keterangan, bukti_claim, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $status_garansi = 'diproses'; // Set default status as 'diproses' when a claim is submitted
    
    // Bind parameters, handling NULL for id_transaksi if necessary
    if ($id_transaksi === null) {
        // You might need to adjust the type string if your database/driver has strict NULL handling
        // 'i' for integer, 's' for string. For NULL, typically pass null and use 's' or 'i' if the column is nullable.
        $stmt->bind_param("iissssss", $loggedInUserId, $product_id, $null_id_transaksi, $masa_garansi_produk_string, $tanggal_berakhir, $status_garansi, $claim_description, $bukti_claim);
        $null_id_transaksi = NULL; // Explicitly set a variable to NULL for binding
    } else {
        $stmt->bind_param("iiisssss", $loggedInUserId, $product_id, $id_transaksi, $masa_garansi_produk_string, $tanggal_berakhir, $status_garansi, $claim_description, $bukti_claim);
    }


    if ($stmt->execute()) {
        // Fetch product name again for the success message
        $product_name_query = $conn->prepare("SELECT nama_produk FROM produk WHERE id = ?");
        $product_name_query->bind_param("i", $product_id);
        $product_name_query->execute();
        $product_name_result = $product_name_query->get_result();
        $product_name_data = $product_name_result->fetch_assoc();
        $product_name = $product_name_data['nama_produk'] ?? 'produk ini';
        $product_name_query->close();

        $_SESSION['claim_status'] = 'success';
        $_SESSION['claim_message'] = 'Klaim garansi Anda untuk ' . htmlspecialchars($product_name) . ' berhasil diajukan.';
    } else {
        $_SESSION['claim_status'] = 'error';
        $_SESSION['claim_message'] = 'Gagal mengajukan klaim garansi: ' . $stmt->error;
    }
    $stmt->close();

    // *** IMPORTANT CHANGE: DO NOT REDIRECT HERE ***
    // We want the current page to render so the JavaScript can show the popup.
    // header('Location: guarantee.php');
    // exit();
}

// If no product is being claimed from guarantee.php and no claim form was submitted,
// redirect back. This prevents direct access to claim_process.php without context.
// Also, if a claim was just submitted, we want this page to display.
if (!$product_to_claim && !isset($_POST['submit_claim'])) {
    header('Location: guarantee.php');
    exit();
}
?>

<?php include 'resource/header.php'; ?>

<style>
/* ... your existing CSS remains unchanged ... */
body {
    background-color: #ffe3f1;
    font-family: Arial, sans-serif;
}

.color-dot {
    width: 14px;
    height: 14px;
    border-radius: 50%;
    border: 1px solid black;
    display: inline-block;
    margin-left: 5px;
    vertical-align: middle;
}

.btn-yellow {
    background-color: #ffb43a;
    border: none;
    color: #fff;
    padding: 8px 16px;
    border-radius: 6px;
}

.btn-yellow:hover {
    background-color: #e09e30;
}

.photo-btn {
    background-color: #ffb43a;
    color: #fff;
    border: none;
    padding: 10px 18px;
    border-radius: 6px;
    font-weight: 500;
    /* Add transition for smooth hover effect */
    transition: background-color 0.3s ease;
}

/* Hover effect for photo-btn */
.photo-btn:hover {
    background-color: #e09e30; /* Darker shade on hover */
}

.image-preview {
    width: 100px; /* Set a fixed width */
    height: 100px; /* Set a fixed height for square shape */
    border: 1px solid #ddd;
    border-radius: 4px;
    display: flex;
    justify-content: center;
    align-items: center;
    overflow: hidden; /* Hide overflowing parts of the image */
    background-color: #f8f8f8;
    margin-top: 10px;
    margin-bottom: 20px;
}

.image-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover; /* This makes the image cover the area without distortion */
    border-radius: 4px;
}

.textbox-wrapper {
    background-color: #fff;
    border-radius: 8px;
    padding: 10px;
    margin-top: 20px;
    margin-bottom: 20px;
    flex-grow: 1;
}

.textbox-inner {
    background-color: #f3f3f3;
    border-radius: 6px;
    padding: 12px;
    height: 180px;
    resize: none;
    width: 100%;
    border: none;
}

.textbox-section {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    max-width: 750px;
    margin: 0 auto;
    gap: 15px;
}

.confirm-btn-wrapper {
    margin-top: 210px;
    flex-shrink: 0;
}

/* Popup styling (if needed for claim process feedback) */
#claimPopup {
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background-color: rgba(0,0,0,0.5);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}

#claimPopup .popup-content {
    background-color: #ffc4e1;
    padding: 30px 40px;
    border-radius: 20px;
    max-width: 500px;
    width: 90%;
    text-align: center;
}

#claimPopup h2 {
    color: #00b300; /* Green for success */
    font-weight: bold;
}
#claimPopup.error h2 {
    color: #ff0000; /* Red for error */
}

#claimPopup p {
    font-weight: 500;
}

#claimPopup button {
    margin-top: 20px;
    background-color: #ff0080;
    border: none;
    padding: 10px 20px;
    border-radius: 10px;
    color: #fff;
    font-weight: bold;
}
</style>

<div class="container py-5 px-4">
    <?php if ($product_to_claim || (isset($_POST['submit_claim']) && (isset($_SESSION['claim_status'])))): ?>
        <div style="max-width: 750px; margin: 0 auto 25px auto; padding: 20px; border-radius: 10px; display: flex;">
            <div style="flex-shrink: 0;">
                <img src="admin/uploads/<?= htmlspecialchars($product_to_claim['gambar'] ?? 'default.jpg') ?>" style="width: 100px; height: 100px; object-fit: cover; border-radius: 0px;">
            </div>

            <div style="flex-grow: 1; margin-left: 20px;">
                <h5 class="mb-1"><?= htmlspecialchars($product_to_claim['nama_produk'] ?? 'Product Name') ?></h5>
                <p class="mb-1">Rp<?= number_format($product_to_claim['harga'] ?? 0, 0, ',', '.') ?></p>
                <p class="mb-1">Colours: <span class="color-dot" style="background-color: #d2faff;"></span></p>
                <p class="mb-0">Size: <span class="border rounded px-2 py-1"><?= htmlspecialchars($product_to_claim['berat'] ?? 'N/A') ?></span></p>
                <p class="mb-0 mt-2" style="font-size: 14px; color: #888;">
                    Masa garansi berakhir pada: <?= htmlspecialchars($guarantee_end_date ?? 'N/A') ?> (berdasarkan masa garansi produk: <?= htmlspecialchars($masa_garansi_produk_string ?? 'N/A') ?>)
                </p>
                <p class="mb-0 mt-2" style="font-size: 14px; color: #888;">
                    ID Transaksi: <?= htmlspecialchars($id_transaksi ?? 'Tidak Tersedia') ?>
                </p>
            </div>
        </div>

        <div style="max-width: 750px; margin: 0 auto;">
            <hr class="mt-4" style="border-top: 1px solid black;">

            <p class="mt-3">Add 50 characters with 1 photo for product guarantee claims</p>

            <form action="claim_process.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="product_id_claim" value="<?= htmlspecialchars($product_to_claim['id'] ?? '') ?>">
                <input type="hidden" name="masa_garansi_produk_hidden" value="<?= htmlspecialchars($masa_garansi_produk_string ?? '') ?>">
                <input type="hidden" name="tanggal_diterima_hidden" value="<?= htmlspecialchars($tanggal_diterima ?? '') ?>">
                <input type="hidden" name="id_transaksi_hidden" value="<?= htmlspecialchars($id_transaksi ?? '') ?>">

                <button type="button" class="photo-btn mb-3" onclick="document.getElementById('claim_photo_input').click();">
                    <i class="fa fa-camera me-2"></i> Add Photo
                </button>
                <input type="file" id="claim_photo_input" name="claim_photo" accept="image/*" style="display: none;">
                <div id="photo_file_info" class="file-info mb-3"></div>
                <div id="image_preview" class="image-preview mb-3"></div> <div class="textbox-section">
                    <div class="textbox-wrapper">
                        <label for="guarantee-description" class="mb-2">Description</label>
                        <textarea id="guarantee-description" name="guarantee_description" class="textbox-inner"
                                 placeholder="Description for product guarantee" maxlength="50" required></textarea>
                    </div>

                    <div class="confirm-btn-wrapper">
                        <button type="submit" name="submit_claim" class="btn-yellow">Confirm</button>
                    </div>
                </div>
            </form>
        </div>
    <?php else: ?>
        <div class="alert alert-warning text-center" role="alert" style="max-width: 750px; margin: 0 auto;">
            Produk tidak ditemukan atau tidak ada informasi klaim yang valid.
        </div>
        <div class="text-center mt-3">
            <a href="guarantee.php" class="btn-yellow">Back to Guarantee List</a>
        </div>
    <?php endif; ?>
</div>

<div id="claimPopup">
    <div class="popup-content">
        <h2></h2> <p></p> <button onclick="closeClaimPopup()">OKE</button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const claimPhotoInput = document.getElementById('claim_photo_input');
    const photoFileInfo = document.getElementById('photo_file_info');
    const imagePreview = document.getElementById('image_preview');
    const guaranteeDescription = document.getElementById('guarantee-description');
    const maxChars = 50;

    if (claimPhotoInput) {
        claimPhotoInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                photoFileInfo.innerHTML = `Selected file: <strong>${file.name}</strong>`;

                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.innerHTML = `<img src="${e.target.result}" alt="Claim Photo Preview">`;
                };
                reader.readAsDataURL(file);
            } else {
                photoFileInfo.innerHTML = '';
                imagePreview.innerHTML = '';
            }
        });
    }

    if (guaranteeDescription) {
        guaranteeDescription.addEventListener('input', function() {
            const currentLength = this.value.length;
            if (currentLength > maxChars) {
                this.value = this.value.substring(0, maxChars);
            }
        });
    }

    const claimPopup = document.getElementById('claimPopup');
    window.closeClaimPopup = function() {
        claimPopup.style.display = 'none';
        // Redirect AFTER the user closes the popup
        window.location.href = 'guarantee.php';
    }

    <?php
    if (isset($_SESSION['claim_status'])) {
        $popup_status = $_SESSION['claim_status'];
        $popup_message = json_encode($_SESSION['claim_message']); // Use json_encode for safe JS string literal

        echo "
        const popupTitle = claimPopup.querySelector('h2');
        const popupParagraph = claimPopup.querySelector('p');
        if ('{$popup_status}' === 'success') {
            popupTitle.innerText = 'Klaim Berhasil!';
            claimPopup.classList.remove('error');
            popupParagraph.innerHTML = {$popup_message};
        } else {
            popupTitle.innerText = 'Klaim Gagal!';
            claimPopup.classList.add('error');
            popupParagraph.innerHTML = {$popup_message};
        }
        claimPopup.style.display = 'flex';

        // NEW: Clear the session variables immediately after displaying the popup
        // This ensures the popup doesn't reappear on subsequent visits to this page
        fetch('clear_claim_session.php', { method: 'POST' })
            .then(response => response.text())
            .then(data => {
                // console.log('Session cleared:', data); // For debugging
            })
            .catch(error => console.error('Error clearing session:', error));
        ";
    }
    ?>
});
</script>

<?php include 'resource/footer.php'; ?>
<?php
if (isset($conn) && $conn) {
    $conn->close();
}
?>