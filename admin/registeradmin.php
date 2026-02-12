<?php
require 'koneksi.php';
include 'resource/headeradmin.php';
session_start();

$error = "";
$success = "";
$showModal = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = md5($_POST['password']);
    $role = 'admin';

    // Cek apakah email sudah digunakan
    $cek = mysqli_query($koneksi, "SELECT * FROM pengguna WHERE email='$email'");
    if (mysqli_num_rows($cek) > 0) {
        $error = "Email sudah terdaftar!";
    } else {
        $query = "INSERT INTO pengguna (nama_pengguna, email, password, role) 
                  VALUES ('$nama', '$email', '$password', '$role')";
        if (mysqli_query($koneksi, $query)) {
            $success = "Akun berhasil dibuat!";
            $showModal = true;
        } else {
            $error = "Gagal mendaftar. Silakan coba lagi.";
        }
    }
}
?>

<!-- Tambahkan link Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<style>
    body {
        margin: 0;
        font-family: 'Segoe UI', sans-serif;
        background-color: #ffc0cb;
    }

    .register-container {
        display: flex;
        height: 100vh;
        align-items: center;
        justify-content: center;
        padding-left: 50px;
    }

    .register-image {
        flex: 1;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .register-image img {
        max-width: 680px;
        height: 680px;
        background-color: transparent;
        opacity: 0.5;
        transform: translateX(-200px);
    }

    .register-form {
        flex: 1;
        max-width: 400px;
        background-color: #ffc0cb;
        padding: 40px;
        border-radius: 10px;
        transform: translateX(-100px);
    }

    .register-form h2 {
        margin-bottom: 10px;
        color: #000;
    }

    .register-form input {
        width: 100%;
        padding: 12px;
        margin: 10px 0;
        border: none;
        border-bottom: 2px solid #ccc;
        font-size: 14px;
        background: transparent;
    }

    .register-form input:focus {
        border-color: deeppink;
        outline: none;
    }

    .register-form button {
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

    .register-form button:hover {
        background-color: #c2185b;
    }

    .register-form a {
        color: deeppink;
        text-decoration: none;
    }

    .register-form a:hover {
        text-decoration: underline;
    }
</style>

<div class="register-container">
    <div class="register-image">
        <img src="img/Logologin.png" alt="Register">
    </div>

    <form class="register-form" method="POST">
        <h2>Create an account</h2>
        <p style="margin-bottom: 20px; font-size: 14px;">Enter your details below</p>

        <?php if (!empty($error)): ?>
            <p style="color: red; font-size: 14px;"><?= $error ?></p>
        <?php endif; ?>

        <input type="text" name="nama" placeholder="Name" required>
        <input type="email" name="email" placeholder="Email or Phone Number" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Create Account</button>

        <p style="margin-top:10px; font-size: 14px;">Already have account? <a href="loginadmin.php">Log in</a></p>
    </form>
</div>

<!-- Modal Bootstrap -->
<?php if ($showModal): ?>
<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">Berhasil!</h5>
      </div>
      <div class="modal-body">
        <p><?= $success ?></p>
        <p>Anda akan dialihkan ke halaman login...</p>
      </div>
    </div>
  </div>
</div>

<script>
    var successModal = new bootstrap.Modal(document.getElementById('successModal'));
    successModal.show();

    // Redirect otomatis setelah 3 detik
    setTimeout(function () {
        window.location.href = 'loginadmin.php';
    }, 3000);
</script>
<?php endif; ?>
