<?php
require_once 'koneksi.php';  // koneksi db
include 'resource/header.php';        // include header
?>

<style>
  .register-container {display: flex; flex: 1; padding: 40px; gap: 40px; align-items: center; justify-content: center;}
  .register-image img {max-width: 800px; height: 600px; transform: translateX(-80px);}
  .register-form {background: #ffcce5; padding: 30px; border-radius: 12px; flex: 1; max-width: 400px;}
  .register-form h2 {margin-bottom: 20px; font-size: 24px;}
  .register-form input, .register-form select {
    width: 100%; padding: 12px; margin-bottom: 15px;
    border: none; border-bottom: 2px solid #ccc;
    background: transparent; color: #111;
  }
  .register-form button {
    width: 100%; background: #e91e63; color: #fff;
    border: none; padding: 12px; border-radius: 8px;
    cursor: pointer; font-weight: bold;
  }
  .register-form a {color: #111; text-decoration: underline;}
</style>

<div class="register-container">
  <div class="register-image">
    <img src="img/logologin.png" alt="Register" />
  </div>
  <form class="register-form" action="register_process.php" method="POST">
    <h2>Create an account</h2>
    <p style="margin-bottom: 20px; font-size: 14px;">Enter your details below</p>
    <input type="text" name="nama_pengguna" placeholder="Name" required>
    <input type="email" name="email" placeholder="Email or Phone Number" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Create Account</button>
    <p style="margin-top:10px; font-size: 14px;">Already have account? <a href="login.php">Log in</a></p>
  </form>
</div>

<?php include 'resource/footer.php'; ?>