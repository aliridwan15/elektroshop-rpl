<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'koneksi.php';
include 'resource/header.php';
$query = isset($_GET['q']) ? trim($_GET['q']) : '';
?>

<style>
    .product-card {
        display: flex;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgb(0 0 0 / 0.15);
        padding: 15px 20px;
        margin-bottom: 20px;
        gap: 20px;
        transition: box-shadow 0.3s ease;
    }
    .product-card:hover {
        box-shadow: 0 4px 12px rgb(0 0 0 / 0.25);
    }
    .product-image {
        flex: 0 0 180px;
        height: 180px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #ddd;
        margin-right: 20px;
    }
    .product-info {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .product-info h3 {
        margin-bottom: 10px;
        color: #e91e63;
    }
    .product-info p {
        margin: 4px 0;
        color: #333;
        font-size: 14px;
    }
    .product-price {
        font-weight: 700;
        color: #111;
        font-size: 16px;
    }
    .product-meta {
        font-size: 13px;
        color: #555;
    }

    /* Responsif: jika layar sangat kecil, buat 1 kolom */
    @media (max-width: 480px) {
        .col-6 {
            flex: 0 0 100% !important;
            max-width: 100% !important;
        }
    }
</style>

<h1>Search Results for "<?= htmlspecialchars($query) ?>"</h1>

<?php
if ($query !== '') {
    $searchTerm = "%$query%";

    $sql = "
        SELECT produk.*, kategori.nama_kategori 
        FROM produk 
        LEFT JOIN kategori ON produk.id_kategori = kategori.id
        WHERE 
            produk.nama_produk LIKE ? OR
            produk.deskripsi LIKE ? OR
            produk.warna LIKE ? OR
            produk.jenis_produk LIKE ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0):
        echo '<div class="row">';

        while ($row = $result->fetch_assoc()):
            $imgPath = 'admin/uploads/' . htmlspecialchars($row['gambar']);
            if (!file_exists($imgPath) || empty($row['gambar'])) {
                $imgPath = 'img/no-image.png';
            }
?>
        <div class="col-6">
            <div class="product-card">
                <img src="<?= $imgPath ?>" alt="<?= htmlspecialchars($row['nama_produk']) ?>" class="product-image" />
                <div class="product-info">
                    <h3><?= htmlspecialchars($row['nama_produk']) ?></h3>
                    <p><?= htmlspecialchars($row['deskripsi']) ?></p>

                    <p class="product-meta"><strong>Kategori:</strong> <?= htmlspecialchars($row['nama_kategori'] ?? 'Unknown') ?></p>
                    <p class="product-meta"><strong>Stok:</strong> <?= (int)$row['stok'] ?></p>
                    <p class="product-meta"><strong>Berat:</strong> <?= htmlspecialchars($row['berat']) ?> gram</p>
                    <p class="product-meta"><strong>Warna:</strong> <?= htmlspecialchars($row['warna']) ?></p>
                    <p class="product-meta"><strong>Jenis Produk:</strong> <?= htmlspecialchars($row['jenis_produk']) ?></p>

                    <p class="product-price">Price: Rp <?= number_format($row['harga'], 0, ',', '.') ?></p>

                    <!-- Tombol Detail Produk -->
                    <button 
                        style="background-color:#e91e63; color:#fff; border:none; padding:8px 16px; border-radius:4px; cursor:pointer; margin-top:10px;"
                        onclick="window.location.href='product_details.php?id=<?= urlencode($row['id']) ?>'">
                        Detail Produk
                    </button>
                </div>

            </div>
        </div>
<?php
        endwhile;

        echo '</div>';
    else:
        echo "<p>No products found.</p>";
    endif;

    $stmt->close();
} else {
    echo "<p>Please enter a search term.</p>";
}

include 'resource/footer.php';
?>
