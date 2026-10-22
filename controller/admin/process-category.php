<?php
session_start();
$conn = require_once $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/config/database.php';

// Proteksi: Hanya Admin
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header('Location: /capsule-letter/resources/views/login.php');
    exit;
}

$action = $_GET['action'] ?? '';

if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    if (!empty($name)) {
        // Buat slug otomatis dari nama kategori
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

        $stmt = mysqli_prepare($conn, "INSERT INTO categories (name, slug) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, "ss", $name, $slug);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = 'Category added successfully!';
        } else {
            $_SESSION['error'] = 'Failed to add category (it might already exist).';
        }
        mysqli_stmt_close($stmt);
    }
} elseif ($action === 'delete') {
    $id = intval($_GET['id'] ?? 0);
    if ($id > 0) {
        $stmt = mysqli_prepare($conn, "DELETE FROM categories WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $_SESSION['success'] = 'Category deleted successfully!';
    }
}

mysqli_close($conn);
header('Location: /capsule-letter/resources/views/admin/dashboard.php');
exit;