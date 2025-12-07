<?php
/*
File: config.php
Fungsi: Koneksi ke database MySQL
*/

// Konfigurasi Database
$db_host = 'localhost';    // Host database
$db_user = 'root';         // Username database
$db_pass = '';             // Password database
$db_name = 'dbtoko';       // Nama database (toko kelontong)

// Membuat koneksi
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Memeriksa koneksi
if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . $conn->connect_error);
}

// Mengatur zona waktu default
date_default_timezone_set('Asia/Jakarta');
?>
