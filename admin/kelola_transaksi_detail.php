<?php
session_start();
if (!isset($_SESSION['admin_nama'])) {
    header("Location: loginadmin.php");
    exit;
}
include 'koneksi.php';

// Default old values
$old = ['id_produk' => '', 'id_transaksi' => '', 'jumlah' => '', 'harga_satuan' => '', 'subtotal' => ''];
$errors = [];

// Tambah Transaksi Detail
if (isset($_POST['tambah'])) {
    $id_produk = $_POST['id_produk'];
    $id_transaksi = $_POST['id_transaksi'];
    $jumlah = (int)$_POST['jumlah'];
    $harga_satuan = (float)$_POST['harga_satuan'];
    $subtotal = $jumlah * $harga_satuan;
    $created_at = date('Y-m-d H:i:s');
    $updated_at = date('Y-m-d H:i:s');

    mysqli_query($koneksi, "INSERT INTO transaksi_detail (id_produk, id_transaksi, jumlah, harga_satuan, subtotal, created_at, updated_at) 
        VALUES ('$id_produk', '$id_transaksi', '$jumlah', '$harga_satuan', '$subtotal', '$created_at', '$updated_at')");
    $_SESSION['tambah_berhasil'] = true;
    header("Location: kelola_transaksi_detail.php");
    exit;
}

// Ubah Transaksi Detail
if (isset($_POST['ubah'])) {
    $id = (int)$_POST['edit_id'];
    $id_produk = $_POST['edit_id_produk'];
    $id_transaksi = $_POST['edit_id_transaksi'];
    $jumlah = (int)$_POST['edit_jumlah'];
    $harga_satuan = (float)$_POST['edit_harga_satuan'];
    $subtotal = $jumlah * $harga_satuan;
    $updated_at = date('Y-m-d H:i:s');

    mysqli_query($koneksi, "UPDATE transaksi_detail SET 
        id_produk='$id_produk', 
        id_transaksi='$id_transaksi', 
        jumlah='$jumlah', 
        harga_satuan='$harga_satuan', 
        subtotal='$subtotal', 
        updated_at='$updated_at' 
        WHERE id=$id");
    $_SESSION['edit_berhasil'] = true;
    header("Location: kelola_transaksi_detail.php");
    exit;
}

// Hapus Transaksi Detail
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM transaksi_detail WHERE id=$id");
    $_SESSION['hapus_berhasil'] = true;
    header("Location: kelola_transaksi_detail.php");
    exit;
}

// Ambil data transaksi detail
$transaksi_detail = mysqli_query($koneksi, "SELECT * FROM transaksi_detail ORDER BY id ASC");

// Untuk dropdown produk dan transaksi, ambil data produk dan transaksi
$produk = mysqli_query($koneksi, "SELECT id, nama_produk FROM produk ORDER BY nama_produk ASC");
$transaksi = mysqli_query($koneksi, "SELECT id, id_user, total_transaksi FROM transaksi ORDER BY id ASC");

include 'resource/headeradmin1.php';
?>

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
    .main-content.sidebar-active {
        margin-left: 270px;
    }
    .breadcrumb {
        font-size: 14px;
        color: #333;
        margin-bottom: 20px;
    }
    .breadcrumb a {
        color: #333;
        text-decoration: none;
    }
    .breadcrumb a:hover {
        text-decoration: underline;
    }
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
    .btn-create:hover {
        background-color: #c71585;
    }
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
    th {
        background-color: #ff90cd;
    }
    tr:nth-child(even) {
        background-color: #ffd4e8;
    }
    .action-icons i {
        cursor: pointer;
        margin-right: 10px;
        font-size: 16px;
    }
    .action-icons i.bi-pencil-fill {
        color: black;
    }
    .action-icons i.bi-trash-fill {
        color: red;
    }
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
    .modal h2 {
        margin-top: 0;
        color: red;
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
        margin-bottom: 15px;
        font-weight: bold;
        font-size: 14px;
        cursor: pointer;
    }
    .btn-cancel:hover {
        background-color: #555;
    }
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-top: 15px;
    }
    .form-grid label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }
    .form-grid input, .form-grid select {
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

<div class="main-content">
    <div class="breadcrumb">
        <span>Home</span> / <strong>Kelola Transaksi</strong> / <strong>Kelola Transaksi Detail</strong>
    </div>

    <div class="card">
        <button class="btn-create" onclick="openModal()">+ Tambah Transaksi Detail</button>
<button class="bi bi-arrow-left btn-cancel " style="margin-left:10px;" onclick="window.location.href='kelola_transaksi.php'">
  Kembali
</button>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Produk</th>
                    <th>Transaksi ID</th>
                    <th>Jumlah</th>
                    <th>Harga Satuan</th>
                    <th>Subtotal</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($transaksi_detail)): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td>
                            <?php
                            // Tampilkan nama produk berdasarkan id_produk
                            $id_produk = $row['id_produk'];
                            $produk_nama = "Unknown";
                            $result = mysqli_query($koneksi, "SELECT nama_produk FROM produk WHERE id=$id_produk");
                            if ($result && mysqli_num_rows($result) > 0) {
                                $produk_nama = mysqli_fetch_assoc($result)['nama_produk'];
                            }
                            echo htmlspecialchars($produk_nama);
                            ?>
                        </td>
                        <td><?= $row['id_transaksi'] ?></td>
                        <td><?= $row['jumlah'] ?></td>
                        <td><?= number_format($row['harga_satuan'], 0, ',', '.') ?></td>
                        <td><?= number_format($row['subtotal'], 0, ',', '.') ?></td>
                        <td><?= $row['created_at'] ?></td>
                        <td><?= $row['updated_at'] ?></td>
                        <td class="action-icons">
                            <i class="bi bi-pencil-fill" onclick='openEditModal(<?= json_encode($row) ?>)'></i>
                            <i class="bi bi-trash-fill" onclick="openDeleteModal(<?= $row['id'] ?>)"></i>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal Berhasil -->
    <?php foreach (['tambah_berhasil' => 'Transaksi Detail berhasil ditambahkan!', 'edit_berhasil' => 'Transaksi Detail berhasil diperbarui!', 'hapus_berhasil' => 'Transaksi Detail berhasil dihapus!'] as $key => $message): ?>
        <?php if (isset($_SESSION[$key])): ?>
            <div id="successModal" class="modal" style="display:flex;">
                <div class="modal-content" style="text-align:center;">
                    <h2 style="color:green;"><?= $message ?></h2>
                    <p>Menutup dalam <span id="countdown">5</span> detik...</p>
                </div>
            </div>
            <?php unset($_SESSION[$key]); ?>
        <?php endif; ?>
    <?php endforeach; ?>

    <?php include 'resource/footeradmin.php'; ?>
</div>

<!-- Modal Tambah Transaksi Detail -->
<div id="modalTambah" class="modal">
    <div class="modal-content">
        <h2>Tambah Transaksi Detail</h2>
        <form method="POST">
            <div class="form-grid">
                <div>
                    <label>Produk:</label>
                    <select name="id_produk" required>
                        <option value="">-- Pilih Produk --</option>
                        <?php while ($p = mysqli_fetch_assoc($produk)): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nama_produk']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label>Transaksi ID:</label>
                    <select name="id_transaksi" required>
                        <option value="">-- Pilih Transaksi --</option>
                        <?php mysqli_data_seek($transaksi, 0); // reset pointer ?>
                        <?php while ($t = mysqli_fetch_assoc($transaksi)): ?>
                            <option value="<?= $t['id'] ?>">ID: <?= $t['id'] ?> | User: <?= $t['id_user'] ?> | Total: <?= number_format($t['total_transaksi'], 0, ',', '.') ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label>Jumlah:</label>
                    <input type="number" name="jumlah" min="1" required>
                </div>
                <div>
                    <label>Harga Satuan:</label>
                    <input type="number" name="harga_satuan" min="0" step="0.01" required>
                </div>
            </div>
            <div class="modal-buttons">
            <button type="submit" name="tambah" class="btn-create">Tambah</button>
            <button type="button" class="btn-cancel" onclick="closeModal()">Batal</button>
        </div>
    </form>
</div>
</div> <!-- Modal Edit Transaksi Detail -->

<div id="modalEdit" class="modal">
    <div class="modal-content">
        <h2>Edit Transaksi Detail</h2>
        <form method="POST" id="formEdit">
            <input type="hidden" name="edit_id" id="edit_id">
            <div class="form-grid">
                <div>
                    <label>Produk:</label>
                    <select name="edit_id_produk" id="edit_id_produk" required>
                        <option value="">-- Pilih Produk --</option>
                        <?php mysqli_data_seek($produk, 0); while ($p = mysqli_fetch_assoc($produk)): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nama_produk']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label>Transaksi ID:</label>
                    <select name="edit_id_transaksi" id="edit_id_transaksi" required>
                        <option value="">-- Pilih Transaksi --</option>
                        <?php mysqli_data_seek($transaksi, 0); while ($t = mysqli_fetch_assoc($transaksi)): ?>
                            <option value="<?= $t['id'] ?>">ID: <?= $t['id'] ?> | User: <?= $t['id_user'] ?> | Total: <?= number_format($t['total_transaksi'], 0, ',', '.') ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label>Jumlah:</label>
                    <input type="number" name="edit_jumlah" id="edit_jumlah" min="1" required>
                </div>
                <div>
                    <label>Harga Satuan:</label>
                    <input type="number" name="edit_harga_satuan" id="edit_harga_satuan" min="0" step="0.01" required>
                </div>
            </div>
            <div class="modal-buttons">
                <button type="submit" name="ubah" class="btn-create">Simpan</button>
                <button type="button" class="btn-cancel" onclick="closeEditModal()">Batal</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Delete Confirmation -->
<div id="modalDelete" class="modal">
    <div class="modal-content" style="max-width: 400px;">
        <h2>Hapus Transaksi Detail?</h2>
        <p>Apakah Anda yakin ingin menghapus transaksi detail ini?</p>
        <div class="modal-buttons" style="justify-content: flex-end;">
            <a href="#" id="deleteConfirmBtn" class="btn-create" style="background-color: red;">Hapus</a>
            <button type="button" class="btn-cancel" onclick="closeDeleteModal()">Batal</button>
        </div>
    </div>
</div>

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
        document.getElementById('edit_id_produk').value = data.id_produk;
        document.getElementById('edit_id_transaksi').value = data.id_transaksi;
        document.getElementById('edit_jumlah').value = data.jumlah;
        document.getElementById('edit_harga_satuan').value = data.harga_satuan;
    }
    function closeEditModal() {
        document.getElementById('modalEdit').style.display = 'none';
    }

    function openDeleteModal(id) {
        document.getElementById('modalDelete').style.display = 'flex';
        document.getElementById('deleteConfirmBtn').href = 'kelola_transaksi_detail.php?hapus=' + id;
    }
    function closeDeleteModal() {
        document.getElementById('modalDelete').style.display = 'none';
    }

    // Auto close success modal after 5 seconds
    const successModal = document.getElementById('successModal');
    if(successModal){
        let countdownEl = document.getElementById('countdown');
        let countdown = 5;
        const interval = setInterval(() => {
            countdown--;
            countdownEl.textContent = countdown;
            if(countdown <= 0){
                successModal.style.display = 'none';
                clearInterval(interval);
            }
        }, 1000);
    }
</script>
