<?php
session_start();
if (!isset($_SESSION['admin_nama'])) {
    header("Location: loginadmin.php");
    exit;
}

include 'koneksi.php';

$errors = [];
$old = [
    'nama_pengguna' => '',
    'email' => '',
    'no_hp' => '',
    'address' => '',
    'role' => '',
    'jenis_kelamin' => '',
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_pengguna = trim($_POST['nama_pengguna']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $no_hp = trim($_POST['no_hp']);
    $address = trim($_POST['address']);
    $role = $_POST['role'];
    $jenis_kelamin = $_POST['jenis_kelamin'];

    $old = compact('nama_pengguna', 'email', 'no_hp', 'address', 'role', 'jenis_kelamin');

    if (empty($nama_pengguna) || !preg_match("/^[a-zA-Z\s]+$/", $nama_pengguna)) {
        $errors['nama_pengguna'] = "Nama hanya boleh berisi huruf dan spasi.";
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Email tidak valid.";
    }

    if (empty($password) || strlen($password) < 6) {
        $errors['password'] = "Password minimal 6 karakter.";
    }

    if (empty($no_hp) || !preg_match("/^[0-9]{12,}$/", $no_hp)) {
        $errors['no_hp'] = "Nomor HP harus terdiri dari minimal 12 digit angka.";
    }

    if (empty($address)) {
        $errors['address'] = "Alamat tidak boleh kosong.";
    }

    if (empty($role) || !in_array($role, ['admin', 'user'])) {
        $errors['role'] = "Role tidak valid.";
    }

    if (empty($jenis_kelamin) || !in_array($jenis_kelamin, ['lakilaki', 'Perempuan'])) {
        $errors['jenis_kelamin'] = "Jenis kelamin tidak valid.";
    }

    if (empty($errors)) {
        $nama_pengguna = mysqli_real_escape_string($koneksi, $nama_pengguna);
        $email = mysqli_real_escape_string($koneksi, $email);
        $password = md5($password);
        $no_hp = mysqli_real_escape_string($koneksi, $no_hp);
        $address = mysqli_real_escape_string($koneksi, $address);
        $role = mysqli_real_escape_string($koneksi, $role);
        $jenis_kelamin = mysqli_real_escape_string($koneksi, $jenis_kelamin);

        $insertQuery = "INSERT INTO pengguna (nama_pengguna, email, password, no_hp, address, role, jenis_kelamin)
                        VALUES ('$nama_pengguna', '$email', '$password', '$no_hp', '$address', '$role', '$jenis_kelamin')";

        if (mysqli_query($koneksi, $insertQuery)) {
            $_SESSION['tambah_berhasil'] = true;
            header("Location: kelola_pengguna.php");
            exit;
        } else {
            $errors['general'] = "Gagal menyimpan ke database.";
        }
    }
}
if (isset($_POST['submit_edit'])) {
    $edit_id = $_POST['edit_id'];
    $nama = trim($_POST['edit_nama_pengguna']);
    $email = trim($_POST['edit_email']);
    $password = $_POST['edit_password'];
    $no_hp = trim($_POST['edit_no_hp']);
    $address = trim($_POST['edit_address']);
    $role = $_POST['edit_role'];
    $jenis_kelamin = $_POST['edit_jenis_kelamin'];

    // Validasi dasar bisa ditambahkan jika diinginkan
    $nama = mysqli_real_escape_string($koneksi, $nama);
    $email = mysqli_real_escape_string($koneksi, $email);
    $no_hp = mysqli_real_escape_string($koneksi, $no_hp);
    $address = mysqli_real_escape_string($koneksi, $address);
    $role = mysqli_real_escape_string($koneksi, $role);
    $jenis_kelamin = mysqli_real_escape_string($koneksi, $jenis_kelamin);

    if (!empty($password)) {
        $password = md5($password);
        $queryUpdate = "UPDATE pengguna SET 
                        nama_pengguna='$nama', 
                        email='$email',
                        password='$password',
                        no_hp='$no_hp',
                        address='$address',
                        role='$role',
                        jenis_kelamin='$jenis_kelamin'
                        WHERE id='$edit_id'";
    } else {
        $queryUpdate = "UPDATE pengguna SET 
                        nama_pengguna='$nama', 
                        email='$email',
                        no_hp='$no_hp',
                        address='$address',
                        role='$role',
                        jenis_kelamin='$jenis_kelamin'
                        WHERE id='$edit_id'";
    }

    if (mysqli_query($koneksi, $queryUpdate)) {
        $_SESSION['edit_berhasil'] = true;
        header("Location: kelola_pengguna.php");
        exit;
    } else {
        $errors['general'] = "Gagal memperbarui data pengguna.";
    }
}
if (isset($_POST['submit_hapus'])) {
    $id = intval($_POST['id']);
    $query = "DELETE FROM pengguna WHERE id = $id";
    if (mysqli_query($koneksi, $query)) {
        $_SESSION['hapus_berhasil'] = true;
        header("Location: kelola_pengguna.php");
        exit;
    } else {
        $errors['general'] = "Gagal menghapus pengguna.";
    }
}
$query = "SELECT * FROM pengguna ORDER BY id ASC";
$result = mysqli_query($koneksi, $query);
include 'resource/headeradmin1.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Pengguna</title>
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
</head>
<body>

<div class="main-content">
    <div class="breadcrumb">
        <span>Home</span> / <strong>Kelola Pengguna</strong>
    </div>

    <div class="card">
        <button class="btn-create" onclick="openModal()">+ Create new</button>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Password</th>
                    <th>No HP</th>
                    <th>Address</th>
                    <th>Role</th>
                    <th>Jenis Kelamin</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($data = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= $data['id'] ?></td>
                        <td><?= htmlspecialchars($data['nama_pengguna']) ?></td>
                        <td><?= htmlspecialchars($data['email']) ?></td>
                        <td>**********</td>
                        <td><?= htmlspecialchars($data['no_hp']) ?></td>
                        <td><?= htmlspecialchars($data['address']) ?></td>
                        <td><?= htmlspecialchars($data['role']) ?></td>
                        <td><?= htmlspecialchars($data['jenis_kelamin']) ?></td>
                        <td class="action-icons">
                            <i class="bi bi-pencil-fill" onclick="openEditModal(<?= htmlspecialchars(json_encode($data)) ?>)"></i>
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
                <h2 style="color:green;">Pengguna berhasil ditambahkan!</h2>
                <p>Menutup dalam <span id="countdown">5</span> detik...</p>
            </div>
        </div>
        <?php unset($_SESSION['tambah_berhasil']); ?>
    <?php endif; ?>

    
    <?php if (isset($_SESSION['edit_berhasil'])): ?>
    <div id="successModal" class="modal" style="display:flex;">
        <div class="modal-content" style="text-align:center;">
            <h2 style="color:green;">Pengguna berhasil diperbarui!</h2>
            <p>Menutup dalam <span id="countdown">5</span> detik...</p>
        </div>
    </div>
    <?php unset($_SESSION['edit_berhasil']); ?>
<?php endif; ?>
    <?php if (isset($_SESSION['hapus_berhasil'])): ?>
    <div id="successModal" class="modal" style="display:flex;">
        <div class="modal-content" style="text-align:center;">
            <h2 style="color:green;">Pengguna berhasil dihapus!</h2>
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
        <h2>Menambahkan Pengguna</h2>
        <?php if (!empty($errors['general'])): ?>
            <p class="error-message"><?= $errors['general'] ?></p>
        <?php endif; ?>
        <form method="POST">
            <div class="form-grid">
                <div>
                    <label>Nama:</label>
                    <input type="text" name="nama_pengguna" value="<?= htmlspecialchars($old['nama_pengguna']) ?>">
                    <?php if (!empty($errors['nama_pengguna'])): ?>
                        <div class="error-message"><?= $errors['nama_pengguna'] ?></div>
                    <?php endif; ?>
                </div>
                <div>
                    <label>Email:</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($old['email']) ?>">
                    <?php if (!empty($errors['email'])): ?>
                        <div class="error-message"><?= $errors['email'] ?></div>
                    <?php endif; ?>
                </div>
                <div>
                    <label>Password:</label>
                    <input type="password" name="password">
                    <?php if (!empty($errors['password'])): ?>
                        <div class="error-message"><?= $errors['password'] ?></div>
                    <?php endif; ?>
                </div>
                <div>
                    <label>No HP:</label>
                    <input type="text" name="no_hp" value="<?= htmlspecialchars($old['no_hp']) ?>">
                    <?php if (!empty($errors['no_hp'])): ?>
                        <div class="error-message"><?= $errors['no_hp'] ?></div>
                    <?php endif; ?>
                </div>
                <div>
                    <label>Alamat:</label>
                    <input type="text" name="address" value="<?= htmlspecialchars($old['address']) ?>">
                    <?php if (!empty($errors['address'])): ?>
                        <div class="error-message"><?= $errors['address'] ?></div>
                    <?php endif; ?>
                </div>
                <div>
                    <label>Role:</label>
                    <select name="role">
                        <option value="">-- Pilih --</option>
                        <option value="admin" <?= $old['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="user" <?= $old['role'] === 'user' ? 'selected' : '' ?>>User</option>
                    </select>
                    <?php if (!empty($errors['role'])): ?>
                        <div class="error-message"><?= $errors['role'] ?></div>
                    <?php endif; ?>
                </div>
                <div>
                    <label>Jenis Kelamin:</label>
                    <select name="jenis_kelamin">
                        <option value="">-- Pilih --</option>
                        <option value="lakilaki" <?= $old['jenis_kelamin'] === 'lakilaki' ? 'selected' : '' ?>>laki-laki</option>
                        <option value="Perempuan" <?= $old['jenis_kelamin'] === 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
                    </select>
                    <?php if (!empty($errors['jenis_kelamin'])): ?>
                        <div class="error-message"><?= $errors['jenis_kelamin'] ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="modal-buttons">
                <button type="submit" class="btn-create">Tambahkan</button>
                <button type="button" onclick="closeModal()" class="btn-cancel">Batalkan</button>
            </div>
        </form>
    </div>
</div>
<!-- Modal Edit -->
<div id="modalEdit" class="modal">
    <div class="modal-content">
        <h2>Edit Pengguna</h2>
        <form method="POST" id="formEdit">
            <input type="hidden" name="edit_id" id="edit_id">
            <div class="form-grid">
                <div>
                    <label>Nama:</label>
                    <input type="text" name="edit_nama_pengguna" id="edit_nama_pengguna">
                </div>
                <div>
                    <label>Email:</label>
                    <input type="email" name="edit_email" id="edit_email">
                </div>
                <div>
                    <label>Password (kosongkan jika tidak diubah):</label>
                    <input type="password" name="edit_password" id="edit_password">
                </div>
                <div>
                    <label>No HP:</label>
                    <input type="text" name="edit_no_hp" id="edit_no_hp">
                </div>
                <div>
                    <label>Alamat:</label>
                    <input type="text" name="edit_address" id="edit_address">
                </div>
                <div>
                    <label>Role:</label>
                    <select name="edit_role" id="edit_role">
                        <option value="admin">Admin</option>
                        <option value="user">User</option>
                    </select>
                </div>
                <div>
                    <label>Jenis Kelamin:</label>
                    <select name="edit_jenis_kelamin" id="edit_jenis_kelamin">
                        <option value="lakilaki">Laki-laki</option>
                        <option value="Perempuan">Perempuan</option>
                    </select>
                </div>
            </div>
            <div class="modal-buttons">
                <button type="submit" name="submit_edit" class="btn-create">Simpan Perubahan</button>
                <button type="button" onclick="closeEditModal()" class="btn-cancel">Batalkan</button>
            </div>
        </form>
    </div>
</div>
<!-- Modal Konfirmasi Hapus -->
<div id="modalHapus" class="modal">
    <div class="modal-content" style="text-align:center;">
        <h2 style="color:red;">Yakin ingin menghapus pengguna ini?</h2>
        <form method="POST" action="">
            <input type="hidden" name="id" id="hapus_id">
            <div class="modal-buttons">
                <button type="submit" name="submit_hapus" class="btn-create">Ya, Hapus</button>
                <button type="button" onclick="closeDeleteModal()" class="btn-cancel">Batal</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(user) {
    document.getElementById('modalEdit').style.display = 'flex';

    document.getElementById('edit_id').value = user.id;
    document.getElementById('edit_nama_pengguna').value = user.nama_pengguna;
    document.getElementById('edit_email').value = user.email;
    document.getElementById('edit_no_hp').value = user.no_hp;
    document.getElementById('edit_address').value = user.address;
    document.getElementById('edit_role').value = user.role;
    document.getElementById('edit_jenis_kelamin').value = user.jenis_kelamin;
    document.getElementById('edit_password').value = ''; // kosongkan untuk opsional
}

function closeEditModal() {
    document.getElementById('modalEdit').style.display = 'none';
}

window.onclick = function(event) {
    const modalTambah = document.getElementById('modalTambah');
    const modalEdit = document.getElementById('modalEdit');
    if (event.target == modalTambah) {
        closeModal();
    } else if (event.target == modalEdit) {
        closeEditModal();
    }
}

    function openModal() {
        document.getElementById('modalTambah').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('modalTambah').style.display = 'none';
    }
function openDeleteModal(id) {
    document.getElementById("hapus_id").value = id;
    document.getElementById("modalHapus").style.display = "flex";
}

function closeDeleteModal() {
    document.getElementById("modalHapus").style.display = "none";
}

    window.onclick = function(event) {
    const modalTambah = document.getElementById('modalTambah');
    const modalEdit = document.getElementById('modalEdit');
    const modalHapus = document.getElementById('modalHapus');
    if (event.target == modalTambah) {
        closeModal();
    } else if (event.target == modalEdit) {
        closeEditModal();
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
                if (successModal) {
                    successModal.style.display = 'none';
                }
            }
        }, 1000);
    }
</script>

</body>
</html>
