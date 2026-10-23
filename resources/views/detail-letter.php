<?php
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once $_SERVER['DOCUMENT_ROOT']
    . '/capsule-letter/config/app.php';

// 1. Pastikan user sudah login.
if (
    !isset($_SESSION['logged_in']) ||
    $_SESSION['logged_in'] !== true
) {
    header('Location: ' . $baseUrl
        . '/resources/views/auth/login.php');
    exit;
}

$userId = (int) ($_SESSION['user_id'] ?? 0);

// 2. Validasi ID surat dari URL.
$letterId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if ($userId <= 0 || !$letterId || $letterId <= 0) {
    header('Location: ' . $baseUrl
        . '/resources/views/my-letter.php');
    exit;
}

// 3. Koneksi database dan ambil surat milik user.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = require $_SERVER['DOCUMENT_ROOT']
        . '/capsule-letter/config/database.php';

    mysqli_set_charset($conn, 'utf8mb4');
    mysqli_query($conn, "SET time_zone = '+07:00'");

    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, title, content, attachment,
                unlock_date, created_at, is_public
         FROM letters
         WHERE id = ? AND user_id = ?
         LIMIT 1"
    );

    mysqli_stmt_bind_param($stmt, 'ii', $letterId, $userId);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $letter = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

} catch (Throwable $error) {
    error_log('Detail Letter: ' . $error->getMessage());

    http_response_code(500);
    exit('The letter could not be loaded. Please try again later.');
}

// 4. Pastikan surat ditemukan.
if (!$letter) {
    http_response_code(404);
    exit('Letter not found.');
}

// 5. Periksa apakah surat sudah boleh dibuka.
$timezone = new DateTimeZone('Asia/Jakarta');
$now = new DateTimeImmutable('now', $timezone);

$unlockDate = new DateTimeImmutable(
    $letter['unlock_date'],
    $timezone
);

$isOpen = $now >= $unlockDate;

if (isset($_GET['download']) && $_GET['download'] === '1') {

    // Surat harus sudah melewati waktu pembukaan.
    if (!$isOpen) {
        http_response_code(403);
        exit('This letter is still locked.');
    }

    $attachment = $letter['attachment'] ?? '';

    if (
        !is_string($attachment) ||
        $attachment === '' ||
        basename($attachment) !== $attachment
    ) {
        http_response_code(404);
        exit('Attachment not found.');
    }

    $uploadDirectory = realpath(
        $_SERVER['DOCUMENT_ROOT']
        . '/capsule-letter/resources/uploads'
    );

    if ($uploadDirectory === false) {
        http_response_code(404);
        exit('Upload directory not found.');
    }

    $filePath = realpath(
        $uploadDirectory . DIRECTORY_SEPARATOR . $attachment
    );

    if (
        $filePath === false ||
        dirname($filePath) !== $uploadDirectory ||
        !is_file($filePath) ||
        !is_readable($filePath)
    ) {
        http_response_code(404);
        exit('Attachment not found.');
    }

    $extension = strtolower(pathinfo($attachment, PATHINFO_EXTENSION));

    $allowedExtensions = [
        'jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'
    ];

    if (!in_array($extension, $allowedExtensions, true)) {
        http_response_code(415);
        exit('Unsupported attachment type.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($filePath);

    $allowedMimeTypes = [
        'jpg'  => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png'  => ['image/png'],
        'pdf'  => ['application/pdf'],
        'doc'  => ['application/msword'],
        'docx' => [
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ]
    ];

    if (
        $mimeType === false ||
        !in_array($mimeType, $allowedMimeTypes[$extension], true)
    ) {
        http_response_code(415);
        exit('Invalid attachment type.');
    }

    // Gambar dan PDF bisa dibuka di browser.
    // Dokumen Word akan diunduh.
    $disposition = in_array(
        $extension,
        ['jpg', 'jpeg', 'png', 'pdf'],
        true
    ) ? 'inline' : 'attachment';

    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    header('Content-Type: ' . $mimeType);
    header('X-Content-Type-Options: nosniff');
    header('Content-Length: ' . filesize($filePath));
    header(
        'Content-Disposition: ' . $disposition
        . '; filename="' . $attachment . '"'
    );
    header('Cache-Control: private, no-store');

    readfile($filePath);
    exit;
}

// 6. Helper untuk menampilkan teks secara aman.
$escape = static function ($value): string {
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
};


$sanitizeLetterHtml = static function (string $html): string {
    $allowedTags = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u',
        'ul', 'ol', 'li', 'blockquote'
    ];

    $previousLibxml = libxml_use_internal_errors(true);

    $dom = new DOMDocument('1.0', 'UTF-8');

    $dom->loadHTML(
        '<?xml encoding="UTF-8"><div id="letter-content">'
        . $html
        . '</div>',
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    );

    $xpath = new DOMXPath($dom);
    $root = $xpath->query('//*[@id="letter-content"]')->item(0);

    $cleanNode = function (DOMNode $node) use (
        &$cleanNode,
        $allowedTags,
        $dom
    ): void {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMElement) {
                $tag = strtolower($child->tagName);

                if (!in_array($tag, $allowedTags, true)) {
                    $node->removeChild($child);
                    continue;
                }

                // Hanya izinkan atribut style text-align
                // dengan nilai yang telah ditentukan.
                $style = $child->getAttribute('style');

                foreach (iterator_to_array($child->attributes) as $attr) {
                    $child->removeAttribute($attr->name);
                }

                if (preg_match(
                    '/^\s*text-align\s*:\s*(left|right|center|justify)\s*;?\s*$/i',
                    $style,
                    $match
                )) {
                    $child->setAttribute(
                        'style',
                        'text-align: ' . strtolower($match[1])
                    );
                }

                $cleanNode($child);
            } elseif (!(
                $child instanceof DOMText
            ) && !(
                $child instanceof DOMComment
            )) {
                $node->removeChild($child);
            }
        }
    };

    if ($root !== null) {
        $cleanNode($root);

        $output = '';
        foreach ($root->childNodes as $child) {
            $output .= $dom->saveHTML($child);
        }
    } else {
        $output = '';
    }

    libxml_clear_errors();
    libxml_use_internal_errors($previousLibxml);

    return $output;
};


?>


<!DOCTYPE html>
<html lang="id">
<head>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/resources/includes/head.php'; ?>
    <title>Letter Details | Capsule Letter</title>
</head>

<body>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/resources/includes/sidebar.php'; ?>

    <div class="main-content">
        <header class="p-4 border-bottom d-flex justify-content-between align-items-center bg-white">
            <span class="text-uppercase text-secondary fw-semibold"
                  style="font-size: 12px; letter-spacing: 1.5px;">
                LETTER DETAILS
            </span>

            <?php include $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/resources/includes/profile-badge.php'; ?>
        </header>

        <main class="container-fluid p-4 p-md-5">
            <div class="row justify-content-center">
                <div class="col-12 col-xl-9">

                    <a href="<?= $escape($baseUrl . '/resources/views/my-letter.php') ?>"
                       class="btn btn-outline-dark mb-4">
                        <i class="bi bi-arrow-left me-2"></i>
                        Back to My Letters
                    </a>

                    <article class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-4 p-md-5">

                            <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
                                <div>
                                    <span class="badge text-bg-light mb-2">
                                        Letter Details
                                    </span>

                                    <h1 class="h3 fw-bold text-break">
                                        <?= $escape($letter['title']) ?>
                                    </h1>
                                </div>

                                <span class="badge <?= $isOpen ? 'text-bg-success' : 'text-bg-warning' ?>">
                                    <?= $isOpen ? 'Unlocked' : 'Locked' ?>
                                </span>
                            </div>

                            <hr>

                            <div class="row g-3 mb-4">
                                <div class="col-sm-6">
                                    <div class="p-3 bg-light rounded-3 h-100">
                                        <small class="text-secondary d-block mb-1">
                                            Created
                                        </small>
                                        <strong>
                                            <?= $escape(
                                                (new DateTimeImmutable(
                                                    $letter['created_at'],
                                                    $timezone
                                                ))->format('d M Y, H:i')
                                            ) ?>
                                        </strong>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="p-3 bg-light rounded-3 h-100">
                                        <small class="text-secondary d-block mb-1">
                                            Unlock Date
                                        </small>
                                        <strong>
                                            <?= $escape(
                                                $unlockDate->format('d M Y, H:i')
                                            ) ?>
                                        </strong>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="p-3 bg-light rounded-3">
                                        <small class="text-secondary d-block mb-1">
                                            Visibility
                                        </small>
                                        <strong>
                                            <?= (int) $letter['is_public'] === 1
                                                ? 'Public'
                                                : 'Private' ?>
                                        </strong>
                                    </div>
                                </div>
                            </div>

                            <h2 class="h5 fw-bold mb-3">Letter Content</h2>

                            <?php if ($isOpen): ?>
                                <div class="border rounded-3 p-4"
                                     style="white-space: pre-wrap; overflow-wrap: anywhere;"><?= $sanitizeLetterHtml($letter['content']) ?></div>
                            <?php else: ?>
                                <div class="border rounded-3 p-4 text-center bg-light">
                                    <i class="bi bi-lock fs-1 text-secondary"></i>

                                    <h3 class="h5 mt-3">
                                        This letter is still locked
                                    </h3>

                                    <p class="text-secondary mb-0">
                                        You can read its contents after the unlock date.
                                    </p>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($letter['attachment'])): ?>
                                <div class="mt-4">
                                    <h2 class="h5 fw-bold mb-3">Attachment</h2>

                                    <?php if ($isOpen): ?>
                                        <a
                                            href="<?= $escape(
                                                $baseUrl
                                                . '/resources/views/detail-letter.php?id='
                                                . (int) $letter['id']
                                                . '&download=1'
                                            ) ?>"
                                            class="btn btn-outline-dark"
                                            target="_blank"
                                            rel="noopener noreferrer">

                                            <i class="bi bi-paperclip me-2"></i>
                                            Open Attachment
                                        </a>
                                    <?php else: ?>
                                        <p class="text-secondary mb-0">
                                            <i class="bi bi-lock me-2"></i>
                                            Attachment is locked until the unlock date.
                                        </p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                        </div>
                    </article>

                </div>
            </div>
        </main>

        <?php include $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/resources/includes/footer.php'; ?>
    </div>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/resources/includes/scripts.php'; ?>
</body>
</html>
