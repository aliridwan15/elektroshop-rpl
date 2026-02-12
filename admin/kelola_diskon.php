<?php
session_start();
if (!isset($_SESSION['admin_nama'])) {
    header("Location: loginadmin.php");
    exit;
}
include 'koneksi.php';

// Tambah Diskon
if (isset($_POST['tambah'])) {
    $campaign_name = mysqli_real_escape_string($koneksi, $_POST['campaign_name']);
    $code = mysqli_real_escape_string($koneksi, $_POST['code']);
    $diskon = (int)$_POST['diskon'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);
$cek = mysqli_query($koneksi, "SELECT id FROM kupon_diskon WHERE code='$code'");
if (mysqli_num_rows($cek) > 0) {
    $_SESSION['error'] = "Kode diskon '$code' sudah digunakan!";
    header("Location: kelola_diskon.php");
    exit;
}

    $query = "INSERT INTO kupon_diskon 
              (campaign_name, code, diskon, start_date, end_date, status)
              VALUES ('$campaign_name', '$code', $diskon, '$start_date', '$end_date', '$status')";
    
    mysqli_query($koneksi, $query);
    $_SESSION['tambah_berhasil'] = true;
    header("Location: kelola_diskon.php");
    exit;
}

// Ubah Diskon
if (isset($_POST['ubah'])) {
    $id = (int)$_POST['edit_id'];
    $campaign_name = mysqli_real_escape_string($koneksi, $_POST['edit_campaign_name']);
    $code = mysqli_real_escape_string($koneksi, $_POST['edit_code']);
    $diskon = (int)$_POST['edit_diskon'];
    $start_date = $_POST['edit_start_date'];
    $end_date = $_POST['edit_end_date'];
    $status = mysqli_real_escape_string($koneksi, $_POST['edit_status']);
$cek = mysqli_query($koneksi, "SELECT id FROM kupon_diskon WHERE code='$code' AND id != $id");
if (mysqli_num_rows($cek) > 0) {
    $_SESSION['error'] = "Kode diskon '$code' sudah digunakan oleh kupon lain!";
    header("Location: kelola_diskon.php");
    exit;
}

    $query = "UPDATE kupon_diskon 
              SET campaign_name='$campaign_name',
                  code='$code',
                  diskon=$diskon,
                  start_date='$start_date',
                  end_date='$end_date',
                  status='$status'
              WHERE id=$id";

    mysqli_query($koneksi, $query);
    $_SESSION['edit_berhasil'] = true;
    header("Location: kelola_diskon.php");
    exit;
}

// Hapus Diskon
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM kupon_diskon WHERE id=$id");
    $_SESSION['hapus_berhasil'] = true;
    header("Location: kelola_diskon.php");
    exit;
}

// Ambil Semua Diskon
$diskon = mysqli_query($koneksi, "SELECT * FROM kupon_diskon ORDER BY id ASC");

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
    <style>
    <?php include 'kelola_kategori_styles.css'; // opsional jika kamu sudah pisahkan CSS ke file ?>
</style>

<!-- Tampilan utama -->
<<div class="main-content">
    <div class="breadcrumb">
        <span>Home</span> / <strong>Kelola Diskon</strong>
    </div>

    <div class="card">
        <button class="btn-create" onclick="openModal()">+ Tambah Diskon</button>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>CAMPAIGN NAME</th>
                    <th>CODE</th>
                    <th>DISCOUNT</th>
                    <th>START DATE</th>
                    <th>END DATE</th>
                    <th>STATUS</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($d = mysqli_fetch_assoc($diskon)): ?>
                    <tr>
                        <td><?= $d['id'] ?></td>
                        <td><?= htmlspecialchars($d['campaign_name']) ?></td>
                        <td><?= htmlspecialchars($d['code']) ?></td>
                        <td><?= $d['diskon'] ?>%</td>
                        <td><?= htmlspecialchars($d['start_date']) ?></td>
                        <td><?= htmlspecialchars($d['end_date']) ?></td>
                        <td><?= htmlspecialchars($d['status']) ?></td>
                        <td class="action-icons">
                            <i class="bi bi-pencil-fill" onclick='openEditModal(<?= json_encode($d) ?>)'></i>
                            <i class="bi bi-trash-fill" onclick="openDeleteModal(<?= $d['id'] ?>)"></i>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php include 'resource/footeradmin.php'; ?>
</div>

    <?php foreach (['tambah_berhasil' => 'Diskon berhasil ditambahkan!', 'edit_berhasil' => 'Diskon berhasil diperbarui!', 'hapus_berhasil' => 'Diskon berhasil dihapus!'] as $key => $message): ?>
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

    
</div>

<<!-- Modal Tambah Diskon --> 
<div id="modalTambah" class="modal">
    <div class="modal-content">
        <h2>Tambah Diskon</h2>
        <form method="POST">
            <div class="form-grid">
                <div>
                    <label>CAMPAIGN NAME:</label>
                    <input type="text" name="campaign_name" required>
                </div>
                <div>
                    <label>CODE:</label>
                    <input type="text" name="code" required>
                </div>
                <div>
                    <label>DISCOUNT:</label>
                    <input type="number" name="diskon" min="1" max="100" required>
                </div>
                <div>
                    <label>START DATE:</label>
                    <input type="date" name="start_date" required>
                </div>
                <div>
                    <label>END DATE:</label>
                    <input type="date" name="end_date" required>
                </div>
                <div>
                    <label>STATUS:</label>
                    <select name="status" required>
                        <option value="Aktif">Active</option>
                        <option value="Tidak Aktif">Non Active</option>
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

<!-- Modal Edit Diskon -->
<div id="modalEdit" class="modal">
    <div class="modal-content">
        <h2>Edit Diskon</h2>
        <form method="POST" id="formEdit">
            <input type="hidden" name="edit_id" id="edit_id">
            <div class="form-grid">
                <div>
                    <label>Campaign Name:</label>
                    <input type="text" name="edit_campaign_name" id="edit_campaign_name" required>
                </div>
                <div>
                    <label>code:</label>
                    <input type="text" name="edit_code" id="edit_code" required>
                </div>
                <div>
                    <label>diskon (%):</label>
                    <input type="number" name="edit_diskon" id="edit_diskon" min="1" max="100" required>
                </div>
                <div>
                    <label>Start Date:</label>
                    <input type="date" name="edit_start_date" id="edit_start_date" required>
                </div>
                <div>
                    <label>End Date:</label>
                    <input type="date" name="edit_end_date" id="edit_end_date" required>
                </div>
                <div>
                    <label>Status:</label>
                    <select name="edit_status" id="edit_status" required>
                        <option value="Aktif">Aktif</option>
                        <option value="Tidak Aktif">Tidak Aktif</option>
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

<!-- Modal Hapus Diskon -->
<div id="modalHapus" class="modal">
    <div class="modal-content" style="text-align:center;">
        <h2 style="color:red;">Yakin ingin menghapus kupon ini?</h2>
        <form method="GET">
            <input type="hidden" name="hapus" id="hapus_id">
            <div class="modal-buttons">
                <button type="submit" class="btn-create">Ya, Hapus</button>
                <button type="button" onclick="closeDeleteModal()" class="btn-cancel">Batal</button>
            </div>
        </form>
    </div>
</div>

<!-- Script Modal -->
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
    document.getElementById('edit_code').value = data.code;
    document.getElementById('edit_diskon').value = data.diskon;
    document.getElementById('edit_end_date').value = data.end_date;
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
