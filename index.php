<?php
session_start();
require_once 'config.php';

// Ambil semua produk dari database
$result = $conn->query("SELECT * FROM produk ORDER BY nama_produk ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>adilokamart</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card-img-top {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .navbar-brand {
            font-weight: bold;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px 0;
            margin-top: 40px;
        }

        /* Animasi Slide-in Spesifik */
        .slide-in-from-left {
            opacity: 0;
            transform: translateX(-100%);
            animation: slideInLeft 0.8s cubic-bezier(0.250, 0.460, 0.450, 0.940) both;
        }

        .slide-in-from-right {
            opacity: 0;
            transform: translateX(100%);
            animation: slideInRight 0.8s cubic-bezier(0.250, 0.460, 0.450, 0.940) 0.2s both; /* Delay */
        }

        @keyframes slideInLeft {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideInRight {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="assets/img/logo.jpg" alt="adilokamart Logo" style="height: 30px; margin-right: 10px; border-radius: 50%;">
                adilokamart
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="keranjang.php">
                            Keranjang 
                            <span class="badge bg-primary rounded-pill">
                                <?php echo isset($_SESSION['keranjang']) ? count($_SESSION['keranjang']) : 0; ?>
                            </span>
                        </a>
                    </li>
                    <?php if (isset($_SESSION['user_logged_in'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Halo, <?php echo htmlspecialchars($_SESSION['nama_pengguna']); ?>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="riwayat_pesanan.php">Riwayat Pesanan</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="https://wa.me/6285742981988" target="_blank">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/6/6b/WhatsApp.svg/512px-WhatsApp.svg.png" alt="WhatsApp" style="height: 24px; margin-right: 5px;">
                            +62 857-4298-1988
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Konten Utama -->
    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-12 text-center">
                <h1 class="slide-in-from-left">Selamat Datang di SKAnsaMart</h1>
                <p class="lead slide-in-from-right">Semua kebutuhan Anda ada di sini!</p>
            </div>
        </div>

        <!-- Lacak Pesanan -->
        <div class="row justify-content-center mb-5">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-center">Lacak Pesanan Anda</h5>
                        <form action="cek_pesanan.php" method="GET">
                            <div class="input-group">
                                <input type="text" class="form-control" name="id_pesanan" placeholder="Masukkan nomor pesanan Anda..." required>
                                <button class="btn btn-primary" type="submit">Lacak</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daftar Produk -->
        <div class="row">
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="col-md-4 col-lg-3 mb-4">
                        <div class="card h-100 shadow-sm">
                            <img src="assets/img/<?php echo htmlspecialchars($row['gambar']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['nama_produk']); ?>">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo htmlspecialchars($row['nama_produk']); ?></h5>
                                <p class="card-text text-muted flex-grow-1"><?php echo htmlspecialchars($row['deskripsi']); ?></p>
                                <h6 class="card-subtitle mb-2 text-danger">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></h6>
                                <a href="keranjang.php?tambah=<?php echo $row['id_produk']; ?>" class="btn btn-primary mt-auto">Tambah ke Keranjang</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <p class="text-center">Belum ada produk yang tersedia.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Riwayat Pesanan Terakhir -->
        <div class="row mt-5">
            <div class="col-12">
                <?php 
                // Logika untuk menampilkan riwayat pesanan
                $is_user_logged_in = isset($_SESSION['user_logged_in']);
                if ($is_user_logged_in) {
                    echo '<h2 class="text-center mb-4">Riwayat Pesanan Anda</h2>';
                    $id_pengguna = $_SESSION['id_pengguna'];
                    $pesanan_result = $conn->prepare("SELECT * FROM pesanan WHERE id_pengguna = ? ORDER BY tanggal_pesanan DESC LIMIT 10");
                    $pesanan_result->bind_param("i", $id_pengguna);
                    $pesanan_result->execute();
                    $pesanan_result = $pesanan_result->get_result();
                } else {
                    echo '<h2 class="text-center mb-4">Riwayat Pesanan Terakhir</h2>';
                    $pesanan_result = $conn->query("SELECT * FROM pesanan ORDER BY tanggal_pesanan DESC LIMIT 10");
                }
                ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
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
                            <?php if ($pesanan_result->num_rows > 0): ?>
                                <?php while($pesanan = $pesanan_result->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?php echo htmlspecialchars($pesanan['id_pesanan']); ?></td>
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
                                    <td colspan="7" class="text-center">Belum ada pesanan.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer text-center">
        <div class="container">
            <span class="text-muted">&copy; <?php echo date("Y"); ?> adilokamart.</span>
            <p><a href="admin/">Admin Panel</a></p>
        </div>
    </footer>

    <!-- Login Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginModalLabel">Login Admin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="admin/login.php" method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>