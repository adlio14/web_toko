<?php
// File: setup_database.php
// Fungsi: Membuat database dan tabel secara otomatis.

echo "<!DOCTYPE html><html lang='id'><head><title>Setup Database</title><link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'></head><body class='container mt-4'>";
echo "<h1>Proses Setup Database</h1>";
echo "<p>Script ini akan mencoba membuat database dan tabel yang diperlukan secara otomatis.</p><hr class='my-4'>";

// Konfigurasi dasar - tanpa nama database
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'dbtoko'; // Nama database yang kita inginkan

// 1. Membuat koneksi ke server MySQL
echo "<div class='alert alert-info'>Langkah 1: Menghubungkan ke server MySQL di '$db_host'...</div>";
$conn = new mysqli($db_host, $db_user, $db_pass);

if ($conn->connect_error) {
    die("<div class='alert alert-danger'><strong>Koneksi ke server MySQL GAGAL:</strong> " . $conn->connect_error . "<br>Pastikan XAMPP dan server MySQL Anda sudah berjalan.</div></body></html>");
}
echo "<div class='alert alert-success'>Koneksi ke server MySQL berhasil.</div>";

// 2. Membuat database jika belum ada
echo "<div class='alert alert-info'>Langkah 2: Mencoba membuat database '<strong>$db_name</strong>'...</div>";
$sql_create_db = "CREATE DATABASE IF NOT EXISTS $db_name";
if ($conn->query($sql_create_db) === TRUE) {
    echo "<div class='alert alert-success'>Database '<strong>$db_name</strong>' berhasil dibuat atau sudah ada.</div>";
} else {
    die("<div class='alert alert-danger'><strong>Gagal membuat database:</strong> " . $conn->error . "</div></body></html>");
}

// 3. Memilih database yang baru dibuat
$conn->select_db($db_name);
echo "<div class='alert alert-info'>Langkah 3: Memilih database '<strong>$db_name</strong>'.</div>";

// 4. SQL untuk membuat semua tabel dan mengisi data
$sql_commands = "
DROP TABLE IF EXISTS `detail_pesanan`;
DROP TABLE IF EXISTS `pesanan`;
DROP TABLE IF EXISTS `produk`;
DROP TABLE IF EXISTS `admin`;

CREATE TABLE `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `produk` (
  `id_produk` int(11) NOT NULL AUTO_INCREMENT,
  `nama_produk` varchar(255) NOT NULL,
  `harga` int(11) NOT NULL,
  `gambar` varchar(255) NOT NULL,
  `deskripsi` text NOT NULL,
  PRIMARY KEY (`id_produk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `pesanan` (
  `id_pesanan` int(11) NOT NULL AUTO_INCREMENT,
  `kode_unik` varchar(255) NOT NULL,
  `nama_pemesan` varchar(255) NOT NULL,
  `alamat_pemesan` text NOT NULL,
  `telepon_pemesan` varchar(25) NOT NULL,
  `tanggal_pesanan` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_harga` int(11) NOT NULL,
  `status` enum('diproses','dikirim','selesai','dibatalkan') NOT NULL DEFAULT 'diproses',
  PRIMARY KEY (`id_pesanan`),
  UNIQUE KEY `kode_unik` (`kode_unik`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `detail_pesanan` (
  `id_detail` int(11) NOT NULL AUTO_INCREMENT,
  `id_pesanan` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `harga_saat_pesan` int(11) NOT NULL,
  PRIMARY KEY (`id_detail`),
  KEY `id_pesanan` (`id_pesanan`),
  KEY `id_produk` (`id_produk`),
  CONSTRAINT `detail_pesanan_ibfk_1` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`) ON DELETE CASCADE,
  CONSTRAINT `detail_pesanan_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `admin` (`username`, `password`) VALUES
('adli', MD5('adli'));

INSERT INTO `produk` (`nama_produk`, `harga`, `gambar`, `deskripsi`) VALUES
('Mie Geprek', 3500, 'img12.jpg', 'Mie goreng dengan sambal geprek pedas dan ayam crispy.'),
('Mie Gelas', 3000, 'img11.jpg', 'Mie instan seduh dalam gelas, praktis dan cepat.'),
('Kecap Bango 60ml', 3500, 'img2.jpeg', 'Kecap manis legendaris yang terbuat dari kedelai hitam berkualitas.'),
('Chocolatos Drink', 2500, 'img13.jpg', 'Minuman coklat premium khas Italia.'),
('Mie Indomie Goreng', 3500, 'img5.jpeg', 'Mie instan goreng favorit semua orang.'),
('Pempers Bayi', 3000, 'img6.jpg', 'pempers untuk bayi'),
('Royco', 1000, 'img7.jpg', 'Untuk masakkan anda lebih nikmat'),
('susu kental manis cokelat', 3000, 'img8.jpg', 'susu bendera'),
('susu kental manis putih/vanilla', 3000, 'img9.jpg', 'Susu bendera'),
('Tisue', 13000, 'img10.jpg', 'Tisue besar');
";

// 5. Menjalankan multi-query
echo "<div class='alert alert-info'>Langkah 4: Menjalankan perintah untuk membuat tabel dan mengisi data...</div>";
if ($conn->multi_query($sql_commands)) {
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->next_result());
    echo "<div class='alert alert-success'><strong>Semua tabel berhasil dibuat dan data contoh berhasil ditambahkan!</strong></div>";
} else {
    echo "<div class='alert alert-danger'><strong>Terjadi error saat membuat tabel:</strong> " . $conn->error . "</div>";
}

echo "<hr class='my-4'><h2>Setup Selesai!</h2>";
echo "<p>Database Anda sekarang seharusnya sudah siap. Silakan coba buka kembali halaman utama toko.</p>";
echo "<a href='index.php' class='btn btn-primary btn-lg'>Buka Toko Sekarang</a>";

$conn->close();
echo "</body></html>";
?>
