<?php
session_start();
if (!isset($_SESSION['admin_nama'])) {
    header("Location: loginadmin.admin.php");
    exit;
}
include 'koneksi.php';

$old = ['id_user' => '', 'id_produk' => '', 'id_transaksi' => '', 'masa_garansi' => '', 'tanggal_berakhir' => '', 'status_garansi' => '', 'keterangan' => '', 'bukti_claim' => ''];
$errors = [];

$resultUser = mysqli_query($koneksi, "SELECT id, nama_pengguna FROM pengguna ORDER BY nama_pengguna ASC");
$resultProduk = mysqli_query($koneksi,"SELECT id, nama_produk FROM produk ORDER BY nama_produk ASC");
$resultTransaksi = mysqli_query($koneksi, "SELECT id FROM transaksi ORDER BY id ASC");
$users = mysqli_fetch_all($resultUser, MYSQLI_ASSOC);
$produks = mysqli_fetch_all($resultProduk, MYSQLI_ASSOC);
$transaksis = mysqli_fetch_all($resultTransaksi, MYSQLI_ASSOC);


// Tambah Garansi
if (isset($_POST['tambah'])) {
    $id_user = $_POST['id_user'];
    $id_produk = $_POST['id_produk'];
    $id_transaksi = $_POST['id_transaksi'];
    $masa_garansi = $_POST['masa_garansi'];
    $tanggal_berakhir = $_POST['tanggal_berakhir'];
    $status_garansi = $_POST['status_garansi'];
    $keterangan = $_POST['keterangan'];

    $bukti_claim = '';
    if ($_FILES['bukti_claim']['name']) {
        $target_dir = "uploads/garansi/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $bukti_claim = $target_dir . basename($_FILES["bukti_claim"]["name"]);
        move_uploaded_file($_FILES["bukti_claim"]["tmp_name"], $bukti_claim);
    }

    mysqli_query($koneksi, "INSERT INTO garansi (id_user, id_produk, id_transaksi, masa_garansi, tanggal_berakhir, status_garansi, keterangan, bukti_claim, created_at, updated_at) VALUES ('$id_user','$id_produk','$id_transaksi','$masa_garansi','$tanggal_berakhir','$status_garansi','$keterangan','$bukti_claim',NOW(),NOW())");
    $_SESSION['tambah_berhasil'] = true;
    header("Location: kelola_garansi.php");
    exit;
}

// Ubah Garansi
if (isset($_POST['ubah'])) {
    $id = $_POST['edit_id'];
    $status_garansi = $_POST['edit_status_garansi']; // Hanya status_garansi yang akan diubah

    // Ambil data garansi yang sudah ada untuk mendapatkan nilai-nilai lainnya
    $existing_garansi_query = mysqli_query($koneksi, "SELECT * FROM garansi WHERE id=$id");
    $existing_data = mysqli_fetch_assoc($existing_garansi_query);

    // Tetap gunakan nilai-nilai lama untuk field yang tidak diedit
    $id_user = $existing_data['id_user'];
    $id_produk = $existing_data['id_produk'];
    $id_transaksi = $existing_data['id_transaksi'];
    $masa_garansi = $existing_data['masa_garansi'];
    $tanggal_berakhir = $existing_data['tanggal_berakhir'];
    $keterangan = $existing_data['keterangan'];
    $bukti_claim = $existing_data['bukti_claim']; // Tetap gunakan bukti_claim yang sudah ada

    $query = "UPDATE garansi SET id_user='$id_user', id_produk='$id_produk', id_transaksi='$id_transaksi', masa_garansi='$masa_garansi', tanggal_berakhir='$tanggal_berakhir', status_garansi='$status_garansi', keterangan='$keterangan', bukti_claim='$bukti_claim', updated_at=NOW() WHERE id=$id";
    
    // Perhatikan: bagian upload bukti_claim dihilangkan karena tidak akan diedit
    // if ($_FILES['edit_bukti_claim']['name']) {
    //     $target_dir = "uploads/garansi/";
    //     $bukti_claim = $target_dir . basename($_FILES["edit_bukti_claim"]["name"]);
    //     move_uploaded_file($_FILES["edit_bukti_claim"]["tmp_name"], $bukti_claim);
    //     $query .= ", bukti_claim='$bukti_claim'";
    // }

    mysqli_query($koneksi, $query);
    $_SESSION['edit_berhasil'] = true;
    header("Location: kelola_garansi.php");
    exit;
}

// Hapus Garansi
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM garansi WHERE id=$id");
    $_SESSION['hapus_berhasil'] = true;
    header("Location: kelola_garansi.php");
    exit;
}

// Ambil data Garansi
$garansi = mysqli_query($koneksi, "SELECT * FROM garansi ORDER BY id ASC");
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
    .form-grid input, .form-grid select, .form-grid textarea {
        width: 100%;
        padding: 8px;
        border-radius: 6px;
        border: 1px solid #ccc;
        font-size: 14px;
    }
    /* Style for readonly inputs */
    .form-grid input[readonly], .form-grid select[readonly], .form-grid textarea[readonly] {
        background-color: #f0f0f0; /* Light gray background */
        cursor: not-allowed; /* Indicate it's not editable */
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
    <div class="breadcrumb"><span>Home</span> / <strong>Kelola Garansi</strong></div>

    <div class="card">
        <button class="btn-create" onclick="openModal()">+ Tambah Garansi</button>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>ID User</th>
            <th>ID Produk</th>
            <th>ID Transaksi</th>
            <th>Masa Garansi</th>
            <th>Tanggal Berakhir</th>
            <th>Status</th>
            <th>Keterangan</th>
            <th>Bukti Claim</th>
            <th>Created At</th>
            <th>Updated At</th>
            <th>ACTIONS</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($data = mysqli_fetch_assoc($garansi)): ?>
            <tr>
                <td><?= $data['id'] ?></td>
                <td><?= $data['id_user'] ?></td>
                <td><?= $data['id_produk'] ?></td>
                <td><?= $data['id_transaksi'] ?></td>
                <td><?= $data['masa_garansi'] ?></td>
                <td><?= $data['tanggal_berakhir'] ?></td>
                <td><?= $data['status_garansi'] ?></td>
                <td><?= htmlspecialchars($data['keterangan']) ?></td>
                <td>
                    <?php if ($data['bukti_claim']): ?>
                        <a href="uploads/garansi/<?= $data['bukti_claim'] ?>" target="_blank">Lihat</a>
                    <?php else: ?>
                        Tidak ada
                    <?php endif; ?>
                </td>
                <td><?= $data['created_at'] ?></td>
                <td><?= $data['updated_at'] ?></td>
                <td class="action-icons">
                    <i class="bi bi-pencil-fill" onclick='openEditModal(<?= json_encode($data) ?>, <?= json_encode($users) ?>, <?= json_encode($produks) ?>, <?= json_encode($transaksis) ?>)'></i>
                    <i class="bi bi-trash-fill" onclick="openDeleteModal(<?= $data['id'] ?>)"></i>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

    </div>

    <?php foreach (['tambah_berhasil' => 'Data garansi berhasil ditambahkan!', 'edit_berhasil' => 'Data garansi berhasil diperbarui!', 'hapus_berhasil' => 'Data garansi berhasil dihapus!'] as $key => $message): ?>
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
        <h2>Tambah Garansi</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                <div>
                    <label>ID User:</label>
                    <select name="id_user" required>
                        <option value="">--Pilih User--</option>
                        <?php foreach ($users as $rowUser) : ?>
                            <option value="<?= $rowUser['id'] ?>"><?= htmlspecialchars($rowUser['nama_pengguna']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>ID Produk:</label>
                    <select name="id_produk" required>
                        <option value="">--Pilih Produk--</option>
                        <?php foreach ($produks as $rowProduk) : ?>
                            <option value="<?= $rowProduk['id'] ?>"><?= htmlspecialchars($rowProduk['nama_produk']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>ID Transaksi:</label>
                    <select name="id_transaksi" required>
                           <option value="">--Pilih Transaksi--</option>
                        <?php foreach ($transaksis as $rowTransaksi) : ?>
                            <option value="<?= $rowTransaksi['id'] ?>">Transaksi <?= $rowTransaksi['id'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>Masa Garansi:</label>
                    <select name="masa_garansi" id="masa_garansi" required>
                        <option value="">--Pilih Masa Garansi--</option>
                        <option value="1 bulan">1 Bulan</option>
                        <option value="3 bulan">3 Bulan</option>
                        <option value="6 bulan">6 Bulan</option>
                        <option value="12 bulan">12 Bulan</option>
                    </select>
                </div>
                <div>
                    <label>Tanggal Berakhir:</label>
                    <input type="date" name="tanggal_berakhir" id="tanggal_berakhir" required>
                </div>
                <div>
                    <label>Status Garansi:</label>
                    <select name="status_garansi" id="status_garansi" required>
                        <option value="aktif">Aktif</option>
                        <option value="diproses">Diproses</option>
                        <option value="disetujui">Disetujui</option>
                        <option value="ditolak">Ditolak</option>
                        <option value="kadaluarsa">Kadaluarsa</option>
                    </select>
                </div>
                <div>
                    <label>Keterangan:</label>
                    <textarea name="keterangan" id="keterangan" rows="3" style="width: 100%;"></textarea>
                </div>
                <div>
                    <label> Bukti Claim :</label>
                    <input type="file" name="bukti_claim">
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
        <h2>Edit Garansi</h2>
        <form method="POST" id="formEdit" enctype="multipart/form-data">
            <input type="hidden" name="edit_id" id="edit_id">
            <div class="form-grid">
                <div>
                    <label>ID Garansi:</label>
                    <input type="text" id="display_id" readonly>
                </div>
                <div>
                    <label>User:</label>
                    <input type="text" id="display_id_user_name" readonly>
                    <input type="hidden" name="edit_id_user" id="edit_id_user"> </div>
                <div>
                    <label>Produk:</label>
                    <input type="text" id="display_id_produk_name" readonly>
                    <input type="hidden" name="edit_id_produk" id="edit_id_produk"> </div>
                <div>
                    <label>Transaksi:</label>
                    <input type="text" id="display_id_transaksi" readonly>
                    <input type="hidden" name="edit_id_transaksi" id="edit_id_transaksi"> </div>
                <div>
                    <label>Masa Garansi:</label>
                    <input type="text" id="display_masa_garansi" readonly>
                    <input type="hidden" name="edit_masa_garansi" id="edit_masa_garansi"> </div>
                <div>
                    <label>Tanggal Berakhir:</label>
                    <input type="date" id="display_tanggal_berakhir" readonly>
                    <input type="hidden" name="edit_tanggal_berakhir" id="edit_tanggal_berakhir"> </div>
                <div>
                    <label>Status Garansi:</label>
                    <select name="edit_status_garansi" id="edit_status_garansi" required>
                        <option value="aktif">Aktif</option>
                        <option value="diproses">Diproses</option>
                        <option value="disetujui">Disetujui</option>
                        <option value="ditolak">Ditolak</option>
                        <option value="kadaluarsa">Kadaluarsa</option>
                    </select>
                </div>
                <div>
                    <label>Keterangan:</label>
                    <textarea name="edit_keterangan" id="display_keterangan" rows="3" style="width: 100%;" readonly></textarea>
                    <input type="hidden" name="edit_keterangan_hidden" id="edit_keterangan"> </div>
                <div>
                    <label>Bukti Claim:</label>
                    <span id="display_bukti_claim_link"></span>
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
        <h2 style="color:red;">Yakin ingin menghapus data garansi ini?</h2>
        <form method="GET" action="">
            <input type="hidden" name="hapus" id="hapus_id">
            <div class="modal-buttons">
                <button type="submit" class="btn-create">Ya, Hapus</button>
                <button type="button" onclick="closeDeleteModal()" class="btn-cancel">Batal</button>
            </div>
        </form>
    </div>
</div>


<script>
function openModal() { document.getElementById('modalTambah').style.display = 'flex'; }
function closeModal() { document.getElementById('modalTambah').style.display = 'none'; }

function openEditModal(data, users, produks, transaksis) {
    document.getElementById('modalEdit').style.display = 'flex';
    document.getElementById('edit_id').value = data.id;

    // Set display values for readonly fields
    document.getElementById('display_id').value = data.id;
    document.getElementById('display_masa_garansi').value = data.masa_garansi;
    document.getElementById('display_tanggal_berakhir').value = data.tanggal_berakhir;
    document.getElementById('display_keterangan').value = data.keterangan;

    // Set hidden input values for backend submission
    document.getElementById('edit_id_user').value = data.id_user;
    document.getElementById('edit_id_produk').value = data.id_produk;
    document.getElementById('edit_id_transaksi').value = data.id_transaksi;
    document.getElementById('edit_masa_garansi').value = data.masa_garansi;
    document.getElementById('edit_tanggal_berakhir').value = data.tanggal_berakhir;
    document.getElementById('edit_keterangan').value = data.keterangan; // Ini tetap hidden input untuk keterangan

    // Populate user name
    const userName = users.find(user => user.id == data.id_user);
    document.getElementById('display_id_user_name').value = userName ? userName.nama_pengguna : 'N/A';

    // Populate product name
    const productName = produks.find(produk => produk.id == data.id_produk);
    document.getElementById('display_id_produk_name').value = productName ? productName.nama_produk : 'N/A';

    // Populate transaction ID display
    const transactionIdDisplay = transaksis.find(transaksi => transaksi.id == data.id_transaksi);
    document.getElementById('display_id_transaksi').value = transactionIdDisplay ? `Transaksi ${transactionIdDisplay.id}` : 'N/A';


    // Set status garansi dropdown
    document.getElementById('edit_status_garansi').value = data.status_garansi;

    // Set bukti claim link
    const buktiClaimLink = document.getElementById('display_bukti_claim_link');
    if (data.bukti_claim) {
        buktiClaimLink.innerHTML = `<a href="uploads/garansi/${data.bukti_claim}" target="_blank">Lihat Bukti</a>`;
    } else {
        buktiClaimLink.innerHTML = `Tidak ada`;
    }
}

function closeEditModal() { document.getElementById('modalEdit').style.display = 'none'; }
function openDeleteModal(id) {
    document.getElementById('hapus_id').value = id;
    document.getElementById('modalHapus').style.display = 'flex';
}
function closeDeleteModal() { document.getElementById('modalHapus').style.display = 'none'; }
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