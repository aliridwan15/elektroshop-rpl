<?php
session_start();
if (!isset($_SESSION['admin_nama'])) {
    header("Location: loginadmin.php");
    exit;
}
include 'koneksi.php';

// Ambil kategori untuk dropdown
$kategori_list_query = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY nama_kategori ASC");
// We need to clone the result set for the edit modal, as mysqli_fetch_assoc consumes it.
$kategori_list_for_edit = [];
mysqli_data_seek($kategori_list_query, 0); // Reset pointer
while ($row = mysqli_fetch_assoc($kategori_list_query)) {
    $kategori_list_for_edit[] = $row;
}
mysqli_data_seek($kategori_list_query, 0); // Reset pointer again for the add modal loop

// Define product types
$jenis_produk_options = [
    'wearable',
    'pc',
    'smart_home',
    'camera',
    'storage',
    'networking',
    'gaming',
    'entertainment',
    'office'
];

// Tambah Produk
if (isset($_POST['tambah'])) {
    $nama_produk = $_POST['nama_produk'];
    $id_kategori = $_POST['id_kategori'];
    $stok = $_POST['stok'];
    $harga = $_POST['harga'];
    $berat = $_POST['berat'];
    $warna = $_POST['warna'];
    $masa_garansi = $_POST['masa_garansi']; // New field
    $jenis_produk = $_POST['jenis_produk'];
    $deskripsi = $_POST['deskripsi'];
    $gambar = $_FILES['gambar']['name'];
    $tmp_name = $_FILES['gambar']['tmp_name'];
    move_uploaded_file($tmp_name, "./uploads/" . $gambar);

    mysqli_query($koneksi, "INSERT INTO produk (nama_produk, gambar, deskripsi, id_kategori, stok, harga, berat, warna, masa_garansi, jenis_produk)
        VALUES ('$nama_produk', '$gambar', '$deskripsi', '$id_kategori', '$stok', '$harga', '$berat', '$warna', '$masa_garansi', '$jenis_produk')");
    $_SESSION['tambah_berhasil'] = true;
    header("Location: kelola_produk.php");
    exit;
}

// Edit Produk
if (isset($_POST['ubah'])) {
    $id = $_POST['edit_id'];
    $nama_produk = $_POST['edit_nama_produk'];
    $id_kategori = $_POST['edit_id_kategori'];
    $stok = $_POST['edit_stok'];
    $harga = $_POST['edit_harga'];
    $berat = $_POST['edit_berat'];
    $warna = $_POST['edit_warna'];
    $masa_garansi = $_POST['edit_masa_garansi']; // New field
    $jenis_produk = $_POST['edit_jenis_produk'];
    $deskripsi = $_POST['edit_deskripsi'];
    $gambar = $_FILES['edit_gambar']['name'];
    $query_gambar = '';

    if ($gambar) {
        $tmp_name = $_FILES['edit_gambar']['tmp_name'];
        move_uploaded_file($tmp_name, "./uploads/" . $gambar);
        $query_gambar = ", gambar='$gambar'";
    }

    mysqli_query($koneksi, "UPDATE produk SET
        nama_produk='$nama_produk',
        deskripsi='$deskripsi',
        id_kategori='$id_kategori',
        stok='$stok',
        harga='$harga',
        berat='$berat',
        warna='$warna',
        masa_garansi='$masa_garansi',
        jenis_produk='$jenis_produk'
        $query_gambar
        WHERE id=$id");

    $_SESSION['edit_berhasil'] = true;
    header("Location: kelola_produk.php");
    exit;
}

// Hapus Produk
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM produk WHERE id=$id");
    $_SESSION['hapus_berhasil'] = true;
    header("Location: kelola_produk.php");
    exit;
}

// Ambil data produk
$produk = mysqli_query($koneksi, "SELECT produk.*, kategori.nama_kategori
    FROM produk
    JOIN kategori ON produk.id_kategori = kategori.id
    ORDER BY produk.id ASC");

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

    .form-grid input, .form-grid select, .form-grid textarea { /* Added textarea */
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
        <span>Home</span> / <strong>Kelola Produk</strong>
    </div>

    <div class="card">
        <button class="btn-create" onclick="openModal()">+ Tambah Produk</button>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Kategori</th>
                    <th>Stok</th>
                    <th>Harga</th>
                    <th>Deskripsi</th>
                    <th>Berat</th>
                    <th>Warna</th>
                    <th>Masa Garansi</th> <th>Jenis Produk</th>
                    <th>Gambar</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($produk)): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['nama_produk']) ?></td>
                    <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
                    <td><?= $row['stok'] ?></td>
                    <td>Rp<?= number_format($row['harga'], 0, ',', '.') ?></td>
                    <td><?= nl2br(htmlspecialchars($row['deskripsi'])) ?></td>
                    <td><?= $row['berat'] ?> kg</td>
                    <td><?= htmlspecialchars($row['warna']) ?></td>
                    <td><?= htmlspecialchars($row['masa_garansi']) ?></td> <td><?= htmlspecialchars($row['jenis_produk']) ?></td>
                    <td>
                        <img src="uploads/<?= htmlspecialchars($row['gambar']) ?>"
                             alt="<?= htmlspecialchars($row['nama_produk']) ?>"
                             style="max-width: 50px; max-height: 50px; object-fit: cover; border-radius: 4px;">
                    </td>
                    <td class="action-icons">
                        <i class="bi bi-pencil-fill" onclick='openEditModal(<?= json_encode($row) ?>)'></i>
                        <i class="bi bi-trash-fill" onclick="openDeleteModal(<?= $row['id'] ?>)"></i>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php include 'resource/footeradmin.php'; ?>
    <?php if (isset($_SESSION['tambah_berhasil'])): ?>
        <div id="successModal" class="modal" style="display:flex;">
            <div class="modal-content" style="text-align:center;">
                <h2 style="color:green;">Produk berhasil ditambahkan!</h2>
                <p>Menutup dalam <span id="countdown">5</span> detik...</p>
            </div>
        </div>
        <?php unset($_SESSION['tambah_berhasil']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['edit_berhasil'])): ?>
        <div id="successModal" class="modal" style="display:flex;">
            <div class="modal-content" style="text-align:center;">
                <h2 style="color:green;">Produk berhasil diperbarui!</h2>
                <p>Menutup dalam <span id="countdown">5</span> detik...</p>
            </div>
        </div>
        <?php unset($_SESSION['edit_berhasil']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['hapus_berhasil'])): ?>
        <div id="successModal" class="modal" style="display:flex;">
            <div class="modal-content" style="text-align:center;">
                <h2 style="color:green;">Produk berhasil dihapus!</h2>
                <p>Menutup dalam <span id="countdown">5</span> detik...</p>
            </div>
        </div>
        <?php unset($_SESSION['hapus_berhasil']); ?>
    <?php endif; ?>
</div>

<div id="modalTambah" class="modal">
    <div class="modal-content">
        <h2>Tambah Produk</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                <div>
                    <label>Nama Produk:</label>
                    <input type="text" name="nama_produk" required>
                </div>
                <div>
                    <label>Kategori:</label>
                    <select name="id_kategori" required>
                        <?php mysqli_data_seek($kategori_list_query, 0); // Reset pointer for this loop ?>
                        <?php while ($k = mysqli_fetch_assoc($kategori_list_query)) {
                            echo "<option value='{$k['id']}'>{$k['nama_kategori']}</option>";
                        } ?>
                    </select>
                </div>
                <div>
                    <label>Stok:</label>
                    <input type="number" name="stok" required>
                </div>
                <div>
                    <label>Harga:</label>
                    <input type="number" name="harga" step="0.01" required>
                </div>
                <div>
                    <label>Berat (kg):</label>
                    <input type="number" name="berat" step="0.01" required>
                </div>
                <div>
                    <label>Warna:</label>
                    <input type="text" name="warna" required>
                </div>
                <div>
                    <label>Masa Garansi:</label> <input type="text" name="masa_garansi" placeholder="e.g., 1 Tahun, 6 Bulan" required>
                </div>
                <div>
                    <label>Jenis Produk:</label> <select name="jenis_produk" required>
                        <?php foreach ($jenis_produk_options as $option): ?>
                            <option value="<?= $option ?>"><?= ucwords(str_replace('_', ' ', $option)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="grid-column: span 2;">
                    <label>Deskripsi:</label>
                    <textarea name="deskripsi" rows="3" style="width: 100%;"></textarea>
                </div>
                <div style="grid-column: span 2;">
                    <label>Gambar:</label>
                    <input type="file" name="gambar" accept="image/*" required>
                </div>

            </div>
            <div class="modal-buttons">
                <button type="submit" name="tambah" class="btn-create">Simpan</button>
                <button type="button" onclick="closeModal()" class="btn-cancel">Batal</button>
            </div>
        </form>
    </div>
</div>

<div id="modalEdit" class="modal">
    <div class="modal-content">
        <h2>Edit Produk</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="edit_id" id="edit_id">
            <div class="form-grid">
                <div>
                    <label>Nama Produk:</label>
                    <input type="text" name="edit_nama_produk" id="edit_nama_produk" required>
                </div>
                <div>
                    <label>Kategori:</label>
                    <select name="edit_id_kategori" id="edit_id_kategori" required>
                        <?php foreach ($kategori_list_for_edit as $k) {
                            echo "<option value='{$k['id']}'>{$k['nama_kategori']}</option>";
                        } ?>
                    </select>
                </div>
                <div>
                    <label>Stok:</label>
                    <input type="number" name="edit_stok" id="edit_stok" required>
                </div>
                <div>
                    <label>Harga:</label>
                    <input type="number" name="edit_harga" id="edit_harga" step="0.01" required>
                </div>
                <div>
                    <label>Berat (kg):</label>
                    <input type="number" name="edit_berat" id="edit_berat" step="0.01" required>
                </div>
                <div>
                    <label>Warna:</label>
                    <input type="text" name="edit_warna" id="edit_warna" required>
                </div>
                <div>
                    <label>Masa Garansi:</label> <input type="text" name="edit_masa_garansi" id="edit_masa_garansi" placeholder="e.g., 1 Tahun, 6 Bulan" required>
                </div>
                <div>
                    <label>Jenis Produk:</label> <select name="edit_jenis_produk" id="edit_jenis_produk" required>
                        <?php foreach ($jenis_produk_options as $option): ?>
                            <option value="<?= $option ?>"><?= ucwords(str_replace('_', ' ', $option)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="grid-column: span 2;">
                    <label>Deskripsi:</label>
                    <textarea name="edit_deskripsi" id="edit_deskripsi" rows="3" style="width: 100%;"></textarea>
                </div>
                <div style="grid-column: span 2;">
                    <label>Ganti Gambar:</label>
                    <input type="file" name="edit_gambar" accept="image/*">
                </div>

            </div>
            <div class="modal-buttons">
                <button type="submit" name="ubah" class="btn-create">Simpan Perubahan</button>
                <button type="button" onclick="closeModal('modalEdit')" class="btn-cancel">Batal</button>
            </div>
        </form>
    </div>
</div>

<div id="modalHapus" class="modal">
    <div class="modal-content" style="text-align: center;">
        <h2>Yakin ingin menghapus produk ini?</h2>
        <div class="modal-buttons">
            <a id="confirmDelete" class="btn-create">Ya, Hapus</a>
            <button onclick="closeModal('modalHapus')" class="btn-cancel">Batal</button>
        </div>
    </div>
</div>


<script>
function openModal() {
    document.getElementById('modalTambah').style.display = 'flex';
}

function closeModal(modalId = 'modalTambah') {
    document.getElementById(modalId).style.display = 'none';
}

function openEditModal(data) {
    document.getElementById('modalEdit').style.display = 'flex';
    document.getElementById('edit_id').value = data.id;
    document.getElementById('edit_nama_produk').value = data.nama_produk;
    document.getElementById('edit_id_kategori').value = data.id_kategori;
    document.getElementById('edit_stok').value = data.stok;
    document.getElementById('edit_harga').value = data.harga;
    document.getElementById('edit_berat').value = data.berat;
    document.getElementById('edit_warna').value = data.warna;
    document.getElementById('edit_masa_garansi').value = data.masa_garansi; // Set value for new field
    document.getElementById('edit_jenis_produk').value = data.jenis_produk;
    document.getElementById('edit_deskripsi').value = data.deskripsi;
}

function openDeleteModal(id) {
    document.getElementById('modalHapus').style.display = 'flex';
    document.getElementById('confirmDelete').href = 'kelola_produk.php?hapus=' + id;
}

// Countdown modal sukses
const modal = document.getElementById('successModal');
if (modal) {
    let seconds = 5;
    const countdown = document.getElementById('countdown');
    const interval = setInterval(() => {
        seconds--;
        countdown.innerText = seconds;
        if (seconds <= 0) {
            clearInterval(interval);
            modal.style.display = 'none';
        }
    }, 1000);
}
</script>