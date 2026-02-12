<?php
include 'resource/header.php';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
  .sidebar-order {
    background-color: #ffe3f1;
    padding: 20px;
    border-radius: 10px;
    display: inline-block;
    min-width: 200px;
  }

  .sidebar-order a {
    display: flex;
    align-items: center;
    color: #000;
    padding: 10px;
    text-decoration: none;
    font-weight: bold;
    border-radius: 6px;
    margin-bottom: 10px;
  }

  .sidebar-order a:hover {
    background-color: #ffb4d4;
    color: #000;
  }

  .sidebar-order i {
    margin-right: 10px;
  }

  @media (max-width: 768px) {
    .sidebar-order {
      width: 100%;
    }
  }

 .related-items-container {
    background-color: transparent;
    padding: 0;
    margin-top: 40px;
  }

.related-items-title {
  font-size: 18px;
  font-weight: bold;
  margin-bottom: 0;
  color: #333;
}


  .related-items-grid {
    display: flex;
    gap: 20px;
    overflow-x: auto;
    padding-bottom: 10px;
    scroll-snap-type: x mandatory;
     max-width: 100%;       /* Pastikan tidak melebar */
      padding: 0 20px; /* Tambah padding supaya kontennya tidak mentok */
 transition: transform 0.5s ease-in-out;
  }

.product-card {
  min-width: 300px; /* atau ukuran sesuai kebutuhan slider */
  flex: 0 0 300px;
    scroll-snap-align: start;
  }

  .product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
  }

  .product-image.related {
    width: 100%;
    height: 150px;
    object-fit: cover;
    border-radius: 8px;
    margin-bottom: 10px;
    background-color: #f5f5f5;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #999;
  }

  .product-actions {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
  }

  .product-actions a {
    color: #666;
    transition: color 0.3s;
  }

  .product-actions a:hover {
    color: #ff4d4d;
  }

  .product-name {
    font-weight: bold;
    margin-bottom: 5px;
    color: #333;
  }

  .product-price {
    color: #ff4d4d;
    font-weight: bold;
    margin-bottom: 5px;
  }

  .product-rating {
    color: #ffc107;
    font-size: 14px;
  }

.product-image-wrapper {
  position: relative;
  width: 100%;
  height: 150px; /* tinggi tetap */
  border-radius: 8px;
  background-color: #f5f5f5;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 10px;
  overflow: hidden;
}

.related-items-wrapper {
  display: flex;
  justify-content: center;
}



.product-actions {
  position: absolute;
  top: 8px;
  right: 8px;
  display: flex;
  gap: 8px;
}

.product-actions a {
  color: #666;
  background: white;
  padding: 4px;
  border-radius: 50%;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  font-size: 14px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
}

.product-actions a:hover {
  color: #ff4d4d;
}


.product-card {
  min-width: 250px;
  max-width: 250px;
  flex: 0 0 auto;
  scroll-snap-align: start;
}



  .rating-count {
    color: #666;
    font-size: 12px;
  }

  @media (min-width: 768px) {
    .order-row {
      display: flex;
    }
  }

  @media (max-width: 768px) {
  .product-card {
    width: 160px; /* agar bisa tampil 2 per view */
  }
}
</style>

<div class="container-fluid" style="padding: 40px 20px; background-color: #ffe3f1;">
  <div class="row">
    <!-- Sidebar -->
    <div class="col-md-3 mb-4 d-flex justify-content-start">
      <div class="sidebar-order">
        <h5 class="mb-4"><i class="fas fa-stream text-danger"></i> My Orders</h5>
        <a href="pending.php"><i class="fas fa-exclamation-circle text-danger"></i> Pending</a>
        <a href="packaged.php"><i class="fas fa-box text-dark"></i> Packaged</a>
        <a href="sent.php"><i class="fas fa-truck text-dark"></i> Sent</a>
        <a href="finished.php"><i class="fas fa-check-circle text-success"></i> Finished</a>
      </div>
    </div>
<div class="related-items-container px-2">
 <div class="d-flex justify-content-center mb-3">
  <h5 class="related-items-title mb-0">Related Item</h5>
</div>

  <!-- Slider wrapper untuk menghindari overflow -->
 <div class="related-items-wrapper">
  <div class="related-items-grid px-2" id="productSlider">

      <?php
        $relatedItems = [
          ['img' => 'img/keyboard.jpeg', 'name' => 'RGB Gaming Keyboard', 'price' => 75000, 'rating' => 25],
          ['img' => 'img/mouse.jpg', 'name' => 'Wireless Gaming Mouse', 'price' => 95000, 'rating' => 40],
          ['img' => 'img/mouse.jpg', 'name' => 'Wireless Gaming Mouse', 'price' => 95000, 'rating' => 40],
          ['img' => 'img/mouse.jpg', 'name' => 'Wireless Gaming Mouse', 'price' => 95000, 'rating' => 40]
        ];

        foreach ($relatedItems as $item):
      ?>
        <div class="product-card">
          <div class="product-image-wrapper">
            <img src="<?= $item['img'] ?>" alt="<?= $item['name'] ?>" class="product-image related">
            <div class="product-actions">
              <a href="favorite.php"><i class="far fa-heart"></i></a>
              <a href="product_details.php"><i class="far fa-eye"></i></a>
            </div>
          </div>
          <div class="product-name"><?= $item['name'] ?></div>
          <div class="product-price">Rp <?= number_format($item['price'], 0, ',', '.') ?></div>
          <div class="product-rating">★★★★★ <span class="rating-count">(<?= $item['rating'] ?>)</span></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
  </div>
</div>

<?php include 'resource/footer.php'; ?>