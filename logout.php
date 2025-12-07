<?php
session_start();

// Hapus semua session pengguna
unset($_SESSION['user_logged_in']);
unset($_SESSION['id_pengguna']);
unset($_SESSION['nama_pengguna']);

// Redirect ke halaman utama
header('Location: index.php');
exit;
?>