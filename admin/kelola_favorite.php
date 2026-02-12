<?php
session_start();
if (!isset($_SESSION['admin_nama'])) {
    header("Location: loginadmin.php");
    exit;
}
include 'koneksi.php';
$users = mysqli_query($koneksi, "SELECT id, nama_pengguna FROM pengguna ORDER BY nama_pengguna DESC");
$produk = mysqli_query($koneksi, "SELECT id, nama_produk FROM produk ORDER BY nama_produk DESC");

// Tambah Favorite
if (isset($_POST['tambah'])) {
    $id_user = $_POST['id_user'];
    $id_produk = $_POST['id_produk'];
    mysqli_query($koneksi, "INSERT INTO favorite (id_user, id_produk, created_at) VALUES ('$id_user', '$id_produk', NOW())");
    $_SESSION['tambah_berhasil'] = true;
    header("Location: kelola_favorite.php");
    exit;
}

// Edit Favorite
if (isset($_POST['ubah'])) {
    $id = (int)$_POST['edit_id'];
    $id_user = $_POST['edit_id_user'];
    $id_produk = $_POST['edit_id_produk'];
    mysqli_query($koneksi, "UPDATE favorite SET id_user='$id_user', id_produk='$id_produk' WHERE id=$id");
    $_SESSION['edit_berhasil'] = true;
    header("Location: kelola_favorite.php");
    exit;
}

// Hapus Favorite
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM favorite WHERE id=$id");
    $_SESSION['hapus_berhasil'] = true;
    header("Location: kelola_favorite.php");
    exit;
}

// Ambil data Favorite
$favorites = mysqli_query($koneksi, "SELECT * FROM favorite ORDER BY id ASC");
include 'resource/headeradmin1.php';
?>

<!-- Styles and Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<style>
    body {
        margin: 0;
        font-family: 'Segoe UI', sans-serif;
        background-color: #ffc0cb;
    }
    .main-content {
        margin-left: 20px;
        padding: 100px 40px 40px 40px;
        background-color: #ffc0cb;
        min-height: 100vh;
        transition: margin-left 0.3s ease;
    }
    .main-content.sidebar-active { margin-left: 270px; }
    .breadcrumb { font-size: 14px; color: #333; margin-bottom: 20px; }
    .breadcrumb a { color: #333; text-decoration: none; }
    .breadcrumb a:hover { text-decoration: underline; }
    .btn-create {
        background-color: deeppink;
        color: white;
        padding: 8px 16px;
        border: none;
        border-radius: 6px;
        margin-bottom: 15px;
        font-weight: bold;
        font-size: 14px;
        cursor: pointer;
    }
    .btn-create:hover { background-color: #c71585; }
    table {
        width: 100%;
        border-collapse: collapse;
        background-color: #ffc5e3;
        border-radius: 10px;
        overflow: hidden;
    }
    th, td {
        padding: 12px;
        text-align: left;
        font-size: 14px;
    }
    th { background-color: #ff90cd; }
    tr:nth-child(even) { background-color: #ffd4e8; }
    .action-icons i {
        cursor: pointer;
        margin-right: 10px;
        font-size: 16px;
    }
    .action-icons i.bi-pencil-fill { color: black; }
    .action-icons i.bi-trash-fill { color: red; }
    .card {
        background-color: #ffa5d8;
        padding: 20px;
        border-radius: 10px;
    }
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0; top: 0;
        width: 100%; height: 100%;
        background-color: rgba(0, 0, 0, 0.4);
        justify-content: center;
        align-items: center;
    }
    .modal-content {
        background-color: #ffe6f0;
        padding: 30px;
        border-radius: 10px;
        width: 600px;
        max-width: 90%;
    }
    .modal-buttons {
        margin-top: 20px;
        display: flex;
        justify-content: space-between;
    }
    .btn-cancel {
        background-color: grey;
        color: white;
        padding: 8px 16px;
        border: none;
        border-radius: 6px;
        font-weight: bold;
        font-size: 14px;
        cursor: pointer;
    }
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-top: 15px;
    }
    .form-grid label {
        font-weight: bold;
        margin-bottom: 5px;
        display: block;
    }
    .form-grid select {
        width: 100%;
        padding: 8px;
        border-radius: 6px;
        border: 1px solid #ccc;
        font-size: 14px;
    }
</style>
<script>
        document.addEventListener("DOMContentLoaded", function () {
            const sidebar = document.getElementById("sidebar");
            const mainContent = document.querySelector(".main-content");

            if (sidebar && sidebar.classList.contains("active")) {
                mainContent.classList.add("sidebar-active");
            }

            window.toggleSidebar = function () {
                sidebar.classList.toggle("active");
                mainContent.classList.toggle("sidebar-active");
            }
        });
    </script>
<!-- Breadcrumb + Table -->
<div class="main-content">
    <div class="breadcrumb">
        <span>Home</span> / <strong>Kelola Favorite</strong>
    </div>
    <div class="card">
        <button class="btn-create" onclick="openModal()">+ Tambah Favorite</button>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ID User</th>
                    <th>ID Produk</th>
                    <th>Created At</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($data = mysqli_fetch_assoc($favorites)): ?>
                <tr>
                    <td><?= $data['id'] ?></td>
                    <td><?= htmlspecialchars($data['id_user']) ?></td>
                    <td><?= htmlspecialchars($data['id_produk']) ?></td>
                    <td><?= htmlspecialchars($data['created_at']) ?></td>
                    <td class="action-icons">
                        <i class="bi bi-pencil-fill" onclick='openEditModal(<?= json_encode($data) ?>)'></i>
                        <i class="bi bi-trash-fill" onclick="openDeleteModal(<?= $data['id'] ?>)"></i>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal Notifikasi -->
    <?php foreach (['tambah_berhasil' => 'Favorite berhasil ditambahkan!', 'edit_berhasil' => 'Favorite berhasil diperbarui!', 'hapus_berhasil' => 'Favorite berhasil dihapus!'] as $key => $msg): ?>
        <?php if (isset($_SESSION[$key])): ?>
            <div id="successModal" class="modal" style="display:flex;">
                <div class="modal-content" style="text-align:center;">
                    <h2 style="color:green;"><?= $msg ?></h2>
                    <p>Menutup dalam <span id="countdown">5</span> detik...</p>
                </div>
            </div>
            <?php unset($_SESSION[$key]); ?>
        <?php endif; ?>
    <?php endforeach; ?>

    <?php include 'resource/footeradmin.php'; ?>
</div>

<!-- Modal Tambah -->
<div id="modalTambah" class="modal">
    <div class="modal-content">
        <h2>Tambah Favorite</h2>
        <form method="POST">
            <div class="form-grid">
                <div>
                    <label>ID User:</label>
                    <select name="id_user" required>
                        <option value="">-- Pilih User --</option>
                        <?php while ($u = mysqli_fetch_assoc($users)) : ?>
                            <option value="<?= $u['id'] ?>"><?= $u['id'] ?> - <?= htmlspecialchars($u['nama_pengguna']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label>ID Produk:</label>
                    <select name="id_produk" required>
                        <option value="">-- Pilih Produk --</option>
                        <?php mysqli_data_seek($produk, 0); while ($p = mysqli_fetch_assoc($produk)) : ?>
                            <option value="<?= $p['id'] ?>"><?= $p['id'] ?> - <?= htmlspecialchars($p['nama_produk']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <div class="modal-buttons">
                <button type="submit" name="tambah" class="btn-create">Tambahkan</button>
                <button type="button" onclick="closeModal()" class="btn-cancel">Batal</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div id="modalEdit" class="modal">
    <div class="modal-content">
        <h2>Edit Favorite</h2>
        <form method="POST">
            <input type="hidden" name="edit_id" id="edit_id">
            <div class="form-grid">
                <div>
                    <label>ID User:</label>
                    <select name="edit_id_user" id="edit_id_user" required>
                        <option value="">-- Pilih User --</option>
                        <?php mysqli_data_seek($users, 0); while ($u = mysqli_fetch_assoc($users)) : ?>
                            <option value="<?= $u['id'] ?>"><?= $u['id'] ?> - <?= htmlspecialchars($u['nama_pengguna']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label>ID Produk:</label>
                    <select name="edit_id_produk" id="edit_id_produk" required>
                        <option value="">-- Pilih Produk --</option>
                        <?php mysqli_data_seek($produk, 0); while ($p = mysqli_fetch_assoc($produk)) : ?>
                            <option value="<?= $p['id'] ?>"><?= $p['id'] ?> - <?= htmlspecialchars($p['nama_produk']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <div class="modal-buttons">
                <button type="submit" name="ubah" class="btn-create">Simpan</button>
                <button type="button" onclick="closeEditModal()" class="btn-cancel">Batal</button>
            </div>
        </form>
    </div>
</div>


<!-- Modal Hapus -->
<div id="modalHapus" class="modal">
    <div class="modal-content" style="text-align:center;">
        <h2 style="color:red;">Yakin ingin menghapus Favorite ini?</h2>
        <form method="GET">
            <input type="hidden" name="hapus" id="hapus_id">
            <div class="modal-buttons">
                <button type="submit" class="btn-create">Ya, Hapus</button>
                <button type="button" onclick="closeDeleteModal()" class="btn-cancel">Batal</button>
            </div>
        </form>
    </div>
</div>

<!-- Script -->
<script>
function openModal() {
    document.getElementById('modalTambah').style.display = 'flex';
}
function closeModal() {
    document.getElementById('modalTambah').style.display = 'none';
}
function openEditModal(data) {
    document.getElementById('modalEdit').style.display = 'flex';
    document.getElementById('edit_id').value = data.id;
    document.getElementById('edit_id_user').value = data.id_user;
    document.getElementById('edit_id_produk').value = data.id_produk;
}
function closeEditModal() {
    document.getElementById('modalEdit').style.display = 'none';
}
function openDeleteModal(id) {
    document.getElementById('hapus_id').value = id;
    document.getElementById('modalHapus').style.display = 'flex';
}
function closeDeleteModal() {
    document.getElementById('modalHapus').style.display = 'none';
}
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = "none";
    }
}
let countdown = 5;
const countdownElement = document.getElementById("countdown");
const successModal = document.getElementById("successModal");

if (countdownElement) {
    const timer = setInterval(() => {
        countdown--;
        countdownElement.textContent = countdown;
        if (countdown <= 0) {
            clearInterval(timer);
            if (successModal) successModal.style.display = 'none';
        }
    }, 1000);
}
</script>
