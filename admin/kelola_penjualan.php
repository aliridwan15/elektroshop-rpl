<?php
session_start();
if (!isset($_SESSION['admin_nama'])) {
    header("Location: loginadmin.php");
    exit;
}
include 'koneksi.php';

// Penjualan Bulanan
$query_bulanan = mysqli_query($koneksi, "
    SELECT 
        MONTH(created_at) AS bulan, 
        SUM(total_transaksi) AS total_penjualan 
    FROM transaksi 
    GROUP BY MONTH(created_at)
");

// Penjualan Tahunan
$query_tahunan = mysqli_query($koneksi, "
    SELECT 
        YEAR(created_at) AS tahun, 
        SUM(total_transaksi) AS total_penjualan 
    FROM transaksi 
    GROUP BY YEAR(created_at)
");

// Format bulan
$nama_bulan = [
    1 => "Jan", 2 => "Feb", 3 => "Mar", 4 => "Apr", 5 => "Mei", 6 => "Jun",
    7 => "Jul", 8 => "Agu", 9 => "Sep", 10 => "Okt", 11 => "Nov", 12 => "Des"
];

$label_bulanan = [];
$data_bulanan = [];
$warna_bulanan = [];

$target_bulanan = 10000000; // Target penjualan per bulan

while ($row = mysqli_fetch_assoc($query_bulanan)) {
    $bulan = (int)$row['bulan'];
    $total = (int)$row['total_penjualan'];

    $label_bulanan[] = $nama_bulan[$bulan];
    $data_bulanan[] = $total;

    if ($total > $target_bulanan) {
        $warna_bulanan[] = 'deeppink'; // Deep pink
    } elseif ($total == $target_bulanan) {
        $warna_bulanan[] = 'pink'; // Pink
    } else {
        $warna_bulanan[] = 'red'; // Merah
    }
}

$label_tahunan = [];
$data_tahunan = [];

while ($row = mysqli_fetch_assoc($query_tahunan)) {
    $label_tahunan[] = $row['tahun'];
    $data_tahunan[] = (int)$row['total_penjualan'];
}

include 'resource/headeradmin1.php';
?>

<style>
    body {
        transition: margin-left 0.3s ease;
        background-color: #ffc0cb;
    }

    .main-content {
        transition: margin-left 0.3s ease;
        padding: 20px;
        margin-left: 250px;
        background-color: #ffc0cb;
    }

    body.sidebar-collapsed .main-content {
        margin-left: 80px;
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

    @media (max-width: 768px) {
        .main-content {
            margin-left: 0 !important;
        }
    }

    .chart-container {
        background: #fff;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
        margin: 20px 0;
        background-color: transparent;
    }

    .toggle-buttons {
        text-align: right;
        margin-bottom: 10px;
    }

    .toggle-buttons button {
        background: #e0e0e0;
        border: none;
        padding: 10px 20px;
        margin-left: 10px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: bold;
        transition: background 0.3s;
    }

    .toggle-buttons button.active {
        background-color: #2196f3;
        color: white;
    }

    .toggle-buttons button:hover {
        background-color: #1976d2;
        color: white;
    }
</style>

<div class="main-content">
    <div class="chart-container">
        <div class="breadcrumb">
        <span>Home</span> / <strong>Kelola Penjualan</strong>
    </div>
        <h2>Grafik Penjualan</h2>
        <div class="toggle-buttons">
            <button id="btn-bulanan" class="active">Bulanan</button>
            <button id="btn-tahunan">Tahunan</button>
        </div>
        <canvas id="penjualanChart" height="120"></canvas>
    </div>
    <?php include 'resource/footeradmin.php'; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const labelBulanan = <?= json_encode($label_bulanan) ?>;
    const dataBulanan = <?= json_encode($data_bulanan) ?>;
    const warnaBulanan = <?= json_encode($warna_bulanan) ?>;

    const labelTahunan = <?= json_encode($label_tahunan) ?>;
    const dataTahunan = <?= json_encode($data_tahunan) ?>;

    const ctx = document.getElementById('penjualanChart').getContext('2d');
    let chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labelBulanan,
            datasets: [{
                label: 'Total Penjualan',
                data: dataBulanan,
                backgroundColor: warnaBulanan,
                borderColor: 'rgba(0,0,0,0.1)',
                borderWidth: 1,
                borderRadius: 10
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                title: {
                    display: true,
                    text: 'Penjualan Bulanan',
                    font: { size: 18 }
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    document.getElementById('btn-bulanan').addEventListener('click', () => {
        chart.data.labels = labelBulanan;
        chart.data.datasets[0].data = dataBulanan;
        chart.data.datasets[0].backgroundColor = warnaBulanan;
        chart.options.plugins.title.text = 'Penjualan Bulanan';
        chart.update();
        toggleActive('btn-bulanan');
    });

    document.getElementById('btn-tahunan').addEventListener('click', () => {
        chart.data.labels = labelTahunan;
        chart.data.datasets[0].data = dataTahunan;
        chart.data.datasets[0].backgroundColor = 'rgba(33, 150, 243, 0.6)';
        chart.options.plugins.title.text = 'Penjualan Tahunan';
        chart.update();
        toggleActive('btn-tahunan');
    });

    function toggleActive(activeId) {
        document.getElementById('btn-bulanan').classList.remove('active');
        document.getElementById('btn-tahunan').classList.remove('active');
        document.getElementById(activeId).classList.add('active');
    }

    document.addEventListener("DOMContentLoaded", function () {
        const sidebar = document.getElementById("sidebar");
        const body = document.body;

        if (sidebar && sidebar.classList.contains("active")) {
            body.classList.remove("sidebar-collapsed");
        } else {
            body.classList.add("sidebar-collapsed");
        }

        window.toggleSidebar = function () {
            sidebar.classList.toggle("active");
            body.classList.toggle("sidebar-collapsed");
        }
    });
</script>
