<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

  <footer>
    <div class="footer-grid" style="display:flex; justify-content:space-between; flex-wrap:wrap; gap:40px; padding:40px 20px; font-size:14px; background:#fff;">
      <div class="footer-col subscribe">
  <img src="img/logoelektroshop.png" alt="Electroshop Logo" style="height: 70px;">
        <p>Get 10% off your first order</p>
        <form>
          <input type="email" placeholder="Enter your email" style="padding:10px; border:1px solid #ccc; border-radius:4px;">
          <button type="submit" style="padding:10px; background:#e91e63; color:#fff; border:none; border-radius:4px; margin-left:5px;">&gt;</button>
        </form>
      </div>
      <div class="footer-col">
        <h4>Support</h4>
        <ul style="list-style:none; padding:0;">
          <li>Telang indah, Kamal, Bangkalan, Jawa timur.</li>
          <li>ElectroshopCS@gmail.com</li>
          <li>+62 823-3727-1130</li>
        </ul>
      </div>
      <div class="footer-col">
  <h4>Account</h4>
  <ul style="list-style:none; padding:0;">
    <li>
      <a href="<?php echo isset($_SESSION['nama']) ? 'myaccount.php' : 'login.php'; ?>">
        My Account
      </a>
    </li>
    <li><a href="login.php">Login / Register</a></li>
    <li><a href="cart.php">Cart</a></li>
    <li><a href="favorite.php">Wishlist</a></li>
    <li><a href="index.php">Shop</a></li>
  </ul>
</div>

      <div class="footer-col">
        <h4>Quick Link</h4>
        <ul style="list-style:none; padding:0;">
          <li><a href="privacy_policy.php">Privacy Policy</a></li>
          <li><a href="term_of_use.php">Terms Of Use</a></li>
          <li><a href="faq.php">FAQ</a></li>
          <li><a href="contact.php">Contact</a></li>
        </ul>
      </div>
    </div>
  </footer>
</body>
</html>
