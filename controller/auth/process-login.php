<?php
session_start();
$conn = require_once $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/config/database.php';

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    $_SESSION['error'] = 'Email dan password wajib diisi.';
    header('Location: /capsule-letter/resources/views/login.php');
    exit;
}

// Ambil data user berdasarkan email
$stmt = mysqli_prepare($conn, "SELECT id, name, email, password FROM users WHERE email = ?");

if (!$stmt) {
    error_log('Gagal menyiapkan query login: ' . mysqli_error($conn));
    http_response_code(500);
    exit('Terjadi kesalahan pada sistem. Silakan coba kembali.');
}

mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($user = mysqli_fetch_assoc($result)) {
    // Verifikasi password yang di-hash
    if (password_verify($password, $user['password'])) {
        // Set Session Login
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['logged_in'] = true;

        header('Location: /capsule-letter/resources/views/home.php');
        exit;
    }
}

$_SESSION['error'] = 'Email atau password salah.';
header('Location: /capsule-letter/resources/views/login.php');
exit;