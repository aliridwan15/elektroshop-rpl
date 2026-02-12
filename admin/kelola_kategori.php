<?php
session_start();
if (!isset($_SESSION['admin_nama'])) {
    header("Location: loginadmin.php");
    exit;
}

include 'koneksi.php';
$old = ['nama_kategori' => ''];
$errors = [];

// Tambah Kategori
if (isset($_POST['tambah'])) {
    $nama_kategori = $_POST['nama_kategori'];
    mysqli_query($koneksi, "INSERT INTO kategori (nama_kategori) VALUES ('$nama_kategori')");
    $_SESSION['tambah_berhasil'] = true;
    header("Location: kelola_kategori.php");
    exit;
}

// Ubah Kategori
if (isset($_POST['ubah'])) {
$id = (int) $_POST['edit_id'];
$nama_kategori = mysqli_real_escape_string($koneksi, $_POST['edit_nama_kategori']);

    mysqli_query($koneksi, "UPDATE kategori SET nama_kategori='$nama_kategori' WHERE id=$id");
    $_SESSION['edit_berhasil'] = true;
    header("Location: kelola_kategori.php");
    exit;
}


// Hapus Kategori
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM kategori WHERE id=$id");
    $_SESSION['hapus_berhasil'] = true;
    header("Location: kelola_kategori.php");
    exit;
}

// Ambil data kategori
$kategori = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY id ASC");

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
        <span>Home</span> / <strong>Kelola Kategori</strong>
    </div>

    <div class="card">
        <button class="btn-create" onclick="openModal()">+ Create new</button>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Kategori</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($data = mysqli_fetch_assoc($kategori)): ?>
                    <tr>
                        <td><?= $data['id'] ?></td>
                        <td><?= htmlspecialchars($data['nama_kategori']) ?></td>
                        <td class="action-icons">
                            <i class="bi bi-pencil-fill" onclick='openEditModal(<?= json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'></i>
                            <i class="bi bi-trash-fill" onclick="openDeleteModal(<?= $data['id'] ?>)"></i>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal sukses -->
    <?php if (isset($_SESSION['tambah_berhasil'])): ?>
        <div id="successModal" class="modal" style="display:flex;">
            <div class="modal-content" style="text-align:center;">
                <h2 style="color:green;">Kategori berhasil ditambahkan!</h2>
                <p>Menutup dalam <span id="countdown">5</span> detik...</p>
            </div>
        </div>
        <?php unset($_SESSION['tambah_berhasil']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['edit_berhasil'])): ?>
        <div id="successModal" class="modal" style="display:flex;">
            <div class="modal-content" style="text-align:center;">
                <h2 style="color:green;">Kategori berhasil diperbarui!</h2>
                <p>Menutup dalam <span id="countdown">5</span> detik...</p>
            </div>
        </div>
        <?php unset($_SESSION['edit_berhasil']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['hapus_berhasil'])): ?>
        <div id="successModal" class="modal" style="display:flex;">
            <div class="modal-content" style="text-align:center;">
                <h2 style="color:green;">Kategori berhasil dihapus!</h2>
                <p>Menutup dalam <span id="countdown">5</span> detik...</p>
            </div>
        </div>
        <?php unset($_SESSION['hapus_berhasil']); ?>
    <?php endif; ?>

    <?php include 'resource/footeradmin.php'; ?>
</div>

<!-- Modal Tambah -->
<div id="modalTambah" class="modal" style="<?= !empty($errors) ? 'display:flex;' : '' ?>">
    <div class="modal-content">
        <h2>Tambah Kategori</h2>
        <form method="POST">
    <div class="form-grid">
        <div>
            <label>Nama Kategori:</label>
            <input type="text" name="nama_kategori" value="<?= htmlspecialchars($old['nama_kategori']) ?>">
            <?php if (!empty($errors['nama_kategori'])): ?>
                <div class="error-message"><?= $errors['nama_kategori'] ?></div>
            <?php endif; ?>
        </div>
    </div>
    <div class="modal-buttons">
        <button type="submit" name="tambah" class="btn-create">Tambahkan</button>
        <button type="button" onclick="closeModal()" class="btn-cancel">Batalkan</button>
    </div>
</form>

    </div>
</div>

<!-- Modal Edit -->
<div id="modalEdit" class="modal">
    <div class="modal-content">
        <h2>Edit Kategori</h2>
        <form method="POST" id="formEdit">
            <input type="hidden" name="edit_id" id="edit_id">
            <div class="form-grid">
                <div>
                    <label>Nama Kategori:</label>
                    <input type="text" name="edit_nama_kategori" id="edit_nama_kategori">
                </div>
            </div>
            <div class="modal-buttons">
                <button type="submit" name="ubah" class="btn-create">Simpan Perubahan</button>
                <button type="button" onclick="closeEditModal()" class="btn-cancel">Batalkan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Hapus -->
<div id="modalHapus" class="modal">
    <div class="modal-content" style="text-align:center;">
        <h2 style="color:red;">Yakin ingin menghapus kategori ini?</h2>
        <form method="POST" action="">
            <input type="hidden" name="id" id="hapus_id">
            <div class="modal-buttons">
                <button type="submit" name="hapus" class="btn-create">Ya, Hapus</button>
                <button type="button" onclick="closeDeleteModal()" class="btn-cancel">Batal</button>
            </div>
        </form>
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
    document.getElementById('edit_nama_kategori').value = data.nama_kategori;
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
    const modalTambah = document.getElementById('modalTambah');
    const modalEdit = document.getElementById('modalEdit');
    const modalHapus = document.getElementById('modalHapus');
    if (event.target == modalTambah) closeModal();
    if (event.target == modalEdit) closeEditModal();
    if (event.target == modalHapus) closeDeleteModal();
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