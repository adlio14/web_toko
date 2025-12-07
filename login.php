<?php
require_once 'config.php';
session_start();

// Jika sudah login, redirect ke halaman utama
if (isset($_SESSION['user_logged_in'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = 'Email dan password wajib diisi.';
    } else {
        $stmt = $conn->prepare("SELECT * FROM pengguna WHERE email_pengguna = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            // Verifikasi password
            if (password_verify($password, $user['password_pengguna'])) {
                // Set session
                $_SESSION['user_logged_in'] = true;
                $_SESSION['id_pengguna'] = $user['id_pengguna'];
                $_SESSION['nama_pengguna'] = $user['nama_pengguna'];

                // Redirect ke halaman sebelumnya jika ada
                if (isset($_SESSION['redirect_to'])) {
                    $redirect_url = $_SESSION['redirect_to'];
                    unset($_SESSION['redirect_to']);
                    header('Location: ' . $redirect_url);
                } else {
                    header('Location: index.php');
                }
                exit;
            } else {
                $error = 'Email atau password salah.';
            }
        } else {
            $error = 'Email atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - adilokamart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="card-header">
                <h4>Login Pelanggan</h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Alamat Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
                </form>
            </div>
            <div class="card-footer text-center">
                Belum punya akun? <a href="register.php">Daftar di sini</a><br>
                <a href="index.php">Kembali ke toko</a>
            </div>
        </div>
    </div>
</body>
</html>