<?php
session_start();

// Cek jika admin belum login, alihkan ke halaman login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once '../config.php';

// Update status pembayaran jika ada request
if (isset($_POST['update_pembayaran'])) {
    $id_pesanan = $_POST['id_pesanan'];
    $status_pembayaran_baru = $_POST['status_pembayaran'];
    $stmt = $conn->prepare("UPDATE pesanan SET status_pembayaran = ? WHERE id_pesanan = ?");
    $stmt->bind_param("si", $status_pembayaran_baru, $id_pesanan);
    $stmt->execute();
    header('Location: index.php');
    exit();
}

// Update status pesanan jika ada request
if (isset($_POST['update_status'])) {
    $id_pesanan = $_POST['id_pesanan'];
    $status_baru = $_POST['status'];
    $stmt = $conn->prepare("UPDATE pesanan SET status = ? WHERE id_pesanan = ?");
    $stmt->bind_param("si", $status_baru, $id_pesanan);
    $stmt->execute();
    header('Location: index.php');
    exit();
}

// Ambil data untuk statistik
// 1. Total Pendapatan (hanya dari pesanan yang statusnya 'selesai' dan pembayarannya 'lunas')
$total_penjualan_result = $conn->query("SELECT SUM(total_harga) AS total FROM pesanan WHERE status = 'selesai' AND status_pembayaran = 'lunas'");
$total_penjualan = $total_penjualan_result->fetch_assoc()['total'] ?? 0;

// 2. Ambil rincian produk yang terjual dari pesanan yang 'selesai' dan 'lunas'
$produk_terjual_result = $conn->query("
    SELECT p.nama_produk, dp.jumlah, dp.harga_saat_pesan, ps.id_pesanan, ps.tanggal_pesanan
    FROM detail_pesanan dp
    JOIN produk p ON dp.id_produk = p.id_produk
    JOIN pesanan ps ON dp.id_pesanan = ps.id_pesanan
    WHERE ps.status = 'selesai' AND ps.status_pembayaran = 'lunas'
    ORDER BY ps.tanggal_pesanan DESC
");


// Ambil semua data pesanan untuk tabel utama
$result = $conn->query("SELECT * FROM pesanan ORDER BY tanggal_pesanan DESC");

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Pesanan Masuk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="#">Admin Panel</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link active" href="index.php">Lihat Pesanan</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="kelola_produk.php">Kelola Produk</a>
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
    <h3>Statistik Penjualan</h3>
    <hr>
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Total Pendapatan (dari Pesanan Selesai)</h5>
                    <p class="card-text fs-4 fw-bold">Rp <?php echo number_format($total_penjualan, 0, ',', '.'); ?></p>
                </div>
            </div>
        </div>
    </div>

    <h4 class="mt-5">Rincian Produk Terjual (dari Pesanan Selesai)</h4>
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Tanggal Pesanan</th>
                    <th>ID Pesanan</th>
                    <th>Nama Produk</th>
                    <th>Jumlah</th>
                    <th>Harga Satuan</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($produk_terjual_result->num_rows > 0): ?>
                    <?php while($item = $produk_terjual_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo date('d M Y', strtotime($item['tanggal_pesanan'])); ?></td>
                        <td>#<?php echo $item['id_pesanan']; ?></td>
                        <td><?php echo htmlspecialchars($item['nama_produk']); ?></td>
                        <td><?php echo $item['jumlah']; ?></td>
                        <td>Rp <?php echo number_format($item['harga_saat_pesan'], 0, ',', '.'); ?></td>
                        <td>Rp <?php echo number_format($item['jumlah'] * $item['harga_saat_pesan'], 0, ',', '.'); ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">Belum ada produk yang terjual dari pesanan yang selesai.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <h3 class="mt-5">Daftar Pesanan Masuk</h3>
    <hr>

    <?php
    if (isset($_GET['status'])) {
        if ($_GET['status'] == 'sukses_hapus') {
            echo '<div class="alert alert-success">Pesanan berhasil dihapus.</div>';
        } elseif ($_GET['status'] == 'gagal_hapus') {
            echo '<div class="alert alert-danger">Gagal menghapus pesanan.</div>';
        } elseif ($_GET['status'] == 'gagal_hapus_selesai') {
            echo '<div class="alert alert-warning">Pesanan yang sudah selesai tidak dapat dihapus. Ubah statusnya jika ingin membatalkan.</div>';
        }
    }
    ?>

    <table class="table table-bordered table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID Pesanan</th>
                <th>Tanggal</th>
                <th>Pemesan</th>
                <th>Total</th>
                <th>Pembayaran</th>
                <th>Status Pembayaran</th>
                <th>Bukti</th>
                <th>Status Pesanan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while($pesanan = $result->fetch_assoc()): ?>
                <tr>
                    <td>#<?php echo $pesanan['id_pesanan']; ?></td>
                    <td><?php echo date('d M Y, H:i', strtotime($pesanan['tanggal_pesanan'])); ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($pesanan['nama_pemesan']); ?></strong><br>
                        <small><?php echo htmlspecialchars($pesanan['alamat_pemesan']); ?></small><br>
                        <small><?php echo htmlspecialchars($pesanan['telepon_pemesan']); ?></small>
                    </td>
                    <td>Rp <?php echo number_format($pesanan['total_harga'], 0, ',', '.'); ?></td>
                    <td><?php echo htmlspecialchars($pesanan['metode_pembayaran']); ?></td>
                    <td>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="id_pesanan" value="<?php echo $pesanan['id_pesanan']; ?>">
                            <select name="status_pembayaran" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="pending" <?php echo ($pesanan['status_pembayaran'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="lunas" <?php echo ($pesanan['status_pembayaran'] == 'lunas') ? 'selected' : ''; ?>>Lunas</option>
                                <option value="ditolak" <?php echo ($pesanan['status_pembayaran'] == 'ditolak') ? 'selected' : ''; ?>>Ditolak</option>
                            </select>
                            <input type="hidden" name="update_pembayaran" value="1">
                        </form>
                    </td>
                    <td>
                        <?php if (!empty($pesanan['bukti_pembayaran'])): ?>
                            <a href="../assets/bukti_pembayaran/<?php echo htmlspecialchars($pesanan['bukti_pembayaran']); ?>" target="_blank">Lihat</a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="id_pesanan" value="<?php echo $pesanan['id_pesanan']; ?>">
                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="diproses" <?php echo ($pesanan['status'] == 'diproses') ? 'selected' : ''; ?>>Diproses</option>
                                <option value="dikirim" <?php echo ($pesanan['status'] == 'dikirim') ? 'selected' : ''; ?>>Dikirim</option>
                                <option value="selesai" <?php echo ($pesanan['status'] == 'selesai') ? 'selected' : ''; ?>>Selesai</option>
                                <option value="dibatalkan" <?php echo ($pesanan['status'] == 'dibatalkan') ? 'selected' : ''; ?>>Dibatalkan</option>
                            </select>
                            <input type="hidden" name="update_status" value="1">
                        </form>
                    </td>
                    <td>
                        <?php if ($pesanan['status'] != 'selesai'): ?>
                            <a href="hapus_pesanan.php?id=<?php echo $pesanan['id_pesanan']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus pesanan ini?')">Hapus</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="text-center">Belum ada pesanan yang masuk.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
