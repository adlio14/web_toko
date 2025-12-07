<?php
session_start();
require_once 'config.php';

$pesanan = null;
if (isset($_GET['id'])) {
    $id_pesanan = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM pesanan WHERE id_pesanan = ?");
    $stmt->bind_param("i", $id_pesanan);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $pesanan = $result->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Berhasil - adilokamart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container">
            <a class="navbar-brand" style="font-weight: bold;" href="index.php">
                <img src="assets/img/logo.jpg" alt="adilokamart Logo" style="height: 30px; margin-right: 10px; border-radius: 50%;">
                adilokamart
            </a>
        </div>
    </nav>

    <!-- Konten Sukses -->
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2 text-center">
                <?php if ($pesanan): ?>
                    <div class="alert alert-success p-5">
                        <h2 class="alert-heading">Terima Kasih!</h2>
                        <p>Pesanan Anda telah berhasil kami terima.</p>
                        <p>Nomor pesanan Anda adalah: <strong>#<?php echo htmlspecialchars($pesanan['id_pesanan']); ?></strong></p>
                        <hr>
                        <?php if ($pesanan['metode_pembayaran'] == 'Transfer Bank'): ?>
                            <p>Silakan lakukan pembayaran sebesar <strong>Rp <?php echo number_format($pesanan['total_harga'], 0, ',', '.'); ?></strong> ke DANA berikut:</p>
                            <ul class="list-unstyled">
                                <li><strong>DANA:</strong> 085742981988 a.n. adilokamart</li>
                            </ul>
                            <img src="assets/img/qr.jpg" class="img-fluid rounded" style="max-width: 200px;" alt="QR Code DANA">
                            <p class="mt-3">Setelah melakukan pembayaran, Anda dapat mengunggah bukti pembayaran melalui halaman cek pesanan.</p>
                            <a href="cek_pesanan.php?id_pesanan=<?php echo $pesanan['id_pesanan']; ?>" class="btn btn-info mt-2">Cek Status & Upload Bukti</a>
                        <?php else: // COD ?>
                            <p>Anda memilih metode pembayaran <strong>COD (Bayar di Tempat)</strong>.</p>
                            <p class="mb-0">Tim kami akan segera menghubungi Anda untuk konfirmasi. Mohon siapkan uang pas untuk dibayarkan kepada kurir saat pesanan tiba.</p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger p-5">
                        <h2 class="alert-heading">Error!</h2>
                        <p>Pesanan tidak ditemukan.</p>
                    </div>
                <?php endif; ?>
                <a href="index.php" class="btn btn-primary mt-3">Kembali ke Halaman Utama</a>
            </div>
        </div>
    </div>

</body>
</html>
