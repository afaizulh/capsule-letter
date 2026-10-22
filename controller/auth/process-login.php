<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /capsule-letter/resources/views/auth/login.php');
    exit;
}

$conn = require_once $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/config/database.php';

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    $_SESSION['error'] = 'Email and password are required.';
    header('Location: /capsule-letter/resources/views/auth/login.php');
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT id, name, email, password, role FROM users WHERE email = ?");

if (!$stmt) {
    error_log('Failed to prepare login query: ' . mysqli_error($conn));
    http_response_code(500);
    exit('An error occurred on the server. Please try again later.');
}

mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($user = mysqli_fetch_assoc($result)) {
    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['role']       = $user['role'] ?? 'user';
        $_SESSION['logged_in']  = true;

        if ($_SESSION['role'] === 'admin') {
            header('Location: /capsule-letter/resources/views/admin/dashboard.php');
        } else {
            header('Location: /capsule-letter/resources/views/home.php');
        }
        exit;
    }
}

$_SESSION['error'] = 'Email or password is incorrect.';
header('Location: /capsule-letter/resources/views/auth/login.php');
exit;