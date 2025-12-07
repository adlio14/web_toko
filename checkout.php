<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once 'config.php';

// Wajibkan login untuk checkout
if (!isset($_SESSION['user_logged_in'])) {
    // Simpan URL checkout saat ini agar bisa kembali setelah login
    $_SESSION['redirect_to'] = 'checkout.php';
    header('Location: login.php');
    exit();
}

// Jika keranjang kosong, redirect ke halaman utama
if (empty($_SESSION['keranjang'])) {
    header('Location: index.php');
    exit();
}

// Proses checkout saat form disubmit
if (isset($_POST['checkout'])) {
    $nama = $conn->real_escape_string($_POST['nama']);
    $alamat = $conn->real_escape_string($_POST['alamat']);
    $telepon = $conn->real_escape_string($_POST['telepon']);
    $metode_pembayaran = $conn->real_escape_string($_POST['metode_pembayaran']);

    // Validasi sederhana
    if (empty($nama) || empty($alamat) || empty($telepon) || empty($metode_pembayaran)) {
        $error = "Semua field, termasuk metode pembayaran, wajib diisi!";
    } else {
        // Hitung ulang total belanja di server
        $total_harga = 0;
        $ids_string = implode(',', array_keys($_SESSION['keranjang']));
        $result = $conn->query("SELECT id_produk, harga FROM produk WHERE id_produk IN ($ids_string)");
        $produk_data = [];
        while($row = $result->fetch_assoc()) {
            $produk_data[$row['id_produk']] = $row['harga'];
        }

        foreach ($_SESSION['keranjang'] as $id_produk => $jumlah) {
            $total_harga += $produk_data[$id_produk] * $jumlah;
        }

        // Mulai transaksi database
        $conn->begin_transaction();

        try {
            // Ambil id_pengguna dari session
            $id_pengguna = $_SESSION['id_pengguna'];

            // Untuk semua metode pembayaran, status awal adalah 'pending'
            $status_pembayaran = 'pending';
            $stmt = $conn->prepare("INSERT INTO pesanan (id_pengguna, nama_pemesan, alamat_pemesan, telepon_pemesan, total_harga, metode_pembayaran, status, status_pembayaran) VALUES (?, ?, ?, ?, ?, ?, 'diproses', ?)");
            $stmt->bind_param("isssiss", $id_pengguna, $nama, $alamat, $telepon, $total_harga, $metode_pembayaran, $status_pembayaran);
            $stmt->execute();
            $id_pesanan_baru = $stmt->insert_id;

            // 2. Simpan detail pesanan
            $stmt_detail = $conn->prepare("INSERT INTO detail_pesanan (id_pesanan, id_produk, jumlah, harga_saat_pesan) VALUES (?, ?, ?, ?)");
            foreach ($_SESSION['keranjang'] as $id_produk => $jumlah) {
                $harga_saat_pesan = $produk_data[$id_produk];
                $stmt_detail->bind_param("iiid", $id_pesanan_baru, $id_produk, $jumlah, $harga_saat_pesan);
                $stmt_detail->execute();
            }

            // Jika semua berhasil, commit transaksi
            $conn->commit();

            // Kosongkan keranjang dan redirect ke halaman sukses
            unset($_SESSION['keranjang']);
            header('Location: sukses.php?id=' . $id_pesanan_baru);
            exit();

        } catch (Exception $e) {
            // Jika ada error, rollback transaksi
            $conn->rollback();
            $error = "Terjadi kesalahan saat memproses pesanan: " . $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - adilokamart</title>
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

    <!-- Konten Checkout -->
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-7">
                <h3>Data Pengiriman</h3>
                <hr>
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="nama" name="nama" required>
                    </div>
                    <div class="mb-3">
                        <label for="alamat" class="form-label">Alamat Lengkap</label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="telepon" class="form-label">Nomor Telepon</label>
                        <input type="text" class="form-control" id="telepon" name="telepon" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Metode Pembayaran</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="metode_pembayaran" id="cod" value="COD" required>
                            <label class="form-check-label" for="cod">
                                COD (Bayar di Tempat)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="metode_pembayaran" id="transfer" value="Transfer Bank">
                            <label class="form-check-label" for="transfer">
                                Transfer Bank (DANA)
                            </label>
                        </div>
                    </div>

                    <!-- Area QR Code (tersembunyi by default) -->
                    <div id="qr-code-area" class="mb-3 text-center" style="display: none;">
                        <p>Silakan scan QR Code di bawah ini untuk pembayaran melalui DANA:</p>
                        <img src="assets/img/qr.jpg" class="img-fluid rounded" style="max-width: 200px;" alt="QR Code DANA">
                    </div>

                    <button type="submit" name="checkout" class="btn btn-success w-100">Buat Pesanan</button>
                </form>
            </div>
            <div class="col-md-5">
                <h3>Ringkasan Pesanan</h3>
                <hr>
                <table class="table">
                    <tbody>
                    <?php
                    $total_belanja = 0;
                    $ids_string = implode(',', array_keys($_SESSION['keranjang']));
                    $result = $conn->query("SELECT * FROM produk WHERE id_produk IN ($ids_string)");
                    while($produk = $result->fetch_assoc()):
                        $jumlah = $_SESSION['keranjang'][$produk['id_produk']];
                        $subtotal = $produk['harga'] * $jumlah;
                        $total_belanja += $subtotal;
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($produk['nama_produk']); ?> (x<?php echo $jumlah; ?>)</td>
                        <td class="text-end">Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold">
                            <td>Total</td>
                            <td class="text-end">Rp <?php echo number_format($total_belanja, 0, ',', '.'); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const radioTransfer = document.getElementById('transfer');
            const radioCod = document.getElementById('cod');
            const qrCodeArea = document.getElementById('qr-code-area');

            function toggleQrCode() {
                if (radioTransfer.checked) {
                    qrCodeArea.style.display = 'block';
                } else {
                    qrCodeArea.style.display = 'none';
                }
            }

            radioTransfer.addEventListener('change', toggleQrCode);
            radioCod.addEventListener('change', toggleQrCode);

            // Panggil sekali saat load untuk handle jika ada nilai default
            toggleQrCode();
        });
    </script>

</body>
</html>
