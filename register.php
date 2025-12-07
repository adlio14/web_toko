<?php
require_once 'config.php';
session_start();

$error = '';
$sukses = '';

if (isset($_POST['register'])) {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password_konfirmasi = $_POST['password_konfirmasi'];

    if (empty($nama) || empty($email) || empty($password)) {
        $error = 'Semua field wajib diisi.';
    } elseif ($password !== $password_konfirmasi) {
        $error = 'Konfirmasi password tidak cocok.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } else {
        // Cek apakah email sudah terdaftar
        $stmt = $conn->prepare("SELECT id_pengguna FROM pengguna WHERE email_pengguna = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $error = 'Email sudah terdaftar. Silakan gunakan email lain.';
        } else {
            // Hash password sebelum disimpan
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO pengguna (nama_pengguna, email_pengguna, password_pengguna) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nama, $email, $hashed_password);
            if ($stmt->execute()) {
                $sukses = 'Pendaftaran berhasil! Silakan <a href="login.php">login</a>.';
            } else {
                $error = 'Terjadi kesalahan. Gagal mendaftar.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - adilokamart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="card-header">
                <h4>Register Akun Baru</h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if ($sukses): ?>
                    <div class="alert alert-success"><?php echo $sukses; ?></div>
                <?php else: ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password</label>
                        <input type="password" name="password_konfirmasi" class="form-control" required>
                    </div>
                    <button type="submit" name="register" class="btn btn-primary w-100">Daftar</button>
                </form>
                <?php endif; ?>
            </div>
            <div class="card-footer text-center">
                Sudah punya akun? <a href="login.php">Login di sini</a><br>
                <a href="index.php">Kembali ke toko</a>
            </div>
        </div>
    </div>
</body>
</html>