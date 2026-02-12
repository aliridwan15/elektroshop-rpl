<?php
session_start();
include 'koneksi.php'; // Pastikan jalur ini benar dan $conn tersedia

// Pastikan pengguna sudah login
if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit();
}

$loggedInUserId = (int)$_SESSION['id'];
$products_for_rating = []; // This will hold all products to be rated
$current_id_transaksi = null; // Initialize transaction ID

// --- Ambil ID produk atau ID transaksi dari URL ---
if (isset($_GET['id_produk'])) {
    // Jika id_produk secara eksplisit dilewatkan, hanya tampilkan satu produk ini
    $id_produk = (int)$_GET['id_produk'];
    $stmt = $conn->prepare("SELECT id, nama_produk, harga, gambar, warna, berat FROM produk WHERE id = ?");
    $stmt->bind_param("i", $id_produk);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($singleProduct = $result->fetch_assoc()) {
        $products_for_rating[] = $singleProduct;
    }
    $stmt->close();

} elseif (isset($_GET['id_transaksi'])) {
    // Jika id_transaksi dilewatkan, ambil SEMUA produk dari transaksi tersebut
    $current_id_transaksi = (int)$_GET['id_transaksi']; // Simpan ID transaksi saat ini

    $stmt = $conn->prepare("
        SELECT p.id, p.nama_produk, p.harga, p.gambar, p.warna, p.berat
        FROM transaksi_detail td
        JOIN produk p ON td.id_produk = p.id
        WHERE td.id_transaksi = ?
    ");
    $stmt->bind_param("i", $current_id_transaksi);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $products_for_rating[] = $row;
    }
    $stmt->close();
}

// Jika tidak ada produk yang ditemukan untuk di-rate, arahkan kembali atau tampilkan pesan
if (empty($products_for_rating)) {
    echo "<script>alert('Tidak ada produk yang ditemukan untuk diulas.'); window.location.href='index.php';</script>";
    exit();
}

// --- Proses Form Submission ---
$upload_dir = 'uploads/ratings/'; // Direktori untuk menyimpan gambar/video ulasan
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true); // Buat direktori jika belum ada
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $success_messages = [];
    $error_messages = [];

    // Ambil ID transaksi dari form submission jika ada
    $submitted_id_transaksi = $_POST['submitted_id_transaksi'] ?? null;
    if ($submitted_id_transaksi) {
        $submitted_id_transaksi = (int)$submitted_id_transaksi;
    }

    // Loop melalui setiap produk yang di-POST untuk rating
    foreach ($products_for_rating as $productToRate) {
        $id_produk_current = $productToRate['id'];

        // Hanya proses jika data rating untuk produk ini benar-benar disubmit (tidak disabled)
        if (isset($_POST['bintang'][$id_produk_current])) {
            $bintang = (int)$_POST['bintang'][$id_produk_current];
            $ulasan = $_POST['ulasan_text'][$id_produk_current] ?? '';
            $nilai_design = $_POST['design'][$id_produk_current] ?? '';
            $nilai_flexibility = $_POST['flexibility'][$id_produk_current] ?? '';
            $nilai_usage = $_POST['usage'][$id_produk_current] ?? '';

            $gambar_ulasan = null;
            $video_ulasan = null;

            // Cek apakah produk ini sudah diulas untuk TRANSAKSI INI oleh PENGGUNA INI
            $already_rated = false;
            if ($submitted_id_transaksi) {
                // Untuk rating dalam konteks transaksi
                $stmt_check = $conn->prepare("SELECT COUNT(*) FROM rating WHERE id_user = ? AND id_produk = ? AND id_transaksi = ?");
                $stmt_check->bind_param("iii", $loggedInUserId, $id_produk_current, $submitted_id_transaksi);
                $stmt_check->execute();
                $stmt_check->bind_result($count);
                $stmt_check->fetch();
                $stmt_check->close();
                if ($count > 0) {
                    $already_rated = true;
                    $error_messages[] = "Produk **" . htmlspecialchars($productToRate['nama_produk']) . "** sudah diulas untuk transaksi ini.";
                    continue; // Lewati pemrosesan produk ini jika sudah diulas
                }
            } else {
                // Untuk rating produk tunggal (tanpa id_transaksi)
                $stmt_check = $conn->prepare("SELECT COUNT(*) FROM rating WHERE id_user = ? AND id_produk = ? AND id_transaksi IS NULL");
                $stmt_check->bind_param("ii", $loggedInUserId, $id_produk_current);
                $stmt_check->execute();
                $stmt_check->bind_result($count);
                $stmt_check->fetch();
                $stmt_check->close();
                if ($count > 0) {
                    $already_rated = true;
                    $error_messages[] = "Produk **" . htmlspecialchars($productToRate['nama_produk']) . "** sudah diulas (mode produk tunggal).";
                    continue; // Lewati pemrosesan produk ini jika sudah diulas
                }
            }

            // Handle Image Upload for THIS product
            $photo_input_name = 'photoInput_' . $id_produk_current;
            if (isset($_FILES[$photo_input_name]) && $_FILES[$photo_input_name]['error'] === UPLOAD_ERR_OK) {
                $file_tmp = $_FILES[$photo_input_name]['tmp_name'];
                $file_name = uniqid('img_') . '_' . basename($_FILES[$photo_input_name]['name']);
                $target_file = $upload_dir . $file_name;
                $imageMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $fileMimeType = mime_content_type($file_tmp);

                if (in_array($fileMimeType, $imageMimeTypes) && move_uploaded_file($file_tmp, $target_file)) {
                    $gambar_ulasan = $file_name;
                } else {
                    $error_messages[] = "Gagal mengunggah gambar untuk " . htmlspecialchars($productToRate['nama_produk']) . ". Pastikan format file adalah gambar.";
                }
            }

            // Handle Video Upload for THIS product
            $video_input_name = 'videoInput_' . $id_produk_current;
            if (isset($_FILES[$video_input_name]) && $_FILES[$video_input_name]['error'] === UPLOAD_ERR_OK) {
                $file_tmp = $_FILES[$video_input_name]['tmp_name'];
                $file_name = uniqid('vid_') . '_' . basename($_FILES[$video_input_name]['name']);
                $target_file = $upload_dir . $file_name;
                $videoMimeTypes = ['video/mp4', 'video/webm', 'video/ogg'];
                $fileMimeType = mime_content_type($file_tmp);

                if (in_array($fileMimeType, $videoMimeTypes) && move_uploaded_file($file_tmp, $target_file)) {
                    $video_ulasan = $file_name;
                } else {
                    $error_messages[] = "Gagal mengunggah video untuk " . htmlspecialchars($productToRate['nama_produk']) . ". Pastikan format file adalah video.";
                }
            }

            // Masukkan data ke database, termasuk id_transaksi
            $stmt = $conn->prepare("INSERT INTO rating (bintang, id_user, id_produk, id_transaksi, gambar_ulasan, ulasan, video_ulasan, nilai_design, nilai_flexibility, nilai_usage) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            // Gunakan $submitted_id_transaksi untuk kolom id_transaksi
            $stmt->bind_param("iiiissssss", $bintang, $loggedInUserId, $id_produk_current, $submitted_id_transaksi, $gambar_ulasan, $ulasan, $video_ulasan, $nilai_design, $nilai_flexibility, $nilai_usage);

            if ($stmt->execute()) {
                $success_messages[] = "Rating untuk produk **" . htmlspecialchars($productToRate['nama_produk']) . "** berhasil disimpan.";
            } else {
                $error_messages[] = "Gagal menyimpan rating untuk " . htmlspecialchars($productToRate['nama_produk']) . ": " . $stmt->error;
            }
            $stmt->close();
        }
    }

    if (!empty($success_messages) || !empty($error_messages)) {
        $_SESSION['rating_status'] = !empty($success_messages) ? 'success' : 'error';
        $_SESSION['rating_message'] = implode('<br>', array_merge($success_messages, $error_messages));
    } else {
        // Jika tidak ada produk yang diproses (misalnya, semua sudah diulas)
        $_SESSION['rating_status'] = 'info';
        $_SESSION['rating_message'] = 'Tidak ada rating baru yang berhasil disimpan atau semua produk sudah diulas.';
    }

    // Redirect untuk menghindari resubmission form
    $redirect_url_params = '';
    if (isset($_GET['id_transaksi'])) {
        $redirect_url_params = '?id_transaksi=' . htmlspecialchars($_GET['id_transaksi']);
    } elseif (isset($_GET['id_produk'])) {
        $redirect_url_params = '?id_produk=' . htmlspecialchars($_GET['id_produk']);
    }
    header('Location: ' . $_SERVER['PHP_SELF'] . $redirect_url_params);
    exit();
}

// Cek status untuk setiap produk sebelum merender HTML
// Ini akan menentukan apakah field input harus disabled
$products_status = [];
$all_products_rated = true; // Akan menjadi false jika ada setidaknya 1 produk yang belum diulas

foreach ($products_for_rating as $product) {
    $product_id = $product['id'];
    $is_rated = false;
    $existing_rating = null;

    if ($current_id_transaksi) {
        // Cek rating berdasarkan user, produk, dan transaksi
        $stmt_check_rating = $conn->prepare("SELECT bintang, ulasan, nilai_design, nilai_flexibility, nilai_usage FROM rating WHERE id_user = ? AND id_produk = ? AND id_transaksi = ?");
        $stmt_check_rating->bind_param("iii", $loggedInUserId, $product_id, $current_id_transaksi);
        $stmt_check_rating->execute();
        $result_check_rating = $stmt_check_rating->get_result();
        if ($result_check_rating->num_rows > 0) {
            $is_rated = true;
            $existing_rating = $result_check_rating->fetch_assoc();
        } else {
            $all_products_rated = false; // Ada produk yang belum diulas
        }
        $stmt_check_rating->close();
    } else { // Skenario rating produk tunggal (tidak terkait transaksi)
        // Cek rating berdasarkan user dan produk, dengan id_transaksi NULL
        $stmt_check_rating = $conn->prepare("SELECT bintang, ulasan, nilai_design, nilai_flexibility, nilai_usage FROM rating WHERE id_user = ? AND id_produk = ? AND id_transaksi IS NULL");
        $stmt_check_rating->bind_param("ii", $loggedInUserId, $product_id);
        $stmt_check_rating->execute();
        $result_check_rating = $stmt_check_rating->get_result();
        if ($result_check_rating->num_rows > 0) {
            $is_rated = true;
            $existing_rating = $result_check_rating->fetch_assoc();
        } else {
            $all_products_rated = false; // Ada produk yang belum diulas
        }
        $stmt_check_rating->close();
    }
    $products_status[$product_id] = [
        'is_rated' => $is_rated,
        'existing_rating' => $existing_rating
    ];
}

// Definisikan rating messages untuk digunakan di PHP dan JavaScript
$ratingMessages = [
    1 => "Very Bad",
    2 => "Bad",
    3 => "Good",
    4 => "Very Good",
    5 => "Excellent"
];


// Tampilkan popup jika ada pesan status setelah redirect
if (isset($_SESSION['rating_status'])) {
    $popup_status = $_SESSION['rating_status'];
    $popup_message = $_SESSION['rating_message'];
    unset($_SESSION['rating_status']);
    unset($_SESSION['rating_message']);
    
    // Safely encode the message for JavaScript
    $js_popup_message = json_encode($popup_message);

    echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                const popup = document.getElementById('ratingPopup');
                const popupTitle = popup.querySelector('h2');
                const popupParagraph = popup.querySelector('p');
                const popupCloseButton = popup.querySelector('button');

                if ('{$popup_status}' === 'success') {
                    popupTitle.innerText = 'Rating Berhasil!';
                    popupTitle.style.color = '#00b300';
                    popupParagraph.innerHTML = {$js_popup_message}; 
                    popupCloseButton.onclick = function() {
                        closePopupAndRedirect('finished.php');
                    };
                } else if ('{$popup_status}' === 'error') {
                    popupTitle.innerText = 'Rating Gagal!';
                    popupTitle.style.color = '#ff0000';
                    popupParagraph.innerHTML = {$js_popup_message}; 
                    popupCloseButton.onclick = function() {
                        closePopup(); 
                    };
                } else if ('{$popup_status}' === 'info') { // Tambahkan kondisi info
                    popupTitle.innerText = 'Informasi Rating';
                    popupTitle.style.color = '#17a2b8'; // Contoh warna info
                    popupParagraph.innerHTML = {$js_popup_message}; 
                    popupCloseButton.onclick = function() {
                        closePopup(); 
                    };
                }
                popup.style.display = 'flex';
            });
          </script>";
}
?>

<?php include 'resource/header.php'; ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
/* Existing CSS styles - NO CHANGES HERE */
body {
    background-color: #ffe3f1;
    font-family: Arial, sans-serif;
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

.photo-btn,
.video-btn {
    background-color: #ffb43a;
    color: #fff;
    border: none;
    padding: 10px 18px;
    border-radius: 6px;
    font-weight: 500;
    margin-right: 10px;
}

.photo-btn:hover,
.video-btn:hover {
    background-color: #e09e30;
    cursor: pointer;
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
    background-color: #fef3f3;
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
    /* Adjusted for potentially multiple product forms */
    margin-top: 20px; /* Reduced from 300px as it's per product form now */
    text-align: right; /* Align button to the right */
    width: 100%; /* Take full width of its parent */
}

input[type="text"] {
    width: 70%;
    border: none;
    border-bottom: 1px solid #ccc;
    background: none;
    padding: 4px 0;
    margin-left: 10px;
}
.input-group {
    display: flex;
    align-items: center;
    margin-bottom: 12px;
}

.file-info {
    margin-top: 10px;
    display: inline-block;
    font-size: 14px;
}

/* Star Rating CSS */
.stars {
    direction: rtl; /* Stars from right to left */
    font-size: 28px; /* Ukuran bintang lebih besar */
    display: inline-block;
    vertical-align: middle;
}
.stars input[type="radio"] {
    display: none;
}
.stars label {
    color: #aaa;
    float: right;
    cursor: pointer;
    transition: transform 0.2s ease-in-out, color 0.2s ease-in-out; /* Efek transisi */
}
.stars label:hover,
.stars label:hover ~ label {
    color: gold;
    transform: scale(1.2); /* Efek zoom */
}
.stars input[type="radio"]:checked ~ label {
    color: gold;
    transform: scale(1.1); /* Sedikit zoom saat terpilih */
}
/* Ensure the checked star stays scaled when others are hovered */
.stars input[type="radio"]:checked + label:hover {
    transform: scale(1.2); /* Keep zoom effect on hover for the currently checked star */
}

/* Styling for disabled elements */
.product-rated-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(255, 255, 255, 0.7); /* Semi-transparent white */
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5em;
    font-weight: bold;
    color: #e91e63; /* Pink color */
    border-radius: 10px;
    pointer-events: none; /* Allow clicks to pass through to underlying elements if needed, but not on overlay itself */
    z-index: 10; /* Ensure it's above other elements */
}

.product-card.rated {
    position: relative; /* Needed for positioning the overlay */
}

.product-card.rated input,
.product-card.rated textarea,
.product-card.rated button {
    pointer-events: none; /* Disable interaction */
    opacity: 0.6; /* Dim the elements */
}

/* Popup styling */
#ratingPopup, #videoErrorPopup {
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background-color: rgba(0,0,0,0.5);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}

#ratingPopup .popup-content, #videoErrorPopup .popup-content {
    background-color: #ffc4e1;
    padding: 30px 40px;
    border-radius: 20px;
    max-width: 500px;
    width: 90%;
    text-align: center;
}

#ratingPopup h2 {
    color: #00b300;
    font-weight: bold;
}
#videoErrorPopup h2 {
    color: #ff0000; /* Red color for error */
    font-weight: bold;
}

#ratingPopup p, #videoErrorPopup p {
    font-weight: 500;
}

#ratingPopup button, #videoErrorPopup button {
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
    <h3 class="text-center mb-4">Berikan Ulasan untuk Produk Anda</h3>

    <form action="rating.php<?= isset($_GET['id_transaksi']) ? '?id_transaksi=' . htmlspecialchars($_GET['id_transaksi']) : (isset($_GET['id_produk']) ? '?id_produk=' . htmlspecialchars($_GET['id_produk']) : '') ?>" method="POST" enctype="multipart/form-data">
        <?php if ($current_id_transaksi): ?>
            <input type="hidden" name="submitted_id_transaksi" value="<?= htmlspecialchars($current_id_transaksi) ?>">
        <?php endif; ?>

        <?php foreach ($products_for_rating as $product): ?>
            <?php
                $product_id = $product['id'];
                $status = $products_status[$product_id];
                $is_rated_for_display = $status['is_rated'];
                $existing_rating_data = $status['existing_rating'];
            ?>
            <div class="product-card <?= $is_rated_for_display ? 'rated' : '' ?>" style="max-width: 750px; margin: 0 auto 25px auto; padding: 20px; border-radius: 10px; display: flex; border: 1px solid #ddd; background-color: #fff; position: relative;">
                <?php if ($is_rated_for_display): ?>
                    <div class="product-rated-overlay">Produk Sudah Diulas</div>
                <?php endif; ?>
                <div style="flex-shrink: 0;">
                    <img src="admin/uploads/<?= htmlspecialchars($product['gambar'] ?? 'default.jpg') ?>" style="width: 100px; height: 100px; object-fit: cover; border-radius: 0px;" alt="<?= htmlspecialchars($product['nama_produk'] ?? 'Product Image') ?>">
                </div>
                <div style="flex-grow: 1; margin-left: 20px;">
                    <h5 class="mb-1"><?= htmlspecialchars($product['nama_produk'] ?? 'Product Name') ?></h5>
                    <p class="mb-1">Rp<?= number_format($product['harga'] ?? 0, 0, ',', '.') ?></p>
                    <p class="mb-1">Colours: <strong><?= htmlspecialchars($product['warna'] ?? 'N/A') ?></strong></p>
                    <p class="mb-0">Size: <span class="border rounded px-2 py-1"><?= htmlspecialchars($product['berat'] ?? 'N/A') ?></span></p>

                    <hr class="my-3">

                    <input type="hidden" name="id_produk_hidden[<?= $product['id'] ?>]" value="<?= htmlspecialchars($product['id']) ?>">

                    <div class="mb-3 d-flex align-items-center" style="display: flex;">
                        <div class="d-flex align-items-center" style="gap: 10px; flex: 1;">
                            <strong>Quality Product:</strong>
                            <div class="stars">
                                <input type="radio" id="star5_<?= $product['id'] ?>" name="bintang[<?= $product['id'] ?>]" value="5" <?= ($is_rated_for_display && $existing_rating_data['bintang'] == 5) ? 'checked' : (!$is_rated_for_display ? 'checked' : '') ?> <?= $is_rated_for_display ? 'disabled' : '' ?>><label for="star5_<?= $product['id'] ?>" title="Excellent">★</label>
                                <input type="radio" id="star4_<?= $product['id'] ?>" name="bintang[<?= $product['id'] ?>]" value="4" <?= ($is_rated_for_display && $existing_rating_data['bintang'] == 4) ? 'checked' : '' ?> <?= $is_rated_for_display ? 'disabled' : '' ?>><label for="star4_<?= $product['id'] ?>" title="Very Good">★</label>
                                <input type="radio" id="star3_<?= $product['id'] ?>" name="bintang[<?= $product['id'] ?>]" value="3" <?= ($is_rated_for_display && $existing_rating_data['bintang'] == 3) ? 'checked' : '' ?> <?= $is_rated_for_display ? 'disabled' : '' ?>><label for="star3_<?= $product['id'] ?>" title="Good">★</label>
                                <input type="radio" id="star2_<?= $product['id'] ?>" name="bintang[<?= $product['id'] ?>]" value="2" <?= ($is_rated_for_display && $existing_rating_data['bintang'] == 2) ? 'checked' : '' ?> <?= $is_rated_for_display ? 'disabled' : '' ?>><label for="star2_<?= $product['id'] ?>" title="Bad">★</label>
                                <input type="radio" id="star1_<?= $product['id'] ?>" name="bintang[<?= $product['id'] ?>]" value="1" <?= ($is_rated_for_display && $existing_rating_data['bintang'] == 1) ? 'checked' : '' ?> <?= $is_rated_for_display ? 'disabled' : '' ?>><label for="star1_<?= $product['id'] ?>" title="Very Bad">★</label>
                            </div>
                        </div>
                        <div style="white-space: nowrap;">
                            <strong>Status:</strong> <span id="ratingStatus_<?= $product['id'] ?>"><?= $is_rated_for_display ? htmlspecialchars($ratingMessages[$existing_rating_data['bintang']] ?? '') : 'Excellent' ?></span>
                        </div>
                    </div>

                    <p class="mt-3">Add 1 photo or 1 video for this product</p>

                    <div class="mb-3">
                        <button type="button" class="photo-btn" onclick="triggerFile('photo', <?= $product['id'] ?>)" <?= $is_rated_for_display ? 'disabled' : '' ?>><i class="fa fa-camera me-2"></i> Add Photo</button>
                        <button type="button" class="video-btn" onclick="triggerFile('video', <?= $product['id'] ?>)" <?= $is_rated_for_display ? 'disabled' : '' ?>><i class="fa fa-video me-2"></i> Add Video</button>

                        <input type="file" id="photoInput_<?= $product['id'] ?>" name="photoInput_<?= $product['id'] ?>" accept="image/*" style="display: none;" <?= $is_rated_for_display ? 'disabled' : '' ?>>
                        <input type="file" id="videoInput_<?= $product['id'] ?>" name="videoInput_<?= $product['id'] ?>" accept="video/*" style="display: none;" <?= $is_rated_for_display ? 'disabled' : '' ?>>
                        
                        <div id="fileInfoContainer_<?= $product['id'] ?>" class="file-info"></div>
                    </div>

                    <div class="textbox-wrapper">
                        <div class="input-group">
                            <label><strong>Design :</strong></label>
                            <input type="text" name="design[<?= $product['id'] ?>]" placeholder="Enter product design" value="<?= $is_rated_for_display ? htmlspecialchars($existing_rating_data['nilai_design'] ?? '') : '' ?>" <?= $is_rated_for_display ? 'disabled' : '' ?>>
                        </div>
                        <div class="input-group">
                            <label><strong>Flexibility :</strong></label>
                            <input type="text" name="flexibility[<?= $product['id'] ?>]" placeholder="Enter flexibility info" value="<?= $is_rated_for_display ? htmlspecialchars($existing_rating_data['nilai_flexibility'] ?? '') : '' ?>" <?= $is_rated_for_display ? 'disabled' : '' ?>>
                        </div>
                        <div class="input-group">
                            <label><strong>Usage :</strong></label>
                            <input type="text" name="usage[<?= $product['id'] ?>]" placeholder="Enter usage" value="<?= $is_rated_for_display ? htmlspecialchars($existing_rating_data['nilai_usage'] ?? '') : '' ?>" <?= $is_rated_for_display ? 'disabled' : '' ?>>
                        </div>

                        <hr>
                        <textarea class="textbox-inner" name="ulasan_text[<?= $product['id'] ?>]" placeholder="Share your rating and help other Users make better choices!" <?= $is_rated_for_display ? 'disabled' : '' ?>><?= $is_rated_for_display ? htmlspecialchars($existing_rating_data['ulasan'] ?? '') : '' ?></textarea>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="confirm-btn-wrapper" style="max-width: 750px; margin: 0 auto;">
            <button type="submit" class="btn-yellow" <?= $all_products_rated ? 'disabled' : '' ?>>Confirm All Ratings</button>
        </div>
    </form>
</div>

<div id="ratingPopup">
    <div class="popup-content">
        <h2></h2> <p></p> <button onclick="closePopup()">OKE</button>
    </div>
</div>

<div id="videoErrorPopup">
    <div class="popup-content">
        <h2>Upload Video Gagal</h2>
        <p>Durasi video melebihi batas waktu 1 menit.</p> <button onclick="closeVideoErrorPopup()">OKE</button>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ratingPopup = document.getElementById('ratingPopup');
        const videoErrorPopup = document.getElementById('videoErrorPopup');

        const ratingMessages = {
            1: "Very Bad",
            2: "Bad",
            3: "Good",
            4: "Very Good",
            5: "Excellent"
        };

        // Function to update rating status text for a specific product
        function updateRatingStatus(productId) {
            const bintangRadios = document.querySelectorAll(`input[name="bintang[${productId}]"]`);
            const ratingStatusElement = document.getElementById(`ratingStatus_${productId}`);
            let selectedRating = 0;
            for (const radio of bintangRadios) {
                if (radio.checked) {
                    selectedRating = parseInt(radio.value);
                    break;
                }
            }
            if (ratingStatusElement) { // Check if element exists before updating
                ratingStatusElement.innerText = ratingMessages[selectedRating] || '';
            }
        }

        // --- Loop through each product to apply event listeners ---
        <?php foreach ($products_for_rating as $product): ?>
            (function(productId, isRated) { // IIFE starts here, passing product ID and its rated status
                const currentBintangRadios = document.querySelectorAll(`input[name="bintang[${productId}]"]`);
                currentBintangRadios.forEach(radio => {
                    if (!isRated) { // Hanya tambahkan listener jika belum diulas
                        radio.addEventListener('change', () => updateRatingStatus(productId));
                    }
                });
                // Update status awal untuk setiap produk
                if (!isRated) {
                    updateRatingStatus(productId);
                }

                const photoInput = document.getElementById(`photoInput_${productId}`);
                const videoInput = document.getElementById(`videoInput_${productId}`);
                const fileInfoContainer = document.getElementById(`fileInfoContainer_${productId}`);

                if (photoInput && !isRated) {
                    photoInput.addEventListener('change', function () {
                        if (this.files[0]) {
                            displayFileInfo('photo', this.files[0], productId);
                        }
                    });
                }

                if (videoInput && !isRated) {
                    videoInput.addEventListener('change', function () {
                        if (this.files[0]) {
                            const videoFile = this.files[0];
                            const video = document.createElement('video');
                            video.preload = 'metadata';

                            video.onloadedmetadata = function() {
                                window.URL.revokeObjectURL(video.src);
                                // Batas durasi video menjadi 60 detik (1 menit)
                                if (video.duration > 60) {
                                    videoErrorPopup.style.display = 'flex';
                                    videoInput.value = ''; // Hapus input
                                    if (fileInfoContainer) fileInfoContainer.innerHTML = ''; // Hapus preview
                                } else {
                                    displayFileInfo('video', videoFile, productId);
                                }
                            };

                            video.onerror = function() {
                                window.URL.revokeObjectURL(video.src);
                                alert('Gagal memuat metadata video. Pastikan file video valid.');
                                videoInput.value = '';
                                if (fileInfoContainer) fileInfoContainer.innerHTML = '';
                            };

                            video.src = URL.createObjectURL(videoFile);
                        }
                    });
                }
            })(<?= $product['id'] ?>, <?= json_encode($products_status[$product['id']]['is_rated']) ?>); // IIFE berakhir
        <?php endforeach; ?>

        // Function to close rating success/error popup and handle redirection
        window.closePopup = function() {
            ratingPopup.style.display = 'none';
        }
        window.closePopupAndRedirect = function(url) {
            ratingPopup.style.display = 'none';
            window.location.href = url;
        }

        // Function to close video error popup
        window.closeVideoErrorPopup = function() {
            videoErrorPopup.style.display = 'none';
        }

        // Attach event listener to the "OKE" button in the video error popup
        document.querySelector('#videoErrorPopup button').addEventListener('click', closeVideoErrorPopup);

        window.triggerFile = function(type, productId) {
            const photoInput = document.getElementById(`photoInput_${productId}`);
            const videoInput = document.getElementById(`videoInput_${productId}`);
            const fileInfoContainer = document.getElementById(`fileInfoContainer_${productId}`);

            // Hapus seleksi dan preview sebelumnya untuk produk ini
            fileInfoContainer.innerHTML = '';

            if (type === 'photo') {
                if (videoInput) videoInput.value = ''; // Hapus input video untuk produk ini
                if (photoInput) {
                    photoInput.value = ''; // Penting: Hapus nilai input file
                    photoInput.click();
                }
            } else if (type === 'video') {
                if (photoInput) photoInput.value = ''; // Hapus input foto untuk produk ini
                if (videoInput) {
                    videoInput.value = ''; // Penting: Hapus nilai input file
                    videoInput.click();
                }
            }
        }

        function displayFileInfo(fileType, file, productId) {
            const container = document.getElementById(`fileInfoContainer_${productId}`);
            if (!container) return; // Keluar dengan aman jika kontainer tidak ada
            let previewElement;

            if (fileType === 'photo') {
                previewElement = document.createElement('img');
                previewElement.src = URL.createObjectURL(file);
                previewElement.alt = "Selected Photo";
                previewElement.style.maxWidth = '150px';
                previewElement.style.maxHeight = '150px';
                previewElement.style.objectFit = 'cover';
                previewElement.style.borderRadius = '6px';
                previewElement.style.marginTop = '10px';
                previewElement.style.marginRight = '10px';
            } else if (fileType === 'video') {
                previewElement = document.createElement('video');
                previewElement.src = URL.createObjectURL(file);
                previewElement.controls = true;
                previewElement.style.maxWidth = '250px';
                previewElement.style.maxHeight = '150px';
                previewElement.style.borderRadius = '6px';
                previewElement.style.marginTop = '10px';
                previewElement.style.marginRight = '10px';
            }

            const fileNameSpan = document.createElement('span');
            fileNameSpan.innerText = file.name;
            fileNameSpan.style.display = 'block';
            fileNameSpan.style.marginTop = '5px';
            fileNameSpan.style.fontSize = '13px';
            fileNameSpan.style.color = '#555';

            const wrapper = document.createElement('div');
            wrapper.style.display = 'flex';
            wrapper.style.alignItems = 'flex-start';
            wrapper.style.gap = '10px';
            wrapper.style.flexDirection = 'column';

            wrapper.appendChild(previewElement);
            wrapper.appendChild(fileNameSpan);
            
            container.innerHTML = ''; // Hapus konten sebelumnya
            container.appendChild(wrapper);
        }
    });
</script>

<?php include 'resource/footer.php'; ?>

<?php
// Tutup koneksi database HANYA setelah semua pemrosesan PHP dan semua file yang disertakan telah menyelesaikan operasi database mereka.
if (isset($conn) && $conn) {
    $conn->close();
}
?>