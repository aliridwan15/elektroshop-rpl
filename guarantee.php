<?php
session_start();
include 'koneksi.php'; // Make sure this path is correct and $conn is available

// Ensure the user is logged in
if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit();
}

$loggedInUserId = (int)$_SESSION['id'];

// --- Fetch products from pengiriman table with status_pengiriman = 'selesai' ---
$completedDeliveryProducts = [];
$stmt = $conn->prepare("
    SELECT
        p.id AS pengiriman_id,
        t.id AS transaksi_id,
        prod.id AS product_id,
        prod.nama_produk,
        prod.gambar,
        td.jumlah,
        p.tanggal_dikirim,
        p.tanggal_diterima,
        p.ekspedisi,
        p.no_resi,
        prod.masa_garansi AS product_masa_garansi
    FROM
        pengiriman p
    JOIN
        transaksi t ON p.id_transaksi = t.id
    JOIN
        transaksi_detail td ON t.id = td.id_transaksi
    JOIN
        produk prod ON td.id_produk = prod.id
    WHERE
        p.status_pengiriman = 'selesai' AND t.id_user = ?
    ORDER BY
        p.tanggal_diterima DESC, prod.nama_produk ASC
");
$stmt->bind_param("i", $loggedInUserId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $completedDeliveryProducts[] = $row;
}
$stmt->close();

// --- Fetch existing guarantee claims for the logged-in user ---
$existingClaims = [];
$stmt = $conn->prepare("
    SELECT
        id_produk,
        id_transaksi,
        status_garansi,
        tanggal_berakhir
    FROM
        garansi
    WHERE
        id_user = ?
");
$stmt->bind_param("i", $loggedInUserId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    // Use a composite key for easy lookup: 'product_id-transaksi_id'
    $existingClaims[$row['id_produk'] . '-' . $row['id_transaksi']] = $row;
}
$stmt->close();


// --- Fetch "Just For You" (Related Items) ---
$justForYouItems = [];
$stmt = $conn->prepare("SELECT id, nama_produk, harga, gambar FROM produk ORDER BY RAND() LIMIT 4");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $ratingCount = rand(10, 100); // Placeholder for rating count
    $justForYouItems[] = [
        'id' => $row['id'],
        'img' => $row['gambar'],
        'name' => $row['nama_produk'],
        'price' => $row['harga'],
        'rating' => $ratingCount,
        'total_ulasan' => rand(5, 50) // Placeholder for total reviews
    ];
}
$stmt->close();

?>

<?php include 'resource/header.php'; ?>

<style>
body {
    margin: 0;
    padding: 0;
    overflow-x: hidden;
}

.container-fluid {
    background-color: #ffe3f1 !important;
    padding-left: 15px;
    padding-right: 15px;
}

.btn-claim {
    background-color: deeppink;
    color: white;
    padding: 8px 12px;
    border: none;
    border-radius: 6px;
    transition: background-color 0.3s ease;
}

.btn-claim:hover {
    background-color: #c71585;
}

.btn-claimed { /* Generic style for disabled/status buttons */
    background-color: #333; /* Dark gray */
    color: white;
    padding: 8px 12px;
    border: none;
    border-radius: 6px;
    cursor: default;
}

.btn-approved {
    background-color: #28a745; /* Green */
    color: white;
}

.btn-rejected {
    background-color: #dc3545; /* Red */
    color: white;
}

.btn-expired {
    background-color: #6c757d; /* Medium gray */
    color: white;
}

.claimed-text {
    font-size: 14px;
    color: #666;
    font-style: italic;
    margin-top: 5px;
}

.color-box {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 2px solid black;
    display: inline-block;
    margin-left: 10px;
}

.guarantee-card { /* Custom class for the guarantee item card */
    background-color: #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: transform 0.2s, box-shadow 0.2s;
    border: 1px solid #ffb6d9; /* Add border for consistency */
    padding: 20px;
    border-radius: 10px;
    margin: 0 auto 25px auto; /* Added margin for spacing */
}

.guarantee-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.related-item-card { /* Custom class for related item card */
    background-color: white;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    transition: transform 0.2s, box-shadow 0.2s;
}

.related-item-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
</style>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">


<div class="container-fluid" style="padding: 60px 20px;">
    <h4 class="mb-4 text-danger"><i class="fas fa-box-open"></i> Delivered Products (with Guarantee Status)</h4>

    <?php if (empty($completedDeliveryProducts)): ?>
        <div class="alert alert-info text-center" role="alert" style="max-width: 750px; margin: 0 auto 25px auto;">
            Tidak ada produk yang sudah dikirim dengan status selesai.
        </div>
    <?php else: ?>
        <?php foreach ($completedDeliveryProducts as $item): ?>
            <?php
            $tanggal_diterima_timestamp = strtotime($item['tanggal_diterima']);

            // Determine the guarantee period string from produk.masa_garansi
            $masa_garansi_produk_string = "1 year"; // Default fallback
            if (!empty($item['product_masa_garansi'])) {
                // If produk.masa_garansi is a number, assume it's MONTHS; otherwise, treat as string like "1 year"
                if (is_numeric($item['product_masa_garansi'])) {
                    $masa_garansi_produk_string = (int)$item['product_masa_garansi'] . " months";
                } else {
                    $masa_garansi_produk_string = $item['product_masa_garansi'];
                }
            }

            // Calculate the potential guarantee end date based on delivery date and product's guarantee period
            $tanggal_berakhir_garansi_calculated = date('Y-m-d', strtotime("+$masa_garansi_produk_string", $tanggal_diterima_timestamp));

            // Check if product is eligible for a new claim (not expired yet)
            $is_eligible_for_new_claim = (time() <= strtotime($tanggal_berakhir_garansi_calculated));

            // Check for existing claim status for this product and transaction
            $claim_key = $item['product_id'] . '-' . $item['transaksi_id'];
            $existing_claim_status = $existingClaims[$claim_key]['status_garansi'] ?? null;
            $existing_claim_end_date = $existingClaims[$claim_key]['tanggal_berakhir'] ?? null;

            $button_class = 'btn-claim';
            $button_text = 'Claim Guarantee';
            $button_disabled = '';
            $status_message = ''; // For displaying the claim status

            if ($existing_claim_status) {
                // A claim already exists for this product-transaction combination
                switch ($existing_claim_status) {
                    case 'diproses':
                        $button_class = 'btn-claimed';
                        $button_text = 'Claim Diproses';
                        $button_disabled = 'disabled';
                        $status_message = '<span style="color: #007bff; font-weight: bold;">(Status: Diproses)</span>';
                        break;
                    case 'disetujui':
                        $button_class = 'btn-approved';
                        $button_text = 'Claim Disetujui';
                        $button_disabled = 'disabled';
                        $status_message = '<span style="color: #28a745; font-weight: bold;">(Status: Disetujui)</span>';
                        break;
                    case 'ditolak':
                        $button_class = 'btn-rejected';
                        $button_text = 'Claim Ditolak';
                        $button_disabled = 'disabled';
                        $status_message = '<span style="color: #dc3545; font-weight: bold;">(Status: Ditolak)</span>';
                        break;
                    // Add other statuses if needed (e.g., 'kadaluarsa' if you want to explicitly store it)
                }
            } elseif (!$is_eligible_for_new_claim) {
                // No existing claim AND the guarantee period has passed
                $button_class = 'btn-expired';
                $button_text = 'Guarantee Expired';
                $button_disabled = 'disabled';
                $status_message = '<span style="color: #6c757d; font-weight: bold;">(Garansi Kadaluarsa)</span>';
            }
            ?>
            <div class="guarantee-card" style="max-width: 750px; display: flex;">
                <div style="flex-shrink: 0;">
                    <img src="admin/uploads/<?= !empty($item['gambar']) ? htmlspecialchars($item['gambar']) : 'default_image.jpg' ?>" style="width: 140px; height: 140px; border-radius: 8px; object-fit: cover;">
                </div>

                <div style="flex-grow: 1; margin-left: 20px;">
                    <div style="display: flex; justify-content: space-between;">
                        <h5><?= htmlspecialchars($item['nama_produk']) ?></h5>
                        <span style="font-size: 13px; color: #555;"><?= htmlspecialchars($item['jumlah']) ?>x Purchased</span>
                    </div>

                    <hr style="border-top: 1px solid black; margin: 10px 0;">

                    <p style="font-size: 14px; color: #333;">
                        Ekspedisi: <?= htmlspecialchars($item['ekspedisi']) ?>, No. Resi: <?= htmlspecialchars($item['no_resi']) ?><br>
                        Tanggal Diterima: <?= htmlspecialchars(date('d F Y', strtotime($item['tanggal_diterima']))) ?>
                    </p>

                    <p style="font-size: 14px; color: #888;">
                        Masa klaim garansi produk ini berakhir pada: <?= htmlspecialchars(date('d F Y', strtotime($tanggal_berakhir_garansi_calculated))) ?> (berdasarkan masa garansi produk: <?= htmlspecialchars($masa_garansi_produk_string) ?>)
                        <?= $status_message ?>
                    </p>

                    <div style="text-align: right;">
                        <?php if ($button_disabled): ?>
                            <button class="<?= $button_class ?>" <?= $button_disabled ?>><?= $button_text ?></button>
                        <?php else: ?>
                            <form method="post" action="claim_process.php" style="display: inline;">
                                <input type="hidden" name="id_transaksi" value="<?= htmlspecialchars($item['transaksi_id']) ?>">
                                <input type="hidden" name="id_produk" value="<?= htmlspecialchars($item['product_id']) ?>">
                                <input type="hidden" name="tanggal_diterima" value="<?= htmlspecialchars($item['tanggal_diterima']) ?>">
                                <input type="hidden" name="masa_garansi_produk" value="<?= htmlspecialchars($masa_garansi_produk_string) ?>">
                                <button type="submit" class="<?= $button_class ?>"><?= $button_text ?></button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

---
<div class="container-fluid" style="padding: 60px 20px; background-color: #ffe3f1;">
    <div class="row justify-content text-center">
        <div class="col-12 mb-3">
            <h4 class="text-danger"><i class="fas fa-stream"></i> Just For You</h4>
        </div>
    </div>
    <div class="row justify-content-center">
        <?php if (empty($justForYouItems)): ?>
            <div class="alert alert-info text-center" role="alert">
                Tidak ada produk terkait yang tersedia.
            </div>
        <?php else: ?>
            <?php foreach ($justForYouItems as $item): ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="card text-center shadow-sm p-2 related-item-card" style="background-color: #fff; border: 1px solid #ddd; border-radius: 10px;">
                        <div class="position-relative">
                            <img src="admin/uploads/<?= !empty($item['img']) ? htmlspecialchars($item['img']) : 'default_image.jpg' ?>" class="img-fluid rounded" alt="<?= htmlspecialchars($item['name']) ?>" style="height: 180px; object-fit: cover; width: 100%;">
                            <div class="position-absolute top-0 end-0 p-2">
                                <a href="favorite.php?product_id=<?= $item['id'] ?>"><i class="fas fa-heart text-dark"></i></a>
                                <a href="product_details.php?id=<?= $item['id'] ?>" class="ms-2"><i class="fas fa-eye text-dark"></i></a>
                            </div>
                        </div>
                        <div class="card-body rounded">
                            <h6 class="fw-bold mb-1"><?= htmlspecialchars($item['name']) ?></h6>
                            <div class="text-danger fw-bold">Rp <?= number_format($item['price'], 0, ',', '.') ?></div>
                            <div class="mt-1">
                                <?php
                                // Assuming 'rating' is a value from 0-100, converting it to a 0-5 star scale
                                $avg_rating = ($item['rating'] / 100) * 5;
                                for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star" style="color:<?= $i <= round($avg_rating) ? '#f5c518' : '#ccc' ?>;"></i>
                                <?php endfor; ?>
                                <small class="text-muted">(<?= $item['total_ulasan'] ?>)</small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'resource/footer.php'; ?>

<?php
// Close the database connection only after all PHP processing and included files have completed their database operations.
if (isset($conn) && $conn) {
    $conn->close();
}
?>