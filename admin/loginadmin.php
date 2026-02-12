<?php
require 'koneksi.php';
include 'resource/headeradmin.php';
session_start();

$error = "";
$showSuccessModal = false;
$showErrorModal = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = md5($_POST['password']);

    $query = "SELECT * FROM pengguna WHERE email='$email' AND password='$password' AND role='admin'";
    $result = mysqli_query($koneksi, $query);

    if (mysqli_num_rows($result) === 1) {
        $admin = mysqli_fetch_assoc($result);
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_nama'] = $admin['nama_pengguna'];
        $showSuccessModal = true;
    } else {
        $error = "Email, password, atau role salah.";
        $showErrorModal = true;
    }
}
?>

<!-- Bootstrap CDN -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<style>
    body {
        margin: 0;
        font-family: 'Segoe UI', sans-serif;
        background-color: #ffc0cb;
    }

    .login-container {
        display: flex;
        height: 100vh;
        align-items: center;
        justify-content: center;
        padding-left: 50px;
    }

    .login-image {
        flex: 1;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .login-image img {
        max-width: 680px;
        height: 680px;
        background-color: transparent;
        opacity: 0.5;
        transform: translateX(-200px);
    }

    .login-form {
        flex: 1;
        max-width: 400px;
        background-color: #ffc0cb;
        padding: 40px;
        border-radius: 10px;
        transform: translateX(-100px);
    }

    .login-form h2 {
        margin-bottom: 10px;
        color: #000;
    }

    .login-form input {
        width: 100%;
        padding: 12px;
        margin: 10px 0;
        border: none;
        border-bottom: 2px solid #ccc;
        font-size: 14px;
        background: transparent;
    }

    .login-form input:focus {
        border-color: deeppink;
        outline: none;
    }

    .login-form button {
        width: 100%;
        padding: 12px;
        margin-top: 15px;
        background-color: deeppink;
        color: white;
        border: none;
        border-radius: 6px;
        font-weight: bold;
        font-size: 14px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .login-form button:hover {
        background-color: #c2185b;
    }

    .login-form a {
        color: deeppink;
        text-decoration: none;
    }

    .login-form a:hover {
        text-decoration: underline;
    }
</style>

<div class="login-container">
    <div class="login-image">
        <img src="img/Logologin.png" alt="Login">
    </div>

    <form class="login-form" method="POST">
        <h2>Login to ADMIN!</h2>
        <p style="margin-bottom: 20px; font-size: 14px;">Enter your credentials to log in</p>
        <input type="email" name="email" placeholder="Email or Phone Number" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Log In</button>
        <p style="margin-top:10px; font-size: 14px;">Don't have an account? <a href="registeradmin.php">Create Account</a></p>
    </form>
</div>

<!-- Modal Sukses -->
<?php if ($showSuccessModal): ?>
<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">Login Berhasil!</h5>
      </div>
      <div class="modal-body">
        <p>Selamat datang, <?= htmlspecialchars($admin['nama_pengguna']) ?>!</p>
        <p>Mengalihkan ke dashboard admin...</p>
      </div>
    </div>
  </div>
</div>
<script>
    var successModal = new bootstrap.Modal(document.getElementById('successModal'));
    successModal.show();
    setTimeout(() => {
        window.location.href = 'dashboardadmin.php';
    }, 3000);
</script>
<?php endif; ?>

<!-- Modal Gagal -->
<?php if ($showErrorModal): ?>
<div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Login Gagal!</h5>
      </div>
      <div class="modal-body">
        <p><?= htmlspecialchars($error) ?></p>
      </div>
    </div>
  </div>
</div>
<script>
    var errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
    errorModal.show();
</script>
<?php endif; ?>
