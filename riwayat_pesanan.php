<?php
session_start();
require_once 'config.php';

// Wajibkan login
if (!isset($_SESSION['user_logged_in'])) {
    header('Location: login.php');
    exit();
}

// Ambil data pesanan milik pengguna yang login
$id_pengguna = $_SESSION['id_pengguna'];
$result = $conn->prepare("SELECT * FROM pesanan WHERE id_pengguna = ? ORDER BY tanggal_pesanan DESC");
$result->bind_param("i", $id_pengguna);
$result->execute();
$pesanan_pengguna = $result->get_result();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan - adilokamart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="assets/img/logo.jpg" alt="adilokamart Logo" style="height: 30px; margin-right: 10px; border-radius: 50%;">
                adilokamart
            </a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="keranjang.php">Keranjang</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Halo, <?php echo htmlspecialchars($_SESSION['nama_pengguna']); ?>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item active" href="riwayat_pesanan.php">Riwayat Pesanan</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Konten Riwayat Pesanan -->
    <div class="container mt-4">
        <h3>Riwayat Pesanan Anda</h3>
        <hr>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>No. Pesanan</th>
                        <th>Tanggal</th>
                        <th>Total</th>
                        <th>Metode Pembayaran</th>
                        <th>Status Pembayaran</th>
                        <th>Status Pesanan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($pesanan_pengguna->num_rows > 0): ?>
                        <?php while($pesanan = $pesanan_pengguna->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $pesanan['id_pesanan']; ?></td>
                            <td><?php echo date('d M Y, H:i', strtotime($pesanan['tanggal_pesanan'])); ?></td>
                            <td>Rp <?php echo number_format($pesanan['total_harga'], 0, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars($pesanan['metode_pembayaran']); ?></td>
                            <td><span class="badge bg-warning"><?php echo ucfirst(htmlspecialchars($pesanan['status_pembayaran'])); ?></span></td>
                            <td><span class="badge bg-info"><?php echo ucfirst(htmlspecialchars($pesanan['status'])); ?></span></td>
                            <td>
                                <a href="cek_pesanan.php?id_pesanan=<?php echo $pesanan['id_pesanan']; ?>" class="btn btn-primary btn-sm">Lihat Detail</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">Anda belum memiliki riwayat pesanan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>