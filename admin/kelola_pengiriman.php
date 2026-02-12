<?php
session_start();
if (!isset($_SESSION['admin_nama'])) {
    header("Location: loginadmin.php");
    exit;
}
include 'koneksi.php';

$result_transaksi = mysqli_query($koneksi, "SELECT id, id_user, total_transaksi FROM transaksi ORDER BY id ASC");
$result_user = mysqli_query($koneksi, "SELECT id, nama_pengguna FROM pengguna ORDER BY nama_pengguna ASC");
// Query to fetch alamat_pengiriman data for dropdowns (STILL NEEDED for descriptive dropdown options)
$result_alamat_pengiriman = mysqli_query($koneksi, "SELECT id_alamat, alamat_lengkap, kode_pos FROM alamat_pengiriman ORDER BY id_alamat ASC");

function generateNoResi($length = 10) {
    $prefix = 'ShoP';
    $random = strtoupper(bin2hex(random_bytes($length / 2)));
    return $prefix . $random;
}

// Tambah Pengiriman
if (isset($_POST['tambah'])) {
    $id_transaksi = (int)$_POST['id_transaksi'];
    $id_user = (int)$_POST['id_user'];
    $id_alamat = (int)$_POST['id_alamat']; // Using id_alamat from the dropdown
    $ekspedisi = mysqli_real_escape_string($koneksi, $_POST['ekspedisi']);
    $status_pengiriman = mysqli_real_escape_string($koneksi, $_POST['status_pengiriman']);
    // $no_resi is generated, not taken from POST for new entry
    $tanggal_dikirim = $_POST['tanggal_dikirim'] ?: NULL;
    $tanggal_diterima = $_POST['tanggal_diterima'] ?: NULL;

    do {
        $no_resi = generateNoResi();
        $cek = mysqli_query($koneksi, "SELECT id FROM pengiriman WHERE no_resi = '$no_resi'");
    } while (mysqli_num_rows($cek) > 0);

    $query = "INSERT INTO pengiriman (id_transaksi, id_user, id_alamat, ekspedisi, status_pengiriman, no_resi, tanggal_dikirim, tanggal_diterima)
              VALUES ($id_transaksi, $id_user, $id_alamat, '$ekspedisi', '$status_pengiriman', '$no_resi', " .
              ($tanggal_dikirim ? "'$tanggal_dikirim'" : "NULL") . ", " .
              ($tanggal_diterima ? "'$tanggal_diterima'" : "NULL") . ")";

    mysqli_query($koneksi, $query);
    $_SESSION['tambah_berhasil'] = true;
    header("Location: kelola_pengiriman.php");
    exit;
}

// Ubah Pengiriman
if (isset($_POST['ubah'])) {
    $id = (int)$_POST['edit_id'];
    $id_transaksi = (int)$_POST['edit_id_transaksi'];
    $id_user = (int)$_POST['edit_id_user'];
    $id_alamat = (int)$_POST['edit_id_alamat']; // Using edit_id_alamat from the dropdown
    $ekspedisi = mysqli_real_escape_string($koneksi, $_POST['edit_ekspedisi']);
    $status_pengiriman = mysqli_real_escape_string($koneksi, $_POST['edit_status_pengiriman']);
    $no_resi = mysqli_real_escape_string($koneksi, $_POST['edit_no_resi']);
    $tanggal_dikirim = $_POST['edit_tanggal_dikirim'] ?: NULL;
    $tanggal_diterima = $_POST['edit_tanggal_diterima'] ?: NULL;

    $query = "UPDATE pengiriman SET
              id_transaksi = $id_transaksi,
              id_user = $id_user,
              id_alamat = $id_alamat,
              ekspedisi = '$ekspedisi',
              status_pengiriman = '$status_pengiriman',
              no_resi = '$no_resi',
              tanggal_dikirim = " . ($tanggal_dikirim ? "'$tanggal_dikirim'" : "NULL") . ",
              tanggal_diterima = " . ($tanggal_diterima ? "'$tanggal_diterima'" : "NULL") . "
              WHERE id = $id";

    mysqli_query($koneksi, $query);
    $_SESSION['edit_berhasil'] = true;
    header("Location: kelola_pengiriman.php");
    exit;
}

// Hapus Pengiriman
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM pengiriman WHERE id=$id");
    $_SESSION['hapus_berhasil'] = true;
    header("Location: kelola_pengiriman.php");
    exit;
}

// Ambil data pengiriman - Changed to only select from 'pengiriman' table, as 'alamat_lengkap'/'kode_pos' are no longer displayed
$pengiriman = mysqli_query($koneksi, "SELECT p.* FROM pengiriman p ORDER BY p.id ASC");

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
        <span>Home</span> / <strong>Kelola Pengiriman</strong>
    </div>

    <div class="card">
        <button class="btn-create" onclick="openModal()">+ Tambah Pengiriman</button>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ID Transaksi</th>
                    <th>ID User</th>
                    <th>ID Alamat</th> <th>Ekspedisi</th>
                    <th>Status</th>
                    <th>No Resi</th>
                    <th>Tanggal Dikirim</th>
                    <th>Tanggal Diterima</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($data = mysqli_fetch_assoc($pengiriman)): ?>
                    <tr>
                        <td><?= $data['id'] ?></td>
                        <td><?= $data['id_transaksi'] ?></td>
                        <td><?= $data['id_user'] ?></td>
                        <td><?= $data['id_alamat'] ?></td> <td><?= htmlspecialchars($data['ekspedisi']) ?></td>
                        <td><?= htmlspecialchars($data['status_pengiriman']) ?></td>
                        <td><?= htmlspecialchars($data['no_resi']) ?></td>
                        <td><?= $data['tanggal_dikirim'] ?></td>
                        <td><?= $data['tanggal_diterima'] ?></td>
                        <td class="action-icons">
                            <i class="bi bi-pencil-fill" onclick='openEditModal(<?= json_encode($data) ?>)'></i>
                            <i class="bi bi-trash-fill" onclick="openDeleteModal(<?= $data['id'] ?>)"></i>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <?php foreach (['tambah_berhasil' => 'Pengiriman berhasil ditambahkan!', 'edit_berhasil' => 'Pengiriman berhasil diperbarui!', 'hapus_berhasil' => 'Pengiriman berhasil dihapus!'] as $key => $message): ?>
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

<div id="modalTambah" class="modal">
    <div class="modal-content">
        <h2>Tambah Pengiriman</h2>
        <form method="POST">
            <div class="form-grid">
                <div>
                    <label>ID Transaksi:</label>
                    <select name="id_transaksi" required>
    <option value="" disabled selected>Pilih Transaksi</option>
    <?php mysqli_data_seek($result_transaksi, 0); // Reset result pointer ?>
    <?php while ($row = mysqli_fetch_assoc($result_transaksi)): ?>
        <option value="<?= $row['id'] ?>">
            ID: <?= $row['id'] ?> - User ID: <?= $row['id_user'] ?> - Total: Rp<?= number_format($row['total_transaksi'], 0, ',', '.') ?>
        </option>
    <?php endwhile; ?>
</select>

                </div>
                <div>
                    <label>ID User:</label>
                    <select name="id_user" required>
    <option value="" disabled selected>Pilih User</option>
    <?php
    mysqli_data_seek($result_user, 0); // Reset result pointer agar bisa digunakan lagi di modal edit
    while ($user = mysqli_fetch_assoc($result_user)): ?>
        <option value="<?= $user['id'] ?>">
            <?= htmlspecialchars($user['nama_pengguna']) ?> (ID: <?= $user['id'] ?>)
        </option>
    <?php endwhile; ?>
</select>

                </div>
                <div>
                    <label>Alamat Pengiriman:</label>
                    <select name="id_alamat" required>
    <option value="" disabled selected>Pilih Alamat</option>
    <?php
    mysqli_data_seek($result_alamat_pengiriman, 0); // Reset result pointer
    while ($alamat = mysqli_fetch_assoc($result_alamat_pengiriman)): ?>
        <option value="<?= $alamat['id_alamat'] ?>">
            ID: <?= $alamat['id_alamat'] ?> - <?= htmlspecialchars($alamat['alamat_lengkap']) ?> (Kode Pos: <?= htmlspecialchars($alamat['kode_pos']) ?>)
        </option>
    <?php endwhile; ?>
</select>

                </div>
                <div>
                    <label>Ekspedisi:</label>
                    <input type="text" name="ekspedisi" required>
                </div>
                <div>
                    <label>Status Pengiriman:</label>
                    <select name="status_pengiriman" required>
    <option value="" disabled selected>Pilih Status</option>
    <option value="diproses">Diproses</option>
    <option value="dikemas">Dikemas</option>
    <option value="dikirim">Dikirim</option>
    <option value="selesai">Selesai</option>
    <option value="gagal">Gagal</option>
    <option value="dibatalkan">Dibatalkan</option>
</select>

                </div>
                <div>
                    <label>No Resi:</label>
                    <input type="hidden" name="no_resi">
                </div>
                <div>
                    <label>Tanggal Dikirim:</label>
                    <input type="date" name="tanggal_dikirim">
                </div>
                <div>
                    <label>Tanggal Diterima:</label>
                    <input type="date" name="tanggal_diterima">
                </div>
            </div>
            <div class="modal-buttons">
                <button type="submit" name="tambah" class="btn-create">Tambahkan</button>
                <button type="button" onclick="closeModal()" class="btn-cancel">Batal</button>
            </div>
        </form>
    </div>
</div>

<div id="modalEdit" class="modal">
    <div class="modal-content">
        <h2>Edit Pengiriman</h2>
        <form method="POST" id="formEdit">
            <input type="hidden" name="edit_id" id="edit_id">
            <div class="form-grid">
                <div>
                    <label>ID Transaksi:</label>
                    <select name="edit_id_transaksi" id="edit_id_transaksi" required>
    <option value="" disabled>Pilih Transaksi</option>
    <?php
    mysqli_data_seek($result_transaksi, 0);
    while ($row = mysqli_fetch_assoc($result_transaksi)): ?>
        <option value="<?= $row['id'] ?>">
            ID: <?= $row['id'] ?> - User ID: <?= $row['id_user'] ?> - Total: Rp<?= number_format($row['total_transaksi'], 0, ',', '.') ?>
        </option>
    <?php endwhile; ?>
</select>
                </div>
                <div>
                    <label>ID User:</label>
                    <select name="edit_id_user" id="edit_id_user" required>
    <option value="" disabled>Pilih User</option>
    <?php
    mysqli_data_seek($result_user, 0);
    while ($user = mysqli_fetch_assoc($result_user)): ?>
        <option value="<?= $user['id'] ?>">
            <?= htmlspecialchars($user['nama_pengguna']) ?> (ID: <?= $user['id'] ?>)
        </option>
    <?php endwhile; ?>
</select>
                </div>
                <div>
                    <label>Alamat Pengiriman:</label>
                    <select name="edit_id_alamat" id="edit_id_alamat" required>
    <option value="" disabled>Pilih Alamat</option>
    <?php
    mysqli_data_seek($result_alamat_pengiriman, 0); // Reset result pointer
    while ($alamat = mysqli_fetch_assoc($result_alamat_pengiriman)): ?>
        <option value="<?= $alamat['id_alamat'] ?>">
            ID: <?= $alamat['id_alamat'] ?> - <?= htmlspecialchars($alamat['alamat_lengkap']) ?> (Kode Pos: <?= htmlspecialchars($alamat['kode_pos']) ?>)
        </option>
    <?php endwhile; ?>
</select>
                </div>
                <div>
                    <label>Ekspedisi:</label>
                    <input type="text" name="edit_ekspedisi" id="edit_ekspedisi" required>
                </div>
                <div>
                    <label>Status Pengiriman:</label>
                   <select name="edit_status_pengiriman" id="edit_status_pengiriman" required>
    <option value="" disabled>Pilih Status</option>
    <option value="diproses">Diproses</option>
    <option value="dikemas">Dikemas</option>
    <option value="dikirim">Dikirim</option>
    <option value="selesai">Selesai</option>
    <option value="gagal">Gagal</option>
    <option value="dibatalkan">Dibatalkan</option>
</select>

                </div>
                <div>
                    <label>No Resi:</label>
                    <input type="text" name="edit_no_resi" id="edit_no_resi">
                </div>
                <div>
                    <label>Tanggal Dikirim:</label>
                    <input type="date" name="edit_tanggal_dikirim" id="edit_tanggal_dikirim">
                </div>
                <div>
                    <label>Tanggal Diterima:</label>
                    <input type="date" name="edit_tanggal_diterima" id="edit_tanggal_diterima">
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
        <h2>Hapus Pengiriman</h2>
        <p>Apakah Anda yakin ingin menghapus data pengiriman ini?</p>
        <div class="modal-buttons" style="justify-content:center;">
            <a href="#" id="hapusLink" class="btn-create" style="text-decoration:none;">Hapus</a>
            <button type="button" onclick="closeDeleteModal()" class="btn-cancel">Batal</button>
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
        const selectTransaksi = document.getElementById('edit_id_transaksi');
        selectTransaksi.value = data.id_transaksi;
        const selectUser = document.getElementById('edit_id_user');
        selectUser.value = data.id_user;
        const selectAlamat = document.getElementById('edit_id_alamat');
        selectAlamat.value = data.id_alamat;

        document.getElementById('edit_ekspedisi').value = data.ekspedisi;
        document.getElementById('edit_status_pengiriman').value = data.status_pengiriman;
        document.getElementById('edit_no_resi').value = data.no_resi;
        document.getElementById('edit_tanggal_dikirim').value = data.tanggal_dikirim;
        document.getElementById('edit_tanggal_diterima').value = data.tanggal_diterima;
    }

    function closeEditModal() {
        document.getElementById('modalEdit').style.display = 'none';
    }

    function openDeleteModal(id) {
        document.getElementById('modalHapus').style.display = 'flex';
        document.getElementById('hapusLink').href = 'kelola_pengiriman.php?hapus=' + id;
    }
    function closeDeleteModal() {
        document.getElementById('modalHapus').style.display = 'none';
    }

    // Modal success countdown close
    const successModal = document.getElementById('successModal');
    if (successModal) {
        let countdownElem = document.getElementById('countdown');
        let count = 5;
        let interval = setInterval(() => {
            count--;
            if (countdownElem) countdownElem.innerText = count;
            if (count === 0) {
                successModal.style.display = 'none';
                clearInterval(interval);
            }
        }, 1000);
    }
</script>