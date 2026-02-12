<?php include 'resource/header.php'; ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Custom CSS -->
<style>
    body {
        background-color: #ffc0de;
        font-family: Arial, sans-serif;
    }

    .about-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        padding: 40px 20px;
    }

    .about-text {
        flex: 1 1 45%;
    }

    .about-text h1 {
        font-weight: bold;
        font-size: 2.5rem;
        margin-bottom: 20px;
    }

    .about-text p {
        font-size: 1rem;
        color: #333;
    }

    .about-image {
        flex: 1 1 45%;
        text-align: center;
    }

    .about-image img {
        max-width: 100%;
        height: auto;
        border-radius: 10px;
    }

    .info-cards {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 20px;
        margin: 40px 20px;
    }

    .info-card {
        background-color: white;
        border-radius: 15px;
        padding: 25px 20px;
        width: 220px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
        cursor: pointer;
        text-align: center;
    }

    .info-card:hover {
        transform: scale(1.05);
    }

    .info-card i {
        font-size: 2.5rem;
        margin-bottom: 15px;
        color: hotpink;
    }

    .info-card .number {
        font-size: 1.6rem;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .info-card .label {
        font-size: 0.9rem;
        color: #333;
    }

    /* Team Section Styling */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .team-section {
        display: flex;
        justify-content: center;
        gap: 30px;
        padding: 50px 20px;
        flex-wrap: wrap;
    }

    .team-member {
        background: linear-gradient(to bottom, #ffc0de, #fff);
        border-radius: 20px;
        width: 260px;
        text-align: center;
        box-shadow: 0 6px 14px rgba(0, 0, 0, 0.15);
        overflow: hidden;
        animation: fadeInUp 0.8s ease forwards;
        opacity: 0;
        transition: transform 0.3s ease;
    }

    .team-member:hover {
        transform: translateY(-6px);
    }

    .team-member img {
        width: 100%;
        height: 180px;
        object-fit: cover;
        border-bottom: 4px solid #e1306c;
    }

    .team-member .name {
        font-weight: bold;
        font-size: 1.2rem;
        margin-top: 15px;
    }

    .team-member .job {
        font-size: 0.9rem;
        color: #555;
        margin: 5px 0 15px;
    }

    .team-member .social {
        display: flex;
        justify-content: center;
        gap: 12px;
        padding-bottom: 15px;
    }

    .team-member .social i {
        color: #e1306c;
        font-size: 1.2rem;
        transition: color 0.3s;
        cursor: pointer;
    }

    .team-member .social i:hover {
        color: #ad1457;
    }

    .info-banner {
        padding: 40px 20px;
        text-align: center;
        margin-top: 40px;
    }

    .info-container {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 40px;
        max-width: 1200px;
        margin: 0 auto;
    }

    .info-item {
        flex: 1 1 200px;
        max-width: 250px;
        text-align: center;
    }

    .info-item i {
        font-size: 32px;
        color: #e91e63;
        margin-bottom: 10px;
    }

    .info-item h4 {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 6px;
    }

    .info-item p {
        font-size: 14px;
        color: #333;
    }
    
</style>

<!-- Main Content -->
<div class="about-container">
    <div class="about-text">
    <h1>Our Story</h1>
    <p>ElektroShop adalah platform e-commerce yang menyediakan berbagai kebutuhan elektronik berkualitas tinggi dengan harga yang kompetitif. Kami berdedikasi untuk memberikan pengalaman berbelanja yang mudah, cepat, dan aman bagi setiap pelanggan di seluruh Indonesia. Dari perangkat rumah tangga hingga komponen elektronik canggih, semua tersedia dalam satu tempat.</p>
    
    <p>Didirikan oleh tim yang berpengalaman di bidang teknologi dan bisnis digital, ElektroShop terus berkembang dengan mengedepankan inovasi dan pelayanan terbaik. Kami percaya bahwa teknologi seharusnya mudah diakses oleh semua orang, dan melalui ElektroShop, kami ingin menjadi jembatan antara produk elektronik terbaik dan kebutuhan pelanggan modern.</p>
</div>

    <div class="about-image">
        <img src="img/logoelektroshop.png" alt="About Image">
    </div>
</div>

<div class="info-cards">
    <div class="info-card">
        <i class="fas fa-store"></i>
        <div class="number">10.5K</div>
        <div class="label">Sallers active our site</div>
    </div>
    <div class="info-card">
        <i class="fas fa-dollar-sign"></i>
        <div class="number">33K</div>
        <div class="label">Monthly Product Sale</div>
    </div>
    <div class="info-card">
        <i class="fas fa-shopping-bag"></i>
        <div class="number">45.5K</div>
        <div class="label">Customer active in our site</div>
    </div>
    <div class="info-card">
        <i class="fas fa-money-bill-wave"></i>
        <div class="number">25K</div>
        <div class="label">Annual gross sale in our site</div>
    </div>
</div>

<!-- TEAM SECTION -->
<div class="team-section" style="display: flex; flex-wrap: wrap; gap: 20px; justify-content: center;">
    <div class="team-member" >
        <img src="img/malik.jpg" alt="Member 1" style="width: 100%; height: auto;">
        <div class="name">ABDUL MALIK ADZ-DZIKRI</div>
        <div class="job" style="background-color: #2196F3; color: white; border-radius: 10px; padding: 5px 10px; margin: 5px;">Frontend</div>
        <div class="job" style="background-color: #F44336; color: white; border-radius: 10px; padding: 5px 10px; margin: 5px;">Backend</div>
        <div class="social" style="margin: 10px;">
            <a href="https://www.instagram.com/malikzert/profilecard/?igsh=a2xhNjdha2VndnNq" target="_blank" rel="noopener">
                <i class="fab fa-instagram" style="font-size: 24px; color: #E1306C;"></i>
            </a>
        </div>
    </div>

    <div class="team-member" >
        <img src="img/ali.jpg" alt="Member 2" style="width: 100%; height: auto;">
        <div class="name">ALI RIDWAN NURHASAN</div>
        <div class="job" style="background-color: #FFEB3B; color: black; border-radius: 10px; padding: 5px 10px; margin: 5px;">Database</div>
        <div class="job" style="background-color: #2196F3; color: white; border-radius: 10px; padding: 5px 10px; margin: 5px;">Frontend</div>
        <div class="social" style="margin: 10px;">
            <a href="https://www.instagram.com/aliridwan.n/profilecard/?igsh=d25pdHY5eHpxNm5u" target="_blank" rel="noopener">
                <i class="fab fa-instagram" style="font-size: 24px; color: #E1306C;"></i>
            </a>
        </div>
    </div>

    <div class="team-member" >
        <img src="img/irna.jpg" alt="Member 3" style="width: 100%; height: auto;">
        <div class="name">IRNA KHALDA LUQYANA</div>
        <div class="job" style="background-color: #4CAF50; color: white; border-radius: 10px; padding: 5px 10px; margin: 5px;">Designer</div>
        <div class="job" style="background-color: #FFEB3B; color: black; border-radius: 10px; padding: 5px 10px; margin: 5px;">Database</div>
        <div class="social" style="margin: 10px;">
            <a href="https://www.instagram.com/irnakhalda/profilecard/?igsh=a2xhNjdha2VndnNq" target="_blank" rel="noopener">
                <i class="fab fa-instagram" style="font-size: 24px; color: #E1306C;"></i>
            </a>
        </div>
    </div>

    <div class="team-member" >
        <img src="img/silvi.jpg" alt="Member 4" style="width: 100%; height: auto;">
        <div class="name">SILVIA RENATA SUWANDI</div>
        <div class="job" style="background-color: #2196F3; color: white; border-radius: 10px; padding: 5px 10px; margin: 5px;">Frontend</div>
        <div class="job" style="background-color: #FFEB3B; color: black; border-radius: 10px; padding: 5px 10px; margin: 5px;">Database</div>
        <div class="social" style="margin: 10px;">
            <a href="https://www.instagram.com/s.rherewn?igsh=MWFqbTgyNHJxNTRkdg==" target="_blank" rel="noopener">
                <i class="fab fa-instagram" style="font-size: 24px; color: #E1306C;"></i>
            </a>
        </div>
    </div>

    <div class="team-member" >
        <img src="img/okta2.jpg" alt="Member 5" style="width: 100%; height: auto;">
        <div class="name">RAFI AFANDY R. O</div>
        <div class="job" style="background-color: #4CAF50; color: white; border-radius: 10px; padding: 5px 10px; margin: 5px;">Designer</div>
        <div class="social" style="margin: 10px;">
            <a href="https://www.instagram.com/" target="_blank" rel="noopener">
                <i class="fab fa-instagram" style="font-size: 24px; color: #E1306C;"></i>
            </a>
        </div>
    </div>
</div>



<section class="info-banner">
    <div class="info-container">
        <div class="info-item">
            <i class="fas fa-truck"></i>
            <h4>Free Shipping</h4>
            <p>Free delivery for orders over Rp500.000</p>
        </div>
        <div class="info-item">
            <i class="fas fa-headset"></i>
            <h4>24/7 Support</h4>
            <p>Friendly support anytime, anywhere</p>
        </div>
        <div class="info-item">
            <i class="fas fa-shield-alt"></i>
            <h4>Secure Payment</h4>
            <p>Your payment is safe with us</p>
        </div>
    </div>
</section>

<?php include 'resource/footer.php'; ?>
