<?php
session_start();
require_once 'config.php';

// Logika untuk menambah produk ke keranjang
if (isset($_GET['tambah'])) {
    $id_produk = $_GET['tambah'];
    if (isset($_SESSION['keranjang'][$id_produk])) {
        $_SESSION['keranjang'][$id_produk]++;
    } else {
        $_SESSION['keranjang'][$id_produk] = 1;
    }
    header('Location: keranjang.php');
    exit();
}

// Logika untuk menghapus produk dari keranjang
if (isset($_GET['hapus'])) {
    $id_produk = $_GET['hapus'];
    if (isset($_SESSION['keranjang'][$id_produk])) {
        unset($_SESSION['keranjang'][$id_produk]);
    }
    header('Location: keranjang.php');
    exit();
}

if (isset($_POST['checkout'])) {
    // Update jumlah dari form
    if (isset($_POST['jumlah']) && is_array($_POST['jumlah'])) {
        foreach ($_POST['jumlah'] as $id_produk => $jumlah) {
            if ($jumlah > 0) {
                $_SESSION['keranjang'][$id_produk] = (int)$jumlah;
            } else {
                unset($_SESSION['keranjang'][$id_produk]);
            }
        }
    }
    // Simpan perubahan session sebelum redirect
    session_write_close();
    
    // Redirect ke halaman checkout
    header('Location: checkout.php');
    exit();
}

// Logika untuk mengupdate jumlah produk
if (isset($_POST['update'])) {
    foreach ($_POST['jumlah'] as $id_produk => $jumlah) {
        if ($jumlah > 0) {
            $_SESSION['keranjang'][$id_produk] = $jumlah;
        } else {
            unset($_SESSION['keranjang'][$id_produk]);
        }
    }
    header('Location: keranjang.php');
    exit();
}

$keranjang_kosong = true;
$produk_ids = [];
if (isset($_SESSION['keranjang']) && !empty($_SESSION['keranjang'])) {
    $keranjang_kosong = false;
    $produk_ids = array_keys($_SESSION['keranjang']);
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>adilokamart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .navbar-brand { font-weight: bold; }
        .footer { background-color: #f8f9fa; padding: 20px 0; margin-top: 40px; }
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
            <div class="ms-auto">
                <a class="nav-link" href="admin/">Admin Panel</a>
            </div>
        </div>
    </nav>

    <!-- Konten Keranjang -->
    <div class="container mt-4">
        <h2>Keranjang Belanja Anda</h2>
        <hr>

        <?php if ($keranjang_kosong): ?>
            <div class="alert alert-info text-center">
                Keranjang belanja Anda masih kosong.
            </div>
            <a href="index.php" class="btn btn-primary">Mulai Belanja</a>
        <?php else: ?>
            <form action="keranjang.php" method="post">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Produk</th>
                            <th>Harga</th>
                            <th style="width: 100px;">Jumlah</th>
                            <th>Subtotal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $grand_total = 0;
                        $ids_string = implode(',', $produk_ids);
                        $result = $conn->query("SELECT * FROM produk WHERE id_produk IN ($ids_string)");
                        
                        while($produk = $result->fetch_assoc()):
                            $jumlah = $_SESSION['keranjang'][$produk['id_produk']];
                            $subtotal = $produk['harga'] * $jumlah;
                            $grand_total += $subtotal;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($produk['nama_produk']); ?></td>
                            <td>Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?></td>
                            <td>
                                <input type="number" name="jumlah[<?php echo $produk['id_produk']; ?>]" class="form-control" value="<?php echo $jumlah; ?>" min="1">
                            </td>
                            <td>Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></td>
                            <td>
                                <a href="keranjang.php?hapus=<?php echo $produk['id_produk']; ?>" class="btn btn-danger btn-sm">Hapus</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="3" class="text-end">Total Belanja:</th>
                            <th colspan="2">Rp <?php echo number_format($grand_total, 0, ',', '.'); ?></th>
                        </tr>
                    </tfoot>
                </table>
                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary">&laquo; Lanjut Belanja</a>
                    <div>
                        <button type="submit" name="update" class="btn btn-info">Perbarui Keranjang</button>
                        <button type="submit" name="checkout" class="btn btn-success">Lanjutkan ke Checkout &raquo;</button>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="footer text-center">
        <div class="container">
            <span class="text-muted">&copy; <?php echo date("Y"); ?> adilokamart.</span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
