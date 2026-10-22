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
    $word = strtolower(trim($_POST['word'] ?? ''));
    if (!empty($word)) {
        $stmt = mysqli_prepare($conn, "INSERT INTO forbidden_words (word) VALUES (?)");
        mysqli_stmt_bind_param($stmt, "s", $word);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = 'Forbidden word has been added successfully.';
        } else {
            $_SESSION['error'] = 'The word is already in the list.';
        }
        mysqli_stmt_close($stmt);
    }
} elseif ($action === 'delete') {
    $id = intval($_GET['id'] ?? 0);
    if ($id > 0) {
        $stmt = mysqli_prepare($conn, "DELETE FROM forbidden_words WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $_SESSION['success'] = 'Forbidden word has been deleted successfully.';
    }
}

mysqli_close($conn);
header('Location: /capsule-letter/resources/views/admin/dashboard.php');
exit;