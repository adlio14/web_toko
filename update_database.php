<?php
// File: update_database.php
// Fungsi: Menambahkan kolom baru ke tabel `pesanan` untuk fitur pembayaran.

echo "<!DOCTYPE html><html lang='id'><head><title>Update Database</title><link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'></head><body class='container mt-4'>";
echo "<h1>Proses Update Database untuk Fitur Pembayaran</h1>";
echo "<p>Script ini akan mencoba menambahkan kolom `metode_pembayaran`, `bukti_pembayaran`, dan `status_pembayaran` ke tabel `pesanan`.</p><hr class='my-4'>";

// 1. Memuat konfigurasi
require_once 'config.php';
echo "<div class='alert alert-info'>Langkah 1: Memuat konfigurasi dan menghubungkan ke database '<strong>" . $db_name . "</strong>'...</div>";

// 2. Memeriksa koneksi
if ($conn->connect_error) {
    die("<div class='alert alert-danger'><strong>Koneksi Gagal:</strong> " . $conn->connect_error . "</div></body></html>");
}
echo "<div class='alert alert-success'>Koneksi Berhasil!</div>";

// 3. SQL untuk mengubah tabel `pesanan`
$alter_sql = "
ALTER TABLE `pesanan`
ADD COLUMN `metode_pembayaran` VARCHAR(50) NOT NULL AFTER `total_harga`,
ADD COLUMN `bukti_pembayaran` VARCHAR(255) DEFAULT NULL AFTER `metode_pembayaran`,
ADD COLUMN `status_pembayaran` ENUM('pending', 'lunas', 'ditolak') NOT NULL DEFAULT 'pending' AFTER `bukti_pembayaran`;
";

echo "<div class='alert alert-info'>Langkah 2: Menjalankan perintah SQL untuk mengubah tabel `pesanan`...</div>";

// Cek apakah kolom sudah ada sebelum mencoba menambahkannya
$check_column_sql = "SHOW COLUMNS FROM `pesanan` LIKE 'metode_pembayaran'";
$result = $conn->query($check_column_sql);

if ($result->num_rows > 0) {
    echo "<div class='alert alert-warning'>Kolom `metode_pembayaran` sudah ada. Proses alter tabel dilewati.</div>";
} else {
    if ($conn->multi_query($alter_sql)) {
        // Loop through results to clear buffer
        while ($conn->next_result()) {
            if ($res = $conn->store_result()) {
                $res->free();
            }
        }
        echo "<div class='alert alert-success'><strong>Tabel `pesanan` berhasil diupdate!</strong> Kolom baru telah ditambahkan.</div>";
    } else {
        echo "<div class='alert alert-danger'><strong>Gagal mengubah tabel `pesanan`:</strong> " . $conn->error . "</div>";
    }
}


echo "<hr class='my-4'><h2>Update Selesai!</h2>";
echo "<p>Database Anda sekarang sudah mendukung fitur pembayaran. Anda bisa menghapus file ini (update_database.php) sekarang.</p>";
echo "<a href='index.php' class='btn btn-primary'>Kembali ke Toko</a>";

$conn->close();
echo "</body></html>";
?>