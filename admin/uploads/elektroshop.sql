-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 10 Jun 2025 pada 11.21
-- Versi server: 8.4.3
-- Versi PHP: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `elektroshop`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `alamat_pengiriman`
--

CREATE TABLE `alamat_pengiriman` (
  `id_alamat` int NOT NULL,
  `id_pengguna` int DEFAULT NULL,
  `nama_penerima` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `no_telepon` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `alamat_lengkap` text COLLATE utf8mb4_general_ci,
  `desa` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `distrik` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kota` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kode_pos` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `provinsi` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `alamat_pengiriman`
--

INSERT INTO `alamat_pengiriman` (`id_alamat`, `id_pengguna`, `nama_penerima`, `no_telepon`, `alamat_lengkap`, `desa`, `distrik`, `kota`, `kode_pos`, `provinsi`, `created_at`) VALUES
(4, 3, 'ali', '8111112323', 'jalan semangka blok m', 'desa kolor', 'kotasumenep', 'sumenep', '698104', 'jawa timur', '2025-06-09 21:40:37'),
(5, 3, 'ali', '81111111', 'jalan semangka blok m', 'desa kolor', 'kotasumenep', 'sumenep', '698101', 'jawa timur', '2025-06-09 21:42:04');

-- --------------------------------------------------------

--
-- Struktur dari tabel `cart`
--

CREATE TABLE `cart` (
  `id` int NOT NULL,
  `id_user` int NOT NULL,
  `id_produk` int NOT NULL,
  `tanggal_tambah` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `quantity` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `cart`
--

INSERT INTO `cart` (`id`, `id_user`, `id_produk`, `tanggal_tambah`, `created_at`, `updated_at`, `quantity`) VALUES
(5, 1, 26, '2025-06-04', '2025-06-04 15:07:00', '2025-06-04 15:14:47', 6),
(23, 3, 22, '2025-06-10', '2025-06-10 09:29:08', '2025-06-10 09:33:24', 3),
(24, 3, 52, '2025-06-10', '2025-06-10 09:54:48', '2025-06-10 09:54:48', 1),
(25, 3, 24, '2025-06-10', '2025-06-10 10:23:36', '2025-06-10 10:28:42', 2);

-- --------------------------------------------------------

--
-- Struktur dari tabel `faq`
--

CREATE TABLE `faq` (
  `id` int NOT NULL,
  `pertanyaan` text COLLATE utf8mb4_general_ci,
  `jawaban` text COLLATE utf8mb4_general_ci,
  `jenis` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `faq`
--

INSERT INTO `faq` (`id`, `pertanyaan`, `jawaban`, `jenis`, `created_at`, `updated_at`) VALUES
(1, 'Apa itu fitur Favorit di Elektroshop?', ' Fitur Favorit memungkinkan Anda menyimpan produk yang Anda sukai untuk dilihat kembali di lain waktu. Produk favorit Anda dapat diakses dari halaman akun Anda.', 'Favorite?', '2025-05-18 11:45:48', '2025-05-18 11:52:35'),
(2, 'Bagaimana cara checkout?', 'Setelah menambahkan produk ke keranjang, klik tombol “Checkout”, lalu isi informasi pengiriman dan pilih metode pembayaran.', 'Checkout?', '2025-05-18 11:48:21', '2025-05-18 11:48:21'),
(3, 'Metode pembayaran apa saja yang tersedia saat checkout? ', 'Kami menerima transfer bank, kartu kredit/debit, dan dompet elektronik seperti GoPay atau OVO.', 'Checkout?', '2025-05-18 11:49:32', '2025-05-18 11:49:32'),
(5, 'Apakah semua produk di Elektroshop bergaransi resmi?', 'Ya, hampir semua produk memiliki garansi resmi dari distributor atau produsen. Beberapa produk memiliki garansi toko selama 7 hari.', 'produk', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(6, 'Bagaimana cara klaim garansi di Elektroshop?', 'Bawa produk ke service center resmi dengan kartu garansi dan bukti pembelian. Tim kami akan membantu proses klaim.', 'produk', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(7, 'Berapa lama waktu perbaikan produk di service center?', 'Waktu perbaikan berkisar antara 3 hingga 7 hari kerja, tergantung tingkat kerusakan dan ketersediaan suku cadang.', 'produk', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(8, 'Apakah perbaikan produk dikenakan biaya?', 'Jika masih dalam masa garansi dan kerusakan bukan karena kesalahan pengguna, tidak dikenakan biaya. Di luar garansi, akan dikenakan biaya service.', 'produk', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(9, 'Bagaimana cara melakukan pembelian di website Elektroshop?', 'Pilih produk, tambahkan ke keranjang, dan ikuti proses checkout hingga pembayaran selesai.', 'pembayaran', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(10, 'Metode pembayaran apa saja yang tersedia?', 'Kami menerima pembayaran melalui transfer bank, kartu kredit/debit, dan layanan pembayaran digital seperti QRIS.', 'pembayaran', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(11, 'Apakah perlu mendaftar akun untuk berbelanja?', 'Tidak wajib, tetapi disarankan agar Anda bisa melacak pesanan dan mendapatkan promo khusus.', 'akun', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(12, 'Bagaimana cara mengubah alamat pengiriman setelah checkout?', 'Segera hubungi layanan pelanggan sebelum produk dikirim agar alamat bisa diperbarui.', 'pengiriman', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(13, 'Apakah Elektroshop menyediakan pengiriman gratis?', 'Ya, kami menyediakan layanan pengiriman gratis untuk area tertentu dan nominal pembelian tertentu.', 'pengiriman', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(14, 'Bagaimana jika saya menerima produk yang rusak saat pengiriman?', 'Silakan kirimkan video unboxing dan hubungi layanan pelanggan maksimal 1x24 jam setelah barang diterima.', 'pengiriman', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(15, 'Apakah saya bisa mengambil barang langsung di toko?', 'Saat ini pembelian dilakukan online, namun beberapa mitra toko kami menyediakan opsi ambil di tempat.', 'umum', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(16, 'Apakah produk yang dijual di Elektroshop adalah asli?', 'Semua produk yang kami jual adalah 100% original dari distributor resmi.', 'produk', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(17, 'Bagaimana cara melacak status pesanan?', 'Setelah pembayaran, Anda akan mendapatkan email dengan link untuk melacak status pengiriman.', 'pengiriman', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(18, 'Berapa lama waktu pengiriman pesanan?', 'Pengiriman biasanya memakan waktu 1-3 hari untuk area Jabodetabek, dan 3-7 hari untuk wilayah lainnya.', 'pengiriman', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(19, 'Apakah bisa membatalkan pesanan?', 'Pesanan hanya bisa dibatalkan jika statusnya masih “Diproses”. Hubungi CS segera untuk permintaan pembatalan.', 'pembayaran', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(20, 'Apakah saya bisa mengajukan pengembalian dana?', 'Ya, refund dapat diajukan jika produk cacat pabrik dan disertai bukti unboxing dalam 24 jam setelah diterima.', 'pembayaran', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(21, 'Apa yang harus dilakukan jika pembayaran gagal?', 'Periksa kembali saldo Anda, metode pembayaran, atau hubungi bank terkait. Anda juga bisa ulangi pembayaran.', 'pembayaran', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(22, 'Bagaimana cara mengatur ulang kata sandi akun saya?', 'Gunakan opsi “Lupa Kata Sandi” pada halaman login dan ikuti instruksi yang dikirimkan ke email Anda.', 'akun', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(23, 'Apakah saya bisa mengubah alamat email akun saya?', 'Silakan login ke akun Anda, masuk ke pengaturan profil, dan perbarui alamat email Anda.', 'akun', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(24, 'Apakah Elektroshop memiliki program loyalitas?', 'Kami sedang mengembangkan sistem poin dan cashback bagi pelanggan setia. Nantikan peluncurannya.', 'umum', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(25, 'Apakah tersedia layanan pemasangan produk elektronik?', 'Ya, untuk beberapa produk seperti TV dan AC kami menyediakan layanan instalasi melalui teknisi mitra.', 'produk', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(26, 'Bagaimana cara mengetahui apakah produk tersedia di gudang?', 'Status stok ditampilkan di halaman produk. Jika tidak tersedia, Anda bisa mendaftar notifikasi stok.', 'produk', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(27, 'Apakah ada layanan konsultasi sebelum pembelian?', 'Ya, hubungi tim kami untuk mendapatkan rekomendasi produk sesuai kebutuhan Anda.', 'umum', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(28, 'Apakah bisa membeli secara grosir atau untuk keperluan bisnis?', 'Ya, kami melayani pembelian dalam jumlah besar. Hubungi tim B2B kami untuk penawaran khusus.', 'umum', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(29, 'Apa yang dimaksud dengan status \"Pre Order\"?', 'Produk dengan status Pre Order membutuhkan waktu tambahan untuk pengiriman karena menunggu stok masuk.', 'produk', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(30, 'Apakah produk Pre Order bisa dibatalkan?', 'Pesanan Pre Order dapat dibatalkan selama barang belum dikirim dari supplier.', 'produk', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(31, 'Apakah saya bisa memilih jasa ekspedisi saat checkout?', 'Saat ini kami menggunakan jasa ekspedisi terpercaya secara otomatis. Namun, beberapa area dapat memilih kurir.', 'pengiriman', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(32, 'Bagaimana saya bisa memberikan ulasan produk?', 'Setelah produk diterima, Anda akan menerima email dengan tautan untuk memberikan ulasan.', 'produk', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(33, 'Apakah Elektroshop punya toko fisik?', 'Elektroshop adalah toko online. Namun, kami bekerja sama dengan beberapa mitra offline untuk layanan tambahan.', 'umum', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(34, 'Apakah tersedia fitur cicilan tanpa kartu kredit?', 'Ya, kami bekerja sama dengan layanan paylater seperti Akulaku dan Kredivo.', 'pembayaran', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(35, 'Bagaimana cara menonaktifkan akun saya?', 'Silakan hubungi layanan pelanggan untuk permintaan penonaktifan akun.', 'akun', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(36, 'Apakah data pribadi saya aman?', 'Kami melindungi data pribadi Anda sesuai standar keamanan industri dan tidak membagikan ke pihak ketiga tanpa izin.', 'akun', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(37, 'Bagaimana cara mendapatkan promo dan diskon?', 'Langganan newsletter kami atau ikuti akun media sosial untuk update promo terbaru.', 'umum', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(38, 'Apakah saya bisa mengubah produk setelah checkout?', 'Penggantian produk hanya dapat dilakukan jika pesanan belum diproses oleh gudang.', 'produk', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(39, 'Bisakah saya membeli produk sebagai hadiah untuk orang lain?', 'Ya, Anda bisa menuliskan alamat penerima berbeda saat checkout.', 'pengiriman', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(40, 'Apakah ada batas maksimal pembelian produk?', 'Untuk pembelian besar, mohon hubungi kami terlebih dahulu untuk pengecekan stok.', 'produk', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(41, 'Apakah saya bisa menggunakan lebih dari satu voucher promo?', 'Saat ini hanya satu voucher yang bisa digunakan per transaksi.', 'pembayaran', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(42, 'Apakah tersedia aplikasi mobile untuk Elektroshop?', 'Aplikasi sedang dalam tahap pengembangan. Sementara ini, gunakan versi mobile dari website kami.', 'umum', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(43, 'Apa itu status pesanan “Dikirim” dan “Selesai”?', '“Dikirim” berarti pesanan sudah di ekspedisi. “Selesai” berarti pesanan sudah diterima oleh pelanggan.', 'pengiriman', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(44, 'Bagaimana cara menghubungi customer service?', 'Anda dapat menghubungi kami via email di cs@elektroshop.id atau melalui live chat di website.', 'umum', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(45, 'Apakah tersedia layanan tukar tambah produk?', 'Saat ini kami belum menyediakan fitur tukar tambah. Namun akan tersedia di update berikutnya.', 'produk', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(46, 'Bagaimana jika saya lupa menambahkan voucher saat checkout?', 'Sayangnya, kami tidak dapat menambahkan voucher setelah pembayaran berhasil.', 'pembayaran', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(47, 'Apakah saya akan mendapatkan faktur pembelian?', 'Ya, invoice pembelian akan dikirimkan melalui email setelah transaksi selesai.', 'pembayaran', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(48, 'Bagaimana cara mengetahui kapasitas dan spesifikasi produk?', 'Detail spesifikasi tercantum di halaman produk masing-masing.', 'produk', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(49, 'Apakah tersedia opsi pengiriman di hari yang sama?', 'Saat ini hanya tersedia di area Jabodetabek dan tergantung pada jenis produk.', 'pengiriman', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(50, 'Bisakah saya melihat riwayat pesanan saya?', 'Ya, login ke akun Anda dan buka menu “Pesanan Saya”.', 'akun', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(51, 'Apa yang harus dilakukan jika saya menerima produk yang salah?', 'Segera laporkan ke CS kami dalam waktu maksimal 1x24 jam setelah barang diterima.', 'pengiriman', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(52, 'Bagaimana sistem penilaian ulasan produk?', 'Ulasan ditampilkan secara publik dan dapat memengaruhi rating produk.', 'produk', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(53, 'Bagaimana saya tahu apakah garansi masih berlaku?', 'Cek tanggal pembelian Anda dan masa garansi di kartu garansi atau invoice.', 'produk', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(54, 'Apakah tersedia produk refurbished di Elektroshop?', 'Tidak, kami hanya menjual produk baru dan original.', 'produk', '2025-06-03 11:08:30', '2025-06-03 11:08:30');

-- --------------------------------------------------------

--
-- Struktur dari tabel `favorite`
--

CREATE TABLE `favorite` (
  `id` int NOT NULL,
  `id_user` int DEFAULT NULL,
  `id_produk` int DEFAULT NULL,
  `tanggal_tambah` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `favorite`
--

INSERT INTO `favorite` (`id`, `id_user`, `id_produk`, `tanggal_tambah`, `created_at`, `updated_at`) VALUES
(26, 3, 1, '2025-06-10', '2025-06-10 08:18:04', '2025-06-10 08:18:04'),
(27, 3, 37, '2025-06-10', '2025-06-10 08:19:20', '2025-06-10 08:19:20'),
(28, 3, 23, '2025-06-10', '2025-06-10 08:19:35', '2025-06-10 08:19:35'),
(29, 3, 31, '2025-06-10', '2025-06-10 08:20:38', '2025-06-10 08:20:38'),
(42, 3, 26, '2025-06-10', '2025-06-10 09:34:35', '2025-06-10 09:34:35'),
(43, 3, 38, '2025-06-10', '2025-06-10 09:41:57', '2025-06-10 09:41:57'),
(45, 3, 27, '2025-06-10', '2025-06-10 09:48:29', '2025-06-10 09:48:29'),
(46, 3, 33, '2025-06-10', '2025-06-10 10:30:02', '2025-06-10 10:30:02'),
(47, 3, 24, '2025-06-10', '2025-06-10 10:34:19', '2025-06-10 10:34:19');

-- --------------------------------------------------------

--
-- Struktur dari tabel `garansi`
--

CREATE TABLE `garansi` (
  `id` int NOT NULL,
  `id_user` int DEFAULT NULL,
  `id_produk` int DEFAULT NULL,
  `id_transaksi` int DEFAULT NULL,
  `masa_garansi` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tanggal_berakhir` date DEFAULT NULL,
  `status_garansi` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `keterangan` text COLLATE utf8mb4_general_ci,
  `bukti_claim` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori`
--

CREATE TABLE `kategori` (
  `id` int NOT NULL,
  `nama_kategori` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `icon_class` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kategori`
--

INSERT INTO `kategori` (`id`, `nama_kategori`, `icon_class`, `created_at`, `updated_at`) VALUES
(1, 'Handphone', 'fas fa-mobile-alt', '2025-05-18 10:23:23', '2025-06-02 14:25:14'),
(2, 'Laptop', 'fas fa-laptop', '2025-05-18 10:30:02', '2025-06-02 14:25:14'),
(3, 'Smartwatch', 'fas fa-clock', '2025-06-02 14:03:21', '2025-06-02 14:26:21'),
(4, 'Komputer', 'fas fa-desktop', '2025-06-02 14:03:21', '2025-06-02 14:25:14'),
(5, 'Headphone', 'fas fa-headphones', '2025-06-02 14:03:21', '2025-06-02 14:25:14'),
(6, 'Kamera', 'fas fa-camera', '2025-06-02 14:03:21', '2025-06-02 14:25:14'),
(7, 'Accessories', 'fas fa-plug', '2025-06-02 14:07:57', '2025-06-02 14:25:14');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kupon_diskon`
--

CREATE TABLE `kupon_diskon` (
  `id` int NOT NULL,
  `campaign_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `code` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `diskon` decimal(5,2) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('aktif','expired') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kupon_diskon`
--

INSERT INTO `kupon_diskon` (`id`, `campaign_name`, `code`, `diskon`, `start_date`, `end_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 'summer', 'mk123', 10.00, '2025-05-01', '2025-06-10', 'aktif', '2025-05-18 12:31:59', '2025-06-09 16:13:25'),
(3, 'MUSIM SEMI', 'ELEKTRONIK10', 10.00, '2025-01-01', '2025-03-01', 'expired', '2025-06-03 11:08:30', '2025-06-09 16:19:45'),
(4, '11.11', 'LAPTOPHEMAT15', 15.00, '2024-12-01', '2025-01-15', 'expired', '2025-06-03 11:08:30', '2025-06-10 07:19:32'),
(5, 'HARBOLNAS', 'TVSUPER20', 20.00, '2024-10-10', '2025-02-10', 'aktif', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(6, 'FLASH SALE', 'SMARTHOME25', 25.50, '2024-09-05', '2025-01-05', 'aktif', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(7, 'TAHUN BARU', 'EARFLASH30', 30.00, '2024-08-01', '2024-10-01', 'expired', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(8, 'RAMADAN', 'KAMERA12', 12.00, '2024-11-01', '2025-01-15', 'aktif', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(9, 'MIDNIGHT SALE', 'ELEKSUMMER18', 18.00, '2024-07-01', '2024-09-01', 'expired', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(10, 'CYBER MONDAY', 'NEWTECH22', 22.00, '2025-01-10', '2025-04-10', 'aktif', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(11, 'BLACK FRIDAY', 'GAMING17', 17.00, '2024-11-15', '2025-02-15', 'aktif', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(12, 'SUMMER SALE', 'DAPURELEKTRONIK10', 9.99, '2024-06-01', '2024-08-01', 'expired', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(13, 'WEEKEND DEALS', 'LAUNCH550', 5.50, '2025-01-20', '2025-03-30', 'aktif', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(14, 'ELECTRONICS FEST', 'GADGETKIDS14', 14.75, '2024-07-10', '2024-09-10', 'expired', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(15, 'BIG SALE', 'AKSESKOM40', 40.00, '2024-08-15', '2024-12-15', 'expired', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(16, 'SUMMER FEST', 'RUMAH35', 35.00, '2024-10-01', '2025-01-01', 'aktif', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(17, 'CLOSING SALE', 'AKHIRMUSIM45', 45.00, '2024-09-20', '2024-12-20', 'expired', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(18, 'FLASH DEALS', 'HEADSET50', 50.00, '2025-02-01', '2025-04-01', 'aktif', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(19, 'SPECIAL PROMO', 'ROUTERHEMAT8', 8.00, '2024-07-01', '2024-08-15', 'expired', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(20, 'NEW YEAR SALE', 'PRINTER16', 16.00, '2024-09-10', '2025-01-10', 'aktif', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(21, 'WEEKLY SALE', 'SERVISHEMAT11', 11.00, '2024-10-05', '2025-01-05', 'aktif', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(22, 'HOLIDAY SALE', 'POWERBANK1999', 19.99, '2024-08-01', '2024-11-01', 'expired', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(23, 'FLASH SALE 24H', 'ELEKTROBARU13', 13.50, '2025-01-05', '2025-03-15', 'expired', '2025-06-03 11:08:30', '2025-06-10 07:20:48'),
(24, 'SUPER DISKON', 'ELEKTRONIK27', 27.00, '2024-11-10', '2025-01-25', 'aktif', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(25, 'MEGA SALE', 'SMARTPHONE2990', 29.90, '2024-10-15', '2025-01-15', 'aktif', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(26, 'LIMITED OFFER', 'VACUUMHEMAT6', 6.00, '2024-09-01', '2024-10-15', 'expired', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(27, 'WEEKEND FLASH', 'MONITOR33', 33.00, '2024-12-01', '2025-03-01', 'aktif', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(28, 'MOBILE FEST', 'AC10', 7.75, '2024-08-10', '2024-10-10', 'expired', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(29, 'BACK TO SCHOOL', 'MOBILELEK21', 21.00, '2024-07-01', '2024-09-01', 'expired', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(30, 'SUPER WEEK', 'GAMING39', 39.00, '2024-10-01', '2025-01-01', 'aktif', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(31, 'AUTUMN SALE', 'DAPURPINTAR24', 24.00, '2024-11-15', '2025-01-15', 'aktif', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(32, 'BIG DISCOUNT', 'CHARGERHEMAT1225', 12.25, '2025-02-10', '2025-05-10', 'aktif', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(33, 'MID-YEAR SALE', 'LEMARI44', 44.00, '2024-07-20', '2024-10-20', 'expired', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(34, 'LIMITED TIME', 'PROYEKTOR9', 9.00, '2024-09-01', '2024-11-01', 'expired', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(35, 'SPECIAL WEEK', 'SMARTWATCH41', 41.00, '2024-08-01', '2024-12-01', 'expired', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(36, 'VIP SALE', 'AUDIO1850', 18.50, '2024-10-10', '2025-01-10', 'aktif', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(37, 'ELECTRONICS DAY', 'PERANGKAT2020', 20.20, '2025-01-01', '2025-04-01', 'aktif', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(38, 'FLASH BUY', 'GAME3750', 37.50, '2024-06-15', '2024-09-15', 'expired', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(39, 'SPECIAL OFFER', 'CCTV1150', 11.50, '2024-11-10', '2025-01-10', 'aktif', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(40, 'HOT DEAL', 'IOT1400', 14.00, '2024-12-05', '2025-02-05', 'aktif', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(41, 'MIDNIGHT SALE', 'BLENDER26', 26.00, '2024-07-01', '2024-09-01', 'expired', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(42, 'YEAR END SALE', 'CUCI38', 38.00, '2024-09-01', '2024-11-30', 'expired', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(43, 'FLASH EVENT', 'ELEKTRONIK19', 19.00, '2025-01-01', '2025-03-01', 'aktif', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(44, 'ONLINE FEST', 'LAMPU13', 13.00, '2024-08-01', '2024-10-01', 'expired', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(45, 'BIGGEST SALE', 'PCHEMAT23', 23.00, '2024-10-01', '2025-01-01', 'aktif', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(46, 'MEGA DEALS', 'VACUUM1750', 17.50, '2024-09-01', '2024-11-01', 'expired', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(47, 'VIP WEEK', 'AKSESELEKTRONIK46', 46.00, '2024-11-01', '2025-01-15', 'aktif', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(48, 'SPECIAL FLASH', 'HDMI42', 42.00, '2025-01-10', '2025-03-10', 'aktif', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(49, 'LIMITED SALE', 'USB36', 36.00, '2024-07-01', '2024-09-01', 'expired', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(50, 'WEEKLY DEAL', 'ELEKTRONIK32', 32.00, '2024-12-01', '2025-02-01', 'aktif', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(51, 'BIG BANG SALE', 'WFHPAKET28', 28.00, '2024-06-01', '2024-08-01', 'expired', '2025-06-03 11:08:30', '2025-06-03 11:08:30'),
(52, 'FLASH 50%', 'ELEKTRONIK31', 50.00, '2025-06-09', '2025-08-05', 'aktif', '2025-06-03 11:08:30', '2025-06-09 16:21:31');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengguna`
--

CREATE TABLE `pengguna` (
  `id` int NOT NULL,
  `nama_pengguna` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` char(32) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `no_hp` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_general_ci,
  `role` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `jenis_kelamin` enum('lakilaki','perempuan') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengguna`
--

INSERT INTO `pengguna` (`id`, `nama_pengguna`, `email`, `password`, `no_hp`, `address`, `role`, `jenis_kelamin`, `created_at`, `updated_at`) VALUES
(1, 'malik', 'malik@gmail.com', '5ddc47513696be03631bd326219bf74b', '081212121212', 'jalan nusa indah', 'admin', 'lakilaki', '2025-05-17 13:53:19', '2025-05-20 12:59:02'),
(3, 'ali', 'ali@gmail.com', '984d8144fa08bfc637d2825463e184fa', '081212121233', 'jalan nusa indah no 14', 'user', 'lakilaki', '2025-05-17 16:30:30', '2025-06-09 14:36:53'),
(6, 'Andi Setiawan', 'andi.setiawan@example.com', '7c6a180b36896a0a8c02787eeafb0e4c', '081234567890', 'Jl. Merdeka No. 1, Jakarta', 'user', 'lakilaki', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(7, 'Siti Rahma', 'siti.rahma@example.com', '6cb75f652a9b52798eb6cf2201057c73', '081298765432', 'Jl. Melati No. 10, Bandung', 'user', 'perempuan', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(8, 'Budi Santoso', 'budi.santoso@example.com', '819b0643d6b89dc9b579fdfc9094f28e', '082112345678', 'Jl. Kenanga No. 23, Surabaya', 'admin', 'lakilaki', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(9, 'Dewi Lestari', 'dewi.lestari@example.com', '34cc93ece0ba9e3f6f235d4af979b16c', '085612345678', 'Jl. Mawar No. 9, Medan', 'user', 'perempuan', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(10, 'Agus Pratama', 'agus.pratama@example.com', 'db0edd04aaac4506f7edab03ac855d56', '083812345678', 'Jl. Anggrek No. 5, Semarang', 'user', 'lakilaki', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(11, 'Rina Ayu', 'rina.ayu@example.com', '218dd27aebeccecae69ad8408d9a36bf', '082134567891', 'Jl. Sakura No. 8, Yogyakarta', 'user', 'perempuan', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(12, 'Joko Purnomo', 'joko.purnomo@example.com', '00cdb7bb942cf6b290ceb97d6aca64a3', '081223344556', 'Jl. Durian No. 3, Solo', 'user', 'lakilaki', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(13, 'Mega Utami', 'mega.utami@example.com', 'b25ef06be3b6948c0bc431da46c2c738', '081345678901', 'Jl. Teratai No. 21, Denpasar', 'user', 'perempuan', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(14, 'Tommy Wijaya', 'tommy.wijaya@example.com', '5d69dd95ac183c9643780ed7027d128a', '089876543210', 'Jl. Apel No. 12, Makassar', 'user', 'lakilaki', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(15, 'Yuni Kartika', 'yuni.kartika@example.com', '87e897e3b54a405da144968b2ca19b45', '082233445566', 'Jl. Pisang No. 6, Malang', 'user', 'perempuan', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(16, 'Rizky Hidayat', 'rizky.hidayat@example.com', '1e5c2776cf544e213c3d279c40719643', '081376543210', 'Jl. Nangka No. 3, Bekasi', 'user', 'lakilaki', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(17, 'Lina Marlina', 'lina.marlina@example.com', 'c24a542f884e144451f9063b79e7994e', '083877665544', 'Jl. Cemara No. 7, Bogor', 'user', 'perempuan', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(18, 'Eko Prasetyo', 'eko.prasetyo@example.com', 'ee684912c7e588d03ccb40f17ed080c9', '082277665533', 'Jl. Salak No. 9, Palembang', 'user', 'lakilaki', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(19, 'Maya Sari', 'maya.sari@example.com', '8ee736784ce419bd16554ed5677ff35b', '085511223344', 'Jl. Mangga No. 10, Batam', 'user', 'perempuan', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(20, 'Ivan Nugroho', 'ivan.nugroho@example.com', '9141fea0574f83e190ab7479d516630d', '081977665544', 'Jl. Kelapa No. 13, Pekanbaru', 'user', 'lakilaki', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(21, 'Desi Fitriani', 'desi.fitriani@example.com', '2b40aaa979727c43411c305540bbed50', '085655443322', 'Jl. Jambu No. 18, Cirebon', 'user', 'perempuan', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(22, 'Ahmad Fauzi', 'ahmad.fauzi@example.com', 'a63f9709abc75bf8bd8f6e1ba9992573', '087788990011', 'Jl. Pepaya No. 27, Tasikmalaya', 'user', 'lakilaki', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(23, 'Tina Nuraini', 'tina.nuraini@example.com', '80b8bdceb474b5127b6aca386bb8ce14', '081998877665', 'Jl. Belimbing No. 5, Tangerang', 'user', 'perempuan', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(24, 'Fajar Nugraha', 'fajar.nugraha@example.com', 'e532ae6f28f4c2be70b500d3d34724eb', '082166778899', 'Jl. Duku No. 11, Padang', 'user', 'lakilaki', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(25, 'Rosa Amelia', 'rosa.amelia@example.com', 'aee67d9bb569ad1562f7b67cfccbd2ef', '082199887766', 'Jl. Matoa No. 14, Pontianak', 'user', 'perempuan', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(26, 'Deni Wahyudi', 'deni.wahyudi@example.com', '568c31f0f2406ab70255a1d83291220f', '087733344455', 'Jl. Sirsak No. 2, Manado', 'user', 'lakilaki', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(27, 'Putri Melati', 'putri.melati@example.com', '069103d83d40b742a336dee5fb92f4e5', '081344556677', 'Jl. Rambutan No. 7, Bandar Lampung', 'user', 'perempuan', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(28, 'Randy Saputra', 'randy.saputra@example.com', '1f82cdf9195b31244721c6026587fb78', '081377788899', 'Jl. Sawo No. 17, Mataram', 'user', 'lakilaki', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(29, 'Wulan Sari', 'wulan.sari@example.com', '58bad6b697dff48f4927941962f23e90', '082155566677', 'Jl. Leci No. 8, Jambi', 'user', 'perempuan', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(30, 'Hendra Gunawan', 'hendra.gunawan@example.com', '6982e82c0b21af5526754d83df2d1635', '085599887744', 'Jl. Kedondong No. 4, Balikpapan', 'user', 'lakilaki', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(31, 'Nina Andriani', 'nina.andriani@example.com', 'dc2d937cba912f093445d008f0461c83', '085644332211', 'Jl. Kesemek No. 16, Kupang', 'user', 'perempuan', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(32, 'Aris Munandar', 'aris.munandar@example.com', 'ccf08fd9a560b266470bf8ab97fc7c26', '087711223344', 'Jl. Duren No. 1, Banjarmasin', 'user', 'lakilaki', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(33, 'Selvi Oktavia', 'selvi.oktavia@example.com', '3b635d4df2c9ece93b97759531d6ed01', '083866554433', 'Jl. Kismis No. 19, Palu', 'user', 'perempuan', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(34, 'Bayu Pamungkas', 'bayu.pamungkas@example.com', '926742e502de7d22686bb1d4a07fe635', '081345987654', 'Jl. Markisa No. 11, Kendari', 'user', 'lakilaki', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(35, 'Dinda Permata', 'dinda.permata@example.com', '3dc94727dbba08bdd21d7b318b410600', '082345987612', 'Jl. Jarak No. 3, Jayapura', 'user', 'perempuan', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(36, 'Irfan Maulana', 'irfan.maulana@example.com', '0c75f443030c092d82b67ef876fa4e4e', '085345678912', 'Jl. Ceremai No. 5, Ambon', 'user', 'lakilaki', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(37, 'Anisa Fitri', 'anisa.fitri@example.com', 'f849618fac31084ff0bafe6f877562e3', '081345678923', 'Jl. Gandaria No. 2, Ternate', 'user', 'perempuan', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(38, 'Teguh Wibowo', 'teguh.wibowo@example.com', 'd61af90de34e181dcb619fdc9c9f817d', '089812345678', 'Jl. Kurma No. 8, Serang', 'user', 'lakilaki', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(39, 'Rani Widya', 'rani.widya@example.com', '7aa4106f8d30c77db0517e813ace4a3b', '082355667788', 'Jl. Lontar No. 22, Cilegon', 'user', 'perempuan', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(40, 'Yusuf Kurniawan', 'yusuf.kurniawan@example.com', '48ad74b74844fadd28274afd5c617ccf', '085355667789', 'Jl. Bidara No. 1, Cianjur', 'user', 'lakilaki', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(41, 'Nadia Salma', 'nadia.salma@example.com', '8ba4260f47598cece4813a294952f7f3', '082244556677', 'Jl. Menteng No. 15, Karawang', 'user', 'perempuan', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(42, 'Dedy Irawan', 'dedy.irawan@example.com', '9ab4b766ba920fca672112a4d81464df', '083899665544', 'Jl. Kepel No. 6, Sukabumi', 'user', 'lakilaki', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(43, 'Melinda Zahra', 'melinda.zahra@example.com', 'b30628ea30edfe26e7650e7bd89cc8a1', '087755443322', 'Jl. Tanjung No. 19, Purwokerto', 'user', 'perempuan', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(44, 'Galih Saputro', 'galih.saputro@example.com', 'be961c906e3b375dced446d4cf0b6856', '082344556677', 'Jl. Kersen No. 20, Garut', 'user', 'lakilaki', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(45, 'Salsa Amelia', 'salsa.amelia@example.com', '831fc3acf61a6ac7f44f73287ece2942', '081333445566', 'Jl. Sawo Kecik No. 7, Majalengka', 'user', 'perempuan', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(46, 'Rizal Ramadhan', 'rizal.ramadhan@example.com', 'decb7cb773821f0e6486650c6f062be5', '082122334455', 'Jl. Kluwek No. 2, Banyumas', 'user', 'lakilaki', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(47, 'Intan Pratiwi', 'intan.pratiwi@example.com', 'b1a6a20d781fde908b1dd9af34deb8ea', '081288776655', 'Jl. Nangka No. 5, Cilacap', 'user', 'perempuan', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(48, 'Reza Alfian', 'reza.alfian@example.com', 'a5669b4e80cfb179cdd36be6eeada2cd', '083812345432', 'Jl. Durian No. 11, Klaten', 'user', 'lakilaki', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(49, 'Citra Anjani', 'citra.anjani@example.com', '9608e3da7f00ffa26507d1aa9a575197', '085766554433', 'Jl. Belimbing No. 9, Jepara', 'user', 'perempuan', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(50, 'Dimas Anggara', 'dimas.anggara@example.com', '0009fa95022c5c2c1276227121652c60', '081366554433', 'Jl. Matoa No. 18, Kudus', 'user', 'lakilaki', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(51, 'Ayu Kartini', 'ayu.kartini@example.com', '6ea84fafdeb8b3857abe9410c7144ccb', '082144556677', 'Jl. Apel No. 13, Pati', 'user', 'perempuan', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(52, 'Nando Prakoso', 'nando.prakoso@example.com', 'ea716d443f74ecc54957c884c0d05612', '085666778899', 'Jl. Pisang No. 6, Blora', 'user', 'lakilaki', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(53, 'Tasya Oktaviani', 'tasya.oktaviani@example.com', '458c7a67e7b9126ae7a9df4b821ea745', '082177889900', 'Jl. Melon No. 4, Rembang', 'user', 'perempuan', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(54, 'Adi Nugraha', 'adi.nugraha@example.com', '0659a802af127843be2e35e0e36c310a', '085211223344', 'Jl. Salak No. 8, Pemalang', 'user', 'lakilaki', '2025-06-03 12:10:23', '2025-06-03 12:10:23'),
(55, 'Yulia Fitri', 'yulia.fitri@example.com', 'ed645bbf72d0c71176142d93c99942c2', '083822334455', 'Jl. Mangga No. 7, Tegal', 'user', 'perempuan', '2025-06-03 12:10:23', '2025-06-03 12:10:23');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengiriman`
--

CREATE TABLE `pengiriman` (
  `id` int NOT NULL,
  `id_transaksi` int DEFAULT NULL,
  `id_user` int DEFAULT NULL,
  `ekspedisi` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status_pengiriman` enum('diproses','dikirim','selesai','gagal') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `no_resi` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tanggal_dikirim` date DEFAULT NULL,
  `tanggal_diterima` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `id_alamat` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `penjualan`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `penjualan` (
`bukti_pembayaran` varchar(255)
,`created_at` timestamp
,`id` int
,`id_kupon` int
,`id_user` int
,`metode_pembayaran` enum('COD','ELPAY','OVO','DANA')
,`status_transaksi` enum('dibayar','pending','cancel')
,`total_transaksi` decimal(12,2)
,`updated_at` timestamp
);

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk`
--

CREATE TABLE `produk` (
  `id` int NOT NULL,
  `nama_produk` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gambar` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `deskripsi` text COLLATE utf8mb4_general_ci,
  `id_kategori` int DEFAULT NULL,
  `stok` int DEFAULT NULL,
  `harga` decimal(10,2) DEFAULT NULL,
  `berat` decimal(10,2) DEFAULT NULL,
  `warna` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `jenis_produk` enum('wearable','pc','smart_home','camera','storage','networking','gaming','entertainment','office') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `produk`
--

INSERT INTO `produk` (`id`, `nama_produk`, `gambar`, `deskripsi`, `id_kategori`, `stok`, `harga`, `berat`, `warna`, `jenis_produk`, `created_at`, `updated_at`) VALUES
(1, 'Samsung S24', 'samsung_s24_blackdoff.jpg', 'Spesifikasi & Harga HP Samsung Galaxy S24  Layar 2340 x 1080 (FHD+) Dynamic AMOLED 2X 6,2 inci Refresh Rate 120 Hz Chipset & OS Prosesor Exynos 2400 GPU Xclipse 940 Android 14, One UI 6.1 Memori RAM 8GB, 12GB ROM 256GB, 512GB Kamera Utama 50MP, f/1.8, 24mm (wide), Dual Pixel PDAF, OIS 10MP, f/2.4, 67mm (telephoto), PDAF, OIS, 3x optical zoom 12MP, f/2.2, 13mm, 120˚ (ultrawide), Super Steady video Video 8K@24/30fps, HDR10+, stereo sound, gyro-EIS Kamera Depan 12MP, f/2.2, 26mm (wide), Dual Pixel PDAF Video 4K@30/60fps Baterai 4.000 mAh Fast charging 25W (50% dalam 30 menit) Lainnya Dual-SIM USB Type-C NFC Fingerprint (under display, ultrasonic) Tahan debu dan air (IP68) ', 1, 65, 14000000.00, 0.14, 'Hitam doff', 'wearable', '2025-05-18 10:52:34', '2025-06-02 13:33:32'),
(2, 'ASUS TUF GAMING F15 FX507ZR-I737D6G-O', 'asus_tuf_f15.webp', 'Spesifikasi :\r\n\r\nProcessor : 12th Gen Intel® Core™ i7-12700H Processor 2.3 GHz (24M Cache, up to 4.7 GHz, 14 cores: 6 P-cores and 8 E-cores)\r\nDisplay : 15.6″ Slim FHD 1920 x 1080, IPS, 300Hz, 300 nits, sRGB 100%, Adaptive-Sync\r\nMemory : 16GB (8GB DDR5-4800 SO-DIMM *2)\r\nStorage : 1TB M.2 NVMe™ PCIe® 3.0 SSD\r\nGraphic : NVIDIA® GeForce RTX™ 3070 Laptop GPU, 8GB GDDR6, 1460MHz* at 140W (1410MHz Boost Clock+50MHz OC, 115W+25W Dynamic Boost)\r\nKeyboard : Backlit Chiclet Keyboard RGB\r\nWireless : Wi-Fi 6(802.11ax)+Bluetooth 5.2 (Dual band) 2*2\r\nConnectivity : 1x RJ45 LAN port, 1x Thunderbolt™ 4 support DisplayPort™, 1x USB 3.2 Gen 2 Type-C support DisplayPort™ / G-SYNC, 2x USB 3.2 Gen 1 Type-A//1x 3.5mm Combo Audio Jack\r\nWebcam : 720P HD\r\nBattery : 90WHrs, 4S1P, 4-cell Li-ion\r\nWindows 11 Home + Office Home Students 2021\r\nFree : TUF backpack', 2, 100, 25999000.00, 2.40, 'Hitam', 'gaming', '2025-05-18 11:21:26', '2025-06-02 13:27:32'),
(20, 'Apple Watch Series 9', 'apple_watch_series9.jpg', 'Smartwatch terbaru dari Apple dengan fitur pelacakan kesehatan dan GPS.', 3, 120, 7999000.00, 0.31, 'midnight', 'wearable', '2025-06-02 14:03:46', '2025-06-02 14:04:11'),
(21, 'Dell XPS 15', 'dell_xps_15.jpg', 'Laptop kelas atas dengan prosesor Intel Core i7 dan layar 15.6 inci 4K.', 2, 50, 23990000.00, 1.80, 'silver', 'office', '2025-06-02 14:03:46', '2025-06-02 14:05:17'),
(22, 'Sony WH-1000XM5', 'sony_wh1000xm5.jpg', 'Headphone nirkabel dengan noise cancelling terbaik dan baterai tahan lama.', 5, 80, 4599000.00, 0.25, 'hitam', 'entertainment', '2025-06-02 14:03:46', '2025-06-02 14:04:37'),
(23, 'Canon EOS R50', 'canon_eos_r50.jpg', 'Kamera mirrorless 24.2MP dengan video 4K dan autofokus canggih.', 6, 30, 12999000.00, 0.76, 'hitam', 'camera', '2025-06-02 14:03:46', '2025-06-02 14:04:55'),
(24, 'Logitech G502 HERO', 'logitech_g502_hero.jpg', 'Mouse gaming dengan sensor HERO 25K dan tombol yang dapat diprogram.', 7, 100, 899000.00, 0.12, 'hitam', 'gaming', '2025-06-02 14:12:06', '2025-06-02 14:12:06'),
(25, 'Razer BlackWidow V4 Pro', 'razer_blackwidow_v4_pro.jpg', 'Keyboard mekanik gaming dengan switch Razer dan RGB lighting.', 7, 60, 2599000.00, 1.30, 'hitam', 'gaming', '2025-06-02 14:12:06', '2025-06-02 14:12:06'),
(26, 'Philips Hue White and Color Ambiance', 'philips_hue.jpg', 'Lampu pintar dengan warna yang dapat diatur dan kontrol suara.', 7, 70, 799000.00, 0.50, 'putih', 'smart_home', '2025-06-02 14:12:06', '2025-06-02 14:12:06'),
(27, 'Google Nest Thermostat', 'google_nest_thermostat.jpg', 'Thermostat pintar yang dapat mengatur suhu otomatis untuk rumahmu.', 7, 30, 2999000.00, 0.20, 'putih', 'smart_home', '2025-06-02 14:12:06', '2025-06-02 14:12:06'),
(28, 'JBL Flip 6', 'jbl_flip6.jpg', 'Speaker bluetooth portabel dengan suara jernih dan tahan air.', 7, 90, 1499000.00, 0.54, 'hitam', 'entertainment', '2025-06-02 14:12:06', '2025-06-02 14:12:06'),
(29, 'Apple AirPods Pro 2', 'apple_airpods_pro2.jpg', 'Earbud nirkabel dengan noise cancelling aktif dan kualitas suara tinggi.', 7, 50, 4599000.00, 0.01, 'putih', 'wearable', '2025-06-02 14:12:06', '2025-06-02 14:12:06'),
(30, 'Logitech MX Master 3S', 'logitech_mx_master_3s.jpg', 'Mouse ergonomis dengan sensor 8000 DPI dan klik senyap.', 7, 75, 1699000.00, 0.14, 'abu-abu', 'office', '2025-06-02 14:12:06', '2025-06-02 14:12:06'),
(31, 'SanDisk Extreme Pro 128GB', 'sandisk_extreme_pro.jpg', 'Kartu memori SDXC cepat dengan kecepatan baca sampai 170MB/s.', 7, 200, 649000.00, 0.01, 'hitam', 'storage', '2025-06-02 14:12:06', '2025-06-02 14:12:06'),
(32, 'Netgear Nighthawk AX8', 'netgear_nighthawk_ax8.jpg', 'Router Wi-Fi 6 dengan kecepatan tinggi dan jangkauan luas.', 7, 40, 2999000.00, 0.75, 'hitam', 'networking', '2025-06-02 14:12:06', '2025-06-02 14:12:06'),
(33, 'Corsair HS70 Pro', 'corsair_hs70_pro.jpg', 'Headset gaming wireless dengan suara surround 7.1 dan mikrofon noise cancelling.', 7, 55, 1799000.00, 0.32, 'hitam', 'gaming', '2025-06-02 14:12:06', '2025-06-02 14:12:06'),
(34, 'Dell XPS 13 9310', 'dell_xps_13_9310.jpg', 'Laptop ultraportable dengan Intel Core i7 generasi ke-11, layar 13.4 inci FHD+.', 2, 50, 17990000.00, 1.20, 'silver', 'pc', '2025-06-02 14:16:15', '2025-06-02 14:16:15'),
(35, 'MacBook Pro 14-inch M2 Pro', 'macbook_pro_14_m2.jpg', 'Laptop profesional dengan chip M2 Pro, layar Retina 14 inci, performa tinggi.', 2, 30, 32990000.00, 1.60, 'space gray', 'pc', '2025-06-02 14:16:15', '2025-06-02 14:16:15'),
(36, 'ASUS ROG Strix G15', 'asus_rog_strix_g15.jpg', 'Laptop gaming dengan AMD Ryzen 9 dan GPU NVIDIA RTX 3070.', 2, 25, 23990000.00, 2.30, 'hitam', 'gaming', '2025-06-02 14:16:15', '2025-06-02 14:16:15'),
(37, 'Lenovo ThinkPad X1 Carbon Gen 10', 'lenovo_thinkpad_x1.jpg', 'Laptop bisnis ringan dengan Intel Core i7, layar 14 inci dan fitur keamanan lengkap.', 2, 40, 20990000.00, 1.10, 'black', 'office', '2025-06-02 14:16:15', '2025-06-02 14:16:15'),
(38, 'Acer Aspire 5', 'acer_aspire_5.jpg', 'Laptop serbaguna dengan Intel Core i5, layar 15.6 inci Full HD.', 2, 60, 8490000.00, 1.90, 'silver', 'pc', '2025-06-02 14:16:15', '2025-06-02 14:16:15'),
(39, 'Microsoft Surface Laptop 5', 'surface_laptop_5.jpg', 'Laptop tipis dengan layar sentuh 13.5 inci dan prosesor Intel Core generasi terbaru.', 2, 35, 16990000.00, 1.30, 'platinum', 'pc', '2025-06-02 14:16:15', '2025-06-02 14:16:15'),
(40, 'HP EliteBook 840 G8', 'hp_elitebook_840_g8.jpg', 'Laptop bisnis kelas atas dengan Intel Core i7, fitur keamanan lengkap.', 2, 25, 19990000.00, 1.40, 'silver', 'office', '2025-06-02 14:16:15', '2025-06-02 14:16:15'),
(41, 'MSI GF65 Thin', 'msi_gf65_thin.jpg', 'Laptop gaming dengan prosesor Intel Core i7 dan GPU NVIDIA RTX 3060.', 2, 40, 17990000.00, 2.20, 'hitam', 'gaming', '2025-06-02 14:16:15', '2025-06-02 14:16:15'),
(42, 'HP Pavilion 15', 'hp_pavilion_15.jpg', 'Laptop multimedia dengan Intel Core i5 dan layar 15.6 inci.', 2, 50, 8990000.00, 1.80, 'silver', 'pc', '2025-06-02 14:16:15', '2025-06-02 14:16:15'),
(43, 'Lenovo Yoga 7i', 'lenovo_yoga_7i.jpg', 'Laptop convertible dengan layar sentuh 14 inci dan prosesor Intel Core i7.', 2, 30, 15990000.00, 1.50, 'abu-abu', 'pc', '2025-06-02 14:16:15', '2025-06-02 14:16:15'),
(44, 'HP Pavilion Gaming Desktop', 'hp_pavilion_gaming_desktop.jpg', 'Desktop gaming dengan prosesor Intel Core i5 dan GPU NVIDIA GTX 1660.', 4, 15, 15990000.00, 7.50, 'hitam', 'gaming', '2025-06-02 14:16:42', '2025-06-02 14:16:42'),
(45, 'Dell Alienware Aurora R13', 'alienware_aurora_r13.jpg', 'Desktop gaming premium dengan Intel Core i9 dan GPU NVIDIA RTX 3080.', 4, 10, 39990000.00, 14.50, 'hitam', 'gaming', '2025-06-02 14:16:42', '2025-06-02 14:16:42'),
(46, 'Lenovo IdeaCentre AIO 3', 'lenovo_ideacentre_aio_3.jpg', 'All-in-one PC dengan prosesor AMD Ryzen 5, layar 24 inci Full HD.', 4, 20, 10990000.00, 6.00, 'putih', 'pc', '2025-06-02 14:16:42', '2025-06-02 14:16:42'),
(47, 'Apple Mac Mini M2', 'apple_mac_mini_m2.jpg', 'Desktop kecil bertenaga dengan chip Apple M2, cocok untuk produktivitas.', 4, 25, 10990000.00, 1.20, 'silver', 'pc', '2025-06-02 14:16:42', '2025-06-02 14:16:42'),
(48, 'Corsair One i300', 'corsair_one_i300.jpg', 'Desktop gaming kompak dengan prosesor Intel Core i9 dan GPU NVIDIA RTX 3080 Ti.', 4, 10, 59990000.00, 10.50, 'hitam', 'gaming', '2025-06-02 14:16:42', '2025-06-02 14:16:42'),
(49, 'HP EliteDesk 800 G6', 'hp_elitedesk_800_g6.jpg', 'Desktop bisnis dengan Intel Core i7, performa handal untuk kerja kantoran.', 4, 30, 13990000.00, 6.80, 'hitam', 'office', '2025-06-02 14:16:42', '2025-06-02 14:16:42'),
(50, 'Dell OptiPlex 7090', 'dell_optiplex_7090.jpg', 'Desktop bisnis yang dapat diandalkan dengan prosesor Intel Core i5.', 4, 25, 12990000.00, 6.50, 'hitam', 'office', '2025-06-02 14:16:42', '2025-06-02 14:16:42'),
(51, 'Lenovo ThinkCentre M90t', 'lenovo_thinkcentre_m90t.jpg', 'Desktop workstation dengan Intel Core i7, cocok untuk profesional.', 4, 15, 17990000.00, 7.00, 'hitam', 'office', '2025-06-02 14:16:42', '2025-06-02 14:16:42'),
(52, 'Acer Predator Orion 3000', 'acer_predator_orion_3000.jpg', 'Desktop gaming dengan prosesor Intel Core i7 dan GPU NVIDIA RTX 3070.', 4, 20, 23990000.00, 8.50, 'hitam', 'gaming', '2025-06-02 14:16:42', '2025-06-02 14:16:42'),
(53, 'ASUS VivoPC K20', 'asus_vivopc_k20.jpg', 'Desktop kompak dengan prosesor Intel Core i5, cocok untuk penggunaan rumah dan kantor.', 4, 35, 8990000.00, 5.00, 'hitam', 'pc', '2025-06-02 14:16:42', '2025-06-02 14:16:42');

-- --------------------------------------------------------

--
-- Struktur dari tabel `rating`
--

CREATE TABLE `rating` (
  `id` int NOT NULL,
  `bintang` enum('1','2','3','4','5') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_user` int DEFAULT NULL,
  `id_produk` int DEFAULT NULL,
  `gambar_ulasan` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ulasan` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `rating`
--

INSERT INTO `rating` (`id`, `bintang`, `id_user`, `id_produk`, `gambar_ulasan`, `ulasan`, `created_at`, `updated_at`) VALUES
(1, '5', 1, 1, '6829f25d0f4e3.jpg', 'ooo', '2025-05-18 08:38:37', '2025-05-18 09:44:45');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi`
--

CREATE TABLE `transaksi` (
  `id` int NOT NULL,
  `id_user` int DEFAULT NULL,
  `total_transaksi` decimal(12,2) DEFAULT NULL,
  `metode_pembayaran` enum('COD','ELPAY','OVO','DANA') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status_transaksi` enum('dibayar','pending','cancel') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_kupon` int DEFAULT NULL,
  `bukti_pembayaran` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transaksi`
--

INSERT INTO `transaksi` (`id`, `id_user`, `total_transaksi`, `metode_pembayaran`, `status_transaksi`, `id_kupon`, `bukti_pembayaran`, `created_at`, `updated_at`) VALUES
(1, 15, 84000000.00, 'OVO', 'dibayar', 1, 'bukti_bayar_1.jpg', '2023-08-25 03:15:30', '2025-06-10 10:10:57'),
(2, 10, 77997000.00, 'DANA', 'dibayar', 4, 'bukti_bayar_2.jpg', '2023-03-12 07:20:05', '2024-01-20 02:35:11'),
(3, 19, 23997000.00, 'OVO', 'pending', NULL, NULL, '2024-01-01 01:00:00', '2024-02-15 04:00:00'),
(4, 12, 18396000.00, 'DANA', 'dibayar', 5, 'bukti_bayar_5.jpg', '2023-11-01 02:45:00', '2025-06-10 10:11:11'),
(5, 17, 12999000.00, 'COD', 'dibayar', NULL, 'bukti_bayar_6.jpg', '2023-02-10 04:00:00', '2023-02-10 04:05:00'),
(6, 18, 4495000.00, 'OVO', 'pending', 3, NULL, '2024-01-25 06:10:00', '2025-06-10 10:11:27'),
(7, 16, 5198000.00, 'DANA', 'dibayar', 6, 'bukti_bayar_8.jpg', '2023-07-07 00:07:07', '2024-06-03 12:21:00'),
(8, 13, 2397000.00, 'OVO', 'cancel', NULL, NULL, '2023-09-15 11:00:00', '2023-09-16 02:00:00'),
(9, 14, 2999000.00, 'OVO', 'dibayar', 8, 'bukti_bayar_10.jpg', '2023-04-01 03:00:00', '2025-06-10 10:11:45'),
(10, 11, 2998000.00, 'COD', 'dibayar', 1, 'bukti_bayar_11.jpg', '2023-10-20 07:00:00', '2025-06-10 10:12:05'),
(11, 9, 18396000.00, 'COD', 'pending', NULL, NULL, '2024-03-05 10:00:00', '2024-03-05 10:00:00'),
(12, 8, 5097000.00, 'DANA', 'dibayar', 4, 'bukti_bayar_13.jpg', '2023-06-01 05:00:00', '2025-06-10 10:12:15'),
(13, 7, 6490000.00, 'DANA', 'dibayar', 10, 'bukti_bayar_14.jpg', '2023-01-10 02:00:00', '2024-01-15 03:00:00'),
(14, 6, 5998000.00, 'OVO', 'pending', NULL, NULL, '2024-04-10 08:00:00', '2024-04-10 08:00:00'),
(15, 3, 32990000.00, 'COD', 'dibayar', 11, 'bukti_bayar_18.jpg', '2023-02-28 01:00:00', '2023-02-28 01:05:00'),
(16, 1, 20990000.00, 'DANA', 'dibayar', 12, 'bukti_bayar_20.jpg', '2023-07-22 06:00:00', '2024-05-15 03:00:00'),
(17, 20, 25470000.00, 'OVO', 'dibayar', 13, 'bukti_bayar_21.jpg', '2023-04-15 02:00:00', '2024-04-25 04:00:00'),
(18, 19, 16990000.00, 'COD', 'pending', 14, NULL, '2024-02-01 04:00:00', '2025-06-10 10:12:34'),
(19, 18, 39980000.00, 'DANA', 'dibayar', 15, 'bukti_bayar_23.jpg', '2023-09-01 07:00:00', '2025-06-10 10:12:41'),
(20, 17, 17990000.00, 'COD', 'dibayar', NULL, 'bukti_bayar_24.jpg', '2023-01-05 03:00:00', '2023-01-05 03:05:00'),
(21, 16, 17980000.00, 'OVO', 'pending', 16, NULL, '2024-03-10 05:00:00', '2025-06-10 10:12:47'),
(22, 15, 14000000.00, 'DANA', 'dibayar', 17, 'bukti_bayar_26.jpg', '2023-08-01 03:00:00', '2024-05-01 07:00:00'),
(23, 14, 7999000.00, 'OVO', 'dibayar', 18, 'bukti_bayar_27.jpg', '2023-03-01 07:00:00', '2024-03-05 09:00:00'),
(24, 13, 9198000.00, 'DANA', 'pending', NULL, NULL, '2024-01-10 01:00:00', '2025-06-10 10:12:55'),
(25, 12, 2697000.00, 'DANA', 'dibayar', 19, 'bukti_bayar_29.jpg', '2023-05-15 09:00:00', '2025-06-10 10:13:03'),
(26, 11, 2599000.00, 'COD', 'dibayar', 20, 'bukti_bayar_30.jpg', '2023-11-20 02:00:00', '2023-11-20 02:05:00'),
(27, 10, 3196000.00, 'COD', 'pending', NULL, NULL, '2024-02-20 06:00:00', '2025-06-10 10:13:12'),
(28, 9, 2998000.00, 'DANA', 'dibayar', 21, 'bukti_bayar_32.jpg', '2023-07-01 00:00:00', '2024-06-03 12:21:00'),
(29, 8, 4599000.00, 'OVO', 'cancel', NULL, NULL, '2023-09-05 11:00:00', '2023-09-06 02:00:00'),
(30, 7, 8495000.00, 'DANA', 'dibayar', 23, 'bukti_bayar_34.jpg', '2023-04-20 03:00:00', '2025-06-10 10:13:20'),
(31, 6, 4543000.00, 'COD', 'dibayar', NULL, 'bukti_bayar_35.jpg', '2023-10-01 07:00:00', '2025-06-10 10:13:25'),
(32, 3, 17990000.00, 'DANA', 'dibayar', 25, 'bukti_bayar_38.jpg', '2023-01-20 02:00:00', '2024-01-25 03:00:00'),
(33, 1, 23990000.00, 'COD', 'dibayar', 27, 'bukti_bayar_40.jpg', '2023-03-05 04:00:00', '2025-06-10 10:13:31'),
(34, 20, 62970000.00, 'DANA', 'dibayar', NULL, 'bukti_bayar_41.jpg', '2023-12-10 03:00:00', '2025-06-10 10:13:36'),
(35, 19, 8490000.00, 'COD', 'dibayar', 29, 'bukti_bayar_42.jpg', '2023-02-01 01:00:00', '2023-02-01 01:05:00'),
(36, 18, 33980000.00, 'OVO', 'pending', 30, NULL, '2024-01-28 09:00:00', '2025-06-10 10:13:43'),
(37, 17, 19990000.00, 'DANA', 'dibayar', 31, 'bukti_bayar_44.jpg', '2023-07-05 06:00:00', '2024-05-20 03:00:00'),
(38, 16, 35980000.00, 'OVO', 'dibayar', 32, 'bukti_bayar_45.jpg', '2023-04-05 02:00:00', '2024-04-15 04:00:00'),
(39, 15, 8990000.00, 'DANA', 'pending', 33, NULL, '2024-02-10 04:00:00', '2025-06-10 10:13:48'),
(40, 14, 42000000.00, 'DANA', 'dibayar', 34, 'bukti_bayar_47.jpg', '2023-09-10 07:00:00', '2025-06-10 10:14:19'),
(41, 13, 51998000.00, 'COD', 'dibayar', NULL, 'bukti_bayar_48.jpg', '2023-01-15 03:00:00', '2023-01-15 03:05:00'),
(42, 12, 12999000.00, 'COD', 'pending', 36, NULL, '2024-03-20 05:00:00', '2025-06-10 10:14:34'),
(43, 11, 3596000.00, 'DANA', 'dibayar', 37, 'bukti_bayar_50.jpg', '2023-08-15 03:00:00', '2024-05-05 07:00:00'),
(44, 10, 15000000.00, 'OVO', 'dibayar', 39, 'bukti_bayar_51.jpg', '2023-09-01 04:00:00', '2024-01-01 05:00:00'),
(45, 9, 2200000.00, 'DANA', 'pending', NULL, NULL, '2024-01-05 02:30:00', '2025-06-10 10:14:48'),
(46, 8, 12000000.00, 'DANA', 'dibayar', 40, 'bukti_bayar_53.jpg', '2023-06-15 07:00:00', '2025-06-10 10:14:54'),
(47, 7, 7500000.00, 'COD', 'dibayar', NULL, 'bukti_bayar_54.jpg', '2023-11-22 03:00:00', '2023-11-22 03:05:00'),
(48, 6, 30000000.00, 'DANA', 'pending', 43, NULL, '2024-03-25 09:00:00', '2025-06-10 10:15:01'),
(49, 3, 5000000.00, 'DANA', 'dibayar', 44, 'bukti_bayar_56.jpg', '2023-04-10 06:00:00', '2024-04-01 07:00:00'),
(50, 1, 19000000.00, 'OVO', 'dibayar', 45, 'bukti_bayar_57.jpg', '2023-07-01 01:00:00', '2024-05-18 02:00:00'),
(51, 20, 9500000.00, 'COD', 'pending', NULL, NULL, '2024-02-15 03:00:00', '2025-06-10 10:15:10'),
(52, 19, 28000000.00, 'COD', 'dibayar', 47, 'bukti_bayar_59.jpg', '2023-10-05 08:00:00', '2025-06-10 10:15:15'),
(53, 18, 4000000.00, 'COD', 'dibayar', NULL, 'bukti_bayar_60.jpg', '2023-01-28 04:00:00', '2023-01-28 04:05:00'),
(54, 17, 11000000.00, 'DANA', 'pending', 48, NULL, '2024-04-05 07:00:00', '2025-06-10 10:15:23'),
(55, 16, 33000000.00, 'DANA', 'dibayar', 50, 'bukti_bayar_62.jpg', '2023-05-01 02:00:00', '2024-03-12 03:00:00'),
(56, 15, 6000000.00, 'OVO', 'dibayar', NULL, 'bukti_bayar_63.jpg', '2023-12-12 05:00:00', '2024-01-01 06:00:00'),
(57, 14, 2500000.00, 'DANA', 'pending', 52, NULL, '2024-01-20 04:00:00', '2025-06-10 10:15:30'),
(58, 13, 18000000.00, 'COD', 'dibayar', NULL, 'bukti_bayar_65.jpg', '2023-08-08 09:00:00', '2025-06-10 10:15:36'),
(59, 12, 9000000.00, 'COD', 'dibayar', 1, 'bukti_bayar_66.jpg', '2023-02-14 03:00:00', '2023-02-14 03:05:00'),
(60, 11, 45000000.00, 'OVO', 'pending', 3, NULL, '2024-03-01 06:00:00', '2025-06-10 10:15:43'),
(61, 10, 7000000.00, 'DANA', 'dibayar', 4, 'bukti_bayar_68.jpg', '2023-09-19 02:00:00', '2024-05-08 03:00:00'),
(62, 9, 3000000.00, 'OVO', 'dibayar', 5, 'bukti_bayar_69.jpg', '2023-04-22 07:00:00', '2024-04-12 08:00:00'),
(63, 8, 20000000.00, 'OVO', 'pending', NULL, NULL, '2024-02-28 04:00:00', '2025-06-10 10:15:49'),
(64, 7, 10000000.00, 'DANA', 'dibayar', 6, 'bukti_bayar_71.jpg', '2023-07-07 00:07:07', '2025-06-10 10:15:58'),
(65, 6, 16000000.00, 'COD', 'dibayar', NULL, 'bukti_bayar_72.jpg', '2023-01-01 01:00:00', '2023-01-01 01:05:00'),
(66, 3, 500000.00, 'COD', 'pending', 8, NULL, '2024-01-12 08:00:00', '2025-06-10 10:16:04'),
(67, 1, 40000000.00, 'DANA', 'dibayar', 9, 'bukti_bayar_74.jpg', '2023-06-05 03:00:00', '2024-04-18 04:00:00'),
(68, 20, 8000000.00, 'OVO', 'dibayar', 10, 'bukti_bayar_75.jpg', '2023-11-01 07:00:00', '2024-02-25 08:00:00'),
(69, 19, 13000000.00, 'DANA', 'pending', NULL, NULL, '2024-03-08 02:00:00', '2025-06-10 10:16:10'),
(70, 18, 21000000.00, 'COD', 'dibayar', 11, 'bukti_bayar_77.jpg', '2023-02-02 05:00:00', '2025-06-10 10:16:16');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi_detail`
--

CREATE TABLE `transaksi_detail` (
  `id` int NOT NULL,
  `id_produk` int DEFAULT NULL,
  `id_transaksi` int DEFAULT NULL,
  `jumlah` int DEFAULT NULL,
  `harga_satuan` decimal(10,2) DEFAULT NULL,
  `subtotal` decimal(12,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transaksi_detail`
--

INSERT INTO `transaksi_detail` (`id`, `id_produk`, `id_transaksi`, `jumlah`, `harga_satuan`, `subtotal`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 2, 14000000.00, 28000000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(2, 2, 1, 1, 25999000.00, 25999000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(3, 20, 2, 3, 7999000.00, 23997000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(4, 21, 2, 1, 23990000.00, 23990000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(5, 22, 3, 2, 4599000.00, 9198000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(6, 23, 3, 1, 12999000.00, 12999000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(7, 24, 4, 5, 899000.00, 4495000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(8, 25, 4, 2, 2599000.00, 5198000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(9, 26, 5, 4, 799000.00, 3196000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(10, 27, 5, 1, 2999000.00, 2999000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(11, 28, 6, 3, 1499000.00, 4497000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(12, 29, 6, 2, 4599000.00, 9198000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(13, 30, 7, 1, 1699000.00, 1699000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(14, 31, 7, 6, 649000.00, 3894000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(15, 32, 8, 1, 2999000.00, 2999000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(16, 33, 8, 3, 1799000.00, 5397000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(17, 34, 9, 1, 17990000.00, 17990000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(18, 35, 9, 1, 32990000.00, 32990000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(19, 36, 10, 2, 23990000.00, 47980000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(20, 37, 10, 1, 20990000.00, 20990000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(21, 38, 11, 3, 8490000.00, 25470000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(22, 39, 11, 1, 16990000.00, 16990000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(23, 40, 12, 2, 19990000.00, 39980000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(24, 41, 12, 1, 17990000.00, 17990000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(25, 42, 13, 3, 8990000.00, 26970000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(26, 1, 13, 1, 14000000.00, 14000000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(27, 2, 14, 2, 25999000.00, 51998000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(28, 20, 14, 1, 7999000.00, 7999000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(29, 21, 15, 2, 23990000.00, 47980000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(30, 22, 15, 1, 4599000.00, 4599000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(31, 23, 16, 3, 12999000.00, 38997000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(32, 24, 16, 2, 899000.00, 1798000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(33, 25, 17, 1, 2599000.00, 2599000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(34, 26, 17, 3, 799000.00, 2397000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(35, 27, 18, 2, 2999000.00, 5998000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(36, 28, 18, 1, 1499000.00, 1499000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(37, 29, 19, 4, 4599000.00, 18396000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(38, 30, 19, 2, 1699000.00, 3398000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(39, 31, 20, 3, 649000.00, 1947000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(40, 32, 20, 1, 2999000.00, 2999000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(41, 33, 21, 2, 1799000.00, 3598000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(42, 34, 21, 1, 17990000.00, 17990000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(43, 35, 22, 1, 32990000.00, 32990000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(44, 36, 22, 1, 23990000.00, 23990000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(45, 37, 23, 3, 20990000.00, 62970000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(46, 38, 23, 1, 8490000.00, 8490000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(47, 39, 24, 2, 16990000.00, 33980000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(48, 40, 24, 1, 19990000.00, 19990000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(49, 41, 25, 1, 17990000.00, 17990000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31'),
(50, 42, 25, 3, 8990000.00, 26970000.00, '2025-06-03 12:48:31', '2025-06-03 12:48:31');

-- --------------------------------------------------------

--
-- Struktur untuk view `penjualan`
--
DROP TABLE IF EXISTS `penjualan`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `penjualan`  AS SELECT `transaksi`.`id` AS `id`, `transaksi`.`id_user` AS `id_user`, `transaksi`.`total_transaksi` AS `total_transaksi`, `transaksi`.`metode_pembayaran` AS `metode_pembayaran`, `transaksi`.`status_transaksi` AS `status_transaksi`, `transaksi`.`id_kupon` AS `id_kupon`, `transaksi`.`bukti_pembayaran` AS `bukti_pembayaran`, `transaksi`.`created_at` AS `created_at`, `transaksi`.`updated_at` AS `updated_at` FROM `transaksi` WHERE (`transaksi`.`status_transaksi` = 'dibayar') ;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `alamat_pengiriman`
--
ALTER TABLE `alamat_pengiriman`
  ADD PRIMARY KEY (`id_alamat`),
  ADD KEY `id_pengguna` (`id_pengguna`);

--
-- Indeks untuk tabel `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_cart_user` (`id_user`),
  ADD KEY `fk_cart_produk` (`id_produk`);

--
-- Indeks untuk tabel `faq`
--
ALTER TABLE `faq`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `favorite`
--
ALTER TABLE `favorite`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_user` (`id_user`,`id_produk`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indeks untuk tabel `garansi`
--
ALTER TABLE `garansi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_produk` (`id_produk`),
  ADD KEY `id_transaksi` (`id_transaksi`);

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `kupon_diskon`
--
ALTER TABLE `kupon_diskon`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indeks untuk tabel `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeks untuk tabel `pengiriman`
--
ALTER TABLE `pengiriman`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_transaksi` (`id_transaksi`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `fk_alamat_pengiriman` (`id_alamat`);

--
-- Indeks untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_kategori` (`id_kategori`);

--
-- Indeks untuk tabel `rating`
--
ALTER TABLE `rating`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indeks untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_kupon` (`id_kupon`);

--
-- Indeks untuk tabel `transaksi_detail`
--
ALTER TABLE `transaksi_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_produk` (`id_produk`),
  ADD KEY `id_transaksi` (`id_transaksi`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `alamat_pengiriman`
--
ALTER TABLE `alamat_pengiriman`
  MODIFY `id_alamat` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT untuk tabel `faq`
--
ALTER TABLE `faq`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT untuk tabel `favorite`
--
ALTER TABLE `favorite`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT untuk tabel `garansi`
--
ALTER TABLE `garansi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `kupon_diskon`
--
ALTER TABLE `kupon_diskon`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT untuk tabel `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT untuk tabel `pengiriman`
--
ALTER TABLE `pengiriman`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `produk`
--
ALTER TABLE `produk`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT untuk tabel `rating`
--
ALTER TABLE `rating`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT untuk tabel `transaksi_detail`
--
ALTER TABLE `transaksi_detail`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `alamat_pengiriman`
--
ALTER TABLE `alamat_pengiriman`
  ADD CONSTRAINT `alamat_pengiriman_ibfk_1` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id`);

--
-- Ketidakleluasaan untuk tabel `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `fk_cart_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cart_user` FOREIGN KEY (`id_user`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `favorite`
--
ALTER TABLE `favorite`
  ADD CONSTRAINT `favorite_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `pengguna` (`id`),
  ADD CONSTRAINT `favorite_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id`);

--
-- Ketidakleluasaan untuk tabel `garansi`
--
ALTER TABLE `garansi`
  ADD CONSTRAINT `garansi_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `pengguna` (`id`),
  ADD CONSTRAINT `garansi_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id`),
  ADD CONSTRAINT `garansi_ibfk_3` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id`);

--
-- Ketidakleluasaan untuk tabel `pengiriman`
--
ALTER TABLE `pengiriman`
  ADD CONSTRAINT `fk_alamat_pengiriman` FOREIGN KEY (`id_alamat`) REFERENCES `alamat_pengiriman` (`id_alamat`),
  ADD CONSTRAINT `pengiriman_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id`),
  ADD CONSTRAINT `pengiriman_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `pengguna` (`id`);

--
-- Ketidakleluasaan untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `produk_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id`);

--
-- Ketidakleluasaan untuk tabel `rating`
--
ALTER TABLE `rating`
  ADD CONSTRAINT `rating_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `pengguna` (`id`),
  ADD CONSTRAINT `rating_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id`);

--
-- Ketidakleluasaan untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `pengguna` (`id`),
  ADD CONSTRAINT `transaksi_ibfk_2` FOREIGN KEY (`id_kupon`) REFERENCES `kupon_diskon` (`id`);

--
-- Ketidakleluasaan untuk tabel `transaksi_detail`
--
ALTER TABLE `transaksi_detail`
  ADD CONSTRAINT `transaksi_detail_ibfk_1` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id`),
  ADD CONSTRAINT `transaksi_detail_ibfk_2` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
