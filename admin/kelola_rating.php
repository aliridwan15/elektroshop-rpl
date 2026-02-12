<?php
session_start();
if (!isset($_SESSION['admin_nama'])) {
    header("Location: loginadmin.php");
    exit;
}
include 'koneksi.php';

// Ambil error & input sebelumnya dari session
$errors = $_SESSION['errors'] ?? [];
$old_input = $_SESSION['old_input'] ?? [];
$show_modal = $_SESSION['show_modal'] ?? '';
unset($_SESSION['errors'], $_SESSION['old_input'], $_SESSION['show_modal']);

$produk = mysqli_query($koneksi, "SELECT id, nama_produk FROM produk");
$pengguna = mysqli_query($koneksi, "SELECT id, nama_pengguna FROM pengguna");
// Fetch only the ID from transactions for the dropdown
$transaksi = mysqli_query($koneksi, "SELECT id FROM transaksi"); // Simplified to get only ID

// Tambah Rating
if (isset($_POST['tambah'])) {
    $bintang = $_POST['bintang'];
    $id_user = $_POST['id_user'];
    $id_transaksi = $_POST['id_transaksi']; // New field
    $id_produk = $_POST['id_produk'];
    $ulasan = $_POST['ulasan'];
    $nilai_design = $_POST['nilai_design'];
    $nilai_flexibility = $_POST['nilai_flexibility'];
    $nilai_usage = $_POST['nilai_usage'];
    $created_at = date('Y-m-d H:i:s');
    $errors = [];

    if (!is_numeric($bintang) || $bintang < 1 || $bintang > 5) {
        $errors[] = "Nilai bintang harus antara 1 - 5.";
    }
    if (empty($ulasan)) {
        $errors[] = "Ulasan tidak boleh kosong.";
    }
    if (empty($id_transaksi)) { // Validation for new field
        $errors[] = "ID Transaksi tidak boleh kosong.";
    }

    $gambar_ulasan = '';
    if (isset($_FILES['gambar_ulasan']) && $_FILES['gambar_ulasan']['error'] === 0) {
        $namaFile = $_FILES['gambar_ulasan']['name'];
        $tmp = $_FILES['gambar_ulasan']['tmp_name'];
        $ext = strtolower(pathinfo($namaFile, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png'];

        if (in_array($ext, $allowed)) {
            $namaBaru = uniqid('img_') . '.' . $ext;
            move_uploaded_file($tmp, 'uploads/' . $namaBaru);
            $gambar_ulasan = $namaBaru;
        } else {
            $errors[] = "Ekstensi gambar ulasan tidak diizinkan. Hanya JPG, JPEG, PNG.";
        }
    }

    $video_ulasan = '';
    if (isset($_FILES['video_ulasan']) && $_FILES['video_ulasan']['error'] === 0) {
        $namaVideo = $_FILES['video_ulasan']['name'];
        $tmpVideo = $_FILES['video_ulasan']['tmp_name'];
        $extVideo = strtolower(pathinfo($namaVideo, PATHINFO_EXTENSION));
        $allowedVideo = ['mp4', 'webm', 'ogg'];

        if (in_array($extVideo, $allowedVideo)) {
            $namaBaruVideo = uniqid('vid_') . '.' . $extVideo;
            move_uploaded_file($tmpVideo, 'uploads/' . $namaBaruVideo);
            $video_ulasan = $namaBaruVideo;
        } else {
            $errors[] = "Ekstensi video ulasan tidak diizinkan. Hanya MP4, WebM, OGG.";
        }
    }


    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['old_input'] = $_POST;
        $_SESSION['show_modal'] = 'tambah';
        header("Location: kelola_rating.php");
        exit;
    }

    mysqli_query($koneksi, "INSERT INTO rating (bintang, id_user, id_transaksi, id_produk, gambar_ulasan, ulasan, video_ulasan, nilai_design, nilai_flexibility, nilai_usage, created_at)
        VALUES ('$bintang', '$id_user', '$id_transaksi', '$id_produk', '$gambar_ulasan', '$ulasan', '$video_ulasan', '$nilai_design', '$nilai_flexibility', '$nilai_usage', '$created_at')");
    $_SESSION['tambah_berhasil'] = true;
    header("Location: kelola_rating.php");
    exit;
}

// Ubah Rating
if (isset($_POST['ubah'])) {
    $id = (int)$_POST['edit_id'];
    $bintang = $_POST['edit_bintang'];
    $id_user = $_POST['edit_id_user'];
    $id_transaksi = $_POST['edit_id_transaksi']; // New field
    $id_produk = $_POST['edit_id_produk'];
    $ulasan = $_POST['edit_ulasan'];
    $nilai_design = $_POST['edit_nilai_design'];
    $nilai_flexibility = $_POST['edit_nilai_flexibility'];
    $nilai_usage = $_POST['edit_nilai_usage'];
    $updated_at = date('Y-m-d H:i:s');
    $errors = [];

    if (!is_numeric($bintang) || $bintang < 1 || $bintang > 5) {
        $errors[] = "Nilai bintang harus antara 1 - 5.";
    }
    if (empty($ulasan)) {
        $errors[] = "Ulasan tidak boleh kosong.";
    }
    if (empty($id_transaksi)) { // Validation for new field
        $errors[] = "ID Transaksi tidak boleh kosong.";
    }


    $gambar_ulasan = $_POST['gambar_lama'];
    if (isset($_FILES['edit_gambar_ulasan']) && $_FILES['edit_gambar_ulasan']['error'] === 0) {
        $namaFile = $_FILES['edit_gambar_ulasan']['name'];
        $tmp = $_FILES['edit_gambar_ulasan']['tmp_name'];
        $ext = strtolower(pathinfo($namaFile, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png'];

        if (in_array($ext, $allowed)) {
            // Delete old image if it exists
            if (!empty($gambar_ulasan) && file_exists('uploads/' . $gambar_ulasan)) {
                unlink('uploads/' . $gambar_ulasan);
            }
            $namaBaru = uniqid('img_') . '.' . $ext;
            move_uploaded_file($tmp, 'uploads/' . $namaBaru);
            $gambar_ulasan = $namaBaru;
        } else {
            $errors[] = "Ekstensi gambar ulasan tidak diizinkan. Hanya JPG, JPEG, PNG.";
        }
    }

    $video_ulasan = $_POST['video_lama'];
    if (isset($_FILES['edit_video_ulasan']) && $_FILES['edit_video_ulasan']['error'] === 0) {
        $namaVideo = $_FILES['edit_video_ulasan']['name'];
        $tmpVideo = $_FILES['edit_video_ulasan']['tmp_name'];
        $extVideo = strtolower(pathinfo($namaVideo, PATHINFO_EXTENSION));
        $allowedVideo = ['mp4', 'webm', 'ogg'];

        if (in_array($extVideo, $allowedVideo)) {
            // Delete old video if it exists
            if (!empty($video_ulasan) && file_exists('uploads/' . $video_ulasan)) {
                unlink('uploads/' . $video_ulasan);
            }
            $namaBaruVideo = uniqid('vid_') . '.' . $extVideo;
            move_uploaded_file($tmpVideo, 'uploads/' . $namaBaruVideo);
            $video_ulasan = $namaBaruVideo;
        } else {
            $errors[] = "Ekstensi video ulasan tidak diizinkan. Hanya MP4, WebM, OGG.";
        }
    }


    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['old_input'] = $_POST;
        $_SESSION['show_modal'] = 'edit';
        header("Location: kelola_rating.php");
        exit;
    }

    mysqli_query($koneksi, "UPDATE rating SET
        bintang='$bintang',
        id_user='$id_user',
        id_transaksi='$id_transaksi', -- Added id_transaksi
        id_produk='$id_produk',
        gambar_ulasan='$gambar_ulasan',
        ulasan='$ulasan',
        video_ulasan='$video_ulasan',
        nilai_design='$nilai_design',
        nilai_flexibility='$nilai_flexibility',
        nilai_usage='$nilai_usage',
        updated_at='$updated_at'
        WHERE id=$id");
    $_SESSION['edit_berhasil'] = true;
    header("Location: kelola_rating.php");
    exit;
}

// Hapus Rating
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    // Optionally, delete associated image and video files before deleting the record
    $result = mysqli_query($koneksi, "SELECT gambar_ulasan, video_ulasan FROM rating WHERE id=$id");
    $row = mysqli_fetch_assoc($result);
    if ($row) {
        if (!empty($row['gambar_ulasan']) && file_exists('uploads/' . $row['gambar_ulasan'])) {
            unlink('uploads/' . $row['gambar_ulasan']);
        }
        if (!empty($row['video_ulasan']) && file_exists('uploads/' . $row['video_ulasan'])) {
            unlink('uploads/' . $row['video_ulasan']);
        }
    }

    mysqli_query($koneksi, "DELETE FROM rating WHERE id=$id");
    $_SESSION['hapus_berhasil'] = true;
    header("Location: kelola_rating.php");
    exit;
}

// Ambil data rating
$rating = mysqli_query($koneksi, "
    SELECT
        r.id, r.bintang, r.ulasan, r.gambar_ulasan, r.video_ulasan,
        r.nilai_design, r.nilai_flexibility, r.nilai_usage,
        r.created_at, r.updated_at,
        r.id_user, r.id_transaksi, r.id_produk, -- Only id_transaksi
        p.nama_produk,
        u.nama_pengguna
    FROM rating r
    JOIN produk p ON r.id_produk = p.id
    JOIN pengguna u ON r.id_user = u.id
    ORDER BY r.id ASC
");

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
    .alert-danger {
        background-color: #ffe5e5;
        color: #d8000c;
        border: 1px solid #d8000c;
        padding: 10px;
        margin-bottom: 10px;
        border-radius: 4px;
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
        display: flex;
        margin-bottom: 5px;
        font-weight: bold;
    }

    .form-grid input, .form-grid select, .form-grid textarea {
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
        <span>Home</span> / <strong>Kelola Rating</strong>
    </div>

    <div class="card">
        <button class="btn-create" onclick="openModal()">+ Tambah Rating</button>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Bintang</th>
                    <th>Pengguna</th>
                    <th>ID Transaksi</th> <th>Produk</th>
                    <th>Gambar Ulasan</th>
                    <th>Video Ulasan</th>
                    <th>Ulasan</th>
                    <th>Nilai Design</th>
                    <th>Nilai Flexibility</th>
                    <th>Nilai Usage</th>
                    <th>Created</th>
                    <th>Updated</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($data = mysqli_fetch_assoc($rating)): ?>
                    <tr>
                        <td><?= $data['id'] ?></td>
                        <td><?= $data['bintang'] ?></td>
                        <td><?= htmlspecialchars($data['nama_pengguna']) ?></td>
                        <td><?= htmlspecialchars($data['id_transaksi'] ?? 'N/A') ?></td> <td><?= htmlspecialchars($data['nama_produk']) ?></td>
                        <td>
                            <?php if ($data['gambar_ulasan']): ?>
                                <img src="uploads/<?= htmlspecialchars($data['gambar_ulasan']) ?>" width="80" alt="Gambar Ulasan">
                            <?php else: ?>
                                Tidak ada
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($data['video_ulasan']): ?>
                                <video width="80" controls>
                                    <source src="uploads/<?= htmlspecialchars($data['video_ulasan']) ?>" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            <?php else: ?>
                                Tidak ada
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($data['ulasan']) ?></td>
                        <td><?= htmlspecialchars($data['nilai_design']) ?></td>
                        <td><?= htmlspecialchars($data['nilai_flexibility']) ?></td>
                        <td><?= htmlspecialchars($data['nilai_usage']) ?></td>
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

    <?php foreach (['tambah_berhasil' => 'Rating berhasil ditambahkan!', 'edit_berhasil' => 'Rating berhasil diperbarui!', 'hapus_berhasil' => 'Rating berhasil dihapus!'] as $key => $message): ?>
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
<div id="modalTambah" class="modal" <?= $show_modal === 'tambah' ? 'style="display:flex;"' : '' ?>>
    <div class="modal-content">
        <h2>Tambah Rating</h2>

        <?php if ($show_modal === 'tambah' && !empty($errors)): ?>
            <div class="alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                <div>
                    <label>Bintang:</label>
                    <input type="number" name="bintang" min="1" max="5" value="<?= $old_input['bintang'] ?? '' ?>" required>
                </div>

                <div>
                    <label>Pengguna:</label>
                    <select name="id_user" required >
                        <option value="">-- Pilih Pengguna --</option>
                        <?php mysqli_data_seek($pengguna, 0); // Reset pointer for this loop ?>
                        <?php while ($user = mysqli_fetch_assoc($pengguna)): ?>
                            <option value="<?= $user['id'] ?>" <?= ($old_input['id_user'] ?? '') == $user['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($user['nama_pengguna']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div>
                    <label>ID Transaksi:</label> <select name="id_transaksi" required>
                        <option value="">-- Pilih Transaksi ID --</option>
                        <?php mysqli_data_seek($transaksi, 0); // Reset pointer for this loop ?>
                        <?php while ($trx = mysqli_fetch_assoc($transaksi)): ?>
                            <option value="<?= $trx['id'] ?>" <?= ($old_input['id_transaksi'] ?? '') == $trx['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($trx['id']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div>
                    <label>Produk:</label>
                    <select name="id_produk" required >
                        <option value="">-- Pilih Produk --</option>
                        <?php mysqli_data_seek($produk, 0); // Reset pointer for this loop ?>
                        <?php while ($prd = mysqli_fetch_assoc($produk)): ?>
                            <option value="<?= $prd['id'] ?>" <?= ($old_input['id_produk'] ?? '') == $prd['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($prd['nama_produk']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div>
                    <label>Gambar Ulasan:</label>
                    <input type="file" name="gambar_ulasan" accept="image/*">
                </div>

                <div>
                    <label>Video Ulasan:</label> <input type="file" name="video_ulasan" accept="video/*">
                </div>

                <div>
                    <label>Nilai Design:</label> <input type="text" name="nilai_design" value="<?= $old_input['nilai_design'] ?? '' ?>">
                </div>

                <div>
                    <label>Nilai Flexibility:</label> <input type="text" name="nilai_flexibility" value="<?= $old_input['nilai_flexibility'] ?? '' ?>">
                </div>

                <div>
                    <label>Nilai Usage:</label> <input type="text" name="nilai_usage" value="<?= $old_input['nilai_usage'] ?? '' ?>">
                </div>

                <div style="grid-column: span 2;">
                    <label>Ulasan:</label>
                    <textarea name="ulasan" rows="3" style="width: 100%;"><?= $old_input['ulasan'] ?? '' ?></textarea>
                </div>

            </div>

            <div class="modal-buttons">
                <button type="submit" name="tambah" class="btn-create">Tambah</button>
                <button type="button" class="btn-cancel" onclick="closeModal()">Batal</button>
            </div>
        </form>
    </div>
</div>


<div id="modalEdit" class="modal" <?= $show_modal === 'edit' ? 'style="display:flex;"' : '' ?>>
    <div class="modal-content">
        <h2>Edit Rating</h2>

        <?php if ($show_modal === 'edit' && !empty($errors)): ?>
            <div class="alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" id="formEdit" enctype="multipart/form-data">
            <input type="hidden" name="edit_id" id="edit_id" value="<?= $old_input['edit_id'] ?? '' ?>">
            <input type="hidden" name="gambar_lama" id="gambar_lama" value="<?= $old_input['gambar_lama'] ?? '' ?>">
            <input type="hidden" name="video_lama" id="video_lama" value="<?= $old_input['video_lama'] ?? '' ?>">
            <div class="form-grid">
                <div>
                    <label>Bintang:</label>
                    <input type="number" name="edit_bintang" id="edit_bintang" min="1" max="5" value="<?= $old_input['edit_bintang'] ?? '' ?>" required>
                </div>
                <div>
                    <label>Pengguna:</label>
                    <select name="edit_id_user" id="edit_id_user" required>
                        <option value="">-- Pilih Pengguna --</option>
                        <?php mysqli_data_seek($pengguna, 0); // Reset pointer for this loop ?>
                        <?php while ($user = mysqli_fetch_assoc($pengguna)): ?>
                            <option value="<?= $user['id'] ?>" <?= ($old_input['edit_id_user'] ?? '') == $user['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($user['nama_pengguna']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label>ID Transaksi:</label> <select name="edit_id_transaksi" id="edit_id_transaksi" required>
                        <option value="">-- Pilih Transaksi ID --</option>
                        <?php mysqli_data_seek($transaksi, 0); // Reset pointer for this loop ?>
                        <?php while ($trx = mysqli_fetch_assoc($transaksi)): ?>
                            <option value="<?= $trx['id'] ?>" <?= ($old_input['edit_id_transaksi'] ?? '') == $trx['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($trx['id']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label>Produk:</label>
                    <select name="edit_id_produk" id="edit_id_produk" required>
                        <option value="">-- Pilih Produk --</option>
                        <?php mysqli_data_seek($produk, 0); // Reset pointer for this loop ?>
                        <?php while ($prd = mysqli_fetch_assoc($produk)): ?>
                            <option value="<?= $prd['id'] ?>" <?= ($old_input['edit_id_produk'] ?? '') == $prd['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($prd['nama_produk']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label>Gambar Ulasan (Current):</label>
                    <span id="current_gambar_ulasan"></span>
                    <input type="file" name="edit_gambar_ulasan" accept="image/*">
                </div>
                <div>
                    <label>Video Ulasan (Current):</label> <span id="current_video_ulasan"></span>
                    <input type="file" name="edit_video_ulasan" accept="video/*">
                </div>
                <div>
                    <label>Nilai Design:</label> <input type="text" name="edit_nilai_design" id="edit_nilai_design" value="<?= $old_input['edit_nilai_design'] ?? '' ?>">
                </div>
                <div>
                    <label>Nilai Flexibility:</label> <input type="text" name="edit_nilai_flexibility" id="edit_nilai_flexibility" value="<?= $old_input['edit_nilai_flexibility'] ?? '' ?>">
                </div>
                <div>
                    <label>Nilai Usage:</label> <input type="text" name="edit_nilai_usage" id="edit_nilai_usage" value="<?= $old_input['edit_nilai_usage'] ?? '' ?>">
                </div>
                <div style="grid-column: span 2;">
                    <label>Ulasan:</label>
                    <textarea name="edit_ulasan" id="edit_ulasan" rows="3" style="width: 100%;"><?= $old_input['edit_ulasan'] ?? '' ?></textarea>
                </div>
            </div>
            <div class="modal-buttons">
                <button type="submit" name="ubah" class="btn-create">Simpan</button>
                <button type="button" onclick="closeEditModal()" class="btn-cancel">Batal</button>
            </div>
        </form>
    </div>
</div>

<div id="modalHapus" class="modal">
    <div class="modal-content" style="text-align:center;">
        <h2 style="color:red;">Yakin ingin menghapus rating ini?</h2>
        <form method="GET">
            <input type="hidden" name="hapus" id="hapus_id">
            <div class="modal-buttons">
                <button type="submit" class="btn-create">Ya, Hapus</button>
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
    document.getElementById('edit_bintang').value = data.bintang;
    document.getElementById('edit_id_user').value = data.id_user;
    document.getElementById('edit_id_transaksi').value = data.id_transaksi; // Populate new field
    document.getElementById('edit_id_produk').value = data.id_produk;
    document.getElementById('edit_ulasan').value = data.ulasan;

    // Set values for new fields
    document.getElementById('edit_nilai_design').value = data.nilai_design;
    document.getElementById('edit_nilai_flexibility').value = data.nilai_flexibility;
    document.getElementById('edit_nilai_usage').value = data.nilai_usage;

    // Handle current image and video display in edit modal
    document.getElementById('gambar_lama').value = data.gambar_ulasan;
    const currentGambarUlasan = document.getElementById('current_gambar_ulasan');
    if (data.gambar_ulasan) {
        currentGambarUlasan.innerHTML = `<img src="uploads/${data.gambar_ulasan}" width="50" style="vertical-align:middle; margin-right: 5px;">`;
    } else {
        currentGambarUlasan.innerHTML = `Tidak ada gambar.`;
    }

    document.getElementById('video_lama').value = data.video_ulasan;
    const currentVideoUlasan = document.getElementById('current_video_ulasan');
    if (data.video_ulasan) {
        currentVideoUlasan.innerHTML = `<video width="50" controls style="vertical-align:middle; margin-right: 5px;"><source src="uploads/${data.video_ulasan}" type="video/mp4"></video>`;
    } else {
        currentVideoUlasan.innerHTML = `Tidak ada video.`;
    }
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

// Reset old input and errors when closing modals normally
document.getElementById('modalTambah').addEventListener('click', function(event) {
    if (event.target === this) { // Only close if clicking outside modal content
        this.style.display = 'none';
        // Clear session storage for next time it's opened
        <?php if (isset($_SESSION['old_input']) || isset($_SESSION['errors'])): ?>
            fetch(window.location.pathname, { method: 'POST', body: new URLSearchParams('clear_session=true') });
        <?php endif; ?>
    }
});

document.getElementById('modalEdit').addEventListener('click', function(event) {
    if (event.target === this) { // Only close if clicking outside modal content
        this.style.display = 'none';
        // Clear session storage for next time it's opened
        <?php if (isset($_SESSION['old_input']) || isset($_SESSION['errors'])): ?>
            fetch(window.location.pathname, { method: 'POST', body: new URLSearchParams('clear_session=true') });
        <?php endif; ?>
    }
});
</script>