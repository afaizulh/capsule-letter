<?php
session_start();
$conn = require_once $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/config/database.php';

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

$errors = [];
if (empty($name)) $errors[] = 'Nama wajib diisi.';
if (empty($email)) $errors[] = 'Email wajib diisi.';
if (empty($password)) $errors[] = 'Password wajib diisi.';

if (!empty($errors)) {
    $_SESSION['error'] = implode('<br>', $errors);
    header('Location: /capsule-letter/resources/views/register.php');
    exit;
}

// Cek apakah email sudah terdaftar
$stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
    $_SESSION['error'] = 'Email sudah terdaftar. Silakan gunakan email lain.';
    header('Location: /capsule-letter/resources/views/register.php');
    exit;
}
mysqli_stmt_close($stmt);

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Simpan user baru ke database
$stmt = mysqli_prepare($conn, "INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
mysqli_stmt_bind_param($stmt, "sss", $name, $email, $hashedPassword);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['success'] = 'Registrasi berhasil! Silakan login.';
    header('Location: /capsule-letter/resources/views/login.php');
} else {
    $_SESSION['error'] = 'Gagal melakukan registrasi: ' . mysqli_error($conn);
    header('Location: /capsule-letter/resources/views/register.php');
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
exit;