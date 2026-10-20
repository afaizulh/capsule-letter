<?php

$conn = require_once $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/config/database.php';

$errors = [];

$user_id = 1;
// $user_id = $_SESSION['user_id'];
$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');
$unlock_date = trim($_POST['unlock_date'] ?? '');
$is_public = intval($_POST['is_public'] ?? 0); // 0 untuk Private, 1 untuk Publik

if (empty($title)) $errors[] = 'You have to fill the title.';
if (empty($content)) $errors[] = 'You have to fill the content.';
if (empty($unlock_date)) $errors[] = 'You have to select an opening date.';

if (!empty($errors)) {
    $_SESSION['error'] = implode('<br>', array_map(function ($e) {
        return htmlspecialchars($e, ENT_QUOTES, 'UTF-8');
    }, $errors));
    header('Location: /resources/views/create.php');
    exit;
}

$query = "INSERT INTO letters (user_id, title, content, unlock_date, is_public) VALUES (?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "isssi", $user_id, $title, $content, $unlock_date, $is_public);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = 'You have successfully created a letter.';
    } else {
        $_SESSION['error'] = 'Failed to create letter. ' . mysqli_error($conn);
    }

    mysqli_stmt_close($stmt);
} else {
    $_SESSION['error'] = 'Failed to prepare database statement. ' . mysqli_error($conn);
}

// Tutup koneksi
mysqli_close($conn);
header('Location: /capsule-letter/resources/views/create.php');
exit;