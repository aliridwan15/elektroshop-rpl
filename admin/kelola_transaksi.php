<?php
session_start();
if (!isset($_SESSION['admin_nama'])) {
    header("Location: loginadmin.php");
    exit;
}
include 'koneksi.php';

$users = mysqli_query($koneksi, "SELECT id, nama_pengguna FROM pengguna");
$kupons = mysqli_query($koneksi, "SELECT id, campaign_name FROM kupon_diskon");

// Ambil nilai enum untuk metode_pembayaran
$enum_metode = [];
$result_metode = mysqli_query($koneksi, "SHOW COLUMNS FROM transaksi WHERE Field = 'metode_pembayaran'");
if ($row = mysqli_fetch_assoc($result_metode)) {
    preg_match("/^enum\((.*)\)$/", $row['Type'], $matches);
    $enum_metode = array_map(function ($v) {
        return trim($v, "'");
    }, explode(",", $matches[1]));
}

// Ambil nilai enum untuk status_transaksi
$enum_status = [];
$result_status = mysqli_query($koneksi, "SHOW COLUMNS FROM transaksi WHERE Field = 'status_transaksi'");
if ($row = mysqli_fetch_assoc($result_status)) {
    preg_match("/^enum\((.*)\)$/", $row['Type'], $matches);
    $enum_status = array_map(function ($v) {
        return trim($v, "'");
    }, explode(",", $matches[1]));
}


// Proses Tambah Transaksi
if (isset($_POST['tambah'])) {
    $id_user = (int)$_POST['tambah_id_user'];
    $total = (float)$_POST['tambah_total'];
    $metode = mysqli_real_escape_string($koneksi, $_POST['tambah_metode']);
    $status = mysqli_real_escape_string($koneksi, $_POST['tambah_status']);
    $id_kupon = !empty($_POST['tambah_kupon']) ? (int)$_POST['tambah_kupon'] : "NULL";
    
    // Upload bukti pembayaran jika ada
    $bukti = '';
    if ($_FILES['tambah_bukti']['name']) {
        $bukti = time() . '_' . basename($_FILES['tambah_bukti']['name']);
        $target = 'uploads/' . $bukti;
        move_uploaded_file($_FILES['tambah_bukti']['tmp_name'], $target);
    }

    mysqli_query($koneksi, "INSERT INTO transaksi (id_user, total_transaksi, metode_pembayaran, status_transaksi, id_kupon, bukti_pembayaran, created_at, updated_at) 
    VALUES ($id_user, $total, '$metode', '$status', $id_kupon, '$bukti', NOW(), NOW())");

    $_SESSION['tambah_berhasil'] = true;
    header("Location: kelola_transaksi.php");
    exit;
}


// Proses Ubah Transaksi
if (isset($_POST['ubah'])) {
    $id = (int)$_POST['edit_id'];
    $status = mysqli_real_escape_string($koneksi, $_POST['edit_status']);
    mysqli_query($koneksi, "UPDATE transaksi SET status_transaksi='$status' WHERE id=$id");
    $_SESSION['edit_berhasil'] = true;
    header("Location: kelola_transaksi.php");
    exit;
}

// Proses Hapus Transaksi
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM transaksi WHERE id=$id");
    $_SESSION['hapus_berhasil'] = true;
    header("Location: kelola_transaksi.php");
    exit;
}

// Ambil Data Transaksi
$transaksi = mysqli_query($koneksi, "SELECT * FROM transaksi ORDER BY id ASC");

include 'resource/headeradmin1.php';
?>
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

        .card {
            background-color: #ffa5d8;
            padding: 20px;
            border-radius: 10px;
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

        .modal-buttons {
            margin-top: 20px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn-cancel {
            background-color: red;
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
            background-color: gray;
            color: white;
        }
        .error-message {
        color: red;
        font-size: 12px;
        margin-top: 4px;
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
        <span>Home</span> / <strong>Kelola Transaksi</strong>
    </div>

    <div class="card">
        <table>
            <button class="btn-create" onclick="openTambahModal()">+ Tambah Transaksi</button>
            <button class="bi bi-currency-dollar btn-create " style="margin-left:10px;" onclick="window.location.href='kelola_transaksi_detail.php'">
  Transaksi Detail
</button>

            <thead>
                <tr>
                    <th>ID</th>
                    <th>ID User</th>
                    <th>Total</th>
                    <th>Metode</th>
                    <th>Status</th>
                    <th>ID Kupon</th>
                    <th>Bukti</th>
                    <th>Dibuat</th>
                    <th>Diupdate</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($data = mysqli_fetch_assoc($transaksi)): ?>
                    <tr>
                        <td><?= $data['id'] ?></td>
                        <td><?= $data['id_user'] ?></td>
                        <td>Rp<?= number_format($data['total_transaksi'], 0, ',', '.') ?></td>
                        <td><?= htmlspecialchars($data['metode_pembayaran']) ?></td>
                        <td><?= htmlspecialchars($data['status_transaksi']) ?></td>
                        <td><?= $data['id_kupon'] ?? '-' ?></td>
                        <td>
                            <?php if ($data['bukti_pembayaran']): ?>
                                <a href="uploads/<?= $data['bukti_pembayaran'] ?>" target="_blank">Lihat</a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td><?= $data['created_at'] ?></td>
                        <td><?= $data['updated_at'] ?></td>
                        <td class="action-icons">
                            <i class="bi bi-pencil-fill" onclick='openEditModal(<?= json_encode($data) ?>)'></i>
                            <i class="bi bi-trash-fill" onclick="openDeleteModal(<?= $data['id'] ?>)"></i>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Notifikasi Sukses -->
    <?php foreach (['tambah_berhasil' => 'Transaksi berhasil ditambahkan!', 'edit_berhasil' => 'Transaksi berhasil diubah!', 'hapus_berhasil' => 'Transaksi berhasil dihapus!'] as $key => $msg): ?>
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
        <h2>Tambah Transaksi</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                <div>
    <label>ID User:</label>
    <select name="tambah_id_user" required>
        <option value="">-- Pilih User --</option>
        <?php while ($user = mysqli_fetch_assoc($users)) : ?>
            <option value="<?= $user['id'] ?>"><?= $user['id'] ?> - <?= htmlspecialchars($user['nama_pengguna']) ?></option>
        <?php endwhile; ?>
    </select>
</div>

                <div>
                    <label>Total Transaksi:</label>
                    <input type="number" name="tambah_total" required>
                </div>
                <!-- Metode Pembayaran -->
<div>
    <label>Metode Pembayaran:</label>
    <select name="tambah_metode" required>
        <option value="">-- Pilih Metode --</option>
        <?php foreach ($enum_metode as $metode) : ?>
            <option value="<?= $metode ?>"><?= $metode ?></option>
        <?php endforeach; ?>
    </select>
</div>

<!-- Status Transaksi -->
<div>
    <label>Status Transaksi:</label>
    <select name="tambah_status" required>
        <option value="">-- Pilih Status --</option>
        <?php foreach ($enum_status as $status) : ?>
            <option value="<?= $status ?>"><?= $status ?></option>
        <?php endforeach; ?>
    </select>
</div>

                <div>
    <label>ID Kupon (opsional):</label>
    <select name="tambah_kupon">
        <option value="">-- Tidak Menggunakan Kupon --</option>
        <?php while ($kupon = mysqli_fetch_assoc($kupons)) : ?>
            <option value="<?= $kupon['id'] ?>"><?= $kupon['id'] ?> - <?= htmlspecialchars($kupon['campaign_name']) ?></option>
        <?php endwhile; ?>
    </select>
</div>

                <div>
                    <label>Bukti Pembayaran (opsional):</label>
                    <input type="file" name="tambah_bukti">
                </div>
            </div>
            <div class="modal-buttons">
                <button type="submit" name="tambah" class="btn-create">Tambah</button>
                <button type="button" class="btn-cancel" onclick="closeTambahModal()">Batal</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<!-- Modal Edit -->
<div id="modalEdit" class="modal">
    <div class="modal-content">
        <h2>Edit Status Transaksi</h2>
        <form method="POST">
            <div class="form-grid">
<input type="hidden" name="edit_id" id="edit_id">
            <label>Status:</label>
            <select name="edit_status" id="edit_status" required>
                <?php foreach ($enum_status as $status) : ?>
                    <option value="<?= $status ?>"><?= $status ?></option>
                <?php endforeach; ?>
            </select>
            <div class="modal-buttons">
                <button type="submit" name="ubah" class="btn-create">Simpan</button>
                <button type="button" class="btn-cancel" onclick="closeEditModal()">Batal</button>
            </div>
            </div>
            
        </form>
    </div>
</div>

<!-- Modal Hapus -->
<div id="modalHapus" class="modal">
    <div class="modal-content" style="text-align:center;">
        <h2 style="color:red;">Yakin ingin menghapus transaksi ini?</h2>
        <form method="GET">
            <input type="hidden" name="hapus" id="hapus_id">
            <div class="modal-buttons">
                <button type="submit" class="btn-create">Ya, Hapus</button>
                <button type="button" class="btn-cancel" onclick="closeDeleteModal()">Batal</button>
            </div>
        </form>
    </div>
</div>

<!-- SCRIPT -->
<script>
    function openTambahModal() {
    document.getElementById('modalTambah').style.display = 'flex';
}
function closeTambahModal() {
    document.getElementById('modalTambah').style.display = 'none';
}

function openEditModal(data) {
    document.getElementById('modalEdit').style.display = 'flex';
    document.getElementById('edit_id').value = data.id;
    document.getElementById('edit_status').value = data.status_transaksi;
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
window.onclick = function(e) {
    if (e.target.classList.contains('modal')) {
        e.target.style.display = 'none';
    }
}

let countdown = 5;
const countdownEl = document.getElementById("countdown");
const modal = document.getElementById("successModal");

if (countdownEl) {
    const timer = setInterval(() => {
        countdown--;
        countdownEl.textContent = countdown;
        if (countdown <= 0) {
            clearInterval(timer);
            if (modal) modal.style.display = 'none';
        }
    }, 1000);
}
</script>
