<?php
session_start();

// Cek jika admin belum login, alihkan ke halaman login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once '../config.php';

$pesan = '';

// Logika untuk menambah produk
if (isset($_POST['tambah'])) {
    $nama = $conn->real_escape_string($_POST['nama']);
    $harga = $conn->real_escape_string($_POST['harga']);
    $deskripsi = $conn->real_escape_string($_POST['deskripsi']);
    $gambar_file = $_FILES['gambar'];

    if (!empty($nama) && !empty($harga) && !empty($deskripsi) && isset($gambar_file) && $gambar_file['error'] === UPLOAD_ERR_OK) {
        $nama_file_gambar = basename($gambar_file['name']);
        $target_path = '../assets/img/' . $nama_file_gambar;

        // Pindahkan file yang diunggah
        if (move_uploaded_file($gambar_file['tmp_name'], $target_path)) {
            $stmt = $conn->prepare("INSERT INTO produk (nama_produk, harga, deskripsi, gambar) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("siss", $nama, $harga, $deskripsi, $nama_file_gambar);
            if ($stmt->execute()) {
                $pesan = "<div class='alert alert-success'>Produk berhasil ditambahkan.</div>";
            } else {
                $pesan = "<div class='alert alert-danger'>Gagal menambahkan produk ke database.</div>";
            }
        } else {
            $pesan = "<div class='alert alert-danger'>Gagal mengunggah file gambar.</div>";
        }
    } else {
        $pesan = "<div class='alert alert-warning'>Semua field, termasuk gambar, wajib diisi.</div>";
    }
}

// Logika untuk menghapus produk
if (isset($_GET['hapus'])) {
    $id_produk = $_GET['hapus'];
    // Hati-hati: Sebaiknya ada konfirmasi sebelum menghapus
    $stmt = $conn->prepare("DELETE FROM produk WHERE id_produk = ?");
    $stmt->bind_param("i", $id_produk);
    if ($stmt->execute()) {
        header('Location: kelola_produk.php?pesan=dihapus');
        exit();
    }
}

if(isset($_GET['pesan']) && $_GET['pesan'] == 'dihapus'){
    $pesan = "<div class='alert alert-success'>Produk berhasil dihapus.</div>";
}
if(isset($_GET['pesan']) && $_GET['pesan'] == 'diupdate'){
    $pesan = "<div class='alert alert-success'>Produk berhasil diperbarui.</div>";
}


// Ambil semua produk
$result = $conn->query("SELECT * FROM produk ORDER BY id_produk DESC");

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Kelola Produk</title>
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
    <div class="row">
        <div class="col-md-8">
            <h3>Daftar Produk</h3>
            <hr>
            <?php echo $pesan; ?>
            <table class="table table-bordered table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Gambar</th>
                        <th>Nama Produk</th>
                        <th>Harga</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($produk = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $produk['id_produk']; ?></td>
                            <td><img src="../assets/img/<?php echo htmlspecialchars($produk['gambar']); ?>" alt="" width="80"></td>
                            <td><?php echo htmlspecialchars($produk['nama_produk']); ?></td>
                            <td>Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?></td>
                            <td>
                                <a href="edit_produk.php?id=<?php echo $produk['id_produk']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="kelola_produk.php?hapus=<?php echo $produk['id_produk']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus produk ini?')">Hapus</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center">Belum ada produk.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="col-md-4">
            <h3>Tambah Produk Baru</h3>
            <hr>
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Nama Produk</label>
                    <input type="text" name="nama" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Harga</label>
                    <input type="number" name="harga" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Gambar Produk</label>
                    <input type="file" name="gambar" class="form-control" required>
                </div>
                <button type="submit" name="tambah" class="btn btn-primary w-100">Tambah Produk</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
