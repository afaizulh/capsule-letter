<?php
session_start();
$conn = require_once $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/config/database.php';

$first_name       = trim($_POST['first_name'] ?? '');
$last_name        = trim($_POST['last_name'] ?? '');
$email            = trim($_POST['email'] ?? '');
$password         = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

$name = trim($first_name . ' ' . $last_name);

$errors = [];
if (empty($first_name)) $errors[] = 'First Name required.';
if (empty($last_name)) $errors[] = 'Last Name required.';
if (empty($email)) $errors[] = 'Email required.';
if (empty($password)) $errors[] = 'Password required.';
if ($password !== $confirm_password) $errors[] = 'Password confirmation does not match.';

if (!empty($errors)) {
    $_SESSION['error'] = implode('<br>', $errors);
    header('Location: /capsule-letter/resources/views/auth/register.php');
    exit;
}

// Cek Email
$stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
    $_SESSION['error'] = 'Email already registered. Please use a different email.';
    header('Location: /capsule-letter/resources/views/auth/register.php');
    exit;
}
mysqli_stmt_close($stmt);

// Hash Password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Simpan User Baru
$stmt = mysqli_prepare($conn, "INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
mysqli_stmt_bind_param($stmt, "sss", $name, $email, $hashedPassword);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['success'] = 'Registration successful! Please login.';
    header('Location: /capsule-letter/resources/views/auth/login.php');
} else {
    $_SESSION['error'] = 'Failed to register: ' . mysqli_error($conn);
    header('Location: /capsule-letter/resources/views/register.php');
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
exit;