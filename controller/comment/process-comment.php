<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$conn = require_once $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/config/database.php';

// Cek apakah user sudah login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /capsule-letter/resources/views/auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId   = $_SESSION['user_id'];
    $letterId = intval($_POST['letter_id'] ?? 0);
    $comment  = trim($_POST['comment'] ?? '');

    $cleanComment = trim(strip_tags($comment));

    if ($letterId <= 0 || empty($cleanComment)) {
        $_SESSION['error'] = 'Comment cannot be empty.';
        header('Location: /capsule-letter/resources/views/detail-letter.php?id=' . $letterId);
        exit;
    }

    // 2Ambil daftar kata terlarang dari database (tabel forbidden_words)
    $forbiddenResult = mysqli_query($conn, "SELECT word FROM forbidden_words");
    $forbiddenWords = [];
    while ($row = mysqli_fetch_assoc($forbiddenResult)) {
        $forbiddenWords[] = trim($row['word']);
    }

    // Logika Filtering Forbidden Words (Sensor dengan bintang ***)
    foreach ($forbiddenWords as $badWord) {
        if (!empty($badWord)) {
            // Gunakan regex untuk pencocokan case-insensitive yang akurat
            $pattern = '/' . preg_quote($badWord, '/') . '/ui';
            $replacement = str_repeat('*', mb_strlen($badWord));
            $filtered = preg_replace($pattern, $replacement, $comment);
            if ($filtered !== null) {
                $comment = $filtered;
            }
        }
    }

    // Simpan komentar yang sudah difilter ke database
    $query = "INSERT INTO comments (letter_id, user_id, comment, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "iis", $letterId, $userId, $comment);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = 'Comment successfully sent.';
        } else {
            $_SESSION['error'] = 'Failed to send comment.';
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error'] = 'There was an error in the system. Please try again later.';
    }

    mysqli_close($conn);

    // Redirect kembali ke halaman detail surat
    header('Location: /capsule-letter/resources/views/detail-letter.php?id=' . $letterId);
    exit;
} else {
    header('Location: /capsule-letter/resources/views/home.php');
    exit;
}
