<?php
require_once 'config.php';

$pesanan = null;
$detail_pesanan = [];
$error = '';
$pesan_sukses = '';

// Logika untuk upload bukti pembayaran
if (isset($_POST['upload_bukti'])) {
    $id_pesanan_upload = $_POST['id_pesanan'];
    $bukti_file = $_FILES['bukti_pembayaran'];

    if (isset($bukti_file) && $bukti_file['error'] === UPLOAD_ERR_OK) {
        $nama_file_bukti = time() . '_' . basename($bukti_file['name']);
        $target_path = 'assets/bukti_pembayaran/' . $nama_file_bukti;

        // Pindahkan file yang diunggah
        if (move_uploaded_file($bukti_file['tmp_name'], $target_path)) {
            $stmt = $conn->prepare("UPDATE pesanan SET bukti_pembayaran = ?, status_pembayaran = 'pending' WHERE id_pesanan = ?");
            $stmt->bind_param("si", $nama_file_bukti, $id_pesanan_upload);
            if ($stmt->execute()) {
                $pesan_sukses = "Bukti pembayaran berhasil diunggah. Admin akan segera memverifikasi.";
            } else {
                $error = "Gagal menyimpan informasi bukti pembayaran ke database.";
            }
        } else {
            $error = "Gagal mengunggah file bukti pembayaran.";
        }
    } else {
        $error = "Tidak ada file yang dipilih atau terjadi kesalahan saat mengunggah.";
    }
    // Set id_pesanan untuk ditampilkan setelah upload
    $_GET['id_pesanan'] = $id_pesanan_upload;
}

if (isset($_GET['id_pesanan']) && !empty($_GET['id_pesanan'])) {
    $id_pesanan = filter_var($_GET['id_pesanan'], FILTER_SANITIZE_NUMBER_INT);

    // Ambil data pesanan utama
    $stmt = $conn->prepare("SELECT * FROM pesanan WHERE id_pesanan = ?");
    $stmt->bind_param("i", $id_pesanan);
    $stmt->execute();
    $result_pesanan = $stmt->get_result();

    if ($result_pesanan->num_rows === 1) {
        $pesanan = $result_pesanan->fetch_assoc();

        // Ambil detail item pesanan
        $stmt_detail = $conn->prepare(
            "SELECT dp.*, p.nama_produk, p.gambar 
             FROM detail_pesanan dp 
             JOIN produk p ON dp.id_produk = p.id_produk 
             WHERE dp.id_pesanan = ?"
        );
        $stmt_detail->bind_param("i", $id_pesanan);
        $stmt_detail->execute();
        $result_detail = $stmt_detail->get_result();
        while($row = $result_detail->fetch_assoc()){
            $detail_pesanan[] = $row;
        }

    } else {
        $error = "Nomor pesanan tidak ditemukan.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>adilokamart</title>
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
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="keranjang.php">Keranjang</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin/">Admin Panel</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Konten Status Pesanan -->
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <a href="index.php" class="btn btn-secondary mb-3">&laquo; Kembali ke Beranda</a>
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Detail Status Pesanan</h4>
                    </div>
                    <div class="card-body">
                                                <?php if ($pesan_sukses): ?>
                                                    <div class="alert alert-success"><?php echo $pesan_sukses; ?></div>
                                                <?php endif; ?>
                                                <?php if (!isset($_GET['id_pesanan']) || empty($_GET['id_pesanan'])):
                                                    // Form pencarian jika tidak ada ID di URL
                                                ?>
                                                    <h5 class="card-title text-center">Lacak Pesanan Anda</h5>
                                                    <form action="cek_pesanan.php" method="GET">
                                                        <div class="input-group">
                                                            <input type="text" class="form-control" name="id_pesanan" placeholder="Masukkan nomor pesanan Anda..." required>
                                                            <button class="btn btn-primary" type="submit">Lacak</button>
                                                        </div>
                                                    </form>
                                                <?php elseif ($error): ?>
                                                    <div class="alert alert-danger"><?php echo $error; ?></div>
                                                <?php elseif ($pesanan): ?>
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <strong>Nomor Pesanan:</strong> #<?php echo htmlspecialchars($pesanan['id_pesanan']); ?><br>
                                    <strong>Tanggal:</strong> <?php echo date('d M Y, H:i', strtotime($pesanan['tanggal_pesanan'])); ?><br>
                                    <strong>Total:</strong> Rp <?php echo number_format($pesanan['total_harga'], 0, ',', '.'); ?><br>
                                    <strong>Metode Pembayaran:</strong> <?php echo htmlspecialchars($pesanan['metode_pembayaran']); ?>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <strong>Status Pembayaran:</strong> 
                                    <span class="badge bg-warning fs-6"><?php echo strtoupper(htmlspecialchars($pesanan['status_pembayaran'])); ?></span><br>
                                    <strong>Status Pesanan:</strong> 
                                    <span class="badge bg-primary fs-6"><?php echo strtoupper(htmlspecialchars($pesanan['status'])); ?></span>
                                </div>
                            </div>

                            <h5>Barang yang Dipesan:</h5>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th>Harga</th>
                                        <th>Jumlah</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($detail_pesanan as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['nama_produk']); ?></td>
                                        <td>Rp <?php echo number_format($item['harga_saat_pesan'], 0, ',', '.'); ?></td>
                                        <td><?php echo $item['jumlah']; ?></td>
                                        <td>Rp <?php echo number_format($item['harga_saat_pesan'] * $item['jumlah'], 0, ',', '.'); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <?php 
                            // Tampilkan form upload atau bukti jika metode pembayaran adalah Transfer Bank
                            if ($pesanan['metode_pembayaran'] == 'Transfer Bank'): 
                            ?>
                                <hr class="my-4">
                                <div class="text-center">
                                    <h5>Konfirmasi Pembayaran</h5>
                                    <?php if (empty($pesanan['bukti_pembayaran'])): ?>
                                        <p>Silakan scan QR Code DANA di bawah ini atau transfer ke nomor <strong>085742981988</strong> (a.n. adilokamart) sebesar <strong>Rp <?php echo number_format($pesanan['total_harga'], 0, ',', '.'); ?></strong>.</p>
                                        <img src="assets/img/qr.jpg" class="img-fluid rounded mb-3" style="max-width: 200px;" alt="QR Code DANA">
                                        <p>Setelah itu, unggah bukti pembayaran Anda untuk mempercepat proses verifikasi.</p>
                                        <form action="cek_pesanan.php" method="POST" enctype="multipart/form-data">
                                            <input type="hidden" name="id_pesanan" value="<?php echo $pesanan['id_pesanan']; ?>">
                                            <div class="input-group mb-3 w-75 mx-auto">
                                                <input type="file" class="form-control" name="bukti_pembayaran" required>
                                                <button class="btn btn-success" type="submit" name="upload_bukti">Upload Bukti</button>
                                            </div>
                                        </form>
                                    <?php else: ?>
                                        <p>Anda telah mengunggah bukti pembayaran. Mohon tunggu verifikasi dari admin.</p>
                                        <img src="assets/bukti_pembayaran/<?php echo htmlspecialchars($pesanan['bukti_pembayaran']); ?>" class="img-fluid rounded" style="max-width: 300px;" alt="Bukti Pembayaran">
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
