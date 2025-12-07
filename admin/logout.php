<?php
session_start(); // Mulai sesi

// Hapus variabel sesi admin
unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_username']);

// Alihkan ke halaman login admin setelah logout
header('Location: login.php');
exit;
?>