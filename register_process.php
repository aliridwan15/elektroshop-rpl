<?php
require_once 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_pengguna = $_POST['nama_pengguna'];
    $email = $_POST['email'];
    $password = md5($_POST['password']); // Enkripsi dengan MD5

    // Default values untuk kolom tambahan
    $no_hp = '';
    $address = '';
    $role = 'pengguna'; // default role
    $jenis_kelamin = 'lakilaki'; // default, bisa diubah nanti

    // Cek apakah email sudah terdaftar
    $cek = mysqli_query($conn, "SELECT * FROM pengguna WHERE email='$email'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Email sudah terdaftar!'); window.location='register.php';</script>";
    } else {
        // Insert ke database
        $query = "INSERT INTO pengguna (nama_pengguna, email, password, no_hp, address, role, jenis_kelamin)
                  VALUES ('$nama_pengguna', '$email', '$password', '$no_hp', '$address', '$role', '$jenis_kelamin')";
        $result = mysqli_query($conn, $query);

        if ($result) {
            echo "<script>alert('Akun berhasil dibuat! Silakan login.'); window.location='login.php';</script>";
        } else {
            echo "<script>alert('Gagal membuat akun.'); window.location='register.php';</script>";
        }
    }
}
?>
