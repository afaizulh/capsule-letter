
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once $_SERVER['DOCUMENT_ROOT']
    . '/capsule-letter/config/app.php';

$myLettersUrl = $baseUrl . '/resources/views/my-letter.php';

function updateRedirect(string $type, string $message): void
{
    $_SESSION[$type === 'success' ? 'update_success' : 'update_error']
        = $message;

    global $myLettersUrl;

    header('Location: ' . $myLettersUrl);
    exit;
}

// 1. Authentication
if (
    !isset($_SESSION['logged_in']) ||
    $_SESSION['logged_in'] !== true
) {
    header('Location: ' . $baseUrl
        . '/resources/views/auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $myLettersUrl);
    exit;
}

// 2. CSRF validation
$sessionToken = $_SESSION['csrf_token'] ?? '';
$postToken = $_POST['csrf_token'] ?? '';

if (
    !is_string($sessionToken) ||
    $sessionToken === '' ||
    !is_string($postToken) ||
    !hash_equals($sessionToken, $postToken)
) {
    updateRedirect(
        'error',
        'Session verification failed. Please reload the page and try again.'
    );
}

$userId = (int) ($_SESSION['user_id'] ?? 0);
$letterId = filter_var(
    $_POST['id'] ?? '',
    FILTER_VALIDATE_INT
);

if ($userId <= 0 || !$letterId || $letterId <= 0) {
    updateRedirect('error', 'Invalid letter request.');
}

// 3. Validate form data
$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');
$unlockDate = trim($_POST['unlock_date'] ?? '');
$categoryRaw = $_POST['category_id'] ?? '';
$visibilityRaw = $_POST['is_public'] ?? '';

if ($title === '' || mb_strlen($title) > 255) {
    updateRedirect(
        'error',
        'Title is required and must not exceed 255 characters.'
    );
}

if ($content === '' || trim(strip_tags($content)) === '') {
    updateRedirect('error', 'Letter content is required.');
}

$categoryId = null;

if ($categoryRaw !== '') {
    $categoryId = filter_var(
        $categoryRaw,
        FILTER_VALIDATE_INT
    );

    if ($categoryId === false || $categoryId <= 0) {
        updateRedirect('error', 'Invalid category.');
    }
}

$isPublic = filter_var(
    $visibilityRaw,
    FILTER_VALIDATE_INT
);

if ($isPublic !== 0 && $isPublic !== 1) {
    updateRedirect(
        'error',
        'Please select Private or Public.'
    );
}

// Validate date format YYYY-MM-DD.
$timezone = new DateTimeZone('Asia/Jakarta');

$date = DateTimeImmutable::createFromFormat(
    '!Y-m-d',
    $unlockDate,
    $timezone
);

$dateErrors = DateTimeImmutable::getLastErrors();

if (
    !$date ||
    $date->format('Y-m-d') !== $unlockDate ||
    (
        $dateErrors !== false &&
        (
            $dateErrors['warning_count'] > 0 ||
            $dateErrors['error_count'] > 0
        )
    )
) {
    updateRedirect('error', 'Invalid opening date.');
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$conn = null;
$newFilePath = null;

try {
    // 4. Database connection
    $conn = require $_SERVER['DOCUMENT_ROOT']
        . '/capsule-letter/config/database.php';

    mysqli_set_charset($conn, 'utf8mb4');
    mysqli_query($conn, "SET time_zone = '+07:00'");

    // 5. Get the letter and verify ownership.
    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, category_id, attachment, created_at
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
        updateRedirect(
            'error',
            'Letter not found or you do not have permission to update it.'
        );
    }

    // 6. Enforce the original 3-day deadline.
    $now = new DateTimeImmutable('now', $timezone);

    $createdAt = new DateTimeImmutable(
        $letter['created_at'],
        $timezone
    );

    if ($now >= $createdAt->modify('+3 days')) {
        updateRedirect(
            'error',
            'The editing period has expired. This letter can no longer be updated.'
        );
    }

    // 7. Validate the selected category.
    if ($categoryId !== null) {
        $stmt = mysqli_prepare(
            $conn,
            'SELECT id FROM categories WHERE id = ? LIMIT 1'
        );

        mysqli_stmt_bind_param($stmt, 'i', $categoryId);
        mysqli_stmt_execute($stmt);

        $categoryResult = mysqli_stmt_get_result($stmt);
        $categoryExists = mysqli_fetch_assoc($categoryResult);

        mysqli_stmt_close($stmt);

        if (!$categoryExists) {
            updateRedirect('error', 'Selected category does not exist.');
        }
    }

    // 8. Preserve the existing attachment unless replaced.
    $attachmentName = $letter['attachment'];

    if (
        isset($_FILES['attachment']) &&
        $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE
    ) {
        $file = $_FILES['attachment'];

        if (
            $file['error'] !== UPLOAD_ERR_OK ||
            !is_uploaded_file($file['tmp_name'])
        ) {
            updateRedirect('error', 'Attachment upload failed.');
        }

        if (
            $file['size'] <= 0 ||
            $file['size'] > 2 * 1024 * 1024
        ) {
            updateRedirect(
                'error',
                'Attachment must not exceed 2 MB.'
            );
        }

        $extension = strtolower(
            pathinfo($file['name'], PATHINFO_EXTENSION)
        );

        $allowedExtensions = [
            'jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'
        ];

        if (!in_array($extension, $allowedExtensions, true)) {
            updateRedirect(
                'error',
                'Unsupported attachment format.'
            );
        }

        // Check MIME type on the server as well.
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);

        $allowedMimes = [
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'pdf' => ['application/pdf'],
            'doc' => [
                'application/msword',
                'application/x-ole-storage',
                'application/CDFV2'
            ],
            'docx' => [
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/zip',
                'application/x-zip'
            ]
        ];

        if (!in_array($mime, $allowedMimes[$extension], true)) {
            updateRedirect(
                'error',
                'File content does not match its extension.'
            );
        }

        $uploadDir = $_SERVER['DOCUMENT_ROOT']
            . '/capsule-letter/resources/uploads/';

        if (
            !is_dir($uploadDir) &&
            !mkdir($uploadDir, 0755, true) &&
            !is_dir($uploadDir)
        ) {
            throw new RuntimeException('Upload directory unavailable.');
        }

        $newFileName = bin2hex(random_bytes(16))
            . '.' . $extension;

        $newFilePath = $uploadDir . $newFileName;

        if (!move_uploaded_file($file['tmp_name'], $newFilePath)) {
            $newFilePath = null;

            updateRedirect(
                'error',
                'Unable to save the new attachment.'
            );
        }

        $attachmentName = $newFileName;
    }

    // 9. Update editable columns only.
    // created_at and opened_at remain unchanged.
    $stmt = mysqli_prepare(
        $conn,
        "UPDATE letters
         SET category_id = ?,
             title = ?,
             content = ?,
             attachment = ?,
             unlock_date = ?,
             is_public = ?
         WHERE id = ? AND user_id = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        'issssiii',
        $categoryId,
        $title,
        $content,
        $attachmentName,
        $unlockDate,
        $isPublic,
        $letterId,
        $userId
    );

    mysqli_stmt_execute($stmt);
    $affectedRows = mysqli_stmt_affected_rows($stmt);

    mysqli_stmt_close($stmt);

    if ($affectedRows < 0) {
        throw new RuntimeException('Database update failed.');
    }

    // 10. Remove the old file only after successful database update.
    $oldAttachment = $letter['attachment'];

    if (
        $newFilePath !== null &&
        !empty($oldAttachment) &&
        $oldAttachment !== $attachmentName
    ) {
        $stmt = mysqli_prepare(
            $conn,
            'SELECT COUNT(*) AS total FROM letters WHERE attachment = ?'
        );

        mysqli_stmt_bind_param($stmt, 's', $oldAttachment);
        mysqli_stmt_execute($stmt);

        $countResult = mysqli_stmt_get_result($stmt);
        $countRow = mysqli_fetch_assoc($countResult);

        mysqli_stmt_close($stmt);

        if ((int) $countRow['total'] === 0) {
            $oldPath = $_SERVER['DOCUMENT_ROOT']
                . '/capsule-letter/resources/uploads/'
                . basename($oldAttachment);

            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }
    }

    mysqli_close($conn);
    $conn = null;

    $_SESSION['update_success'] =
        'Letter updated successfully! Your changes have been saved.';

    header('Location: ' . $myLettersUrl);
    exit;

} catch (Throwable $error) {
    error_log('Process Update Letter: ' . $error->getMessage());

    // Clean up the new file if saving fails.
    if ($newFilePath !== null && is_file($newFilePath)) {
        @unlink($newFilePath);
    }

    if ($conn instanceof mysqli) {
        mysqli_close($conn);
    }

    updateRedirect(
        'error',
        'Unable to update the letter. Please check your data and try again.'
    );
}
