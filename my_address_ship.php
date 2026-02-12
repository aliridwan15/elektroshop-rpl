<?php
require_once 'koneksi.php';
session_start();

// Pastikan pengguna sudah login
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id'];
$success = '';
$error = '';

// --- Penanganan AJAX untuk Edit dan Hapus Alamat ---
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
        // Handle Delete Address
        if ($_POST['action'] === 'delete_address') {
            $id_alamat = intval($_POST['id_alamat']);

            // Pastikan alamat yang dihapus milik pengguna yang sedang login
            $stmt = $conn->prepare("DELETE FROM alamat_pengiriman WHERE id_alamat = ? AND id_pengguna = ?");
            if ($stmt) {
                $stmt->bind_param("ii", $id_alamat, $id_user);
                if ($stmt->execute()) {
                    if ($stmt->affected_rows > 0) {
                        echo json_encode(['status' => 'success', 'message' => 'Alamat berhasil dihapus.']);
                    } else {
                        echo json_encode(['status' => 'error', 'message' => 'Alamat tidak ditemukan atau Anda tidak memiliki izin untuk menghapusnya.']);
                    }
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus alamat: ' . $stmt->error]);
                }
                $stmt->close();
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Gagal menyiapkan query hapus alamat: ' . $conn->error]);
            }
            exit;
        }

        // Handle Edit Address
        if ($_POST['action'] === 'edit_address') {
            $id_alamat = intval($_POST['id_alamat']);
            $nama_penerima = htmlspecialchars(trim($_POST['nama_penerima']));
            $no_telepon_alamat = htmlspecialchars(trim($_POST['no_telepon_alamat']));
            $alamat_lengkap = htmlspecialchars(trim($_POST['alamat_lengkap']));
            $desa = htmlspecialchars(trim($_POST['desa']));
            $distrik = htmlspecialchars(trim($_POST['distrik']));
            $kota = htmlspecialchars(trim($_POST['kota']));
            $kode_pos = htmlspecialchars(trim($_POST['kode_pos']));
            $provinsi = htmlspecialchars(trim($_POST['provinsi']));

            if (empty($nama_penerima) || empty($no_telepon_alamat) || empty($alamat_lengkap) || empty($desa) || empty($distrik) || empty($kota) || empty($kode_pos) || empty($provinsi)) {
                echo json_encode(['status' => 'error', 'message' => 'Semua kolom alamat harus diisi.']);
                exit;
            }

            // Pastikan alamat yang diedit milik pengguna yang sedang login
            $stmt = $conn->prepare("UPDATE alamat_pengiriman SET
                nama_penerima = ?,
                no_telepon = ?,
                alamat_lengkap = ?,
                desa = ?,
                distrik = ?,
                kota = ?,
                kode_pos = ?,
                provinsi = ?
                WHERE id_alamat = ? AND id_pengguna = ?");

            if ($stmt) {
                $stmt->bind_param("ssssssssii", $nama_penerima, $no_telepon_alamat, $alamat_lengkap, $desa, $distrik, $kota, $kode_pos, $provinsi, $id_alamat, $id_user);
                if ($stmt->execute()) {
                    if ($stmt->affected_rows > 0) {
                        echo json_encode(['status' => 'success', 'message' => 'Alamat berhasil diperbarui.']);
                    } else {
                        echo json_encode(['status' => 'info', 'message' => 'Tidak ada perubahan pada alamat.']);
                    }
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui alamat: ' . $stmt->error]);
                }
                $stmt->close();
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Gagal menyiapkan query edit alamat: ' . $conn->error]);
            }
            exit;
        }
    }
    // Jika ada request AJAX tapi bukan action yang dikenali
    echo json_encode(['status' => 'error', 'message' => 'Aksi AJAX tidak valid.']);
    exit;
}

// --- Query untuk mengambil data alamat pengiriman ---
$addresses = [];
$query_addresses = $conn->prepare("SELECT * FROM alamat_pengiriman WHERE id_pengguna = ? ORDER BY id_alamat DESC");
if ($query_addresses) {
    $query_addresses->bind_param("i", $id_user);
    $query_addresses->execute();
    $result_addresses = $query_addresses->get_result();
    while ($row = $result_addresses->fetch_assoc()) {
        $addresses[] = $row;
    }
    $query_addresses->close();
} else {
    $error = "Gagal mengambil data alamat: " . $conn->error;
}

include 'resource/header.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Shipping Addresses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Styling umum dari myaccount.php */
        .modal-bg {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.5); display: none; justify-content: center; align-items: center;
            z-index: 999;
        }
        .modal-box {
            background: #fff; padding: 30px; border-radius: 10px; width: 70%; /* Lebih lebar sedikit untuk form alamat */
            max-width: 800px;
        }
        .alert {
            position: fixed; top: 20px; right: 20px; padding: 15px 20px; border-radius: 5px;
            color: white; font-weight: bold; z-index: 1000;
            opacity: 0; transform: translateY(-20px); transition: opacity 0.5s ease, transform 0.5s ease;
        }
        .alert.show {
            opacity: 1; transform: translateY(0);
        }
        .alert-success { background: green; }
        .alert-error { background: red; }
        .alert-info { background: #17a2b8; } /* Warna untuk pesan info, misalnya "tidak ada perubahan" */

        button {
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        button:hover {
            filter: brightness(0.9);
            cursor: pointer;
        }
        .modal-box .close-btn {
            position: absolute; top: 15px; right: 20px; font-size: 24px; cursor: pointer; color: #aaa; text-decoration: none;
        }
        .modal-box .close-btn:hover {
            color: #555;
        }

        /* Styling khusus untuk halaman alamat */
        .address-card {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .address-card h4 {
            color: #e91e63;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .address-card p {
            margin-bottom: 5px;
            line-height: 1.5;
        }
        .address-card .actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }
        .address-card .actions button {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
        }
        .address-card .actions .edit-btn {
            background: #e91e63;
        }
        .address-card .actions .delete-btn {
            background: #dc3545; /* Merah untuk hapus */
        }
        .address-card .actions .edit-btn:hover {
            background-color: #c2185b;
        }
        .address-card .actions .delete-btn:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>

<main style="display: flex; padding: 40px 20px; background: #ffc0cb; min-height: 80vh;">
    <aside style="width: 25%; padding-right: 20px;">
        <h3>Manage My Account</h3>
        <ul style="list-style: none; margin-top: 10px; padding:0;">
            <li><a href="myaccount.php" style="text-decoration:none; color:#000;">My Profile</a></li>
            <li style="color:#e91e63; font-weight:bold;"><a href="my_address_ship.php" style="text-decoration:none; color:#e91e63;">My Address Ship</a></li>
        </ul>
        <h3 style="margin-top: 20px;">My Orders</h3>
        <ul style="list-style: none; margin-top: 10px; padding:0;">
            <li><a href="cancel.php" style="text-decoration:none; color:#000;">My Cancellations</a></li>
            <li><a href="my_reviews.php" style="text-decoration:none; color:#000;">My Rating</a></li>
            <li><a href="my_history.php" style="text-decoration:none; color:#000;">My History</a></li> 
        </ul>
        <h3 style="margin-top: 20px;">My Wishlist</h3>
        <ul style="list-style: none; margin-top: 10px; padding:0;">
            <li><a href="guarantee.php" style="text-decoration:none; color:#000;">Guarantee</a></li>
        </ul>
    </aside>

    <section style="width: 75%; background: white; padding: 30px; border-radius: 10px;">
        <h2 style="color:#e91e63; margin-bottom: 20px;">My Shipping Addresses</h2>

        <?php if (!empty($addresses)): ?>
            <?php foreach ($addresses as $addr): ?>
                <div class="address-card" id="address-<?= $addr['id_alamat'] ?>">
                    <h4><?= htmlspecialchars($addr['nama_penerima']) ?></h4>
                    <p><strong>Phone:</strong> <?= htmlspecialchars($addr['no_telepon']) ?></p>
                    <p><strong>Address:</strong> <?= htmlspecialchars($addr['alamat_lengkap']) ?>, Desa <?= htmlspecialchars($addr['desa']) ?>, Distrik <?= htmlspecialchars($addr['distrik']) ?></p>
                    <p><strong>City/Prov:</strong> <?= htmlspecialchars($addr['kota']) ?>, <?= htmlspecialchars($addr['provinsi']) ?> <?= htmlspecialchars($addr['kode_pos']) ?></p>
                    <div class="actions">
                        <button type="button" class="edit-btn" onclick="openEditAddressModal(<?= htmlspecialchars(json_encode($addr)) ?>)">Edit</button>
                        <button type="button" class="delete-btn" onclick="deleteAddress(<?= $addr['id_alamat'] ?>)">Delete</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Anda belum memiliki alamat pengiriman. Tambahkan satu di halaman <a href="myaccount.php" style="color: #e91e63;">profil Anda</a>.</p>
        <?php endif; ?>

        <div style="margin-top: 30px;">
            <button type="button" onclick="window.location.href='myaccount.php'" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 6px;">Back to Profile</button>
        </div>
    </section>

</main>

<div id="editAddressModal" class="modal-bg">
    <div class="modal-box">
        <a href="#" class="close-btn" onclick="closeEditAddressModal()">&times;</a>
        <h2 style="color:#e91e63; margin-bottom: 20px;">Edit Shipping Address</h2>
        <form id="editAddressForm">
            <input type="hidden" name="action" value="edit_address">
            <input type="hidden" name="id_alamat" id="edit_id_alamat">

            <div style="display: flex; gap: 20px; margin-bottom: 15px;">
                <div style="flex: 1;">
                    <label>Recipient Name</label>
                    <input type="text" id="edit_nama_penerima" name="nama_penerima" required style="width:100%; padding:10px; margin-bottom: 15px;">
                </div>
                <div style="flex: 1;">
                    <label>Phone Number</label>
                    <input type="text" id="edit_no_telepon_alamat" name="no_telepon_alamat" required style="width:100%; padding:10px; margin-bottom: 15px;">
                </div>
            </div>

            <div style="margin-bottom: 15px;">
                <label>Full Address</label>
                <textarea id="edit_alamat_lengkap" name="alamat_lengkap" rows="3" required style="width:100%; padding:10px; margin-bottom: 15px;"></textarea>
            </div>

            <div style="display: flex; gap: 20px; margin-bottom: 15px;">
                <div style="flex: 1;">
                    <label>Province</label>
                    <input type="text" id="edit_provinsi" name="provinsi" required style="width:100%; padding:10px; margin-bottom: 15px;">
                </div>
                <div style="flex: 1;">
                    <label>City</label>
                    <input type="text" id="edit_kota" name="kota" required style="width:100%; padding:10px; margin-bottom: 15px;">
                </div>
            </div>

            <div style="display: flex; gap: 20px; margin-bottom: 15px;">
                <div style="flex: 1;">
                    <label>District</label>
                    <input type="text" id="edit_distrik" name="distrik" required style="width:100%; padding:10px; margin-bottom: 15px;">
                </div>
                <div style="flex: 1;">
                    <label>Village</label>
                    <input type="text" id="edit_desa" name="desa" required style="width:100%; padding:10px; margin-bottom: 15px;">
                </div>
                <div style="flex: 1;">
                    <label>Postal Code</label>
                    <input type="text" id="edit_kode_pos" name="kode_pos" required style="width:100%; padding:10px; margin-bottom: 15px;">
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="cancel-btn" onclick="closeEditAddressModal()" style="padding: 10px 20px; background: #ccc; border: none; border-radius: 6px;">Cancel</button>
                <button type="submit" class="save-changes" style="padding: 10px 20px; background: hotpink; color: white; border: none; border-radius: 6px;">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<?php if (isset($success) && !empty($success)): ?>
    <div class="alert alert-success" id="successAlert"><?= $success ?></div>
<?php endif; ?>
<?php if (isset($error) && !empty($error)): ?>
    <div class="alert alert-error" id="errorAlert"><?= $error ?></div>
<?php endif; ?>

<script>
    // Fungsi untuk menampilkan alert
    function showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.textContent = message;
        document.body.appendChild(alertDiv);
        setTimeout(() => { alertDiv.classList.add('show'); }, 10);

        setTimeout(() => {
            alertDiv.classList.remove('show');
            setTimeout(() => alertDiv.remove(), 500);
        }, 5000);
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Tampilkan alert dari PHP jika ada
        const successAlert = document.getElementById('successAlert');
        const errorAlert = document.getElementById('errorAlert');

        if (successAlert) {
            showAlert(successAlert.textContent, 'success');
            successAlert.remove(); // Hapus elemen PHP setelah ditangani JS
        }
        if (errorAlert) {
            showAlert(errorAlert.textContent, 'error');
            errorAlert.remove(); // Hapus elemen PHP setelah ditangani JS
        }
    });

    // --- Modal Edit Alamat ---
    const editAddressModal = document.getElementById('editAddressModal');
    const editAddressForm = document.getElementById('editAddressForm');

    function openEditAddressModal(addressData) {
        // Isi form modal dengan data alamat yang dipilih
        document.getElementById('edit_id_alamat').value = addressData.id_alamat;
        document.getElementById('edit_nama_penerima').value = addressData.nama_penerima;
        document.getElementById('edit_no_telepon_alamat').value = addressData.no_telepon;
        document.getElementById('edit_alamat_lengkap').value = addressData.alamat_lengkap;
        document.getElementById('edit_desa').value = addressData.desa;
        document.getElementById('edit_distrik').value = addressData.distrik;
        document.getElementById('edit_kota').value = addressData.kota;
        document.getElementById('edit_kode_pos').value = addressData.kode_pos;
        document.getElementById('edit_provinsi').value = addressData.provinsi;

        editAddressModal.style.display = 'flex';
    }

    function closeEditAddressModal() {
        editAddressModal.style.display = 'none';
        editAddressForm.reset(); // Kosongkan form setelah ditutup
    }

    // Handle submit form Edit Alamat via AJAX
    if (editAddressForm) {
        editAddressForm.addEventListener('submit', function(event) {
            event.preventDefault(); // Mencegah submit form default

            const formData = new FormData(this);
            const headers = new Headers();
            headers.append('X-Requested-With', 'XMLHttpRequest'); // Tambahkan header AJAX

            fetch('my_address_ship.php', { // Mengirim request ke file yang sama
                method: 'POST',
                body: formData,
                headers: headers
            })
            .then(response => response.json())
            .then(data => {
                showAlert(data.message, data.status);
                if (data.status === 'success') {
                    closeEditAddressModal();
                    // Reload halaman atau update tampilan alamat secara dinamis
                    location.reload(); // Untuk kemudahan, kita reload saja halamannya
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Terjadi kesalahan saat memperbarui alamat. Silakan coba lagi.', 'error');
            });
        });
    }

    // --- Fungsi Hapus Alamat via AJAX ---
    function deleteAddress(id_alamat) {
        if (confirm('Apakah Anda yakin ingin menghapus alamat ini?')) {
            const formData = new FormData();
            formData.append('action', 'delete_address');
            formData.append('id_alamat', id_alamat);

            const headers = new Headers();
            headers.append('X-Requested-With', 'XMLHttpRequest');

            fetch('my_address_ship.php', {
                method: 'POST',
                body: formData,
                headers: headers
            })
            .then(response => response.json())
            .then(data => {
                showAlert(data.message, data.status);
                if (data.status === 'success') {
                    // Hapus elemen alamat dari DOM
                    const addressCard = document.getElementById(`address-${id_alamat}`);
                    if (addressCard) {
                        addressCard.remove();
                    }
                    // Jika tidak ada alamat tersisa, tampilkan pesan
                    if (document.querySelectorAll('.address-card').length === 0) {
                        const section = document.querySelector('section[style*="width: 75%"]');
                        if (section) {
                             section.innerHTML = `<h2 style="color:#e91e63; margin-bottom: 20px;">My Shipping Addresses</h2><p>Anda belum memiliki alamat pengiriman. Tambahkan satu di halaman <a href="myaccount.php" style="color: #e91e63;">profil Anda</a>.</p><div style="margin-top: 30px;"><button type="button" onclick="window.location.href='myaccount.php'" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 6px;">Back to Profile</button></div>`;
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Terjadi kesalahan saat menghapus alamat. Silakan coba lagi.', 'error');
            });
        }
    }
</script>

<?php include 'resource/footer.php'; ?>
</body>
</html>