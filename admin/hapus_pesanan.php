<?php
session_start();

// Cek jika admin belum login, alihkan ke halaman login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once '../config.php';

if (isset($_GET['id'])) {
    $id_pesanan = $_GET['id'];

    // Cek status pesanan sebelum menghapus
    $stmt_check = $conn->prepare("SELECT status FROM pesanan WHERE id_pesanan = ?");
    $stmt_check->bind_param("i", $id_pesanan);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    if ($row = $result_check->fetch_assoc()) {
        if ($row['status'] == 'selesai') {
            // Jika statusnya 'selesai', jangan hapus. Alihkan dengan pesan error.
            header('Location: index.php?status=gagal_hapus_selesai');
            exit();
        }
    }

    // Mulai transaksi
    $conn->begin_transaction();

    try {
        // Hapus terlebih dahulu item pesanan terkait di tabel detail_pesanan
        $stmt_detail = $conn->prepare("DELETE FROM detail_pesanan WHERE id_pesanan = ?");
        $stmt_detail->bind_param("i", $id_pesanan);
        $stmt_detail->execute();

        // Setelah itu, hapus pesanan utama dari tabel pesanan
        $stmt_pesanan = $conn->prepare("DELETE FROM pesanan WHERE id_pesanan = ?");
        $stmt_pesanan->bind_param("i", $id_pesanan);
        $stmt_pesanan->execute();

        // Jika semua query berhasil, commit transaksi
        $conn->commit();

        // Arahkan kembali ke halaman utama admin dengan pesan sukses
        header('Location: index.php?status=sukses_hapus');
        exit();

    } catch (mysqli_sql_exception $exception) {
        // Jika terjadi error, rollback transaksi
        $conn->rollback();

        // Arahkan kembali dengan pesan error
        header('Location: index.php?status=gagal_hapus');
        exit();
    }

} else {
    // Jika tidak ada ID, kembali ke halaman utama
    header('Location: index.php');
    exit();
}
?>