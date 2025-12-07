<?php
// File: cek_db.php
// Fungsi: Untuk memeriksa koneksi database dan melihat tabel yang ada.

echo "<h1>Pemeriksaan Database</h1>";

// 1. Memuat konfigurasi
require_once 'config.php';
echo "<p>File config.php berhasil dimuat.</p>";
echo "<p>Menghubungkan ke database '<strong>" . $db_name . "</strong>' di host '<strong>" . $db_host . "</strong>'...</p>";

// 2. Memeriksa koneksi
if ($conn->connect_error) {
    die("<p style='color:red;'><strong>Koneksi Gagal:</strong> " . $conn->connect_error . "</p>");
}
echo "<p style='color:green;'><strong>Koneksi Berhasil!</strong></p>";
echo "<hr>";

// 3. Menjalankan query untuk melihat semua tabel
$result = $conn->query("SHOW TABLES");

if (!$result) {
    die("<p style='color:red;'><strong>Gagal menjalankan query SHOW TABLES:</strong> " . $conn->error . "</p>");
}

// 4. Menampilkan hasil
echo "<h2>Tabel yang ditemukan di database '" . $db_name . "':</h2>";

if ($result->num_rows > 0) {
    echo "<ul>";
    while ($row = $result->fetch_row()) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color:orange;'><strong>Tidak ada tabel yang ditemukan di database ini.</strong></p>";
}

$conn->close();
?>
