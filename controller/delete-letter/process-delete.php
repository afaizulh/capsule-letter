
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once $_SERVER['DOCUMENT_ROOT']
    . '/capsule-letter/config/app.php';

$myLettersUrl = $baseUrl . '/resources/views/my-letter.php';

function deleteRedirect(string $type, string $message): void
{
    $_SESSION[$type === 'success' ? 'delete_success' : 'delete_error']
        = $message;

    global $myLettersUrl;

    header('Location: ' . $myLettersUrl);
    exit;
}

// 1. Pastikan user sudah login.
if (
    !isset($_SESSION['logged_in']) ||
    $_SESSION['logged_in'] !== true
) {
    header('Location: ' . $baseUrl
        . '/resources/views/auth/login.php');
    exit;
}

// 2. Hanya terima request POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $myLettersUrl);
    exit;
}

// 3. Validasi CSRF token.
$sessionToken = $_SESSION['csrf_token'] ?? '';
$postToken = $_POST['csrf_token'] ?? '';

if (
    !is_string($sessionToken) ||
    $sessionToken === '' ||
    !is_string($postToken) ||
    !hash_equals($sessionToken, $postToken)
) {
    deleteRedirect('error', 'Invalid request. Please try again.');
}

$userId = (int) ($_SESSION['user_id'] ?? 0);
$letterId = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);

if ($userId <= 0 || !$letterId || $letterId <= 0) {
    deleteRedirect('error', 'Invalid letter request.');
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$conn = null;

try {
    // 4. Sambungkan ke database.
    $conn = require $_SERVER['DOCUMENT_ROOT']
        . '/capsule-letter/config/database.php';

    mysqli_set_charset($conn, 'utf8mb4');
    mysqli_query($conn, "SET time_zone = '+07:00'");

    // 5. Ambil surat milik user yang sedang login.
    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, created_at, attachment
        FROM letters
        WHERE id = ? AND user_id = ?
        LIMIT 1"
    );

    mysqli_stmt_bind_param($stmt, 'ii', $letterId, $userId);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $letter = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    if (!$letter) {
        deleteRedirect(
            'error',
            'Letter not found or you do not have permission to delete it.'
        );
    }

    // 6. Periksa batas waktu 7 hari.
    $timezone = new DateTimeZone('Asia/Jakarta');
    $now = new DateTimeImmutable('now', $timezone);

    $createdAt = new DateTimeImmutable(
        $letter['created_at'],
        $timezone
    );

    if ($now >= $createdAt->modify('+7 days')) {
        deleteRedirect(
            'error',
            'The deletion period has expired. This letter can no longer be deleted.'
        );
    }

    
    // 7. Hapus surat dari database.
    $attachment = $letter['attachment'] ?? null;

    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM letters
         WHERE id = ? AND user_id = ?"
    );

    mysqli_stmt_bind_param($stmt, 'ii', $letterId, $userId);
    mysqli_stmt_execute($stmt);

    $deletedRows = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);

    if ($deletedRows !== 1) {
        throw new RuntimeException('Letter was not deleted.');
    }

    // 8. Hapus file lampiran jika ada.
    if (
        is_string($attachment) &&
        $attachment !== '' &&
        basename($attachment) === $attachment
    ) {
        $uploadDirectory = realpath(
            $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/resources/uploads'
        );

        if ($uploadDirectory !== false) {
            $filePath = $uploadDirectory
                . DIRECTORY_SEPARATOR
                . $attachment;

            $realFilePath = realpath($filePath);

            if (
                $realFilePath !== false &&
                dirname($realFilePath) === $uploadDirectory &&
                is_file($realFilePath)
            ) {
                // Pastikan file tidak dipakai surat lain.
                $checkStmt = mysqli_prepare(
                    $conn,
                    "SELECT COUNT(*) AS total
                     FROM letters
                     WHERE attachment = ?"
                );

                mysqli_stmt_bind_param(
                    $checkStmt,
                    's',
                    $attachment
                );

                mysqli_stmt_execute($checkStmt);

                $checkResult = mysqli_stmt_get_result($checkStmt);
                $checkRow = mysqli_fetch_assoc($checkResult);

                mysqli_stmt_close($checkStmt);

                if ((int) $checkRow['total'] === 0) {
                    if (!unlink($realFilePath)) {
                        error_log(
                            'Unable to delete attachment: ' . $attachment
                        );
                    }
                }
            }
        }
    }

    mysqli_close($conn);
    $conn = null;

    $_SESSION['delete_success'] =
        'Letter deleted successfully.';

    header('Location: ' . $myLettersUrl);
    exit;

} catch (Throwable $error) {
    error_log('Process Delete Letter: ' . $error->getMessage());

    if ($conn instanceof mysqli) {
        mysqli_close($conn);
    }

    deleteRedirect(
        'error',
        'Unable to delete the letter. Please try again.'
    );
}
