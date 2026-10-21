<?php

$conn = require_once $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/config/database.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /capsule-letter/resources/views/login.php');
    exit;
}

$errors = [];

$user_id = 1;
// $user_id = $_SESSION['user_id'];
$title = trim($_POST['title'] ?? '');
// $category_id = intval($_POST['category_id'] ?? 0);
$category_id = intval($_POST['category_id'] ?? null);
$content = trim($_POST['content'] ?? '');
$unlock_date = trim($_POST['unlock_date'] ?? '');
$is_public = intval($_POST['is_public'] ?? 0); // 0 untuk Private, 1 untuk Publik

if (empty($title)) $errors[] = 'You have to fill the title.';
// if (empty($category_id)) $errors[] = 'Category must be selected.';
if (empty($content)) $errors[] = 'You have to fill the content.';
if (empty($unlock_date)) $errors[] = 'You have to select an opening date.';

// Handle Upload File / Attachment
$attachmentName = null;
if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath   = $_FILES['attachment']['tmp_name'];
    $fileName      = $_FILES['attachment']['name'];
    $fileSize      = $_FILES['attachment']['size'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // Ekstensi yang diizinkan
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
    
    // Maksimal ukuran 2MB
    if ($fileSize > 2 * 1024 * 1024) {
        $errors[] = 'File size is too large. Maximum 2MB.';
    } elseif (!in_array($fileExtension, $allowedExtensions)) {
        $errors[] = 'Invalid file type. Allowed types: ' . implode(', ', $allowedExtensions);
    } else {
        // Buat nama file unik agar tidak bentrok
        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
        
        // Tentukan direktori upload (Pastikan folder uploads/ sudah dibuat di root project)
        $uploadFileDir = $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/resources/uploads/';
        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0755, true);
        }
        
        $dest_path = $uploadFileDir . $newFileName;
        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            $attachmentName = $newFileName;
        } else {
            $errors[] = 'An error occurred while uploading the file.';
        }
    }
}

if (!empty($errors)) {
    $_SESSION['error'] = implode('<br>', array_map(function ($e) {
        return htmlspecialchars($e, ENT_QUOTES, 'UTF-8');
    }, $errors));
    header('Location: /capsule-letter/resources/views/create.php');
    exit;
}

$query = "INSERT INTO letters (user_id, category_id, title, content, attachment, unlock_date, is_public) VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "iissssi", $user_id, $category_id, $title, $content, $attachmentName, $unlock_date, $is_public);

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