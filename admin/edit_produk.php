<?php
session_start();

// Cek jika admin belum login, alihkan ke halaman login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once '../config.php';

$pesan = '';
$produk = null;

// Pastikan ada ID produk
if (!isset($_GET['id'])) {
    header('Location: kelola_produk.php');
    exit();
}
$id_produk = $_GET['id'];

// Logika untuk update produk
if (isset($_POST['update'])) {
    $nama = $conn->real_escape_string($_POST['nama']);
    $harga = $conn->real_escape_string($_POST['harga']);
    $deskripsi = $conn->real_escape_string($_POST['deskripsi']);
    $gambar_baru = $_FILES['gambar_baru'];

    if (!empty($nama) && !empty($harga) && !empty($deskripsi)) {
        // Cek apakah ada gambar baru yang diunggah
        if ($gambar_baru['error'] === UPLOAD_ERR_OK) {
            $nama_file_gambar = basename($gambar_baru['name']);
            $target_path = '../assets/img/' . $nama_file_gambar;

            // Pindahkan file yang diunggah ke folder tujuan
            if (move_uploaded_file($gambar_baru['tmp_name'], $target_path)) {
                // Update semua data termasuk gambar baru
                $stmt = $conn->prepare("UPDATE produk SET nama_produk = ?, harga = ?, deskripsi = ?, gambar = ? WHERE id_produk = ?");
                $stmt->bind_param("sissi", $nama, $harga, $deskripsi, $nama_file_gambar, $id_produk);
            } else {
                $pesan = "<div class='alert alert-danger'>Gagal mengunggah gambar baru.</div>";
                $stmt = null; // Jangan eksekusi query jika upload gagal
            }
        } else {
            // Update data tanpa mengubah gambar
            $stmt = $conn->prepare("UPDATE produk SET nama_produk = ?, harga = ?, deskripsi = ? WHERE id_produk = ?");
            $stmt->bind_param("sisi", $nama, $harga, $deskripsi, $id_produk);
        }

        // Eksekusi query jika $stmt sudah disiapkan
        if (isset($stmt) && $stmt->execute()) {
            header('Location: kelola_produk.php?pesan=diupdate');
            exit();
        } else if (empty($pesan)) {
            $pesan = "<div class='alert alert-danger'>Gagal memperbarui produk.</div>";
        }

    } else {
        $pesan = "<div class='alert alert-warning'>Nama, harga, dan deskripsi wajib diisi.</div>";
    }
}

// Ambil data produk yang akan diedit
$stmt = $conn->prepare("SELECT * FROM produk WHERE id_produk = ?");
$stmt->bind_param("i", $id_produk);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 1) {
    $produk = $result->fetch_assoc();
} else {
    // Jika produk tidak ditemukan, kembali ke halaman kelola
    header('Location: kelola_produk.php');
    exit();
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Edit Produk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="#">Admin Panel</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Lihat Pesanan</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="kelola_produk.php">Kelola Produk</a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                 <li class="nav-item">
                    <a class="nav-link" href="../index.php" target="_blank">Lihat Toko</a>
                </li>
                 <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h3>Edit Produk: <?php echo htmlspecialchars($produk['nama_produk']); ?></h3>
    <hr>
    <a href="kelola_produk.php" class="btn btn-secondary mb-3">&laquo; Kembali ke Daftar Produk</a>
    <?php echo $pesan; ?>
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Nama Produk</label>
            <input type="text" name="nama" class="form-control" value="<?php echo htmlspecialchars($produk['nama_produk']); ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Harga</label>
            <input type="number" name="harga" class="form-control" value="<?php echo htmlspecialchars($produk['harga']); ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Deskripsi</label>
            <textarea name="deskripsi" class="form-control" rows="3" required><?php echo htmlspecialchars($produk['deskripsi']); ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Gambar Saat Ini</label><br>
            <img src="../assets/img/<?php echo htmlspecialchars($produk['gambar']); ?>" alt="Gambar Produk" style="max-width: 200px; max-height: 200px;" class="mb-2">
        </div>
        <div class="mb-3">
            <label class="form-label">Unggah Gambar Baru (Opsional)</label>
            <input type="file" name="gambar_baru" class="form-control">
            <div class="form-text">Kosongkan jika tidak ingin mengubah gambar. Memilih file baru akan menggantikan gambar saat ini.</div>
        </div>
        <button type="submit" name="update" class="btn btn-primary w-100">Update Produk</button>
    </form>
</div>

</body>
</html>
