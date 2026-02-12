<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'resource/header.php';
include 'koneksi.php'; // Pastikan jalur ini benar dan $conn tersedia

// Pastikan pengguna sudah login
if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit();
}

$loggedInUserId = (int)$_SESSION['id'];

// Mengambil data review dari database untuk user yang sedang login
$sql = "SELECT 
            r.bintang,
            r.ulasan,
            r.gambar_ulasan,
            r.video_ulasan,
            r.nilai_design,
            r.nilai_flexibility,
            r.nilai_usage,
            r.created_at,
            p.nama_produk,
            p.gambar AS product_image,
            r.id_produk 
        FROM 
            rating r
        JOIN 
            produk p ON r.id_produk = p.id
        WHERE 
            r.id_user = ?
        ORDER BY 
            r.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $loggedInUserId);
$stmt->execute();
$result = $stmt->get_result();
$reviews = $result->fetch_all(MYSQLI_ASSOC); 
$stmt->close();
?>

<style>
    .media-thumbnails {
        display: flex;
        gap: 10px;
        overflow-x: auto;
        padding-bottom: 5px;
        flex-wrap: nowrap;
        align-items: center;
        width: 500px;
    }
    /* Style untuk kontainer thumbnail gambar */
    .media-thumbnails img {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 6px;
        cursor: pointer;
        flex-shrink: 0;
    }

    /* Style untuk kontainer thumbnail video */
    .video-thumbnail-container {
        position: relative;
        width: 100px; /* Match video/image width */
        height: 100px; /* Match video/image height */
        cursor: pointer;
        border-radius: 6px;
        overflow: hidden; 
        flex-shrink: 0;
    }

    .video-thumbnail-container video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 6px;
        display: block; 
    }

    /* Style untuk ikon play */
    .play-icon {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 30px; /* Ukuran simbol play */
        color: white;
        text-shadow: 0 0 10px rgba(0,0,0,0.7); /* Efek bayangan untuk visibilitas */
        pointer-events: none; /* Memastikan klik menembus ke kontainer di bawahnya */
    }

    .media-modal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0; top: 0;
        width: 100%; height: 100%;
        background: rgba(0,0,0,0.8);
        justify-content: center;
        align-items: center;
    }
    .media-modal-content {
        max-width: 80%; /* Menjadikan konten modal 80% dari lebar layar */
        max-height: 80%; /* Menjadikan konten modal 80% dari tinggi layar */
    }
    .media-modal video, .media-modal img {
        max-width: 100%; 
        max-height: 100%;
        object-fit: contain; /* Ini yang memastikan gambar/video tidak terpotong dan terlihat penuh */
    }
    /* Style untuk tombol tutup modal */
    .close-button {
        position: absolute;
        top: 15px;
        right: 25px;
        color: #fff;
        font-size: 35px;
        font-weight: bold;
        cursor: pointer;
        z-index: 10000; 
    }
    .close-button:hover,
    .close-button:focus {
        color: #bbb;
        text-decoration: none;
    }

    /* Styles for the review card */
    .review-card {
        display: flex;
        background: #fff;
        padding: 30px;
        border-radius: 10px;
        align-items: flex-start;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    .review-card-product-image {
        flex-shrink: 0;
        width: 100px; 
        height: auto;
        border-radius: 8px;
        object-fit: cover; 
    }
    .review-card-content {
        flex-grow: 1;
        margin-left: 20px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .review-card-title {
        margin: 0;
        font-weight: bold;
    }
    .review-card-stars {
        color: #FFD700;
        font-size: 18px;
        margin: 5px 0;
    }
    .review-card-datetime {
        font-size: 13px;
        color: #888;
    }
    .review-card-details-box {
        margin-top: 10px;
        border: 1px solid #ccc;
        border-radius: 8px;
        padding: 15px;
        background: #f9f9f9;
    }
    .review-card-input-group {
        display: flex; 
        align-items: center; 
        margin-bottom: 8px; 
    }
    .review-card-input-group label {
        font-weight: bold;
        margin-right: 10px; 
    }
    .review-card-comment-box {
        white-space: pre-wrap;
    }
    .buy-again-button {
        background-color: deeppink;
        color: white;
        padding: 8px 14px;
        border: none;
        border-radius: 6px;
        text-decoration: none;
        font-size: 14px;
    }
</style>

<div style="max-width: 1200px; margin: 30px auto;"> 
    <?php if (empty($reviews)): ?>
        <p style="text-align: center; color: #555;">Anda belum memiliki ulasan produk.</p>
    <?php else: ?>
        <?php foreach ($reviews as $review):
            $date = date('d-m-Y', strtotime($review['created_at']));
            $time = date('H:i', strtotime($review['created_at']));
            
            // Paths for uploaded media (from rating.php logic)
            $photo_path = !empty($review['gambar_ulasan']) ? 'uploads/ratings/' . htmlspecialchars($review['gambar_ulasan']) : null;
            $video_path = !empty($review['video_ulasan']) ? 'uploads/ratings/' . htmlspecialchars($review['video_ulasan']) : null;
            
            // Path for product image (from admin/uploads)
            $product_image_path = !empty($review['product_image']) ? 'admin/uploads/' . htmlspecialchars($review['product_image']) : 'img/default_product.jpg'; 
        ?>
        <div class="review-card">
            <div style="flex-shrink: 0;">
                <img src="<?= $product_image_path ?>" class="review-card-product-image" alt="<?= htmlspecialchars($review['nama_produk']) ?>">
            </div>
            <div class="review-card-content">
                <div>
                    <h4 class="review-card-title"><?= htmlspecialchars($review['nama_produk']) ?></h4>
                    <div class="review-card-stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <?= $i <= $review['bintang'] ? '★' : '☆' ?>
                        <?php endfor; ?>
                    </div>
                    <div class="review-card-datetime"><?= $date ?> <?= $time ?></div>

                    <div class="review-card-details-box">
                        <?php if ($photo_path || $video_path): ?>
                            <div style="margin-bottom: 15px;">
                                <label><strong>Media Review:</strong></label>
                                <div class="media-thumbnails">
                                    <?php if ($photo_path): ?>
                                        <img src="<?= $photo_path ?>" alt="User photo" onclick="openModal('image', '<?= $photo_path ?>')">
                                    <?php endif; ?>
                                    <?php if ($video_path): ?>
                                        <div class="video-thumbnail-container" onclick="openModal('video', '<?= $video_path ?>')">
                                            <video>
                                                <source src="<?= $video_path ?>" type="video/mp4">
                                            </video>
                                            <div class="play-icon">▶</div> </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="review-card-input-group">
                            <label><strong>Design:</strong></label>
                            <div><?= htmlspecialchars($review['nilai_design'] ?? '-') ?></div>
                        </div>
                        <div class="review-card-input-group">
                            <label><strong>Flexibility:</strong></label>
                            <div><?= htmlspecialchars($review['nilai_flexibility'] ?? '-') ?></div>
                        </div>
                        <div class="review-card-input-group">
                            <label><strong>Usage:</strong></label>
                            <div><?= htmlspecialchars($review['nilai_usage'] ?? '-') ?></div>
                        </div>
                        <hr>
                        <div class="review-card-comment-box"><?= htmlspecialchars($review['ulasan'] ?? '') ?></div>
                    </div>
                </div>

                <div style="text-align: right; margin-top: 10px;">
                    <a href="index.php" class="buy-again-button">
                        Buy Again
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div id="mediaModal" class="media-modal">
    <span class="close-button" onclick="closeModal()">&times;</span> 
    <div id="mediaModalContent" class="media-modal-content"></div>
</div>

<script>
    function openModal(type, src) {
        const modal = document.getElementById("mediaModal");
        const container = document.getElementById("mediaModalContent");
        container.innerHTML = ""; // Bersihkan konten sebelumnya

        if (type === 'image') {
            const img = document.createElement("img");
            img.src = src;
            container.appendChild(img);
        } else if (type === 'video') {
            const video = document.createElement("video");
            video.src = src;
            video.controls = true;
            video.autoplay = true; 
            container.appendChild(video);
        }

        modal.style.display = "flex";
    }

    // Event listener untuk klik pada background modal
    const modalBackground = document.getElementById("mediaModal");
    if (modalBackground) {
        modalBackground.addEventListener('click', function(event) {
            // Pastikan klik langsung pada background modal, bukan pada kontennya
            if (event.target === modalBackground) {
                closeModal();
            }
        });
    }

    function closeModal() {
        const modal = document.getElementById("mediaModal");
        modal.style.display = "none";
        const videoElement = document.querySelector('#mediaModalContent video');
        if (videoElement) {
            videoElement.pause();
            videoElement.currentTime = 0;
        }
        document.getElementById("mediaModalContent").innerHTML = ""; // Bersihkan konten untuk menghentikan pemutaran video di latar belakang
    }
</script>

<?php include 'resource/footer.php'; ?>

<?php
if (isset($conn) && $conn) {
    $conn->close();
}
?>