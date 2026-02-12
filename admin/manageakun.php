<?php
session_start();
if (!isset($_SESSION['admin_nama'])) {
    header("Location: loginadmin.php");
    exit;
}
include 'koneksi.php';

// Handle Edit dan Hapus Akun
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit_edit'])) {
        $id = $_POST['id'];
        $nama_pengguna = mysqli_real_escape_string($koneksi, $_POST['nama_pengguna']);
        $no_hp = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
        $jenis_kelamin = $_POST['jenis_kelamin'];
        $email = mysqli_real_escape_string($koneksi, $_POST['email']);
        $address = mysqli_real_escape_string($koneksi, $_POST['address']);
        $updated_at = date('Y-m-d H:i:s');

        $update = mysqli_query($koneksi, "UPDATE pengguna SET 
            nama_pengguna='$nama_pengguna', 
            no_hp='$no_hp', 
            jenis_kelamin='$jenis_kelamin', 
            email='$email', 
            address='$address', 
            updated_at='$updated_at' 
            WHERE id='$id'");

        if ($update) {
            $_SESSION['admin_nama'] = $nama_pengguna; // update session
            $_SESSION['edit_berhasil'] = true;
            header("Location: manageakun.php");
            exit;
        } else {
            $_SESSION['edit_gagal'] = true;
            header("Location: manageakun.php");
            exit;
        }

    } elseif (isset($_POST['submit_delete'])) {
        $id = $_POST['id'];
        $delete = mysqli_query($koneksi, "DELETE FROM pengguna WHERE id='$id'");

        if ($delete) {
            session_destroy();
            session_start();
            $_SESSION['hapus_berhasil'] = true;
            header("Location: loginadmin.php");
            exit;
        } else {
            $_SESSION['hapus_gagal'] = true;
            header("Location: manageakun.php");
            exit;
        }
    }
}

// Ambil data admin
$adminNama = $_SESSION['admin_nama'];
$query = mysqli_query($koneksi, "SELECT * FROM pengguna WHERE nama_pengguna='$adminNama'");
$admin = mysqli_fetch_assoc($query);
include 'resource/headeradmin1.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manage Akun</title>
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

        function openEditModal() {
            document.getElementById("editModal").style.display = "flex";
        }

        function closeEditModal() {
            document.getElementById("editModal").style.display = "none";
        }

        function openDeleteModal() {
            document.getElementById("deleteModal").style.display = "flex";
        }

        function closeDeleteModal() {
            document.getElementById("deleteModal").style.display = "none";
        }

        window.onclick = function(event) {
            const editModal = document.getElementById("editModal");
            const deleteModal = document.getElementById("deleteModal");
            if (event.target === editModal) closeEditModal();
            if (event.target === deleteModal) closeDeleteModal();
        }
    </script>
</head>
<body>
<div class="main-content">
    <div class="breadcrumb">
        <span>Welcome! <strong style="color: deeppink;"><?php echo $_SESSION['admin_nama']; ?></strong></span>
    </div>

    <div class="card">
        <form>
            <div class="form-grid">
                <div><label>Name</label><input type="text" value="<?= $admin['nama_pengguna']; ?>" readonly></div>
                <div><label>Role</label><input type="text" value="<?= $admin['role']; ?>" readonly></div>
                <div><label>Number</label><input type="text" value="<?= $admin['no_hp']; ?>" readonly></div>
                <div>
                    <label>Jenis Kelamin</label>
                    <select disabled>
                        <option value="laki-laki" <?= ($admin['jenis_kelamin'] == 'laki-laki' ? 'selected' : '') ?>>laki-laki</option>
                        <option value="perempuan" <?= ($admin['jenis_kelamin'] == 'perempuan' ? 'selected' : '') ?>>perempuan</option>
                    </select>
                </div>
                <div><label>Email</label><input type="email" value="<?= $admin['email']; ?>" readonly></div>
                <div><label>Created at</label><input type="text" value="<?= $admin['created_at']; ?>" readonly></div>
                <div><label>Address</label><input type="text" value="<?= $admin['address']; ?>" readonly></div>
                <div><label>Updated at</label><input type="text" value="<?= $admin['updated_at']; ?>" readonly></div>
            </div>
            <div class="modal-buttons" style="margin-top: 30px;">
                <button type="button" class="btn-create" onclick="openEditModal()">Edit Profil</button>
                <button type="button" class="btn-cancel" onclick="openDeleteModal()">Hapus Akun</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <h2>Edit Profil</h2>
        <form action="" method="post">
            <input type="hidden" name="id" value="<?= $admin['id']; ?>">
            <div class="form-grid">
                <div><label>Name</label><input type="text" name="nama_pengguna" value="<?= $admin['nama_pengguna']; ?>" required></div>
                <div><label>Role</label><input type="text" name="role" value="<?= $admin['role']; ?>" readonly></div>
                <div><label>Number</label><input type="text" name="no_hp" value="<?= $admin['no_hp']; ?>" required></div>
                <div>
                    <label>Jenis Kelamin</label>
                    <select name="jenis_kelamin" required>
                        <option value="laki-laki" <?= ($admin['jenis_kelamin'] == 'laki-laki' ? 'selected' : '') ?>>laki-laki</option>
                        <option value="perempuan" <?= ($admin['jenis_kelamin'] == 'perempuan' ? 'selected' : '') ?>>perempuan</option>
                    </select>
                </div>
                <div><label>Email</label><input type="email" name="email" value="<?= $admin['email']; ?>" required></div>
                <div><label>Address</label><input type="text" name="address" value="<?= $admin['address']; ?>" required></div>
            </div>
            <div class="modal-buttons">
                <button type="submit" class="btn-create" name="submit_edit">Simpan</button>

                <button type="button" class="btn-cancel" onclick="closeEditModal()">Batal</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Hapus -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <h2>Hapus Akun</h2>
        <p>Apakah Anda yakin ingin menghapus akun ini?</p>
        <form action="" method="post">
            <input type="hidden" name="id" value="<?= $admin['id']; ?>">
            <div class="modal-buttons">
                <button type="submit" class="btn-cancel" name="submit_delete">Ya, Hapus</button>

                <button type="button" class="btn-create" onclick="closeDeleteModal()">Batal</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>

<?php
$notifMap = [
    'edit_berhasil' => 'Profil berhasil diperbarui!',
    'edit_gagal' => 'Gagal memperbarui profil.',
    'hapus_berhasil' => 'Akun berhasil dihapus!',
    'hapus_gagal' => 'Gagal menghapus akun.'
];
foreach ($notifMap as $key => $msg):
    if (isset($_SESSION[$key])):
?>
    <div id="successModal" class="modal" style="display:flex;">
        <div class="modal-content" style="text-align:center;">
            <h2 style="color:<?= strpos($key, 'gagal') !== false ? 'red' : 'green'; ?>;"><?= $msg ?></h2>
            <p>Menutup dalam <span id="countdown">5</span> detik...</p>
        </div>
    </div>
    <script>
        let seconds = 5;
        const countdownEl = document.getElementById("countdown");
        const modal = document.getElementById("successModal");
        const interval = setInterval(() => {
            seconds--;
            countdownEl.textContent = seconds;
            if (seconds <= 0) {
                clearInterval(interval);
                modal.style.display = "none";
            }
        }, 1000);
    </script>
<?php
        unset($_SESSION[$key]);
    endif;
endforeach;
?>
