<?php
// File: update_database_v2.php
// Fungsi: Menambahkan tabel pengguna dan mengupdate tabel pesanan.

echo "<!DOCTYPE html><html lang='id'><head><title>Update Database v2</title><link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'></head><body class='container mt-4'>";
echo "<h1>Proses Update Database untuk Fitur Pengguna</h1>";

require_once 'config.php';

if ($conn->connect_error) {
    die("<div class='alert alert-danger'><strong>Koneksi Gagal:</strong> " . $conn->connect_error . "</div></body></html>");
}
echo "<div class='alert alert-success'>Koneksi Berhasil!</div>";

// SQL untuk membuat tabel pengguna
$sql_create_pengguna = "
CREATE TABLE IF NOT EXISTS `pengguna` (
  `id_pengguna` int(11) NOT NULL AUTO_INCREMENT,
  `nama_pengguna` varchar(255) NOT NULL,
  `email_pengguna` varchar(255) NOT NULL,
  `password_pengguna` varchar(255) NOT NULL,
  PRIMARY KEY (`id_pengguna`),
  UNIQUE KEY `email_pengguna` (`email_pengguna`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

echo "<div class='alert alert-info'>Langkah 1: Membuat tabel `pengguna`...</div>";
if ($conn->query($sql_create_pengguna) === TRUE) {
    echo "<div class='alert alert-success'>Tabel `pengguna` berhasil dibuat atau sudah ada.</div>";
} else {
    echo "<div class='alert alert-danger'>Gagal membuat tabel `pengguna`: " . $conn->error . "</div>";
}

// SQL untuk mengubah tabel pesanan
$sql_alter_pesanan = "
ALTER TABLE `pesanan`
ADD COLUMN `id_pengguna` INT(11) NULL DEFAULT NULL AFTER `id_pesanan`,
ADD KEY `id_pengguna` (`id_pengguna`),
ADD CONSTRAINT `pesanan_ibfk_3` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id_pengguna`) ON DELETE SET NULL;
";

echo "<div class='alert alert-info'>Langkah 2: Mengubah tabel `pesanan` untuk menambahkan `id_pengguna`...</div>";

// Cek dulu apakah kolomnya sudah ada
$check_column = $conn->query("SHOW COLUMNS FROM `pesanan` LIKE 'id_pengguna'");
if ($check_column->num_rows > 0) {
    echo "<div class='alert alert-warning'>Kolom `id_pengguna` sudah ada di tabel `pesanan`. Proses dilewati.</div>";
} else {
    if ($conn->query($sql_alter_pesanan) === TRUE) {
        echo "<div class='alert alert-success'>Tabel `pesanan` berhasil diubah.</div>";
    } else {
        echo "<div class='alert alert-danger'>Gagal mengubah tabel `pesanan`: " . $conn->error . "</div>";
    }
}

echo "<hr><p>Proses update selesai. Anda bisa menghapus file ini sekarang.</p>";
echo "<a href='index.php' class='btn btn-primary'>Kembali ke Toko</a>";

$conn->close();
echo "</body></html>";
?>