<?php
$host = "localhost";     // Nama host, biasanya "localhost"
$user = "root";          // Username MySQL
$password = "";          // Password MySQL
$database = "elektroshop"; // Nama database

// Membuat koneksi
$koneksi = mysqli_connect($host, $user, $password, $database);

// Mengecek koneksi
if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

?>
