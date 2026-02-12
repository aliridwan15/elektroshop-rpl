<?php
session_start();
if (!isset($_SESSION['admin_nama'])) {
    header("Location: loginadmin.php");
    exit;
}
include 'koneksi.php';

$old = ['pertanyaan' => '', 'jawaban' => '', 'jenis' => ''];
$errors = [];

// Tambah FAQ
if (isset($_POST['tambah'])) {
    $pertanyaan = $_POST['pertanyaan'];
    $jawaban = $_POST['jawaban'];
    $jenis = $_POST['jenis'];

    mysqli_query($koneksi, "INSERT INTO faq (pertanyaan, jawaban, jenis) VALUES ('$pertanyaan', '$jawaban', '$jenis')");
    $_SESSION['tambah_berhasil'] = true;
    header("Location: kelola_faq.php");
    exit;
}

// Ubah FAQ
if (isset($_POST['ubah'])) {
    $id = (int)$_POST['edit_id'];
    $pertanyaan = mysqli_real_escape_string($koneksi, $_POST['edit_pertanyaan']);
    $jawaban = mysqli_real_escape_string($koneksi, $_POST['edit_jawaban']);
    $jenis = mysqli_real_escape_string($koneksi, $_POST['edit_jenis']);

    mysqli_query($koneksi, "UPDATE faq SET pertanyaan='$pertanyaan', jawaban='$jawaban', jenis='$jenis' WHERE id=$id");
    $_SESSION['edit_berhasil'] = true;
    header("Location: kelola_faq.php");
    exit;
}

// Hapus FAQ
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM faq WHERE id=$id");
    $_SESSION['hapus_berhasil'] = true;
    header("Location: kelola_faq.php");
    exit;
}

// Ambil data FAQ
$faq = mysqli_query($koneksi, "SELECT * FROM faq ORDER BY id ASC");
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
    
</style>

<div class="main-content">
    <div class="breadcrumb">
        <span>Home</span> / <strong>Kelola FAQ</strong>
    </div>

    <div class="card">
        <button class="btn-create" onclick="openModal()">+ Tambah FAQ</button>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Pertanyaan</th>
                    <th>Jawaban</th>
                    <th>Jenis</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($data = mysqli_fetch_assoc($faq)): ?>
                    <tr>
                        <td><?= $data['id'] ?></td>
                        <td><?= htmlspecialchars($data['pertanyaan']) ?></td>
                        <td><?= htmlspecialchars($data['jawaban']) ?></td>
                        <td><?= htmlspecialchars($data['jenis']) ?></td>
                        <td class="action-icons">
                            <i class="bi bi-pencil-fill" onclick='openEditModal(<?= json_encode($data) ?>)'></i>
                            <i class="bi bi-trash-fill" onclick="openDeleteModal(<?= $data['id'] ?>)"></i>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal Berhasil -->
    <?php foreach (['tambah_berhasil' => 'FAQ berhasil ditambahkan!', 'edit_berhasil' => 'FAQ berhasil diperbarui!', 'hapus_berhasil' => 'FAQ berhasil dihapus!'] as $key => $message): ?>
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

<!-- Modal Tambah FAQ -->
<div id="modalTambah" class="modal">
    <div class="modal-content">
        <h2>Tambah FAQ</h2>
        <form method="POST">
            <div class="form-grid">
                <div>
                    <label>Pertanyaan:</label>
                    <input type="text" name="pertanyaan" value="<?= htmlspecialchars($old['pertanyaan']) ?>">
                </div>
                <div>
                    <label>Jawaban:</label>
                    <input type="text" name="jawaban" value="<?= htmlspecialchars($old['jawaban']) ?>">
                </div>
                <div>
                    <label>Jenis:</label>
                    <input type="text" name="jenis" value="<?= htmlspecialchars($old['jenis']) ?>">
                </div>
            </div>
            <div class="modal-buttons">
                <button type="submit" name="tambah" class="btn-create">Tambahkan</button>
                <button type="button" onclick="closeModal()" class="btn-cancel">Batal</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit FAQ -->
<div id="modalEdit" class="modal">
    <div class="modal-content">
        <h2>Edit FAQ</h2>
        <form method="POST" id="formEdit">
            <input type="hidden" name="edit_id" id="edit_id">
            <div class="form-grid">
                <div>
                    <label>Pertanyaan:</label>
                    <input type="text" name="edit_pertanyaan" id="edit_pertanyaan">
                </div>
                <div>
                    <label>Jawaban:</label>
                    <input type="text" name="edit_jawaban" id="edit_jawaban">
                </div>
                <div>
                    <label>Jenis:</label>
                    <input type="text" name="edit_jenis" id="edit_jenis">
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
        <h2 style="color:red;">Yakin ingin menghapus FAQ ini?</h2>
        <form method="GET" action="">
            <input type="hidden" name="hapus" id="hapus_id">
            <div class="modal-buttons">
                <button type="submit" class="btn-create">Ya, Hapus</button>
                <button type="button" onclick="closeDeleteModal()" class="btn-cancel">Batal</button>
            </div>
        </form>
    </div>
</div>

<!-- SCRIPT -->
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
    document.getElementById('edit_pertanyaan').value = data.pertanyaan;
    document.getElementById('edit_jawaban').value = data.jawaban;
    document.getElementById('edit_jenis').value = data.jenis;
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
